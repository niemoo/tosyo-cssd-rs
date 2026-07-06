<x-layouts.app title="Edit — {{ $tray->name }}">

    <x-slot name="backButton">
        <a href="{{ route('trays.show', $tray) }}"
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
        <a href="{{ route('trays.show', $tray) }}" class="hover:text-gray-600">{{ $tray->name }}</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Edit</span>
    </x-slot>

    <x-slot name="pageHeader">Edit Tray</x-slot>
    <x-slot name="pageSubHeader">{{ $tray->name }}</x-slot>

    <form method="POST" action="{{ route('trays.update', $tray) }}" x-data="{
        items: {{ $existingItems }},
        addItem() {
            this.items.push({ instrument_item_id: '', notes: '' });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        totalItems() { return this.items.filter(i => i.instrument_item_id !== '').length; }
    }">
        @csrf @method('PUT')

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

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Rumah
                                Sakit</label>
                            <input type="text" value="{{ $tray->hospital->name }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nama Tray <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $tray->name) }}"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Kode</label>
                            <input type="text" value="{{ $tray->code }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                            <p class="mt-1 text-[10px] text-gray-400">Kode tidak dapat diubah</p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Barcode</label>
                            <input type="text" name="barcode" value="{{ old('barcode', $tray->barcode) }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('barcode') ? 'border-red-400' : '' }}" />
                            @error('barcode')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Template</label>
                            <input type="text" value="{{ $tray->template?->name ?? 'Tray Bebas (tanpa template)' }}"
                                disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes', $tray->notes) }}</textarea>
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
                                <div class="relative flex-1" x-data="{
                                    open: false,
                                    search: '',
                                    options: {{ $instrumentOptions }},
                                    get filtered() {
                                        if (!this.search) return this.options;
                                        const s = this.search.toLowerCase();
                                        return this.options.filter(o => o.label.toLowerCase().includes(s));
                                    },
                                    get selectedLabel() {
                                        const found = this.options.find(o => o.id === item.instrument_item_id);
                                        return found ? found.label : '';
                                    },
                                    pos: { top: 0, left: 0, width: 0 },
                                    toggle(btn) {
                                        if (this.open) { this.open = false; return; }
                                        const rect = btn.getBoundingClientRect();
                                        this.pos = { top: rect.bottom + 4, left: rect.left, width: rect.width };
                                        this.open = true;
                                        this.$nextTick(() => {
                                            const input = document.getElementById('edit-ins-' + index);
                                            if (input) input.focus();
                                        });
                                    },
                                    choose(option) {
                                        item.instrument_item_id = option.id;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }" @click.outside="open = false"
                                    x-init="const mainEl = document.querySelector('main');
                                    if (mainEl) mainEl.addEventListener('scroll', () => { if (open) open = false; }, { passive: true });">
                                    <input type="hidden" :name="'items[' + index + '][instrument_item_id]'"
                                        :value="item.instrument_item_id" />
                                    <button type="button" @click="toggle($el)"
                                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs transition
                                                   hover:border-gray-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20"
                                        :class="item.instrument_item_id ? 'text-gray-900' : 'text-gray-400'">
                                        <span class="truncate"
                                            x-text="item.instrument_item_id ? selectedLabel : 'Pilih item instrumen...'"></span>
                                        <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400"
                                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <template x-teleport="#dropdown-portal">
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            :style="`position: fixed; top: ${pos.top}px; left: ${pos.left}px; width: ${pos.width}px; z-index: 9999;`"
                                            class="rounded-xl border border-gray-200 bg-white shadow-lg"
                                            style="display: none;" x-cloak>
                                            <div class="border-b border-gray-50 p-2">
                                                <div
                                                    class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="11" cy="11" r="8" />
                                                        <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                                                    </svg>
                                                    <input type="text" x-model="search" :id="'edit-ins-' + index"
                                                        placeholder="Cari item instrumen..." @click.stop
                                                        @keydown.escape="open = false"
                                                        class="w-full border-none bg-transparent p-0 text-xs text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
                                                </div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto py-1">
                                                <template x-for="option in filtered" :key="option.id">
                                                    <button type="button" @click="choose(option)"
                                                        class="flex w-full items-center px-3 py-2 text-left text-xs transition hover:bg-gray-50"
                                                        :class="item.instrument_item_id === option.id ?
                                                            'bg-primary-50 text-primary-600 font-medium' :
                                                            'text-gray-700'">
                                                        <span x-text="option.label"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filtered.length === 0"
                                                    class="px-3 py-4 text-center text-xs text-gray-400">
                                                    Item tidak ditemukan
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

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
                            Tambah instrumen
                        </button>

                    </div>
                </div>

            </div>

            <div class="space-y-4">

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-50">
                            <svg class="h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Status Tray</h3>
                    </div>
                    <div class="p-5">
                        @php $statusInfo = \App\Models\Tray::STATUSES[$tray->status] ?? ['label' => $tray->status, 'color' => 'gray']; @endphp
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3.5">
                            <span class="text-xs text-gray-500">Status saat ini</span>
                            <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-400">Edit hanya tersedia saat status ASSEMBLING atau
                            NEEDS_REPROCESSING</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Informasi Audit</h4>
                    <div class="space-y-3">
                        @foreach ([['label' => 'Dibuat oleh', 'value' => $tray->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $tray->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $tray->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $tray->updated_at->format('d M Y, H:i')]] as $audit)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                                <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>

            </div>
        </div>

        <div class="pb-96"></div>

    </form>

</x-layouts.app>
