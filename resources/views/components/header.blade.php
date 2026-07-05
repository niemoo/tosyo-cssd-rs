<header
    class="flex h-14 shrink-0 items-center gap-3 border-b border-gray-300 bg-white px-4 sm:h-[60px] sm:gap-4 sm:px-6">

    {{-- Hamburger (mobile only) --}}
    <button @click="sidebarOpen = true"
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-100 text-gray-500 transition hover:bg-gray-50 lg:hidden">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Active Hospital Switcher --}}
    @if (session('active_hospital_id'))
        @php $hospital = \App\Models\Hospital::find(session('active_hospital_id')); @endphp
        @if ($hospital)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-1.5 rounded-full border border-green-200 bg-primary-50 px-2.5 py-1.5 sm:px-3">
                    <svg class="h-3 w-3 shrink-0 text-primary-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <span class="max-w-[120px] truncate text-xs font-semibold text-primary-600 sm:max-w-[200px]">
                        {{ $hospital->name }}
                    </span>
                    <svg class="h-3 w-3 shrink-0 text-primary-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                @php
                    $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
                @endphp
                @if ($userHospitals->count() > 1)
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute left-0 top-full z-50 mt-1 w-56 rounded-xl border border-gray-100 bg-white py-1 shadow-lg"
                        style="display:none;">
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Ganti
                            Rumah Sakit</div>
                        @foreach ($userHospitals as $h)
                            <a href="{{ route('switch-hospital', $h->id) }}"
                                class="flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50
                                      {{ $h->id == session('active_hospital_id') ? 'text-primary-600 font-semibold' : 'text-gray-600' }}">
                                @if ($h->id == session('active_hospital_id'))
                                    <svg class="h-3 w-3 text-primary-500" fill="none" stroke="currentColor"
                                        stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <span class="h-3 w-3"></span>
                                @endif
                                {{ $h->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- Search (hidden on small mobile) --}}
    {{-- <div
        class="hidden flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 sm:flex sm:max-w-xs">
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" />
            <path stroke-linecap="round" d="m21 21-4.35-4.35" />
        </svg>
        <input type="text" placeholder="Cari tray, instrumen..."
            class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
    </div> --}}

    <div class="ml-auto flex items-center gap-2">

        {{-- Search icon mobile --}}
        {{-- <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-100 bg-white sm:hidden">
            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path stroke-linecap="round" d="m21 21-4.35-4.35" />
            </svg>
        </button> --}}

        {{-- Notification Bell --}}
        <div class="relative">
            <button
                class="relative flex h-8 w-8 items-center justify-center rounded-lg border border-gray-100 bg-white transition hover:bg-gray-50">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>
            </button>
        </div>

    </div>

</header>
