<x-layouts.app title="{{ $sterilizationBatch->batch_number }}">

    <x-slot name="backButton">
        <a href="{{ route('sterilization-batches.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('sterilization-batches.index') }}" class="hover:text-gray-600">Batch Sterilisasi</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $sterilizationBatch->batch_number }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $sterilizationBatch->batch_number }}</x-slot>
    <x-slot name="pageSubHeader">{{ $sterilizationBatch->sterilizer->name }} ·
        {{ $sterilizationBatch->hospital->name }}</x-slot>

    <x-slot name="pageActions">
        @if ($sterilizationBatch->status === \App\Models\SterilizationBatch::STATUS_IN_PROGRESS)
            @can('sterilization-batches.edit')
                <a href="{{ route('sterilization-batches.edit', $sterilizationBatch) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Parameter
                </a>
            @endcan
        @endif
    </x-slot>

    @php $statusInfo = \App\Models\SterilizationBatch::STATUSES[$sterilizationBatch->status] ?? ['label' => $sterilizationBatch->status, 'color' => 'gray']; @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Info Batch --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Batch</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nomor Batch', 'value' => $sterilizationBatch->batch_number], ['label' => 'Sterilizer', 'value' => $sterilizationBatch->sterilizer->name . ' (' . $sterilizationBatch->sterilizer->type . ')'], ['label' => 'Operator', 'value' => $sterilizationBatch->operator->name], ['label' => 'Rumah Sakit', 'value' => $sterilizationBatch->hospital->name], ['label' => 'Waktu Mulai', 'value' => $sterilizationBatch->started_at?->format('d M Y, H:i') ?? '-'], ['label' => 'Waktu Selesai', 'value' => $sterilizationBatch->completed_at?->format('d M Y, H:i') ?? '-']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-36 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                    </div>
                </div>
            </div>

            {{-- Parameter --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Parameter Sterilisasi</h3>
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-50">
                    <div class="p-5 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $sterilizationBatch->temperature ?? '—' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-400">Suhu (°C)</div>
                    </div>
                    <div class="p-5 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $sterilizationBatch->pressure ?? '—' }}</div>
                        <div class="mt-1 text-xs text-gray-400">Tekanan (Bar)</div>
                    </div>
                    <div class="p-5 text-center">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $sterilizationBatch->duration_minutes ?? '—' }}</div>
                        <div class="mt-1 text-xs text-gray-400">Durasi (Menit)</div>
                    </div>
                </div>
            </div>

            {{-- Hasil per Tray --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Hasil Sterilisasi per Tray</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        {{ $sterilizationBatch->items->count() }} tray
                    </span>
                </div>

                @if ($sterilizationBatch->status === \App\Models\SterilizationBatch::STATUS_IN_PROGRESS)
                    {{-- Form input hasil --}}
                    <form method="POST" action="{{ route('sterilization-batches.result', $sterilizationBatch) }}">
                        @csrf @method('PATCH')

                        <div class="p-5 space-y-3">

                            {{-- Pilih Rak untuk tray yang PASSED --}}
                            <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50 p-3">
                                <label
                                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-blue-600">
                                    Simpan ke Rak (untuk tray yang LULUS)
                                </label>
                                <select name="rack_id"
                                    class="w-full rounded-lg border border-blue-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition
                                               focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                    <option value="">Tidak langsung disimpan ke rak</option>
                                    @foreach ($racks as $rack)
                                        <option value="{{ $rack->id }}">{{ $rack->name }} —
                                            {{ $rack->location_desc }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @foreach ($sterilizationBatch->items as $i => $batchItem)
                                <div class="rounded-lg border border-gray-200 p-4" x-data="{ result: '{{ $batchItem->result }}' }">
                                    <input type="hidden" name="results[{{ $i }}][tray_id]"
                                        value="{{ $batchItem->tray_id }}" />

                                    <div class="mb-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $batchItem->tray->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $batchItem->tray->code }}</div>
                                        </div>
                                        @if ($batchItem->result === 'PENDING')
                                            <x-badge color="gray">Belum diinput</x-badge>
                                        @elseif($batchItem->result === 'PASSED')
                                            <x-badge color="green">Lulus</x-badge>
                                        @else
                                            <x-badge color="red">Gagal</x-badge>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-lg border p-2.5 transition"
                                            :class="result === 'PASSED' ? 'border-green-400 bg-green-50' :
                                                'border-gray-200 hover:border-green-300'">
                                            <input type="radio" name="results[{{ $i }}][result]"
                                                value="PASSED" x-model="result"
                                                class="border-gray-300 text-green-500 focus:ring-green-400" />
                                            <div>
                                                <div class="text-xs font-semibold text-green-700">✓ Lulus (PASSED)</div>
                                                <div class="text-[10px] text-green-500">Tray menjadi Steril</div>
                                            </div>
                                        </label>
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-lg border p-2.5 transition"
                                            :class="result === 'FAILED' ? 'border-red-400 bg-red-50' :
                                                'border-gray-200 hover:border-red-300'">
                                            <input type="radio" name="results[{{ $i }}][result]"
                                                value="FAILED" x-model="result"
                                                class="border-gray-300 text-red-500 focus:ring-red-400" />
                                            <div>
                                                <div class="text-xs font-semibold text-red-700">✗ Gagal (FAILED)</div>
                                                <div class="text-[10px] text-red-500">Tray perlu diproses ulang</div>
                                            </div>
                                        </label>
                                    </div>

                                    <div x-show="result === 'FAILED'" x-cloak class="mt-2">
                                        <input type="text" name="results[{{ $i }}][failure_notes]"
                                            value="{{ $batchItem->failure_notes }}"
                                            placeholder="Catatan kegagalan (wajib jika FAILED)..."
                                            class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-gray-700 transition
                                                      focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400/20" />
                                    </div>
                                </div>
                            @endforeach

                            <button type="submit"
                                class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                                <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Hasil Sterilisasi
                            </button>
                        </div>
                    </form>
                @else
                    {{-- View only --}}
                    <div class="divide-y divide-gray-50">
                        @foreach ($sterilizationBatch->items as $batchItem)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $batchItem->tray->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $batchItem->tray->code }}</div>
                                    @if ($batchItem->failure_notes)
                                        <div class="mt-0.5 text-xs text-red-500">{{ $batchItem->failure_notes }}</div>
                                    @endif
                                </div>
                                @if ($batchItem->result === 'PASSED')
                                    <x-badge color="green" dot>Lulus</x-badge>
                                @elseif($batchItem->result === 'FAILED')
                                    <x-badge color="red" dot>Gagal</x-badge>
                                @else
                                    <x-badge color="gray" dot>Pending</x-badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Pemakaian Consumable --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white" x-data="{ open: {{ $errors->has('quantity') ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Pemakaian Consumable</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        {{ $sterilizationBatch->consumableUsages->count() }} catatan
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($sterilizationBatch->consumableUsages as $usage)
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
                        <div class="px-5 py-6 text-center text-sm text-gray-400">Belum ada pemakaian consumable
                            tercatat</div>
                    @endforelse
                </div>

                @can('sterilization-batches.edit')
                    <div class="border-t border-gray-50 p-4">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-1.5 text-sm font-medium text-primary-500 transition hover:text-primary-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span x-text="open ? 'Tutup' : 'Tambah Pemakaian Consumable'"></span>
                        </button>

                        <form x-show="open" x-cloak method="POST" action="{{ route('consumable-usages.store') }}"
                            class="mt-3 space-y-3">
                            @csrf
                            <input type="hidden" name="usageable_type" value="sterilization_batch" />
                            <input type="hidden" name="usageable_id" value="{{ $sterilizationBatch->id }}" />

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
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $sterilizationBatch->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $sterilizationBatch->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $sterilizationBatch->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $sterilizationBatch->updated_at->format('d M Y, H:i')]] as $audit)
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
