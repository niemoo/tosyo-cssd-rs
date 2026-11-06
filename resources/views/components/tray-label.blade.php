@props(['tray'])

@php
    $operatorName = optional($tray->lifecycleEvents->firstWhere('event_type', 'STERILIZE_PASS'))->performer?->name
        ?? $tray->assembler?->name
        ?? '-';
@endphp

<div class="print-label mb-6 rounded-xl border-2 border-gray-300 bg-white p-5 print:mb-0 print:rounded-none print:border">
    <div class="flex items-start gap-4">
        <div class="shrink-0">
            {!! $tray->qrCodeSvg(110) !!}
        </div>

        <div class="min-w-0 flex-1">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $tray->hospital->name }}</div>
            <div class="mt-0.5 text-2xl font-extrabold tracking-tight text-gray-900">{{ $tray->code }}</div>
            <div class="truncate text-sm font-medium text-gray-600">
                {{ $tray->name }} · {{ $tray->template?->name ?? 'Tray Bebas' }}
            </div>

            <div class="mt-3 rounded-lg border-2 border-red-500 bg-red-50 px-3 py-2 text-center">
                <div class="text-[10px] font-bold uppercase tracking-wider text-red-600">Kedaluwarsa</div>
                <div class="text-xl font-extrabold text-red-700">
                    {{ $tray->expiry_at?->format('d M Y') ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-1.5 border-t border-gray-200 pt-3 text-xs">
        <div class="flex justify-between gap-2">
            <span class="text-gray-400">Tgl Steril</span>
            <span class="font-semibold text-gray-800">{{ $tray->sterile_at?->format('d M Y, H:i') ?? '-' }}</span>
        </div>
        <div class="flex justify-between gap-2">
            <span class="text-gray-400">No. Load</span>
            <span class="font-semibold text-gray-800">{{ $tray->current_load_number ?? '-' }}</span>
        </div>
        <div class="flex justify-between gap-2">
            <span class="text-gray-400">Operator</span>
            <span class="font-semibold text-gray-800">{{ $operatorName }}</span>
        </div>
        <div class="flex justify-between gap-2">
            <span class="text-gray-400">Jml Instrumen</span>
            <span class="font-semibold text-gray-800">{{ $tray->items->count() }} item</span>
        </div>
    </div>
</div>
