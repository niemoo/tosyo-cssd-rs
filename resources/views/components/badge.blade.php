@props([
    'color' => 'gray',
    'dot' => false,
])

@php
    $colors = [
        'green' => 'bg-green-100 text-green-700',
        'red' => 'bg-red-100 text-red-600',
        'amber' => 'bg-amber-100 text-amber-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'gray' => 'bg-gray-100 text-gray-600',
        'teal' => 'bg-primary-50 text-primary-600',
    ];
@endphp

<span
    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $colors[$color] ?? $colors['gray'] }}">
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    @endif
    {{ $slot }}
</span>
