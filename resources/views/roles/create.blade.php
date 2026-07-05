<x-layouts.app title="Tambah Role">

    <x-slot name="backButton">
        <a href="{{ route('roles.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('roles.index') }}" class="hover:text-gray-600">Role & Akses</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Tambah Baru</span>
    </x-slot>

    <x-slot name="pageHeader">Tambah Role</x-slot>
    <x-slot name="pageSubHeader">Buat role baru dan atur hak aksesnya</x-slot>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Form Utama --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Nama Role --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-3.5 w-3.5 text-purple-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Role</h3>
                    </div>
                    <div class="p-5">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="cth. Supervisor CSSD"
                            class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                      focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                      {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}" />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Permissions --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                    <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                                <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Permissions</h3>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" id="select-all" class="peer sr-only"
                                onchange="document.querySelectorAll('input.permission-cb, input.module-toggle').forEach(cb => cb.checked = this.checked)" />
                            <div
                                class="peer relative h-5 w-9 rounded-full bg-gray-200 transition-colors
                peer-checked:bg-primary-400
                after:absolute after:left-0.5 after:top-0.5
                after:h-4 after:w-4 after:rounded-full
                after:bg-white after:shadow after:transition-all after:content-['']
                peer-checked:after:translate-x-4">
                            </div>
                            Pilih Semua
                        </label>
                    </div>
                    <x-permission-table :permissions="$permissions" :actions="$actions" :checked-ids="old('permissions', [])" />
                </div>

            </div>

            {{-- Sidebar Kanan --}}
            <div class="space-y-4">

                {{-- Info --}}
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
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Nama role harus unik</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Pilih permission yang sesuai dengan
                            tanggung jawab role</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Permission bisa diubah kapan saja</li>
                        <li class="flex gap-1.5"><span class="shrink-0">•</span>Role Admin otomatis mendapat semua
                            permission</li>
                    </ul>
                </div>

                {{-- Tombol Simpan --}}
                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Role
                </button>

            </div>
        </div>
    </form>

</x-layouts.app>
