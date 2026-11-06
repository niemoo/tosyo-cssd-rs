@props(['event', 'last' => true])

@php
    $referenceRoute = match ($event->reference_type) {
        \App\Models\SterilizationBatch::class => route('sterilization-batches.show', $event->reference_id),
        \App\Models\DistributionRequest::class => route('distribution-requests.show', $event->reference_id),
        \App\Models\TrayReturn::class => $event->reference
            ? route('distribution-requests.show', $event->reference->distribution_request_id)
            : null,
        default => null,
    };

    $context = $event->context ?? [];
@endphp

<li class="relative flex gap-3 pb-4 last:pb-0">
    <div class="flex flex-col items-center">
        <span @class([
            'mt-1 h-2.5 w-2.5 shrink-0 rounded-full',
            'bg-blue-500' => $event->color === 'blue',
            'bg-amber-500' => $event->color === 'amber',
            'bg-purple-500' => $event->color === 'purple',
            'bg-green-500' => $event->color === 'green',
            'bg-teal-500' => $event->color === 'teal',
            'bg-red-500' => $event->color === 'red',
            'bg-gray-400' => $event->color === 'gray',
        ])></span>
        @unless ($last)
            <span class="mt-1 w-px flex-1 bg-gray-200"></span>
        @endunless
    </div>
    <div class="-mt-0.5 flex-1">
    <div class="text-sm font-semibold text-gray-900">{{ $event->label }}</div>

    @if ($event->from_status)
        <div class="text-[10px] font-medium uppercase tracking-wide text-gray-400">
            {{ \App\Models\Tray::STATUSES[$event->from_status]['label'] ?? $event->from_status }}
            → {{ \App\Models\Tray::STATUSES[$event->to_status]['label'] ?? $event->to_status }}
        </div>
    @endif

    <div class="mt-0.5 text-xs text-gray-400">
        {{ $event->performer?->name ?? 'Sistem' }} · {{ $event->performed_at->format('d M Y, H:i') }}
    </div>

    @if ($event->notes)
        <div class="text-[10px] italic text-gray-400">{{ $event->notes }}</div>
    @endif

    {{-- Chip bukti dari context --}}
    @if ($event->event_type === 'STERILIZE_PASS')
        <div class="mt-1.5 flex flex-wrap gap-1">
            @if (! empty($context['bi_result']))
                @php $bi = \App\Models\SterilizationBatch::BI_RESULTS[$context['bi_result']] ?? ['label' => $context['bi_result'], 'color' => 'gray']; @endphp
                <x-badge :color="$bi['color']">BI: {{ $bi['label'] }}</x-badge>
            @endif
            @if (! empty($context['ci_result']))
                <x-badge :color="$context['ci_result'] === 'PASS' ? 'green' : 'red'">
                    CI: {{ $context['ci_result'] === 'PASS' ? 'Lulus' : 'Gagal' }}
                </x-badge>
            @endif
            @if (! empty($context['expiry_at']))
                <x-badge color="teal">Steril s/d {{ \Illuminate\Support\Carbon::parse($context['expiry_at'])->format('d M Y') }}</x-badge>
            @endif
            @if (! empty($context['load_number']))
                <x-badge color="purple">No. Load {{ $context['load_number'] }}</x-badge>
            @endif
        </div>
    @elseif ($event->event_type === 'STERILIZE_FAIL')
        <div class="mt-1.5 flex flex-wrap gap-1">
            @if (! empty($context['ci_result']))
                <x-badge :color="$context['ci_result'] === 'PASS' ? 'green' : 'red'">
                    CI: {{ $context['ci_result'] === 'PASS' ? 'Lulus' : 'Gagal' }}
                </x-badge>
            @endif
        </div>
        @if (! empty($context['failure_notes']))
            <div class="mt-0.5 text-[10px] italic text-red-500">{{ $context['failure_notes'] }}</div>
        @endif
    @elseif (in_array($event->event_type, ['RETURN', 'RETURN_DAMAGED']))
        <div class="mt-1.5 flex flex-wrap gap-1">
            @if (! empty($context['condition']))
                @php $cond = \App\Models\TrayReturn::CONDITIONS[$context['condition']] ?? ['label' => $context['condition'], 'color' => 'gray']; @endphp
                <x-badge :color="$cond['color']">{{ $cond['label'] }}</x-badge>
            @endif
        </div>
        @if (! empty($context['missing_items']))
            <div class="mt-0.5 text-[10px] text-red-500">Hilang: {{ $context['missing_items'] }}</div>
        @endif
    @endif

    @if ($referenceRoute)
        <a href="{{ $referenceRoute }}"
            class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-0.5 text-[10px] font-medium text-primary-600 transition hover:bg-primary-50">
            Lihat sumber
            <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    @endif
    </div>
</li>
