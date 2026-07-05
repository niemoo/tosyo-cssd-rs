<x-layouts.app title="Sterilizer">

    <x-slot name="pageHeader">Sterilizer</x-slot>
    <x-slot name="pageSubHeader">Kelola mesin sterilizer CSSD</x-slot>
    <x-slot name="pageActions">
        @can('sterilizers.create')
            <a href="{{ route('sterilizers.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Sterilizer
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Sterilizer" :value="$stats['total']" color="teal"
            icon="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        <x-stat-card label="Aktif" :value="$stats['active']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Perlu Maintenance" :value="$stats['maintenance']" color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 bg-white p-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, kode, atau serial..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS Saya</option>
                    @foreach ($userHospitals as $hospital)
                        <option value="{{ $hospital->id }}"
                            {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                            {{ $hospital->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="type"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Tipe</option>
                @foreach ($types as $key => $type)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                        {{ $type['label'] }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-aktif</option>
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

            @if (request()->hasAny(['search', 'status', 'show_deleted', 'hospital_id', 'type']))
                <a href="{{ route('sterilizers.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $sterilizers->firstItem() }}–{{ $sterilizers->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $sterilizers->total() }}</span> sterilizer
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="code" label="Kode" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Rumah Sakit
                            </th>
                        @endif
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="type" label="Tipe" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Kapasitas
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="next_maintenance_at" label="Maintenance" :current-sort="$sortBy"
                                :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="is_active" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($sterilizers as $sterilizer)
                        <tr class="transition hover:bg-gray-50/50 {{ $sterilizer->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $sterilizer->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $sterilizer->name }}</div>
                                @if ($sterilizer->serial_number)
                                    <div class="text-xs text-gray-400">S/N: {{ $sterilizer->serial_number }}</div>
                                @endif
                                @if ($sterilizer->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $sterilizer->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $sterilizer->hospital->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">{{ $sterilizer->hospital->code }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                @php $type = $types[$sterilizer->type] ?? ['label' => $sterilizer->type, 'color' => 'gray']; @endphp
                                <x-badge :color="$type['color']">{{ $type['label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($sterilizer->capacity)
                                    <span
                                        class="text-sm font-semibold text-gray-900">{{ $sterilizer->capacity }}</span>
                                    <span class="text-xs text-gray-400"> tray</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($sterilizer->next_maintenance_at)
                                    @if ($sterilizer->isMaintenanceDue())
                                        <div class="text-xs font-semibold text-red-600">
                                            {{ $sterilizer->next_maintenance_at->format('d M Y') }}
                                        </div>
                                        <x-badge color="red">Segera</x-badge>
                                    @else
                                        <div class="text-xs text-gray-600">
                                            {{ $sterilizer->next_maintenance_at->format('d M Y') }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ $sterilizer->next_maintenance_at->diffForHumans() }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($sterilizer->trashed())
                                    <x-badge color="red" dot>Dihapus</x-badge>
                                @else
                                    <x-badge :color="$sterilizer->is_active ? 'green' : 'red'" dot>
                                        {{ $sterilizer->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($sterilizer->trashed())
                                        @can('sterilizers.delete')
                                            <form method="POST"
                                                action="{{ route('sterilizers.restore', $sterilizer->id) }}">
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
                                        @can('sterilizers.view')
                                            <a href="{{ route('sterilizers.show', $sterilizer) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('sterilizers.edit')
                                            <a href="{{ route('sterilizers.edit', $sterilizer) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            <button type="button"
                                                onclick="document.getElementById('confirm-toggle-{{ $sterilizer->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium hover:bg-gray-50
                                                           {{ $sterilizer->is_active ? 'text-orange-600' : 'text-green-600' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    @if ($sterilizer->is_active)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                                {{ $sterilizer->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endcan

                                        @can('sterilizers.delete')
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <button type="button"
                                                onclick="document.getElementById('confirm-{{ $sterilizer->id }}').showModal()"
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
                            <td colspan="{{ $multiHospital ? 8 : 7 }}">
                                <x-empty-state title="Belum ada sterilizer"
                                    description="Tambahkan mesin sterilizer untuk mulai memproses sterilisasi."
                                    action-label="Tambah Sterilizer" action-route="sterilizers.create"
                                    icon="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sterilizers->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $sterilizers->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete & Toggle Forms + Dialogs --}}
    @foreach ($sterilizers as $sterilizer)
        @unless ($sterilizer->trashed())
            @can('sterilizers.delete')
                <form method="POST" action="{{ route('sterilizers.destroy', $sterilizer) }}"
                    id="form-delete-{{ $sterilizer->id }}">
                    @csrf @method('DELETE')
                </form>
                <x-modal-confirm :id="'confirm-' . $sterilizer->id" type="danger" title="Hapus Sterilizer?" :description="'Sterilizer ' . $sterilizer->name . ' akan dihapus.'"
                    confirm-text="Ya, Hapus" :form-id="'form-delete-' . $sterilizer->id" />
            @endcan

            @can('sterilizers.edit')
                <form method="POST" action="{{ route('sterilizers.toggle-active', $sterilizer) }}"
                    id="form-toggle-{{ $sterilizer->id }}">
                    @csrf @method('PATCH')
                </form>
                <x-modal-confirm :id="'confirm-toggle-' . $sterilizer->id" type="warning" :title="$sterilizer->is_active ? 'Nonaktifkan Sterilizer?' : 'Aktifkan Sterilizer?'" :description="$sterilizer->is_active
                    ? $sterilizer->name . ' akan dinonaktifkan.'
                    : $sterilizer->name . ' akan diaktifkan kembali.'" :confirm-text="$sterilizer->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
                    :form-id="'form-toggle-' . $sterilizer->id" />
            @endcan
        @endunless
    @endforeach

</x-layouts.app>
