<x-layouts.app title="Edit Permintaan — {{ $distributionRequest->request_number }}">

    <x-slot name="backButton">
        <a href="{{ route('distribution-requests.show', $distributionRequest) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('distribution-requests.index') }}" class="hover:text-gray-600">Permintaan Distribusi</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <a href="{{ route('distribution-requests.show', $distributionRequest) }}"
            class="hover:text-gray-600">{{ $distributionRequest->request_number }}</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Edit</span>
    </x-slot>

    <x-slot
        name="pageHeader">{{ $distributionRequest->status === \App\Models\DistributionRequest::STATUS_REJECTED ? 'Revisi Permintaan' : 'Edit Permintaan' }}</x-slot>
    <x-slot name="pageSubHeader">{{ $distributionRequest->request_number }}</x-slot>

    @if (
        $distributionRequest->status === \App\Models\DistributionRequest::STATUS_REJECTED &&
            $distributionRequest->rejection_notes)
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 p-4">
            <div class="mb-1 flex items-center gap-1.5 text-xs font-bold text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Alasan Penolakan
            </div>
            <p class="text-sm text-red-700">{{ $distributionRequest->rejection_notes }}</p>
        </div>
    @endif

    @php
        $existingItems = $distributionRequest->items
            ->map(
                fn($i) => [
                    'template_id' => $i->template_id ? (string) $i->template_id : '',
                    'quantity' => $i->quantity,
                    'notes' => $i->notes ?? '',
                ],
            )
            ->values()
            ->toJson();
    @endphp

    <form method="POST" action="{{ route('distribution-requests.update', $distributionRequest) }}"
        x-data="{
            items: {{ $existingItems }},
            addItem() { this.items.push({ template_id: '', quantity: 1, notes: '' }); },
            removeItem(index) { this.items.splice(index, 1); }
        }">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Permintaan</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Rumah
                                Sakit</label>
                            <input type="text" value="{{ $distributionRequest->hospital->name }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Unit / Bangsal <span class="text-red-500">*</span>
                            </label>
                            <select name="unit_id"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                           {{ $errors->has('unit_id') ? 'border-red-400' : 'border-gray-200' }}">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ old('unit_id', $distributionRequest->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes', $distributionRequest->notes) }}</textarea>
                        </div>

                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Daftar Tray yang Diminta</h3>
                    </div>
                    <div class="p-5">

                        <div class="mb-2 flex items-center gap-3 px-1">
                            <span class="flex-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Jenis
                                Tray (Template)</span>
                            <span
                                class="w-20 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Jumlah</span>
                            <span
                                class="w-48 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Catatan</span>
                            <span class="w-8"></span>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="mb-2 flex items-center gap-3">
                                <select :name="'items[' + index + '][template_id]'" x-model="item.template_id"
                                    class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 transition
                                               focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                    <option value="">Tidak spesifik</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->code }} —
                                            {{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity"
                                    min="1" max="99"
                                    class="w-20 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-center text-sm text-gray-900 transition
                                              focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes"
                                    placeholder="Catatan..."
                                    class="w-48 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs text-gray-900 transition
                                              focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                    class="flex h-9 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div x-show="items.length <= 1" class="w-8"></div>
                            </div>
                        </template>

                        <button type="button" @click="addItem()"
                            class="mt-2 flex items-center gap-1.5 text-sm font-medium text-primary-500 transition hover:text-primary-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah item
                        </button>

                        @error('items')
                            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                    </div>
                </div>

            </div>

            <div class="space-y-4">

                <button type="submit" name="submit_type" value="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $distributionRequest->status === \App\Models\DistributionRequest::STATUS_REJECTED ? 'Ajukan Ulang' : 'Ajukan Permintaan' }}
                </button>

                <button type="submit" name="submit_type" value="draft"
                    class="w-full rounded-lg border border-gray-200 bg-white py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                    Simpan sebagai Draft
                </button>

            </div>
        </div>
    </form>

</x-layouts.app>
