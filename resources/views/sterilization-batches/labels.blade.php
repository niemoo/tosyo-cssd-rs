<x-layouts.print title="Label — Batch {{ $sterilizationBatch->batch_number }}">
    @forelse ($trays as $tray)
        <x-tray-label :tray="$tray" />
    @empty
        <p class="text-sm text-gray-500">Tidak ada tray yang lulus dalam batch ini.</p>
    @endforelse
</x-layouts.print>
