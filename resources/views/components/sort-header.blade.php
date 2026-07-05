@props(['column', 'label', 'currentSort' => '', 'currentDirection' => 'asc'])

@php
    $isActive = $currentSort === $column;
    $nextDir = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir, 'page' => 1]);
@endphp

<a href="{{ $url }}"
    class="group inline-flex items-center gap-1 transition-colors hover:text-gray-700
          {{ $isActive ? 'text-gray-700' : 'text-gray-400' }}">
    {{ $label }}
    <span style="display:flex; flex-direction:column; gap:2px; line-height:1; margin-left:2px;">
        <svg style="width:8px; height:5px; display:block;"
            class="transition-colors {{ $isActive && $currentDirection === 'asc' ? 'text-primary-500' : 'text-gray-300 group-hover:text-gray-400' }}"
            fill="currentColor" viewBox="0 0 24 16">
            <path d="M12 0l-12 16h24z" />
        </svg>
        <svg style="width:8px; height:5px; display:block;"
            class="transition-colors {{ $isActive && $currentDirection === 'desc' ? 'text-primary-500' : 'text-gray-300 group-hover:text-gray-400' }}"
            fill="currentColor" viewBox="0 0 24 16">
            <path d="M12 16l12-16h-24z" />
        </svg>
    </span>
</a>
