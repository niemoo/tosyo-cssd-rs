@props([
    'title' => 'Belum ada data',
    'description' => 'Mulai dengan menambahkan data baru.',
    'icon' => 'M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z',
    'actionLabel' => null,
    'actionRoute' => null,
])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100">
        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </div>
    <h3 class="mb-1 text-sm font-semibold text-gray-900">{{ $title }}</h3>
    <p class="mb-4 text-xs text-gray-400">{{ $description }}</p>
    @if ($actionLabel && $actionRoute)
        <a href="{{ route($actionRoute) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>
