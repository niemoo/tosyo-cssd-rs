<x-layouts.app title="Edit — {{ $instrumentItem->code }}">

    <x-slot name="backButton">
        <a href="{{ route('instrument-items.show', $instrumentItem) }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('instrument-items.index') }}" class="hover:text-gray-600">Item Instrumen</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <a href="{{ route('instrument-items.show', $instrumentItem) }}"
            class="hover:text-gray-600">{{ $instrumentItem->code }}</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Edit</span>
    </x-slot>

    <x-slot name="pageHeader">Edit Item Instrumen</x-slot>
    <x-slot name="pageSubHeader">{{ $instrumentItem->code }} · {{ $instrumentItem->instrument->name }}</x-slot>

    <form method="POST" action="{{ route('instrument-items.update', $instrumentItem) }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Item</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Rumah
                                Sakit</label>
                            <input type="text" value="{{ $instrumentItem->hospital->name }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Instrumen</label>
                            <input type="text"
                                value="{{ $instrumentItem->instrument->name }} ({{ $instrumentItem->instrument->code }})"
                                disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Kode
                                Item</label>
                            <input type="text" value="{{ $instrumentItem->code }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-100 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                            <p class="mt-1 text-[10px] text-gray-400">Kode tidak dapat diubah</p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Serial
                                Number</label>
                            <input type="text" name="serial_number"
                                value="{{ old('serial_number', $instrumentItem->serial_number) }}"
                                placeholder="cth. SN-20240001"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('serial_number') ? 'border-red-400' : '' }}" />
                            @error('serial_number')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Barcode</label>
                            <input type="text" name="barcode" value="{{ old('barcode', $instrumentItem->barcode) }}"
                                placeholder="cth. BC1234567890"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('barcode') ? 'border-red-400' : '' }}" />
                            @error('barcode')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div> --}}

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">RFID
                                Tag</label>
                            <input type="text" name="rfid_tag"
                                value="{{ old('rfid_tag', $instrumentItem->rfid_tag) }}" placeholder="cth. RFID-ABC123"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('rfid_tag') ? 'border-red-400' : '' }}" />
                            @error('rfid_tag')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Tanggal
                                Pembelian</label>
                            <input type="date" name="purchased_at"
                                value="{{ old('purchased_at', $instrumentItem->purchased_at?->format('Y-m-d')) }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                    </div>
                </div>
            </div>

            <div class="space-y-4">

                {{-- Kondisi --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Kondisi</h3>
                    </div>
                    <div class="p-5">
                        @error('condition')
                            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="space-y-2">
                            @foreach ($conditions as $key => $cond)
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 transition hover:border-primary-200 hover:bg-primary-50">
                                    <input type="radio" name="condition" value="{{ $key }}"
                                        {{ old('condition', $instrumentItem->condition) === $key ? 'checked' : '' }}
                                        class="border-gray-300 text-primary-500 focus:ring-primary-400" />
                                    <span class="text-sm font-medium text-gray-900">{{ $cond['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-50">
                            <svg class="h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Status</h3>
                    </div>
                    <div class="p-5">
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-3.5">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Item Aktif</div>
                                <div class="text-xs text-gray-400">Dapat digunakan dalam sistem</div>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $instrumentItem->is_active) ? 'checked' : '' }}
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

                {{-- Audit --}}
                <div class="rounded-xl border border-gray-100 bg-white p-5">
                    <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Informasi Audit</h4>
                    <div class="space-y-3">
                        @foreach ([['label' => 'Dibuat oleh', 'value' => $instrumentItem->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $instrumentItem->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $instrumentItem->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $instrumentItem->updated_at->format('d M Y, H:i')]] as $audit)
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
    </form>

</x-layouts.app>
