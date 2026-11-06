<x-layouts.app title="Buat Batch Sterilisasi">

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
        <span class="text-gray-700">Buat Batch</span>
    </x-slot>

    <x-slot name="pageHeader">Buat Batch Sterilisasi</x-slot>
    <x-slot name="pageSubHeader">Masukkan tray ke dalam batch dan jalankan proses sterilisasi</x-slot>

    <form method="POST" action="{{ route('sterilization-batches.store') }}" x-data="{
        selectedTrays: {{ json_encode(array_values(old('tray_ids', []))) }},
        availableTrays: {{ json_encode($availableTrays->map(fn ($t) => ['id' => (string) $t->id, 'code' => $t->code, 'barcode' => $t->barcode])->values()) }},
        scanCode: '',
        scanMessage: '',
        scanTray() {
            const code = this.scanCode.trim();
            this.scanCode = '';
            if (!code) return;

            const match = this.availableTrays.find((t) => t.code === code || t.barcode === code);

            if (!match) {
                this.scanMessage = 'Tray tidak ada di daftar siap sterilisasi.';
                return;
            }

            if (!this.selectedTrays.includes(match.id)) {
                this.selectedTrays.push(match.id);
            }
            this.scanMessage = 'Tray ' + match.code + ' ditambahkan ke batch.';
        }
    }">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                {{-- Informasi Batch --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Batch</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

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

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nomor Batch <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="batch_number" value="{{ old('batch_number', $batchNumber) }}"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('batch_number') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('batch_number')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Sterilizer <span class="text-red-500">*</span>
                            </label>
                            <x-searchable-select name="sterilizer_id" placeholder="Pilih Sterilizer"
                                :options="$sterilizers->map(fn ($s) => ['id' => (string) $s->id, 'label' => $s->name . ' (' . $s->type . ') — Kapasitas ' . ($s->capacity ?? '?') . ' tray'])->values()"
                                :selected="old('sterilizer_id')" :error="$errors->has('sterilizer_id')" />
                            @error('sterilizer_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Waktu Mulai
                            </label>
                            <input type="datetime-local" name="started_at"
                                value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Durasi (Menit)
                            </label>
                            <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}"
                                placeholder="cth. 18" min="1"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Suhu (°C)
                            </label>
                            <input type="number" name="temperature" value="{{ old('temperature') }}"
                                placeholder="cth. 134" step="0.01"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Tekanan (Bar)
                            </label>
                            <input type="number" name="pressure" value="{{ old('pressure') }}" placeholder="cth. 2.10"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Uji Bowie-Dick <span class="font-normal normal-case text-gray-400">(uji harian mesin pra-vakum)</span>
                            </label>
                            <select name="bowie_dick_result"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                                <option value="">— Tidak dicatat —</option>
                                @foreach (\App\Models\SterilizationBatch::BOWIE_DICK as $key => $info)
                                    <option value="{{ $key }}" @selected(old('bowie_dick_result') === $key)>{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- Pilih Tray --}}
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Pilih Tray</h3>
                        <span class="ml-auto text-xs text-gray-400">Hanya tray berstatus <span
                                class="font-semibold text-amber-600">Siap Sterilisasi</span></span>
                    </div>

                    {{-- Scan kode/barcode tray — pola scanner: HID mengetik lalu Enter. --}}
                    <div class="border-b border-gray-50 bg-amber-50/40 px-5 py-3">
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

                    <div class="p-5">
                        @error('tray_ids')
                            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                        @forelse($availableTrays as $tray)
                            <label
                                class="mb-2 flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-200 hover:bg-primary-50"
                                :class="selectedTrays.includes('{{ $tray->id }}') ? 'border-primary-300 bg-primary-50' : ''">
                                <input type="checkbox" name="tray_ids[]" value="{{ $tray->id }}"
                                    x-model="selectedTrays"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $tray->name }}</span>
                                        <span
                                            class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500">{{ $tray->code }}</span>
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-400">
                                        {{ $tray->template?->name ?? 'Tray Bebas' }} · {{ $tray->items->count() }}
                                        instrumen
                                    </div>
                                </div>
                                <x-badge color="amber" dot>Siap Sterilisasi</x-badge>
                            </label>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-200 py-8 text-center">
                                <p class="text-sm text-gray-500">Tidak ada tray yang siap disterilisasi</p>
                                <p class="mt-1 text-xs text-gray-400">Pastikan tray berstatus <span
                                        class="font-semibold">Siap Sterilisasi</span></p>
                                <a href="{{ route('trays.index') }}"
                                    class="mt-3 inline-block text-xs font-semibold text-primary-500 hover:text-primary-600">
                                    Kelola Tray →
                                </a>
                            </div>
                        @endforelse
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
                        <span class="ml-auto text-xs text-gray-400">cth. indikator, tape</span>
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
                                    model="usage.consumable_id"
                                    :options="$consumables->map(fn ($c) => ['id' => (string) $c->id, 'label' => $c->name . ' (' . $c->current_stock . ' ' . $c->unit . ' tersedia)'])->values()"
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

            {{-- Sidebar --}}
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
                                <span class="text-xs text-gray-500">Tray dipilih</span>
                                <span class="text-sm font-bold text-gray-900" x-text="selectedTrays.length"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500">Status awal batch</span>
                                <x-badge color="blue">Berjalan</x-badge>
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
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Batch dibuat langsung dengan status
                            Berjalan</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Tray yang dipilih otomatis berubah ke
                            status Dalam Sterilisasi</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Input hasil (PASSED/FAILED) per tray
                            di halaman detail batch</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Tray FAILED otomatis masuk antrian
                            ulang</li>
                    </ul>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Mulai Sterilisasi
                </button>

            </div>
        </div>
    </form>

</x-layouts.app>
