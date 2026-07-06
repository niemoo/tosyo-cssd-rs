<x-layouts.app title="Batch Sterilisasi">

    <x-slot name="pageHeader">Batch Sterilisasi</x-slot>
    <x-slot name="pageSubHeader">Kelola proses sterilisasi tray CSSD</x-slot>
    <x-slot name="pageActions">
        @can('sterilization-batches.create')
            <a href="{{ route('sterilization-batches.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Batch
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-4 gap-4">
        <x-stat-card label="Total Batch" :value="$stats['total']" color="teal"
            icon="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        <x-stat-card label="Berjalan" :value="$stats['in_progress']" color="blue"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Selesai" :value="$stats['completed']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Gagal" :value="$stats['failed']" color="red"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor batch atau sterilizer..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS</option>
                    @foreach ($userHospitals as $h)
                        <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="status"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Status</option>
                @foreach (\App\Models\SterilizationBatch::STATUSES as $key => $val)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                        {{ $val['label'] }}</option>
                @endforeach
            </select>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" name="show_deleted" value="1" {{ request('show_deleted') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                Tampilkan dihapus
            </label>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'status', 'hospital_id', 'show_deleted']))
                <a href="{{ route('sterilization-batches.index') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $batches->firstItem() }}–{{ $batches->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $batches->total() }}</span> batch
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="batch_number" label="Nomor Batch" :current-sort="$sortBy"
                                :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                RS</th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Sterilizer</th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Tray</th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Parameter</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="started_at" label="Mulai" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="status" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($batches as $batch)
                        @php $statusInfo = \App\Models\SterilizationBatch::STATUSES[$batch->status] ?? ['label' => $batch->status, 'color' => 'gray']; @endphp
                        <tr class="transition hover:bg-gray-50/50 {{ $batch->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600">
                                    {{ $batch->batch_number }}
                                </span>
                                @if ($batch->trashed())
                                    <div class="mt-1 text-[10px] text-red-500">Dihapus
                                        {{ $batch->deleted_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $batch->hospital->name }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                <div class="text-xs font-medium text-gray-900">{{ $batch->sterilizer->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $batch->sterilizer->type }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php
                                    $passed = $batch->items->where('result', 'PASSED')->count();
                                    $failed = $batch->items->where('result', 'FAILED')->count();
                                    $pending = $batch->items->where('result', 'PENDING')->count();
                                    $total = $batch->items->count();
                                @endphp
                                <div class="text-sm font-bold text-gray-900">{{ $total }}</div>
                                @if ($passed > 0 || $failed > 0)
                                    <div class="text-[10px]">
                                        <span class="text-green-600">{{ $passed }} lulus</span>
                                        @if ($failed > 0)
                                            <span class="text-red-500"> · {{ $failed }} gagal</span>
                                        @endif
                                        @if ($pending > 0)
                                            <span class="text-gray-400"> · {{ $pending }} pending</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-[10px] text-gray-500">
                                    @if ($batch->temperature)
                                        <div>{{ $batch->temperature }}°C · {{ $batch->pressure }} bar</div>
                                    @endif
                                    @if ($batch->duration_minutes)
                                        <div>{{ $batch->duration_minutes }} menit</div>
                                    @endif
                                    @if (!$batch->temperature && !$batch->duration_minutes)
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($batch->started_at)
                                    <div class="text-xs text-gray-700">{{ $batch->started_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $batch->started_at->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($batch->trashed())
                                        @can('sterilization-batches.delete')
                                            <form method="POST"
                                                action="{{ route('sterilization-batches.restore', $batch->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-green-600 hover:bg-green-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        @can('sterilization-batches.view')
                                            <a href="{{ route('sterilization-batches.show', $batch) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('sterilization-batches.edit')
                                            @if ($batch->status === \App\Models\SterilizationBatch::STATUS_IN_PROGRESS)
                                                <a href="{{ route('sterilization-batches.edit', $batch) }}"
                                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit Parameter
                                                </a>
                                            @endif
                                        @endcan

                                        @can('sterilization-batches.delete')
                                            @if ($batch->status !== \App\Models\SterilizationBatch::STATUS_IN_PROGRESS)
                                                <div class="my-1 border-t border-gray-200"></div>
                                                <button type="button"
                                                    onclick="document.getElementById('confirm-{{ $batch->id }}').showModal()"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            @endif
                                        @endcan
                                    @endif
                                </x-kebab-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 8 : 7 }}">
                                <x-empty-state title="Belum ada batch sterilisasi"
                                    description="Buat batch untuk memulai proses sterilisasi tray."
                                    action-label="Buat Batch" action-route="sterilization-batches.create"
                                    icon="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $batches->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete dialogs --}}
    @foreach ($batches as $batch)
        @unless ($batch->trashed())
            @if ($batch->status !== \App\Models\SterilizationBatch::STATUS_IN_PROGRESS)
                @can('sterilization-batches.delete')
                    <form method="POST" action="{{ route('sterilization-batches.destroy', $batch) }}"
                        id="form-delete-{{ $batch->id }}">
                        @csrf @method('DELETE')
                    </form>
                    <x-modal-confirm :id="'confirm-' . $batch->id" type="danger" title="Hapus Batch?" :description="'Batch ' . $batch->batch_number . ' akan dihapus.'"
                        confirm-text="Ya, Hapus" :form-id="'form-delete-' . $batch->id" />
                @endcan
            @endif
        @endunless
    @endforeach

</x-layouts.app>
