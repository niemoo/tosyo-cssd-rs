<x-layouts.app title="{{ $instrumentItem->code }}">

    <x-slot name="backButton">
        <a href="{{ route('instrument-items.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('instrument-items.index') }}" class="hover:text-gray-600">Item Instrumen</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $instrumentItem->code }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $instrumentItem->code }}</x-slot>
    <x-slot name="pageSubHeader">{{ $instrumentItem->instrument->name }} ·
        {{ $instrumentItem->hospital->name }}</x-slot>

    <x-slot name="pageActions">
        @can('instrument-items.edit')
            <a href="{{ route('instrument-items.edit', $instrumentItem) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Info Item --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Item</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @php $cond = $conditions[$instrumentItem->condition] ?? ['label' => $instrumentItem->condition, 'color' => 'gray']; @endphp
                    @foreach ([['label' => 'Kode Item', 'value' => $instrumentItem->code], ['label' => 'Instrumen', 'value' => $instrumentItem->instrument->name], ['label' => 'Kategori', 'value' => $instrumentItem->instrument->category->name], ['label' => 'Rumah Sakit', 'value' => $instrumentItem->hospital->name], ['label' => 'Serial Number', 'value' => $instrumentItem->serial_number ?? '-'], ['label' => 'Barcode', 'value' => $instrumentItem->code ?? '-'], ['label' => 'RFID Tag', 'value' => $instrumentItem->rfid_tag ?? '-'], ['label' => 'Tgl. Pembelian', 'value' => $instrumentItem->purchased_at?->format('d M Y') ?? '-']] as $detail)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-36 shrink-0 text-xs text-gray-400">{{ $detail['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $detail['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Kondisi</span>
                        <x-badge :color="$cond['color']" dot>{{ $cond['label'] }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$instrumentItem->is_active ? 'green' : 'red'" dot>
                            {{ $instrumentItem->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Siklus --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Siklus</h3>
                </div>
                <div class="p-5">
                    @php
                        $lifespan = $instrumentItem->instrument->lifespan_cycles;
                        $cycles = $instrumentItem->total_cycles;
                        $remaining = $instrumentItem->remaining_cycles;
                        $pct = $lifespan ? min(100, round(($cycles / $lifespan) * 100)) : 0;
                        $barColor = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-green-500');
                    @endphp
                    <div class="mb-4 grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-gray-50 p-3 text-center">
                            <div class="text-lg font-bold text-gray-900">{{ number_format($cycles) }}</div>
                            <div class="text-[10px] text-gray-400">Total Siklus</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 text-center">
                            <div class="text-lg font-bold text-gray-900">
                                {{ $lifespan ? number_format($lifespan) : '—' }}</div>
                            <div class="text-[10px] text-gray-400">Maks. Siklus</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 text-center">
                            <div
                                class="text-lg font-bold {{ $remaining !== null && $remaining <= 50 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $remaining !== null ? number_format($remaining) : '—' }}
                            </div>
                            <div class="text-[10px] text-gray-400">Sisa Siklus</div>
                        </div>
                    </div>
                    @if ($lifespan)
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $barColor }} transition-all"
                                style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="mt-1 flex justify-between text-[10px] text-gray-400">
                            <span>0</span>
                            <span>{{ $pct }}% terpakai</span>
                            <span>{{ number_format($lifespan) }}</span>
                        </div>
                        @if ($instrumentItem->isNearingLifespan())
                            <div
                                class="mt-3 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2">
                                <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-xs font-medium text-red-700">Instrumen mendekati batas masa pakai.
                                    Pertimbangkan penggantian.</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>

        <div class="space-y-4">

            {{-- QR Code --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5 text-center">
                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">QR Code</h4>
                <div class="mx-auto inline-block">
                    {!! $instrumentItem->qrCodeSvg(160) !!}
                </div>
                <div class="mt-3 font-mono text-sm font-bold text-gray-900">{{ $instrumentItem->code }}</div>
            </div>

            {{-- Tray saat ini --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Tray Saat Ini</h4>
                @if ($instrumentItem->currentTray)
                    <div class="rounded-lg bg-primary-50 p-3">
                        <div class="text-sm font-semibold text-primary-700">{{ $instrumentItem->currentTray->code }}
                        </div>
                        <div class="text-xs text-primary-500">{{ $instrumentItem->currentTray->name ?? '-' }}</div>
                    </div>
                @else
                    <p class="text-xs text-gray-400">Tidak sedang berada di tray manapun</p>
                @endif
            </div>

            {{-- Audit --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $instrumentItem->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $instrumentItem->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $instrumentItem->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $instrumentItem->updated_at->format('d M Y, H:i')]] as $audit)
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
