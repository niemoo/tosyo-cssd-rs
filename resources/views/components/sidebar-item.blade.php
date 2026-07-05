@props(['route', 'icon' => 'circle', 'badge' => 0, 'badgeColor' => 'red'])

@php
    $isActive = request()->routeIs($route) || request()->routeIs(str_replace('.index', '.*', $route));

    $icons = [
        'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
        'building' => 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10',
        'users' =>
            'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75 M9 7a4 4 0 100 8 4 4 0 000-8z',
        'shield' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
        'unit' => 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z',
        'tool' =>
            'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z',
        'layers' => 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5',
        'cpu' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18',
        'archive' =>
            'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
        'package' => 'M16 16l-4 4-4-4M12 12V3M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3',
        'activity' => 'M22 12h-4l-3 9L9 3l-3 9H2',
    ];
@endphp

<a href="{{ route($route) }}"
    class="group flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-all
          {{ $isActive ? 'bg-primary-600 text-white hover:bg-primary-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">

    <svg class="h-4 w-4 shrink-0 {{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        viewBox="0 0 24 24">
        <path d="{{ $icons[$icon] ?? $icons['grid'] }}" />
    </svg>

    <span class="flex-1">{{ $slot }}</span>

    @if ($badge > 0)
        <span
            class="rounded-full px-1.5 py-0.5 text-[10px] font-bold
                     {{ $badgeColor === 'green' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
            {{ $badge }}
        </span>
    @endif

</a>
