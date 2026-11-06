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

    <form method="POST" action="{{ route('distribution-requests.process-fulfillment', $distributionRequest) }}"
        x-data="{
            trayOptions: {{ json_encode($trayOptions) }},
            scanCode: '',
            scanMessage: '',
            scanTray() {
                const code = this.scanCode.trim();
                this.scanCode = '';
                if (!code) return;

                const match = this.trayOptions.find((t) => t.code === code || t.barcode === code);

                if (!match) {
                    this.scanMessage = `Tray ${code} tidak ditemukan di antara tray steril yang tersedia.`;
                    return;
                }

                const selects = Array.from(document.querySelectorAll('select[data-tray-select]'));

                if (selects.some((s) => s.value === match.id)) {
                    this.scanMessage = `Tray ${match.code} sudah dipilih.`;
                    return;
                }

                const candidates = selects.filter((s) => s.dataset.templateId === '' || s.dataset.templateId === (match.template_id ?? ''));
                const emptySlot = candidates.find((s) => s.value === '');

                if (!emptySlot) {
                    this.scanMessage = `Tray ${match.code} tidak cocok dengan jenis yang diminta.`;
                    return;
                }

                emptySlot.value = match.id;
                this.scanMessage = `Tray ${match.code} diisi ke slot.`;
            },
        }">
        @csrf

        {{-- Scan kode/barcode tray — pola scanner: HID mengetik lalu Enter. --}}
        <div class="mb-4 rounded-xl border border-amber-100 bg-amber-50/40 p-4">
            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                Scan Tray
            </label>
            <input type="text" x-model="scanCode" x-ref="scanInput" autofocus
                @keydown.enter.prevent="scanTray(); $refs.scanInput.focus()"
                placeholder="Scan kode/barcode tray lalu Enter..."
                class="w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition
                       focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
            <p x-show="scanMessage" x-text="scanMessage" x-cloak class="mt-1.5 text-xs text-gray-500"></p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                <div class="rounded-xl border border-gray-200 bg-white">
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

                        @foreach ($distributionRequest->items as $item)
                            @php
                                $assigned = $item->trays;
                                $remaining = max(0, $item->quantity - $assigned->count());

                                // Hanya tray yang cocok jenisnya (atau tray bebas untuk item tanpa template).
                                $itemTrayOptions = $item->template_id
                                    ? $trayOptions->filter(fn ($t) => $t['template_id'] === (string) $item->template_id)->values()
                                    : $trayOptions;

                                $oldForItem = (array) old('assignments.' . $item->id, []);
                            @endphp

                            <div
                                class="rounded-lg border p-4 {{ $remaining === 0 ? 'border-green-100 bg-green-50' : 'border-gray-200' }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $item->template?->name ?? 'Tidak spesifik' }}
                                            <span class="text-xs text-gray-400">({{ $assigned->count() }}/{{ $item->quantity }}
                                                unit)</span>
                                        </div>
                                        @if ($item->notes)
                                            <div class="text-xs text-gray-400">{{ $item->notes }}</div>
                                        @endif
                                    </div>
                                    @if ($remaining === 0)
                                        <x-badge color="green">Lengkap</x-badge>
                                    @else
                                        <x-badge color="amber">Kurang {{ $remaining }}</x-badge>
                                    @endif
                                </div>

                                {{-- Tray yang sudah dikirim untuk item ini --}}
                                @foreach ($assigned as $t)
                                    <div class="mb-1.5 flex items-center gap-1.5 text-xs text-green-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $t->code }} — {{ $t->name }}
                                    </div>
                                @endforeach

                                {{-- Slot tersisa --}}
                                @if ($remaining > 0)
                                    @if ($itemTrayOptions->isEmpty())
                                        <p class="mt-2 text-xs text-amber-600">Belum ada tray steril berjenis ini yang
                                            tersedia. Sterilkan tray dulu.</p>
                                    @else
                                        <div class="mt-2 space-y-2">
                                            @for ($s = 0; $s < $remaining; $s++)
                                                <select name="assignments[{{ $item->id }}][]"
                                                    data-tray-select data-template-id="{{ $item->template_id ?? '' }}"
                                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 transition
                                                              focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                                    <option value="">— Pilih tray (slot {{ $s + 1 }}) —</option>
                                                    @foreach ($itemTrayOptions as $opt)
                                                        <option value="{{ $opt['id'] }}"
                                                            @selected(($oldForItem[$s] ?? null) === $opt['id'])>{{ $opt['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            @endfor
                                        </div>
                                    @endif
                                @endif
                            </div>
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
