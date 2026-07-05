<x-layouts.app title="Edit — {{ $trayTemplate->name }}">

    <x-slot name="backButton">
        <a href="{{ route('tray-templates.show', $trayTemplate) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('tray-templates.index') }}" class="hover:text-gray-600">Tray Template</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <a href="{{ route('tray-templates.show', $trayTemplate) }}"
            class="hover:text-gray-600">{{ $trayTemplate->name }}</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Edit</span>
    </x-slot>

    <x-slot name="pageHeader">Edit Template</x-slot>
    <x-slot name="pageSubHeader">{{ $trayTemplate->name }}</x-slot>

    @php
        $instrumentOptions = $instruments
            ->map(
                fn($i) => [
                    'id' => (string) $i->id,
                    'label' => $i->name . ' — ' . $i->category->name,
                ],
            )
            ->values()
            ->toJson();

        $existingItems = $trayTemplate->templateItems
            ->map(
                fn($i) => [
                    'instrument_id' => (string) $i->instrument_id,
                    'quantity' => $i->quantity,
                ],
            )
            ->values()
            ->toJson();
    @endphp

    <form method="POST" action="{{ route('tray-templates.update', $trayTemplate) }}" x-data="{
        items: {{ $existingItems }},
        addItem() {
            this.items.push({ instrument_id: '', quantity: 1 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        totalItems() { return this.items.reduce((sum, i) => sum + (parseInt(i.quantity) || 0), 0); },
        uniqueCount() { return this.items.filter(i => i.instrument_id !== '').length; }
    }">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                {{-- Informasi Template --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Template</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Rumah
                                Sakit</label>
                            <input type="text" value="{{ $trayTemplate->hospital->name }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nama Template <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $trayTemplate->name) }}"
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
                            <input type="text" value="{{ $trayTemplate->code }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                            <p class="mt-1 text-[10px] text-gray-400">Kode tidak dapat diubah</p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Deskripsi</label>
                            <input type="text" name="description"
                                value="{{ old('description', $trayTemplate->description) }}" placeholder="Opsional"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                    </div>
                </div>

                {{-- Daftar Instrumen --}}
                <div class="rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Daftar Instrumen</h3>
                    </div>
                    <div class="p-5">

                        <div class="mb-2 flex items-center gap-3 px-1">
                            <span
                                class="flex-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Instrumen</span>
                            <span
                                class="w-20 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Jumlah</span>
                            <span class="w-8"></span>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="mb-2 flex items-center gap-3">

                                {{-- Custom searchable dropdown --}}
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
                                        const found = this.options.find(o => o.id === item.instrument_id);
                                        return found ? found.label : '';
                                    },
                                    pos: { top: 0, left: 0, width: 0 },
                                    toggle(btn) {
                                        if (this.open) { this.open = false; return; }
                                        const rect = btn.getBoundingClientRect();
                                        this.pos = { top: rect.bottom + 4, left: rect.left, width: rect.width };
                                        this.open = true;
                                        this.$nextTick(() => {
                                            const input = document.getElementById('search-edit-' + index);
                                            if (input) input.focus();
                                        });
                                    },
                                    choose(option) {
                                        item.instrument_id = option.id;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }" @click.outside="open = false"
                                    x-init="const closeOnScroll = () => { if (open) open = false; };
                                    const mainEl = document.querySelector('main');
                                    if (mainEl) mainEl.addEventListener('scroll', closeOnScroll, { passive: true });">

                                    <input type="hidden" :name="'items[' + index + '][instrument_id]'"
                                        :value="item.instrument_id" />

                                    <button type="button" @click="toggle($el)"
                                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition
                                                   hover:border-gray-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20"
                                        :class="item.instrument_id ? 'text-gray-900' : 'text-gray-400'">
                                        <span class="truncate"
                                            x-text="item.instrument_id ? selectedLabel : 'Pilih instrumen...'"></span>
                                        <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400 transition"
                                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <template x-teleport="#dropdown-portal">
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            :style="`position: fixed; top: ${pos.top}px; left: ${pos.left}px; width: ${pos.width}px; z-index: 9999;`"
                                            class="rounded-xl border border-gray-100 bg-white shadow-lg"
                                            style="display: none;" x-cloak>

                                            <div class="border-b border-gray-50 p-2">
                                                <div
                                                    class="flex items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="11" cy="11" r="8" />
                                                        <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                                                    </svg>
                                                    <input type="text" x-model="search"
                                                        :id="'search-edit-' + index" placeholder="Cari instrumen..."
                                                        @click.stop @keydown.escape="open = false"
                                                        class="w-full border-none bg-transparent p-0 text-xs text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
                                                </div>
                                            </div>

                                            <div class="max-h-48 overflow-y-auto py-1">
                                                <template x-for="option in filtered" :key="option.id">
                                                    <button type="button" @click="choose(option)"
                                                        class="flex w-full items-center px-3 py-2 text-left text-xs transition hover:bg-gray-50"
                                                        :class="item.instrument_id === option.id ?
                                                            'bg-primary-50 text-primary-600 font-medium' :
                                                            'text-gray-700'">
                                                        <span x-text="option.label"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filtered.length === 0"
                                                    class="px-3 py-4 text-center text-xs text-gray-400">
                                                    Instrumen tidak ditemukan
                                                </div>
                                            </div>

                                        </div>
                                    </template>

                                </div>

                                <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity"
                                    min="1" max="99"
                                    class="w-20 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-center text-sm text-gray-900 transition
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

                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-50">
                            <svg class="h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Pengaturan</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-3.5">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Template Aktif</div>
                                <div class="text-xs text-gray-400">Dapat digunakan dalam sistem</div>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $trayTemplate->is_active) ? 'checked' : '' }}
                                    class="peer sr-only" />
                                <div
                                    class="peer h-5 w-9 rounded-full bg-gray-200 transition-colors
                                            peer-checked:bg-primary-400
                                            peer-focus:ring-2 peer-focus:ring-primary-400/20
                                            after:absolute after:left-0.5 after:top-0.5
                                            after:h-4 after:w-4 after:rounded-full
                                            after:bg-white after:shadow after:transition-all after:content-['']
                                            peer-checked:after:translate-x-4">
                                </div>
                            </label>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-3.5">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Lockable</div>
                                <div class="text-xs text-gray-400">Tray dikunci setelah dirakit</div>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="is_lockable" value="1"
                                    {{ old('is_lockable', $trayTemplate->is_lockable) ? 'checked' : '' }}
                                    class="peer sr-only" />
                                <div
                                    class="peer h-5 w-9 rounded-full bg-gray-200 transition-colors
                                            peer-checked:bg-primary-400
                                            peer-focus:ring-2 peer-focus:ring-primary-400/20
                                            after:absolute after:left-0.5 after:top-0.5
                                            after:h-4 after:w-4 after:rounded-full
                                            after:bg-white after:shadow after:transition-all after:content-['']
                                            peer-checked:after:translate-x-4">
                                </div>
                            </label>
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
                        Ringkasan
                    </div>
                    <p class="text-xs text-primary-700">
                        <span x-text="uniqueCount()"></span> jenis instrumen ·
                        <span x-text="totalItems()"></span> total unit
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white p-5">
                    <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Informasi Audit</h4>
                    <div class="space-y-3">
                        @foreach ([['label' => 'Dibuat oleh', 'value' => $trayTemplate->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $trayTemplate->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $trayTemplate->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $trayTemplate->updated_at->format('d M Y, H:i')]] as $audit)
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
