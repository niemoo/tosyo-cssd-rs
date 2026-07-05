<x-layouts.app title="{{ $tray->name }}">

    <x-slot name="backButton">
        <a href="{{ route('trays.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('trays.index') }}" class="hover:text-gray-600">Tray</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $tray->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $tray->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $tray->code }} · {{ $tray->hospital->name }}</x-slot>

    <x-slot name="pageActions">
        @can('trays.edit')
            @if (in_array($tray->status, [\App\Models\Tray::STATUS_ASSEMBLING, \App\Models\Tray::STATUS_NEEDS_REPROCESSING]))
                <a href="{{ route('trays.edit', $tray) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endif
        @endcan
    </x-slot>

    @php $statusInfo = \App\Models\Tray::STATUSES[$tray->status] ?? ['label' => $tray->status, 'color' => 'gray']; @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Info Tray --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Tray</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama', 'value' => $tray->name], ['label' => 'Kode', 'value' => $tray->code], ['label' => 'Barcode', 'value' => $tray->barcode ?? '-'], ['label' => 'Template', 'value' => $tray->template?->name ?? 'Tray Bebas'], ['label' => 'Rumah Sakit', 'value' => $tray->hospital->name], ['label' => 'Dirakit oleh', 'value' => $tray->assembler?->name ?? '-'], ['label' => 'Dirakit pada', 'value' => $tray->assembled_at?->format('d M Y, H:i') ?? '-'], ['label' => 'Lokasi', 'value' => $tray->currentRack?->name ?? '-']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-36 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                    </div>
                    @if ($tray->notes)
                        <div class="px-5 py-3">
                            <span class="text-xs text-gray-400">Catatan</span>
                            <p class="mt-1 text-sm text-gray-700">{{ $tray->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Daftar Instrumen --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Instrumen dalam Tray</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        {{ $tray->items->count() }} item
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($tray->items as $item)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->instrumentItem->instrument->name }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $item->instrumentItem->code }}
                                    · {{ $item->instrumentItem->instrument->category->name }}
                                </div>
                                @if ($item->notes)
                                    <div class="text-[10px] italic text-gray-400">{{ $item->notes }}</div>
                                @endif
                            </div>
                            <x-badge :color="\App\Models\InstrumentItem::CONDITIONS[$item->instrumentItem->condition][
                                'color'
                            ] ?? 'gray'">
                                {{ \App\Models\InstrumentItem::CONDITIONS[$item->instrumentItem->condition]['label'] ?? $item->instrumentItem->condition }}
                            </x-badge>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-gray-400">Belum ada instrumen</div>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat Sterilisasi --}}
            @if ($tray->sterilizationBatchItems->count() > 0)
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="border-b border-gray-50 px-5 py-3.5">
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Sterilisasi</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($tray->sterilizationBatchItems as $batchItem)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $batchItem->batch->batch_number }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $batchItem->batch->sterilizer->name }}
                                        · {{ $batchItem->batch->completed_at?->format('d M Y') ?? 'Belum selesai' }}
                                    </div>
                                    @if ($batchItem->failure_notes)
                                        <div class="text-xs text-red-500">{{ $batchItem->failure_notes }}</div>
                                    @endif
                                </div>
                                @if ($batchItem->result === 'PASSED')
                                    <x-badge color="green">Lulus</x-badge>
                                @elseif($batchItem->result === 'FAILED')
                                    <x-badge color="red">Gagal</x-badge>
                                @else
                                    <x-badge color="gray">Pending</x-badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pemakaian Consumable --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white" x-data="{ open: {{ $errors->has('quantity') ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Pemakaian Consumable</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        {{ $tray->consumableUsages->count() }} catatan
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($tray->consumableUsages as $usage)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $usage->consumable->name }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $usage->usedBy->name }} · {{ $usage->used_at->format('d M Y, H:i') }}
                                </div>
                                @if ($usage->notes)
                                    <div class="text-[10px] italic text-gray-400">{{ $usage->notes }}</div>
                                @endif
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ $usage->quantity }}
                                {{ $usage->consumable->unit }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-gray-400">Belum ada pemakaian consumable tercatat
                        </div>
                    @endforelse
                </div>

                @can('trays.edit')
                    <div class="border-t border-gray-50 p-4">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-1.5 text-sm font-medium text-primary-500 transition hover:text-primary-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span x-text="open ? 'Tutup' : 'Tambah Pemakaian Consumable'"></span>
                        </button>

                        <form x-show="open" x-cloak method="POST" action="{{ route('consumable-usages.store') }}"
                            class="mt-3 space-y-3">
                            @csrf
                            <input type="hidden" name="usageable_type" value="tray" />
                            <input type="hidden" name="usageable_id" value="{{ $tray->id }}" />

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <select name="consumable_id"
                                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                    <option value="">Pilih consumable...</option>
                                    @foreach ($consumables as $c)
                                        <option value="{{ $c->id }}"
                                            {{ old('consumable_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }} ({{ $c->current_stock }} {{ $c->unit }} tersedia)
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                                    placeholder="Jumlah"
                                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                <input type="datetime-local" name="used_at"
                                    value="{{ old('used_at', now()->format('Y-m-d\TH:i')) }}"
                                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                            </div>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                placeholder="Catatan (opsional)..."
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                       focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />

                            @error('quantity')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @error('consumable_id')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                                Simpan Pemakaian
                            </button>
                        </form>
                    </div>
                @endcan
            </div>

        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $tray->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $tray->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $tray->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $tray->updated_at->format('d M Y, H:i')]] as $audit)
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
