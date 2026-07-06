<x-layouts.app title="Tray">

    <x-slot name="pageHeader">Tray</x-slot>
    <x-slot name="pageSubHeader">Kelola tray fisik CSSD</x-slot>
    <x-slot name="pageActions">
        @can('trays.create')
            <a href="{{ route('trays.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Tray
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-4 gap-4 lg:grid-cols-7">
        @foreach (\App\Models\Tray::STATUSES as $key => $val)
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $stats[$key] ?? 0 }}</div>
                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $val['label'] }}
                </div>
                <div
                    class="mx-auto mt-1.5 h-1 w-8 rounded-full
                    @if ($val['color'] === 'blue') bg-blue-400
                    @elseif($val['color'] === 'amber') bg-amber-400
                    @elseif($val['color'] === 'purple') bg-purple-400
                    @elseif($val['color'] === 'green') bg-green-400
                    @elseif($val['color'] === 'teal') bg-teal-400
                    @elseif($val['color'] === 'gray') bg-gray-300
                    @elseif($val['color'] === 'red') bg-red-400 @endif">
                </div>
            </div>
        @endforeach
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
                    placeholder="Cari kode, nama, atau barcode..."
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
                @foreach (\App\Models\Tray::STATUSES as $key => $val)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                        {{ $val['label'] }}</option>
                @endforeach
            </select>

            <select name="template_id"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Template</option>
                <option value="free" {{ request('template_id') === 'free' ? 'selected' : '' }}>Tray Bebas</option>
                @foreach ($templates as $t)
                    <option value="{{ $t->id }}" {{ request('template_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}</option>
                @endforeach
            </select>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" name="show_deleted" value="1"
                    {{ request('show_deleted') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                Tampilkan dihapus
            </label>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'status', 'template_id', 'hospital_id', 'show_deleted']))
                <a href="{{ route('trays.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $trays->firstItem() }}–{{ $trays->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $trays->total() }}</span> tray
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="code" label="Kode" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama Tray" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                RS</th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Template</th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Instrumen</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="status" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Lokasi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($trays as $tray)
                        @php
                            $statusInfo = \App\Models\Tray::STATUSES[$tray->status] ?? [
                                'label' => $tray->status,
                                'color' => 'gray',
                            ];
                        @endphp
                        <tr class="transition hover:bg-gray-50/50 {{ $tray->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $tray->code }}
                                </span>
                                @if ($tray->barcode)
                                    <div class="mt-0.5 text-[10px] text-gray-400">{{ $tray->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $tray->name }}</div>
                                @if ($tray->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $tray->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $tray->hospital->name }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                @if ($tray->template)
                                    <div class="text-xs font-medium text-gray-700">{{ $tray->template->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $tray->template->code }}</div>
                                @else
                                    <span class="text-xs italic text-gray-400">Bebas</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="text-sm font-semibold text-gray-900">{{ $tray->items_count ?? $tray->items->count() }}</span>
                                <span class="text-xs text-gray-400"> item</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($tray->currentRack)
                                    <div class="text-xs font-medium text-gray-700">{{ $tray->currentRack->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $tray->currentRack->code }}</div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($tray->trashed())
                                        @can('trays.delete')
                                            <form method="POST" action="{{ route('trays.restore', $tray->id) }}">
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
                                        @can('trays.view')
                                            <a href="{{ route('trays.show', $tray) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('trays.edit')
                                            @if (in_array($tray->status, [\App\Models\Tray::STATUS_ASSEMBLING, \App\Models\Tray::STATUS_NEEDS_REPROCESSING]))
                                                <a href="{{ route('trays.edit', $tray) }}"
                                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endif
                                        @endcan

                                        @can('trays.delete')
                                            @if ($tray->status === \App\Models\Tray::STATUS_ASSEMBLING)
                                                <div class="my-1 border-t border-gray-200"></div>
                                                <button type="button"
                                                    onclick="document.getElementById('confirm-{{ $tray->id }}').showModal()"
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
                                <x-empty-state title="Belum ada tray"
                                    description="Tambahkan tray fisik untuk memulai proses CSSD."
                                    action-label="Tambah Tray" action-route="trays.create"
                                    icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trays->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $trays->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete dialogs --}}
    @foreach ($trays as $tray)
        @unless ($tray->trashed())
            @if ($tray->status === \App\Models\Tray::STATUS_ASSEMBLING)
                @can('trays.delete')
                    <form method="POST" action="{{ route('trays.destroy', $tray) }}" id="form-delete-{{ $tray->id }}">
                        @csrf @method('DELETE')
                    </form>
                    <x-modal-confirm :id="'confirm-' . $tray->id" type="danger" title="Hapus Tray?" :description="'Tray ' . $tray->code . ' akan dihapus.'"
                        confirm-text="Ya, Hapus" :form-id="'form-delete-' . $tray->id" />
                @endcan
            @endif
        @endunless
    @endforeach

</x-layouts.app>
