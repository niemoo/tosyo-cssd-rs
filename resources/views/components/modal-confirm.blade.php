@props([
    'id',
    'title' => 'Konfirmasi',
    'description' => 'Apakah kamu yakin?',
    'type' => 'danger', // danger | warning | info
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'formId' => null,
])

@php
    $types = [
        'danger' => [
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-600',
            'btn' => 'bg-red-500 hover:bg-red-600',
            'icon_path' =>
                'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        ],
        'warning' => [
            'icon_bg' => 'bg-amber-100',
            'icon_color' => 'text-amber-600',
            'btn' => 'bg-amber-500 hover:bg-amber-600',
            'icon_path' =>
                'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'info' => [
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-600',
            'btn' => 'bg-blue-500 hover:bg-blue-600',
            'icon_path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];
    $t = $types[$type] ?? $types['danger'];
@endphp

<dialog id="{{ $id }}" class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
    <div class="p-6">

        {{-- Icon --}}
        <div class="mb-4 flex justify-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full {{ $t['icon_bg'] }}">
                <svg class="h-7 w-7 {{ $t['icon_color'] }}" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon_path'] }}" />
                </svg>
            </div>
        </div>

        {{-- Title --}}
        <h3 class="mb-2 text-center text-base font-bold text-gray-900">{{ $title }}</h3>

        {{-- Description --}}
        <p class="mb-6 text-center text-sm text-gray-500 leading-relaxed">{{ $description }}</p>

        {{-- Slot untuk konten tambahan jika ada --}}
        @if ($slot->isNotEmpty())
            <div class="mb-6">{{ $slot }}</div>
        @endif

        {{-- Actions --}}
        <div class="flex gap-3">
            <button type="button" onclick="document.getElementById('{{ $id }}').close()"
                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                {{ $cancelText }}
            </button>
            <button type="submit" form="{{ $formId ?? $id . '-form' }}"
                class="flex-1 rounded-xl py-2.5 text-sm font-semibold text-white transition {{ $t['btn'] }}">
                {{ $confirmText }}
            </button>
        </div>

    </div>
</dialog>
