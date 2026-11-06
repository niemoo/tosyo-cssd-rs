<x-layouts.app title="Tambah Tray">

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
        <span class="text-gray-700">Tambah Baru</span>
    </x-slot>

    <x-slot name="pageHeader">Tambah Tray</x-slot>
    <x-slot name="pageSubHeader">Rakit tray baru dari template atau bebas</x-slot>

    @php
        $instrumentOptions = $instrumentOptions ?? collect();
        $templateOptions = $templateOptions ?? collect();
        $consumableOptions = $consumables->map(fn ($c) => [
            'id'    => (string) $c->id,
            'label' => $c->name . ' (' . $c->current_stock . ' ' . $c->unit . ' tersedia)',
        ])->values();
    @endphp

    <form method="POST" action="{{ route('trays.store') }}" x-data="{
        useTemplate: false,
        selectedTemplate: null,
        templates: @js($templateOptions),
        items: [{ instrument_item_id: '', notes: '' }],
        addItem() {
            this.items.push({ instrument_item_id: '', notes: '' });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        loadTemplate(templateId) {
            if (!templateId) { this.selectedTemplate = null; return; }
            this.selectedTemplate = this.templates.find(t => t.id === templateId);
        },
        totalItems() { return this.items.filter(i => i.instrument_item_id !== '').length; }
    }">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                {{-- Informasi Tray --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Tray</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        {{-- Rumah Sakit --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Rumah Sakit <span class="text-red-500">*</span>
                            </label>
                            @if ($userHospitals->count() > 1)
                                <x-searchable-select name="hospital_id" placeholder="Pilih Rumah Sakit"
                                    :options="$userHospitals->map(fn ($h) => ['id' => (string) $h->id, 'label' => $h->name])->values()"
                                    :selected="old('hospital_id', session('active_hospital_id'))"
                                    :error="$errors->has('hospital_id')" />
                            @else
                                <input type="hidden" name="hospital_id" value="{{ $userHospitals->first()->id }}" />
                                <input type="text" value="{{ $userHospitals->first()->name }}" disabled
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                            @endif
                            @error('hospital_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nama Tray <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="cth. Set Bedah Minor #1"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kode --}}
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Kode <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code') }}" placeholder="cth. TRY-001"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('code') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('code')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Template (opsional) --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Template (Opsional)
                            </label>
                            <x-searchable-select name="template_id" placeholder="Tanpa Template (Tray Bebas)"
                                :options="$templates->map(fn ($t) => ['id' => (string) $t->id, 'label' => $t->code . ' — ' . $t->name])->values()"
                                :selected="old('template_id')" onChange="loadTemplate(option.id)" />
                            <p class="mt-1 text-[10px] text-gray-400">Pilih template sebagai referensi isi tray. Isi
                                tray tetap bisa diubah.</p>

                            {{-- Preview template --}}
                            <div x-show="selectedTemplate" x-cloak
                                class="mt-2 rounded-lg border border-blue-100 bg-blue-50 p-3">
                                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-blue-600">Isi
                                    Template</p>
                                <template x-for="item in selectedTemplate?.items ?? []" :key="item.instrument_name">
                                    <div class="flex items-center justify-between text-xs text-blue-700">
                                        <span x-text="item.instrument_name"></span>
                                        <span class="font-semibold" x-text="item.quantity + ' unit'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- Daftar Instrumen --}}
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Daftar Instrumen Fisik</h3>
                    </div>
                    <div class="p-5">

                        <div class="mb-2 flex items-center gap-3 px-1">
                            <span class="flex-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Item
                                Instrumen</span>
                            <span
                                class="w-48 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Catatan</span>
                            <span class="w-8"></span>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="mb-2 flex items-start gap-3">

                                {{-- Searchable dropdown instrumen --}}
                                <x-searchable-select class="flex-1" dynamicName="'items[' + index + '][instrument_item_id]'"
                                    model="item.instrument_item_id" :options="$instrumentOptions"
                                    placeholder="Pilih item instrumen..." searchPlaceholder="Cari item instrumen..."
                                    emptyText="Item tidak ditemukan" />

                                {{-- Notes per item --}}
                                <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes"
                                    placeholder="Catatan..."
                                    class="w-48 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs text-gray-900 transition
                                              focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />

                                {{-- Remove --}}
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
                            Tambah instrumen
                        </button>

                        @error('items')
                            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                    </div>
                </div>

                {{-- Pemakaian Consumable --}}
                <div class="rounded-xl border border-gray-200 bg-white" x-data="{
                    usages: [],
                    addUsage() { this.usages.push({ consumable_id: '', quantity: 1, notes: '' }); },
                    removeUsage(index) { this.usages.splice(index, 1); }
                }">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-3.5 w-3.5 text-purple-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Pemakaian Consumable (Opsional)</h3>
                        <span class="ml-auto text-xs text-gray-400">cth. pouch, indikator internal</span>
                    </div>
                    <div class="p-5">

                        <p x-show="usages.length === 0" class="mb-3 text-xs text-gray-400">
                            Belum ada consumable yang dicatat. Klik tombol di bawah untuk menambahkan.
                        </p>

                        <div x-show="usages.length > 0" class="mb-2 flex items-center gap-3 px-1">
                            <span
                                class="flex-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Consumable</span>
                            <span
                                class="w-28 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Jumlah</span>
                            <span
                                class="w-48 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Catatan</span>
                            <span class="w-8"></span>
                        </div>

                        <template x-for="(usage, index) in usages" :key="index">
                            <div class="mb-2 flex items-start gap-3">
                                <x-searchable-select class="flex-1" dynamicName="'consumable_usages[' + index + '][consumable_id]'"
                                    model="usage.consumable_id" :options="$consumableOptions"
                                    placeholder="Pilih consumable..." searchPlaceholder="Cari consumable..."
                                    emptyText="Consumable tidak ditemukan" />
                                <input type="number" :name="'consumable_usages[' + index + '][quantity]'"
                                    x-model="usage.quantity" min="1"
                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-center text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                <input type="text" :name="'consumable_usages[' + index + '][notes]'"
                                    x-model="usage.notes" placeholder="Catatan..."
                                    class="w-48 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                <button type="button" @click="removeUsage(index)"
                                    class="flex h-9 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        @foreach ($errors->get('consumable_usages.*.consumable_id') as $msgs)
                            @foreach ($msgs as $msg)
                                <p class="mt-1 text-xs text-red-500">{{ $msg }}</p>
                            @endforeach
                        @endforeach
                        @foreach ($errors->get('consumable_usages.*.quantity') as $msgs)
                            @foreach ($msgs as $msg)
                                <p class="mt-1 text-xs text-red-500">{{ $msg }}</p>
                            @endforeach
                        @endforeach

                        <button type="button" @click="addUsage()"
                            class="mt-2 flex items-center gap-1.5 text-sm font-medium text-primary-500 transition hover:text-primary-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah pemakaian consumable
                        </button>

                    </div>
                </div>

            </div>

            {{-- Sidebar kanan --}}
            <div class="space-y-4">

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-50">
                            <svg class="h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan</h3>
                    </div>
                    <div class="p-5">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Total instrumen</span>
                                <span class="text-sm font-bold text-gray-900" x-text="totalItems()"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">Status awal</span>
                                <x-badge color="blue">Dirakit</x-badge>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-green-100 bg-primary-50 p-4">
                    <div class="mb-2 flex items-center gap-1.5 text-xs font-bold text-primary-600">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Panduan
                    </div>
                    <ul class="space-y-1.5 text-xs text-primary-700">
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Template bersifat opsional — tray
                            bebas diperbolehkan</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Isi tray boleh berbeda dari template
                        </li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Status awal tray selalu ASSEMBLING
                        </li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Kode tray akan dijadikan barcode unik
                        </li>
                    </ul>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Tray
                </button>

            </div>
        </div>

        <div class="pb-96"></div>

    </form>

</x-layouts.app>
