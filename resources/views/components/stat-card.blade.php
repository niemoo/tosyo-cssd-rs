@props([
    'label' => '',
    'value' => '0',
    'trend' => null,
    'trendLabel' => '',
    'color' => 'green',
    'icon' => null,
])

@php
    $colors = [
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'trend' => 'bg-green-100 text-green-700'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'trend' => 'bg-red-100 text-red-700'],
        'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'trend' => 'bg-amber-100 text-amber-700'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'trend' => 'bg-purple-100 text-purple-700'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'trend' => 'bg-blue-100 text-blue-700'],
    ];
    $c = $colors[$color] ?? $colors['green'];
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-green-200 hover:shadow-sm">
    <div class="mb-3 flex items-center justify-between">
        @if ($icon)
            <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $c['bg'] }}">
                <svg class="h-4 w-4 {{ $c['text'] }}" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="{{ $icon }}" />
                </svg>
            </div>
        @endif
        @if ($trend !== null)
            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $c['trend'] }}">
                {{ $trendLabel ?: $trend }}
            </span>
        @endif
    </div>
    <div class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $value }}</div>
    <div class="mt-1 text-xs font-medium text-gray-400">{{ $label }}</div>
</div>
