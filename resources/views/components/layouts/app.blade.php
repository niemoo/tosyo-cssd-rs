<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — TosyoCSSD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased">

    <div class="flex h-screen overflow-hidden bg-gray-50" x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">

        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden" x-cloak>
        </div>

        {{-- Sidebar --}}
        {{-- Desktop: selalu tampil via CSS (lg:flex) --}}
        {{-- Mobile: dikontrol Alpine (x-show) --}}
        <div class="fixed inset-y-0 left-0 z-30 lg:static lg:z-auto lg:flex"
            :class="sidebarOpen ? 'flex' : 'hidden lg:flex'">
            @include('components.sidebar')
        </div>

        {{-- Main Content --}}
        <div class="flex flex-1 flex-col overflow-hidden min-w-0">
            @include('components.header')

            <main class="flex-1 overflow-y-auto">
                <div class="px-4 py-4 sm:px-6 sm:py-6">

                    {{-- Page Header --}}
                    @isset($pageHeader)
                        <div class="mb-4 sm:mb-6">

                            {{-- Back Button Row --}}
                            @isset($backButton)
                                <div class="mb-3">
                                    {{ $backButton }}
                                </div>
                            @endisset

                            {{-- Title + Actions Row --}}
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    @isset($breadcrumb)
                                        <nav class="mb-1 flex items-center gap-1.5 text-xs text-gray-400">
                                            {{ $breadcrumb }}
                                        </nav>
                                    @endisset
                                    <h1 class="text-lg font-bold tracking-tight text-gray-900">
                                        {{ $pageHeader }}
                                    </h1>
                                    @isset($pageSubHeader)
                                        <p class="mt-0.5 text-sm text-gray-400">{{ $pageSubHeader }}</p>
                                    @endisset
                                </div>
                                @isset($pageActions)
                                    <div class="flex shrink-0 items-center gap-2">
                                        {{ $pageActions }}
                                    </div>
                                @endisset
                            </div>

                        </div>
                    @endisset

                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3"
                            x-cloak>
                            <svg class="h-4 w-4 shrink-0 text-green-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                            <button @click="show = false" class="ml-auto text-green-400 hover:text-green-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                            class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3"
                            x-cloak>
                            <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
                            <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    {{ $slot }}

                </div>
            </main>
        </div>
    </div>

    <div id="dropdown-portal"></div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
