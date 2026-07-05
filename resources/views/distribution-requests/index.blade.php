<x-layouts.app title="Permintaan Distribusi">

    <x-slot name="pageHeader">Permintaan Distribusi</x-slot>
    <x-slot name="pageSubHeader">Kelola permintaan tray dari unit/bangsal</x-slot>
    <x-slot name="pageActions">
        @can('distribution-requests.create')
            <a href="{{ route('distribution-requests.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Permintaan
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-4 gap-4">
        <x-stat-card label="Total Permintaan" :value="$stats['total']" color="teal"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        <x-stat-card label="Menunggu Approval" :value="$stats['pending_approval']" color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Disetujui" :value="$stats['approved']" color="blue"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Terpenuhi" :value="$stats['fulfilled']" color="green" icon="M5 13l4 4L19 7" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 rounded-xl border border-gray-100 bg-white p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor permintaan atau unit..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS</option>
                    @foreach ($userHospitals as $h)
                        <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="unit_id"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}</option>
                @endforeach
            </select>

            <select name="status"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Status</option>
                @foreach (\App\Models\DistributionRequest::STATUSES as $key => $val)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                        {{ $val['label'] }}</option>
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

            @if (request()->hasAny(['search', 'status', 'hospital_id', 'unit_id', 'show_deleted']))
                <a href="{{ route('distribution-requests.index') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $requests->firstItem() }}–{{ $requests->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $requests->total() }}</span> permintaan
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="request_number" label="No. Permintaan" :current-sort="$sortBy"
                                :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                RS</th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Unit</th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Item</th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Pemohon</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="requested_at" label="Tanggal" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="status" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requests as $req)
                        @php $statusInfo = \App\Models\DistributionRequest::STATUSES[$req->status] ?? ['label' => $req->status, 'color' => 'gray']; @endphp
                        <tr class="transition hover:bg-gray-50/50 {{ $req->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600">
                                    {{ $req->request_number }}
                                </span>
                                @if ($req->trashed())
                                    <div class="mt-1 text-[10px] text-red-500">Dihapus
                                        {{ $req->deleted_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $req->hospital->name }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-medium text-gray-900">{{ $req->unit->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $req->unit->code }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-sm font-bold text-gray-900">{{ $req->items->count() }}</span>
                                <span class="text-xs text-gray-400"> jenis</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-600">{{ $req->requester->name }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-xs text-gray-700">{{ $req->requested_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $req->requested_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($req->trashed())
                                        @can('distribution-requests.delete')
                                            <form method="POST"
                                                action="{{ route('distribution-requests.restore', $req->id) }}">
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
                                        @can('distribution-requests.view')
                                            <a href="{{ route('distribution-requests.show', $req) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('distribution-requests.edit')
                                            @if (in_array($req->status, [
                                                    \App\Models\DistributionRequest::STATUS_DRAFT,
                                                    \App\Models\DistributionRequest::STATUS_REJECTED,
                                                ]))
                                                <a href="{{ route('distribution-requests.edit', $req) }}"
                                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    {{ $req->status === \App\Models\DistributionRequest::STATUS_REJECTED ? 'Revisi' : 'Edit' }}
                                                </a>
                                            @endif
                                        @endcan

                                        @can('distribution-requests.delete')
                                            @if ($req->status === \App\Models\DistributionRequest::STATUS_DRAFT)
                                                <div class="my-1 border-t border-gray-100"></div>
                                                <button type="button"
                                                    onclick="document.getElementById('confirm-{{ $req->id }}').showModal()"
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
                                <x-empty-state title="Belum ada permintaan distribusi"
                                    description="Buat permintaan untuk distribusi tray ke unit."
                                    action-label="Buat Permintaan" action-route="distribution-requests.create"
                                    icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $requests->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete dialogs --}}
    @foreach ($requests as $req)
        @unless ($req->trashed())
            @if ($req->status === \App\Models\DistributionRequest::STATUS_DRAFT)
                @can('distribution-requests.delete')
                    <form method="POST" action="{{ route('distribution-requests.destroy', $req) }}"
                        id="form-delete-{{ $req->id }}">
                        @csrf @method('DELETE')
                    </form>
                    <x-modal-confirm :id="'confirm-' . $req->id" type="danger" title="Hapus Permintaan?" :description="'Permintaan ' . $req->request_number . ' akan dihapus.'"
                        confirm-text="Ya, Hapus" :form-id="'form-delete-' . $req->id" />
                @endcan
            @endif
        @endunless
    @endforeach

</x-layouts.app>
