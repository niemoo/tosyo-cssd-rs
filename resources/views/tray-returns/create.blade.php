<x-layouts.app title="Catat Pengembalian — {{ $distributionRequest->request_number }}">

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
        <span class="text-gray-700">Pengembalian</span>
    </x-slot>

    <x-slot name="pageHeader">Catat Pengembalian Tray</x-slot>
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
        $oldReturns = old('returns', []);
    @endphp

    <form method="POST" action="{{ route('tray-returns.store', $distributionRequest) }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                <div class="rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Tray yang Dikembalikan</h3>
                        <span class="ml-auto text-xs text-gray-400">{{ $returnableItems->count() }} tray sedang
                            digunakan</span>
                    </div>

                    <div class="p-5 space-y-4">
                        @foreach ($returnableItems as $index => $item)
                            @php
                                $tray = $item->tray;
                                $oldCondition = $oldReturns[$index]['condition'] ?? 'GOOD';
                            @endphp
                            <div class="rounded-lg border border-gray-100 p-4" x-data="{ condition: '{{ $oldCondition }}' }">

                                <input type="hidden" name="returns[{{ $index }}][tray_id]"
                                    value="{{ $tray->id }}" />

                                <div class="mb-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $tray->name }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ $tray->code }} · {{ $tray->template?->name ?? 'Tray Bebas' }}
                                        </div>
                                    </div>
                                    <x-badge color="teal" dot>Sedang Digunakan</x-badge>
                                </div>

                                {{-- Kondisi --}}
                                <label
                                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                    Kondisi Pengembalian <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2.5 transition"
                                        :class="condition === 'GOOD' ? 'border-green-400 bg-green-50' :
                                            'border-gray-200 hover:border-green-300'">
                                        <input type="radio" name="returns[{{ $index }}][condition]"
                                            value="GOOD" x-model="condition"
                                            class="border-gray-300 text-green-500 focus:ring-green-400" />
                                        <span class="text-xs font-semibold text-green-700">Baik</span>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2.5 transition"
                                        :class="condition === 'DAMAGED' ? 'border-red-400 bg-red-50' :
                                            'border-gray-200 hover:border-red-300'">
                                        <input type="radio" name="returns[{{ $index }}][condition]"
                                            value="DAMAGED" x-model="condition"
                                            class="border-gray-300 text-red-500 focus:ring-red-400" />
                                        <span class="text-xs font-semibold text-red-700">Rusak</span>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2.5 transition"
                                        :class="condition === 'INCOMPLETE' ? 'border-amber-400 bg-amber-50' :
                                            'border-gray-200 hover:border-amber-300'">
                                        <input type="radio" name="returns[{{ $index }}][condition]"
                                            value="INCOMPLETE" x-model="condition"
                                            class="border-gray-300 text-amber-500 focus:ring-amber-400" />
                                        <span class="text-xs font-semibold text-amber-700">Tidak Lengkap</span>
                                    </label>
                                </div>

                                {{-- Missing items, shown only if INCOMPLETE --}}
                                <div x-show="condition === 'INCOMPLETE'" x-cloak class="mt-3">
                                    <label
                                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                        Instrumen yang Hilang/Tidak Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="returns[{{ $index }}][missing_items]" rows="2"
                                        placeholder="cth. Gunting Bedah Mayo (1 unit), Klem Mosquito (2 unit)"
                                        class="w-full resize-none rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                                     focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">{{ $oldReturns[$index]['missing_items'] ?? '' }}</textarea>
                                </div>

                                {{-- Notes --}}
                                <div class="mt-3">
                                    <label
                                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                                    <textarea name="returns[{{ $index }}][notes]" rows="2" placeholder="Catatan tambahan (opsional)..."
                                        class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                                     focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ $oldReturns[$index]['notes'] ?? '' }}</textarea>
                                </div>

                                {{-- Tanggal Pengembalian --}}
                                <div class="mt-3">
                                    <label
                                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                        Tanggal Pengembalian <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" name="returns[{{ $index }}][returned_at]"
                                        value="{{ $oldReturns[$index]['returned_at'] ?? now()->format('Y-m-d\TH:i') }}"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                                  focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                                </div>

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
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Kondisi Baik → tray siap masuk siklus
                            ulang</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Rusak / Tidak Lengkap → tray otomatis
                            berstatus Perlu Diproses Ulang</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Jika semua tray sudah dikembalikan,
                            permintaan otomatis menjadi Selesai</li>
                    </ul>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Pengembalian
                </button>

            </div>
        </div>

        <div class="pb-96"></div>

    </form>

</x-layouts.app>
