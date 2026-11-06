@props(['title', 'hospitalName', 'from' => null, 'to' => null])

<div class="mb-4 border-b-2 border-gray-800 pb-3">
    <div class="text-base font-extrabold uppercase tracking-wide text-gray-900">{{ $hospitalName }}</div>
    <div class="mt-0.5 text-lg font-bold text-gray-900">{{ $title }}</div>
    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-0.5 text-xs text-gray-500">
        @if ($from && $to)
            <span>Periode: {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</span>
        @endif
        <span>Dicetak: {{ now()->format('d M Y, H:i') }}</span>
    </div>
</div>
