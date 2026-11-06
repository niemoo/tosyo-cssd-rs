<x-layouts.app title="Input Pergerakan Stok">

    <x-slot name="backButton">
        <a href="{{ route('consumable-stocks.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('consumable-stocks.index') }}" class="hover:text-gray-600">Stok Consumable</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Input Pergerakan</span>
    </x-slot>

    <x-slot name="pageHeader">Input Pergerakan Stok</x-slot>
    <x-slot name="pageSubHeader">Catat pergerakan stok masuk atau keluar</x-slot>

    @php
        $consumableOptions = $consumables
            ->map(
                fn($c) => [
                    'id' => (string) $c->id,
                    'label' => $c->name . ' (' . $c->code . ') — ' . $c->category->name,
                ],
            )
            ->values();
    @endphp

    <form method="POST" action="{{ route('consumable-stocks.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Detail Pergerakan</h3>
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
                                <p class="mt-1 text-[10px] text-gray-400">Otomatis terpilih sesuai akses Anda</p>
                            @endif
                            @error('hospital_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Consumable — searchable dropdown --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Consumable <span class="text-red-500">*</span>
                            </label>

                            <x-searchable-select name="consumable_id" :options="$consumableOptions"
                                :selected="old('consumable_id', request('consumable_id'))"
                                placeholder="Pilih consumable..." searchPlaceholder="Cari consumable..."
                                emptyText="Consumable tidak ditemukan" :error="$errors->has('consumable_id')" />

                            @error('consumable_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipe --}}
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition
                                              {{ old('type') === 'IN' ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50 hover:border-green-300' }}">
                                    <input type="radio" name="type" value="IN"
                                        {{ old('type', 'IN') === 'IN' ? 'checked' : '' }}
                                        class="border-gray-300 text-green-500 focus:ring-green-400" />
                                    <div>
                                        <div class="text-sm font-semibold text-green-700">Masuk (IN)</div>
                                        <div class="text-[10px] text-green-500">Tambah stok</div>
                                    </div>
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition
                                              {{ old('type') === 'OUT' ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 hover:border-red-300' }}">
                                    <input type="radio" name="type" value="OUT"
                                        {{ old('type') === 'OUT' ? 'checked' : '' }}
                                        class="border-gray-300 text-red-500 focus:ring-red-400" />
                                    <div>
                                        <div class="text-sm font-semibold text-red-700">Keluar (OUT)</div>
                                        <div class="text-[10px] text-red-500">Kurangi stok</div>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jumlah --}}
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('quantity') ? 'border-red-400' : '' }}" />
                            @error('quantity')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="moved_at"
                                value="{{ old('moved_at', now()->format('Y-m-d\TH:i')) }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('moved_at') ? 'border-red-400' : '' }}" />
                            @error('moved_at')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Catatan
                            </label>
                            <textarea name="notes" rows="3"
                                placeholder="cth. Pembelian dari supplier, digunakan untuk sterilisasi batch #123, dll."
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes') }}</textarea>
                        </div>

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
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Panduan</h3>
                    </div>
                    <div class="p-5">
                        <ul class="space-y-2.5 text-xs text-gray-500">
                            <li class="flex gap-2">
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-green-100 text-[10px] font-bold text-green-600">IN</span>
                                <span>Stok masuk — pembelian, penerimaan dari supplier, atau koreksi positif</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-red-100 text-[10px] font-bold text-red-600">OUT</span>
                                <span>Stok keluar — pemakaian, kadaluarsa, atau koreksi negatif</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-0.5 shrink-0 text-gray-400">•</span>
                                <span>Stok tidak bisa minus — jika OUT melebihi stok, akan otomatis menjadi 0</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Pergerakan
                </button>

                <div class="pb-96"></div>

            </div>
        </div>
    </form>

</x-layouts.app>
