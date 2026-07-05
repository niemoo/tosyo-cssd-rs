<x-layouts.app title="Role & Akses">

    <x-slot name="pageHeader">Role & Akses</x-slot>
    <x-slot name="pageSubHeader">Kelola role dan hak akses pengguna</x-slot>
    <x-slot name="pageActions">
        @can('roles.create')
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Role
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Role" :value="$stats['total']" color="teal"
            icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        <x-stat-card label="Total Permission" :value="$stats['permissions']" color="purple"
            icon="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        <x-stat-card label="Total Pengguna" :value="$stats['users']" color="blue"
            icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v2h5M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $roles->firstItem() }}–{{ $roles->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $roles->total() }}</span> role
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="name" label="Nama Role" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Permissions
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Pengguna
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="created_at" label="Dibuat" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($roles as $role)
                        <tr class="relative transition hover:bg-gray-50/50">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-purple-100">
                                        <svg class="h-3.5 w-3.5 text-purple-600" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold text-gray-900">{{ $role->permissions_count }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold text-gray-900">{{ $role->users_count }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-500">
                                {{ $role->created_at->format('d M Y') }}
                            </td>

                            {{-- Kebab Menu --}}
                            <td class="px-5 py-3.5">
                                <x-kebab-menu>
                                    @can('roles.view')
                                        <a href="{{ route('roles.show', $role) }}"
                                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat Detail
                                        </a>
                                    @endcan

                                    @can('roles.edit')
                                        <a href="{{ route('roles.edit', $role) }}"
                                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan

                                    @can('roles.delete')
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <button type="button"
                                            onclick="document.getElementById('confirm-{{ $role->id }}').showModal()"
                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    @endcan
                                </x-kebab-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Belum ada role"
                                    description="Tambahkan role untuk mengatur hak akses pengguna."
                                    action-label="Tambah Role" action-route="roles.create"
                                    icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $roles->links('components.pagination') }}
            </div>
        @endif
    </div>

    {{-- Delete Forms & Dialogs --}}
    @foreach ($roles as $role)
        @can('roles.delete')
            <form method="POST" action="{{ route('roles.destroy', $role) }}" id="form-delete-{{ $role->id }}">
                @csrf @method('DELETE')
            </form>
            <x-modal-confirm :id="'confirm-' . $role->id" type="danger" title="Hapus Role?" :description="'Role \'' .
                $role->name .
                '\' akan dihapus permanen. Pastikan tidak ada pengguna yang menggunakan role ini.'"
                confirm-text="Ya, Hapus" :form-id="'form-delete-' . $role->id" />
        @endcan
    @endforeach

</x-layouts.app>
