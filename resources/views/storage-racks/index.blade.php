<x-layouts.app title="Rak Penyimpanan">

    <x-slot name="pageHeader">Rak Penyimpanan</x-slot>
    <x-slot name="pageSubHeader">Kelola rak penyimpanan tray steril</x-slot>
    <x-slot name="pageActions">
        @can('storage-racks.create')
            <a href="{{ route('storage-racks.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Rak
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Rak" :value="$stats['total']" color="teal"
            icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        <x-stat-card label="Aktif" :value="$stats['active']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Non-aktif" :value="$stats['inactive']" color="red"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, kode, atau lokasi..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS Saya</option>
                    @foreach ($userHospitals as $hospital)
                        <option value="{{ $hospital->id }}"
                            {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                            {{ $hospital->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="status"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-aktif</option>
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

            @if (request()->hasAny(['search', 'status', 'show_deleted', 'hospital_id']))
                <a href="{{ route('storage-racks.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $racks->firstItem() }}–{{ $racks->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $racks->total() }}</span> rak
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="code" label="Kode" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama Rak" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Rumah Sakit
                            </th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Lokasi
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="capacity" label="Kapasitas" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="is_active" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($racks as $rack)
                        <tr class="transition hover:bg-gray-50/50 {{ $rack->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $rack->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $rack->name }}</div>
                                @if ($rack->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $rack->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $rack->hospital->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $rack->hospital->code }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-500">{{ $rack->location_desc ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($rack->capacity)
                                    <span class="text-sm font-semibold text-gray-900">{{ $rack->capacity }}</span>
                                    <span class="text-xs text-gray-400"> tray</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($rack->trashed())
                                    <x-badge color="red" dot>Dihapus</x-badge>
                                @else
                                    <x-badge :color="$rack->is_active ? 'green' : 'red'" dot>
                                        {{ $rack->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($rack->trashed())
                                        @can('storage-racks.delete')
                                            <form method="POST"
                                                action="{{ route('storage-racks.restore', $rack->id) }}">
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
                                        @can('storage-racks.view')
                                            <a href="{{ route('storage-racks.show', $rack) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('storage-racks.edit')
                                            <a href="{{ route('storage-racks.edit', $rack) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            <button type="button"
                                                onclick="document.getElementById('confirm-toggle-{{ $rack->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium hover:bg-gray-50
                                                           {{ $rack->is_active ? 'text-orange-600' : 'text-green-600' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    @if ($rack->is_active)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                                {{ $rack->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endcan

                                        @can('storage-racks.delete')
                                            <div class="my-1 border-t border-gray-200"></div>
                                            <button type="button"
                                                onclick="document.getElementById('confirm-{{ $rack->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        @endcan
                                    @endif
                                </x-kebab-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 7 : 6 }}">
                                <x-empty-state title="Belum ada rak penyimpanan"
                                    description="Tambahkan rak untuk menyimpan tray steril." action-label="Tambah Rak"
                                    action-route="storage-racks.create"
                                    icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($racks->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $racks->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete & Toggle Forms + Dialogs --}}
    @foreach ($racks as $rack)
        @unless ($rack->trashed())
            @can('storage-racks.delete')
                <form method="POST" action="{{ route('storage-racks.destroy', $rack) }}"
                    id="form-delete-{{ $rack->id }}">
                    @csrf @method('DELETE')
                </form>
                <x-modal-confirm :id="'confirm-' . $rack->id" type="danger" title="Hapus Rak?" :description="'Rak ' . $rack->name . ' akan dihapus.'"
                    confirm-text="Ya, Hapus" :form-id="'form-delete-' . $rack->id" />
            @endcan

            @can('storage-racks.edit')
                <form method="POST" action="{{ route('storage-racks.toggle-active', $rack) }}"
                    id="form-toggle-{{ $rack->id }}">
                    @csrf @method('PATCH')
                </form>
                <x-modal-confirm :id="'confirm-toggle-' . $rack->id" type="warning" :title="$rack->is_active ? 'Nonaktifkan Rak?' : 'Aktifkan Rak?'" :description="$rack->is_active
                    ? $rack->name . ' akan dinonaktifkan.'
                    : $rack->name . ' akan diaktifkan kembali.'" :confirm-text="$rack->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
                    :form-id="'form-toggle-' . $rack->id" />
            @endcan
        @endunless
    @endforeach

</x-layouts.app>
