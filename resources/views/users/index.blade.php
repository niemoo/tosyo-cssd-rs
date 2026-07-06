<x-layouts.app title="Pengguna">

    <x-slot name="pageHeader">Pengguna</x-slot>
    <x-slot name="pageSubHeader">Kelola seluruh pengguna sistem</x-slot>
    <x-slot name="pageActions">
        @can('users.create')
            <a href="{{ route('users.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pengguna
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Pengguna" :value="$stats['total']" color="teal"
            icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v2h5M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
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
                    placeholder="Cari nama, username, atau telepon..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            <select name="role"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

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

            @if (request()->hasAny(['search', 'status', 'role', 'show_deleted']))
                <a href="{{ route('users.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $users->total() }}</span> pengguna
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="username" label="Username" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Role
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Rumah Sakit
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="last_login_at" label="Login Terakhir" :current-sort="$sortBy"
                                :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="is_active" label="Status" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="relative transition hover:bg-gray-50/50 {{ $user->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-400 text-[10px] font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                        @if ($user->phone)
                                            <div class="text-xs text-gray-400">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if ($user->trashed())
                                    <span
                                        class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                        Dihapus {{ $user->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-500">
                                    {{ $user->username }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($user->roles->isNotEmpty())
                                    <x-badge color="teal">{{ $user->roles->first()->name }}</x-badge>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->hospitals->take(2) as $hospital)
                                        <x-badge color="blue">{{ $hospital->code }}</x-badge>
                                    @empty
                                        <span class="text-xs text-gray-400">—</span>
                                    @endforelse
                                    @if ($user->hospitals->count() > 2)
                                        <x-badge color="gray">+{{ $user->hospitals->count() - 2 }}</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center text-xs text-gray-500">
                                {{ $user->last_login_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($user->trashed())
                                    <x-badge color="red" dot>Dihapus</x-badge>
                                @else
                                    <x-badge :color="$user->is_active ? 'green' : 'red'" dot>
                                        {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </x-badge>
                                @endif
                            </td>

                            {{-- Kebab Menu --}}
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @if ($user->trashed())
                                        @can('users.delete')
                                            <form method="POST" action="{{ route('users.restore', $user->id) }}">
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
                                        @can('users.view')
                                            <a href="{{ route('users.show', $user) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                        @endcan

                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            @if ($user->id !== auth()->id())
                                                <button type="button"
                                                    onclick="document.getElementById('confirm-toggle-{{ $user->id }}').showModal()"
                                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium hover:bg-gray-50
                                   {{ $user->is_active ? 'text-orange-600' : 'text-green-600' }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        @if ($user->is_active)
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        @endif
                                                    </svg>
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            @endif
                                        @endcan

                                        @can('users.delete')
                                            @if ($user->id !== auth()->id())
                                                <div class="my-1 border-t border-gray-200"></div>
                                                <button type="button"
                                                    onclick="document.getElementById('confirm-{{ $user->id }}').showModal()"
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
                            <td colspan="7">
                                <x-empty-state title="Belum ada pengguna"
                                    description="Mulai dengan menambahkan pengguna pertama."
                                    action-label="Tambah Pengguna" action-route="users.create"
                                    icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v2h5M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $users->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete Forms & Dialogs --}}
    @foreach ($users as $user)
        @unless ($user->trashed())
            @if ($user->id !== auth()->id())
                @can('users.delete')
                    <form method="POST" action="{{ route('users.destroy', $user) }}" id="form-delete-{{ $user->id }}">
                        @csrf @method('DELETE')
                    </form>
                    <x-modal-confirm :id="'confirm-' . $user->id" type="danger" title="Hapus Pengguna?" :description="$user->name . ' akan dihapus. Data tidak hilang permanen dan masih bisa dipulihkan.'"
                        confirm-text="Ya, Hapus" :form-id="'form-delete-' . $user->id" />
                @endcan

                @can('users.edit')
                    <form method="POST" action="{{ route('users.toggle-active', $user) }}"
                        id="form-toggle-{{ $user->id }}">
                        @csrf @method('PATCH')
                    </form>
                    <x-modal-confirm :id="'confirm-toggle-' . $user->id" type="warning" :title="$user->is_active ? 'Nonaktifkan Pengguna?' : 'Aktifkan Pengguna?'" :description="$user->is_active
                        ? $user->name . ' akan dinonaktifkan dan tidak bisa login.'
                        : $user->name . ' akan diaktifkan kembali.'" :confirm-text="$user->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
                        :form-id="'form-toggle-' . $user->id" />
                @endcan
            @endif
        @endunless
    @endforeach

</x-layouts.app>
