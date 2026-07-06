<x-layouts.app title="Kategori Consumable">

    <x-slot name="pageHeader">Kategori Consumable</x-slot>
    <x-slot name="pageSubHeader">Kelola kategori consumable CSSD</x-slot>
    <x-slot name="pageActions">
        @can('consumable-categories.create')
            <a href="{{ route('consumable-categories.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Kategori" :value="$stats['total']" color="teal"
            icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
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
                    placeholder="Cari nama atau kode..."
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
                <a href="{{ route('consumable-categories.index') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $categories->firstItem() }}–{{ $categories->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $categories->total() }}</span> kategori
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="code" label="Kode" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama Kategori" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Rumah Sakit
                            </th>
                        @endif
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Consumable
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="is_active" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($categories as $category)
                        <tr class="transition hover:bg-gray-50/50 {{ $category->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $category->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                @if ($category->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $category->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $category->hospital->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">{{ $category->hospital->code }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="text-sm font-semibold text-gray-900">{{ $category->consumables_count ?? $category->consumables->count() }}</span>
                                <span class="text-xs text-gray-400"> item</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($category->trashed())
                                    <x-badge color="red" dot>Dihapus</x-badge>
                                @else
                                    <x-badge :color="$category->is_active ? 'green' : 'red'" dot>
                                        {{ $category->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($category->trashed())
                                        @can('consumable-categories.delete')
                                            <form method="POST"
                                                action="{{ route('consumable-categories.restore', $category->id) }}">
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
                                        @can('consumable-categories.view')
                                            <a href="{{ route('consumable-categories.show', $category) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('consumable-categories.edit')
                                            <a href="{{ route('consumable-categories.edit', $category) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            <button type="button"
                                                onclick="document.getElementById('confirm-toggle-{{ $category->id }}').showModal()"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium hover:bg-gray-50
                                                           {{ $category->is_active ? 'text-orange-600' : 'text-green-600' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    @if ($category->is_active)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                                {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        @endcan

                                        @can('consumable-categories.delete')
                                            <div class="my-1 border-t border-gray-200"></div>
                                            <button type="button"
                                                onclick="document.getElementById('confirm-{{ $category->id }}').showModal()"
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
                            <td colspan="{{ $multiHospital ? 6 : 5 }}">
                                <x-empty-state title="Belum ada kategori consumable"
                                    description="Tambahkan kategori untuk mengelompokkan consumable CSSD."
                                    action-label="Tambah Kategori" action-route="consumable-categories.create"
                                    icon="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $categories->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete & Toggle Forms + Dialogs --}}
    @foreach ($categories as $category)
        @unless ($category->trashed())
            @can('consumable-categories.delete')
                <form method="POST" action="{{ route('consumable-categories.destroy', $category) }}"
                    id="form-delete-{{ $category->id }}">
                    @csrf @method('DELETE')
                </form>
                <x-modal-confirm :id="'confirm-' . $category->id" type="danger" title="Hapus Kategori?" :description="'Kategori ' .
                    $category->name .
                    ' akan dihapus. Pastikan tidak ada consumable yang menggunakan kategori ini.'"
                    confirm-text="Ya, Hapus" :form-id="'form-delete-' . $category->id" />
            @endcan

            @can('consumable-categories.edit')
                <form method="POST" action="{{ route('consumable-categories.toggle-active', $category) }}"
                    id="form-toggle-{{ $category->id }}">
                    @csrf @method('PATCH')
                </form>
                <x-modal-confirm :id="'confirm-toggle-' . $category->id" type="warning" :title="$category->is_active ? 'Nonaktifkan Kategori?' : 'Aktifkan Kategori?'" :description="$category->is_active
                    ? $category->name . ' akan dinonaktifkan.'
                    : $category->name . ' akan diaktifkan kembali.'" :confirm-text="$category->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
                    :form-id="'form-toggle-' . $category->id" />
            @endcan
        @endunless
    @endforeach

</x-layouts.app>
