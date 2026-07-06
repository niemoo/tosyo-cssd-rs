<x-layouts.app title="{{ $consumable->name }}">

    <x-slot name="backButton">
        <a href="{{ route('consumables.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('consumables.index') }}" class="hover:text-gray-600">Consumable</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $consumable->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $consumable->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $consumable->code }} · {{ $consumable->category->name }}</x-slot>

    <x-slot name="pageActions">
        @can('consumables.edit')
            <a href="{{ route('consumables.edit', $consumable) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </x-slot>

    @php $isLow = $consumable->isLowStock(); @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Info --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Consumable</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama', 'value' => $consumable->name], ['label' => 'Kode', 'value' => $consumable->code], ['label' => 'Kategori', 'value' => $consumable->category->name], ['label' => 'Satuan', 'value' => $units[$consumable->unit] ?? $consumable->unit], ['label' => 'Stok Minimum', 'value' => number_format($consumable->minimum_stock)], ['label' => 'Rumah Sakit', 'value' => $consumable->hospital->name]] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-32 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$consumable->is_active ? 'green' : 'red'" dot>
                            {{ $consumable->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Riwayat Movement --}}
            @if ($consumable->movements->count() > 0)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="border-b border-gray-50 px-5 py-3.5">
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Pergerakan Stok</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($consumable->movements as $movement)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="text-xs font-medium text-gray-900">{{ $movement->type ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $movement->created_at->format('d M Y, H:i') }}</div>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-sm font-semibold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <div class="space-y-4">

            {{-- Stok Card --}}
            <div
                class="overflow-hidden rounded-xl border {{ $isLow ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white' }} p-5">
                <h4
                    class="mb-3 text-[11px] font-bold uppercase tracking-wider {{ $isLow ? 'text-amber-600' : 'text-gray-400' }}">
                    Stok Saat Ini
                </h4>
                <div class="text-center">
                    <div class="text-4xl font-bold {{ $isLow ? 'text-amber-600' : 'text-gray-900' }}">
                        {{ number_format($consumable->current_stock) }}
                    </div>
                    <div class="mt-1 text-xs {{ $isLow ? 'text-amber-500' : 'text-gray-400' }}">
                        {{ $units[$consumable->unit] ?? $consumable->unit }}
                    </div>
                    @if ($isLow)
                        <div class="mt-3 rounded-lg border border-amber-200 bg-white px-3 py-2 text-xs text-amber-700">
                            Stok sudah mencapai batas minimum ({{ $consumable->minimum_stock }})
                        </div>
                    @endif
                </div>
            </div>

            {{-- Audit --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $consumable->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $consumable->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $consumable->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $consumable->updated_at->format('d M Y, H:i')]] as $audit)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                            <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
