<x-layouts.app title="{{ $role->name }}">

    <x-slot name="backButton">
        <a href="{{ route('roles.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('roles.index') }}" class="hover:text-gray-600">Role & Akses</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $role->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $role->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $role->permissions->count() }} permissions · {{ $role->users->count() }}
        pengguna</x-slot>

    <x-slot name="pageActions">
        @can('roles.edit')
            <a href="{{ route('roles.edit', $role) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Permissions --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Permissions</h3>
                </div>
                <x-permission-table :permissions="$permissions" :actions="$actions" :checked-ids="$role->permissions->pluck('id')->toArray()" :readonly="true" />
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Users --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Pengguna dengan Role Ini</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-400 text-[10px] font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="truncate text-xs text-gray-400">{{ $user->username }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-center text-xs text-gray-400">Belum ada pengguna</div>
                    @endforelse
                </div>
            </div>

            {{-- Audit --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat pada', 'value' => $role->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui pada', 'value' => $role->updated_at->format('d M Y, H:i')]] as $audit)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                            <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
