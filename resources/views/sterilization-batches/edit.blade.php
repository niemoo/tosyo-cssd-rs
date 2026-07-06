<x-layouts.app title="Edit Parameter — {{ $sterilizationBatch->batch_number }}">

    <x-slot name="backButton">
        <a href="{{ route('sterilization-batches.show', $sterilizationBatch) }}"
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
        <a href="{{ route('sterilization-batches.show', $sterilizationBatch) }}"
            class="hover:text-gray-600">{{ $sterilizationBatch->batch_number }}</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Edit Parameter</span>
    </x-slot>

    <x-slot name="pageHeader">Edit Parameter Batch</x-slot>
    <x-slot name="pageSubHeader">{{ $sterilizationBatch->batch_number }}</x-slot>

    <form method="POST" action="{{ route('sterilization-batches.update', $sterilizationBatch) }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Parameter Sterilisasi</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Nomor
                                Batch</label>
                            <input type="text" value="{{ $sterilizationBatch->batch_number }}" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-400" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Waktu
                                Mulai</label>
                            <input type="datetime-local" name="started_at"
                                value="{{ old('started_at', $sterilizationBatch->started_at?->format('Y-m-d\TH:i')) }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Durasi
                                (Menit)</label>
                            <input type="number" name="duration_minutes"
                                value="{{ old('duration_minutes', $sterilizationBatch->duration_minutes) }}"
                                min="1"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Suhu
                                (°C)</label>
                            <input type="number" name="temperature"
                                value="{{ old('temperature', $sterilizationBatch->temperature) }}" step="0.01"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Tekanan
                                (Bar)</label>
                            <input type="number" name="pressure"
                                value="{{ old('pressure', $sterilizationBatch->pressure) }}" step="0.01"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">Catatan</label>
                            <textarea name="notes" rows="3"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                             focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20">{{ old('notes', $sterilizationBatch->notes) }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Info Batch</h4>
                    <div class="space-y-3">
                        @foreach ([['label' => 'Sterilizer', 'value' => $sterilizationBatch->sterilizer->name], ['label' => 'Operator', 'value' => $sterilizationBatch->operator->name], ['label' => 'Total Tray', 'value' => $sterilizationBatch->items->count() . ' tray']] as $info)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[10px] text-gray-400">{{ $info['label'] }}</span>
                                <span class="text-xs font-semibold text-gray-700">{{ $info['value'] }}</span>
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
                    Simpan Parameter
                </button>
            </div>
        </div>
    </form>

</x-layouts.app>
