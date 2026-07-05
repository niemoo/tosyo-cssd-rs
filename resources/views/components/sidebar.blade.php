<aside class="flex h-full w-64 shrink-0 flex-col border-r border-gray-300 bg-white lg:w-60">

    {{-- Logo --}}
    <div class="flex items-center justify-between border-b border-gray-300 px-4 py-4">
        <div class="flex items-center gap-2.5">
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-400">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-extrabold tracking-tight text-gray-900">TosyoCSSD</div>
                <div class="text-[9px] font-normal uppercase tracking-widest text-gray-400">Smart CSSD</div>
            </div>
        </div>
        <button @click="sidebarOpen = false"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-300 lg:hidden">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    @php
        $operasionalActive = request()->routeIs(
            'trays.*',
            'sterilization-batches.*',
            'distribution-requests.*',
            'tray-returns.*',
            'consumable-stocks.*',
        );

        $masterDataActive = request()->routeIs(
            'hospitals.*',
            'users.*',
            'roles.*',
            'units.*',
            'instrument-categories.*',
            'instruments.*',
            'instrument-items.*',
            'tray-templates.*',
            'sterilizers.*',
            'storage-racks.*',
            'consumable-categories.*',
            'consumables.*',
        );
    @endphp

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-2 py-3">

        {{-- Dashboard --}}
        <x-sidebar-item route="dashboard" icon="grid">Dashboard</x-sidebar-item>

        {{-- Operasional --}}
        <div class="mt-3" x-data="{ open: true }">
            <button @click="open = !open" type="button"
                class="group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-4 w-4 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    viewBox="0 0 24 24">
                    <path
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="flex-1 text-left">Operasional</span>
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                    :class="open ? '' : '-rotate-90'" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition.opacity.duration.150ms
                class="mt-0.5 ml-3 space-y-0.5 border-l border-gray-300 pl-3">
                @can('trays.view')
                    <x-sidebar-item route="trays.index" icon="layers">Pelacakan Tray</x-sidebar-item>
                @endcan

                @can('sterilization-batches.view')
                    <x-sidebar-item route="sterilization-batches.index" icon="activity">Batch
                        Sterilisasi</x-sidebar-item>
                @endcan

                @can('distribution-requests.view')
                    <x-sidebar-item route="distribution-requests.index" icon="send">Permintaan
                        Distribusi</x-sidebar-item>
                @endcan

                @can('distribution-requests.view')
                    <x-sidebar-item route="tray-returns.index" icon="rotate-ccw">Pengembalian Tray</x-sidebar-item>
                @endcan

                @can('consumables.view')
                    <x-sidebar-item route="consumable-stocks.index" icon="database">Stok & Pergerakan
                        Consumable</x-sidebar-item>
                @endcan
            </div>
        </div>

        {{-- Data Master --}}
        <div class="mt-1" x-data="{ open: {{ $masterDataActive ? 'true' : 'false' }} }">
            <button @click="open = !open" type="button"
                class="group flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-4 w-4 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    viewBox="0 0 24 24">
                    <path
                        d="M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7m-16 5c0 1.657 3.582 3 8 3s8-1.343 8-3" />
                </svg>
                <span class="flex-1 text-left">Data Master</span>
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                    :class="open ? '' : '-rotate-90'" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition.opacity.duration.150ms
                class="mt-0.5 ml-3 space-y-0.5 border-l border-gray-300 pl-3">

                {{-- Manajemen Sistem --}}
                <div class="px-3 pt-2 pb-0.5 text-[9px] font-bold uppercase tracking-widest text-gray-300">Manajemen
                    Sistem</div>

                @can('hospitals.view')
                    <x-sidebar-item route="hospitals.index" icon="building">Rumah Sakit</x-sidebar-item>
                @endcan

                @can('users.view')
                    <x-sidebar-item route="users.index" icon="users">Pengguna</x-sidebar-item>
                @endcan

                @can('roles.view')
                    <x-sidebar-item route="roles.index" icon="shield">Role & Akses</x-sidebar-item>
                @endcan

                @can('units.view')
                    <x-sidebar-item route="units.index" icon="unit">Unit / Bangsal</x-sidebar-item>
                @endcan

                {{-- Instrumen --}}
                <div class="px-3 pt-2 pb-0.5 text-[9px] font-bold uppercase tracking-widest text-gray-300">Instrumen
                </div>

                @can('instrument-categories.view')
                    <x-sidebar-item route="instrument-categories.index" icon="tag">Kategori Instrumen</x-sidebar-item>
                @endcan

                @can('instruments.view')
                    <x-sidebar-item route="instruments.index" icon="tool">Master Instrumen</x-sidebar-item>
                @endcan

                @can('instrument-items.view')
                    <x-sidebar-item route="instrument-items.index" icon="box">Item Instrumen</x-sidebar-item>
                @endcan

                {{-- Tray & Sterilisasi --}}
                <div class="px-3 pt-2 pb-0.5 text-[9px] font-bold uppercase tracking-widest text-gray-300">Tray &
                    Sterilisasi</div>

                @can('tray-templates.view')
                    <x-sidebar-item route="tray-templates.index" icon="layers">Template Tray</x-sidebar-item>
                @endcan

                @can('sterilizers.view')
                    <x-sidebar-item route="sterilizers.index" icon="cpu">Mesin Sterilizer</x-sidebar-item>
                @endcan

                @can('storage-racks.view')
                    <x-sidebar-item route="storage-racks.index" icon="archive">Rak Penyimpanan</x-sidebar-item>
                @endcan

                {{-- Consumable --}}
                <div class="px-3 pt-2 pb-0.5 text-[9px] font-bold uppercase tracking-widest text-gray-300">Consumable
                </div>

                @can('consumable-categories.view')
                    <x-sidebar-item route="consumable-categories.index" icon="tag">Kategori
                        Consumable</x-sidebar-item>
                @endcan

                @can('consumables.view')
                    <x-sidebar-item route="consumables.index" icon="package">Master Consumable</x-sidebar-item>
                @endcan

            </div>
        </div>

    </nav>

    {{-- User Info --}}
    <div class="border-t border-gray-300 px-3 py-3">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-left transition hover:bg-gray-50">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-400 text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="truncate text-xs font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                    <div class="truncate text-[10px] text-gray-400">
                        {{ auth()->user()->getRoleNames()->first() ?? 'No Role' }}
                    </div>
                </div>
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="absolute bottom-full left-0 mb-1 w-full rounded-xl border border-gray-300 bg-white py-1 shadow-lg"
                style="display:none;">
                <a href="#" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil Saya
                </a>
                <div class="my-1 border-t border-gray-300"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

</aside>
