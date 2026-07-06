<x-layouts.app title="Tambah Sterilizer">

    <x-slot name="backButton">
        <a href="{{ route('sterilizers.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('sterilizers.index') }}" class="hover:text-gray-600">Sterilizer</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Tambah Baru</span>
    </x-slot>

    <x-slot name="pageHeader">Tambah Sterilizer</x-slot>
    <x-slot name="pageSubHeader">Daftarkan mesin sterilizer baru</x-slot>

    <form method="POST" action="{{ route('sterilizers.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Sterilizer</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Rumah Sakit <span class="text-red-500">*</span>
                            </label>
                            @if ($userHospitals->count() > 1)
                                <select name="hospital_id"
                                    class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                               focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                               {{ $errors->has('hospital_id') ? 'border-red-400' : 'border-gray-200' }}">
                                    <option value="">Pilih Rumah Sakit</option>
                                    @foreach ($userHospitals as $hospital)
                                        <option value="{{ $hospital->id }}"
                                            {{ old('hospital_id', session('active_hospital_id')) == $hospital->id ? 'selected' : '' }}>
                                            {{ $hospital->name }}
                                        </option>
                                    @endforeach
                                </select>
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

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nama Sterilizer <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="cth. Autoclave A1"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Kode <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code') }}" placeholder="cth. STR-001"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('code') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('code')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <select name="type"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                           focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                           {{ $errors->has('type') ? 'border-red-400' : 'border-gray-200' }}">
                                <option value="">Pilih Tipe</option>
                                @foreach ($types as $key => $type)
                                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                                        {{ $type['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Kapasitas (Tray)
                            </label>
                            <input type="number" name="capacity" value="{{ old('capacity') }}" placeholder="cth. 10"
                                min="1"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                            @error('capacity')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Serial Number
                            </label>
                            <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                                placeholder="cth. SN-20240001"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                    </div>
                </div>

                {{-- Maintenance --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Jadwal Maintenance</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Maintenance Terakhir
                            </label>
                            <input type="date" name="last_maintenance_at"
                                value="{{ old('last_maintenance_at') }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                            @error('last_maintenance_at')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Maintenance Berikutnya
                            </label>
                            <input type="date" name="next_maintenance_at"
                                value="{{ old('next_maintenance_at') }}"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                            @error('next_maintenance_at')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
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
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Status</h3>
                    </div>
                    <div class="p-5">
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3.5">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Sterilizer Aktif</div>
                                <div class="text-xs text-gray-400">Dapat digunakan dalam sistem</div>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }} class="peer sr-only" />
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
                        Panduan
                    </div>
                    <ul class="space-y-1.5 text-xs text-primary-700">
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>STEAM — Autoclave uap panas</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>PLASMA — Sterilisasi suhu rendah</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>EO — Ethylene Oxide gas</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Isi jadwal maintenance untuk pengingat
                            otomatis</li>
                    </ul>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Sterilizer
                </button>

            </div>
        </div>
    </form>

</x-layouts.app>
