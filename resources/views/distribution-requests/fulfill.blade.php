<x-layouts.app title="Pemenuhan — {{ $distributionRequest->request_number }}">

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
        <span class="text-gray-700">Pemenuhan</span>
    </x-slot>

    <x-slot name="pageHeader">Proses Pemenuhan</x-slot>
    <x-slot name="pageSubHeader">{{ $distributionRequest->request_number }} —
        {{ $distributionRequest->unit->name }}</x-slot>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-100 bg-red-50 p-4">
            <div class="mb-1 flex items-center gap-1.5 text-xs font-bold text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Terjadi Kesalahan
            </div>
            <ul class="space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $oldAssignments = old('assignments', []);
    @endphp

    <form method="POST" action="{{ route('distribution-requests.process-fulfillment', $distributionRequest) }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                <div class="rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Assign Tray Steril</h3>
                    </div>
                    <div class="p-5 space-y-3">

                        @if ($availableTrays->isEmpty())
                            <div class="rounded-lg border border-dashed border-amber-200 bg-amber-50 py-6 text-center">
                                <p class="text-sm text-amber-700">Tidak ada tray steril yang tersedia saat ini.</p>
                                <a href="{{ route('trays.index') }}"
                                    class="mt-2 inline-block text-xs font-semibold text-primary-500 hover:text-primary-600">
                                    Kelola Tray →
                                </a>
                            </div>
                        @endif

                        @php $assignIndex = 0; @endphp

                        @foreach ($distributionRequest->items as $item)
                            @if ($item->tray_id)
                                {{-- Item sudah ter-assign --}}
                                <div class="rounded-lg border border-green-100 bg-green-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $item->template?->name ?? 'Tidak spesifik' }}
                                                <span class="text-xs text-gray-400">({{ $item->quantity }} unit)</span>
                                            </div>
                                            <div class="text-xs text-green-600">
                                                ✓ Sudah di-assign: {{ $item->tray->code }} — {{ $item->tray->name }}
                                            </div>
                                        </div>
                                        <x-badge color="green">Selesai</x-badge>
                                    </div>
                                </div>
                            @else
                                @php
                                    $currentIndex = $assignIndex;
                                    $oldSelectedId = $oldAssignments[$currentIndex]['tray_id'] ?? '';
                                    $assignIndex++;
                                @endphp
                                {{-- Item belum ter-assign --}}
                                <div class="relative rounded-lg border border-gray-100 p-4" x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: '{{ $oldSelectedId }}',
                                    options: {{ $trayOptions }}.filter(o =>
                                        {{ $item->template_id ? 'true' : 'false' }} ?
                                        (o.template_id === '{{ $item->template_id }}' || !o.template_id) :
                                        true
                                    ),
                                    get filtered() {
                                        if (!this.search) return this.options;
                                        const s = this.search.toLowerCase();
                                        return this.options.filter(o => o.label.toLowerCase().includes(s));
                                    },
                                    get selectedLabel() {
                                        const found = this.options.find(o => o.id === this.selectedId);
                                        return found ? found.label : '';
                                    },
                                    pos: { top: 0, left: 0, width: 0 },
                                    toggle(btn) {
                                        if (this.open) { this.open = false; return; }
                                        const rect = btn.getBoundingClientRect();
                                        this.pos = { top: rect.bottom + 4, left: rect.left, width: rect.width };
                                        this.open = true;
                                        this.$nextTick(() => {
                                            const inp = document.getElementById('fulfill-search-{{ $currentIndex }}');
                                            if (inp) inp.focus();
                                        });
                                    },
                                    choose(option) {
                                        this.selectedId = option.id;
                                        this.open = false;
                                        this.search = '';
                                    }
                                }"
                                    @click.outside="open = false" x-init="const mainEl = document.querySelector('main');
                                    if (mainEl) mainEl.addEventListener('scroll', () => { if (open) open = false; }, { passive: true });">

                                    <div class="mb-2 flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $item->template?->name ?? 'Tidak spesifik' }}
                                                <span class="text-xs text-gray-400">({{ $item->quantity }} unit)</span>
                                            </div>
                                            @if ($item->notes)
                                                <div class="text-xs text-gray-400">{{ $item->notes }}</div>
                                            @endif
                                        </div>
                                        <x-badge color="amber">Belum di-assign</x-badge>
                                    </div>

                                    <input type="hidden" name="assignments[{{ $currentIndex }}][item_id]"
                                        value="{{ $item->id }}" />
                                    <input type="hidden" name="assignments[{{ $currentIndex }}][tray_id]"
                                        :value="selectedId" />

                                    <button type="button" @click="toggle($el)"
                                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm transition
                                                   hover:border-gray-300 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                        <span class="truncate" :class="selectedId ? 'text-gray-900' : 'text-gray-400'"
                                            x-text="selectedId ? selectedLabel : 'Pilih tray steril...'"></span>
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
                                                        id="fulfill-search-{{ $currentIndex }}"
                                                        placeholder="Cari tray steril..." @click.stop
                                                        @keydown.escape="open = false"
                                                        class="w-full border-none bg-transparent p-0 text-xs text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
                                                </div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto py-1">
                                                <template x-for="option in filtered" :key="option.id">
                                                    <button type="button" @click="choose(option)"
                                                        class="flex w-full items-center px-3 py-2 text-left text-xs transition hover:bg-gray-50"
                                                        :class="selectedId === option.id ?
                                                            'bg-primary-50 text-primary-600 font-medium' :
                                                            'text-gray-700'">
                                                        <span x-text="option.label"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filtered.length === 0"
                                                    class="px-3 py-4 text-center text-xs text-gray-400">
                                                    Tray tidak ditemukan
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        @endforeach

                    </div>
                </div>

            </div>

            <div class="space-y-4">

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
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Hanya tray berstatus Steril yang
                            muncul di pilihan</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Tray yang dipilih otomatis berubah ke
                            status Sedang Digunakan</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Jika semua item ter-assign, permintaan
                            otomatis menjadi Terpenuhi</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Bisa di-assign sebagian dulu jika tray
                            belum cukup</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Setiap item wajib dipilih tray-nya
                            sebelum mengirim</li>
                    </ul>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Kirim ke Unit
                </button>

            </div>
        </div>

        <div class="pb-96"></div>

    </form>

</x-layouts.app>
