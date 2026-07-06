<x-layouts.app title="Tambah Pengguna">

    <x-slot name="backButton">
        <a href="{{ route('users.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('users.index') }}" class="hover:text-gray-600">Pengguna</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Tambah Baru</span>
    </x-slot>

    <x-slot name="pageHeader">Tambah Pengguna</x-slot>
    <x-slot name="pageSubHeader">Daftarkan pengguna baru ke dalam sistem</x-slot>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Form Utama --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Informasi Dasar --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi Dasar</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="cth. Dr. Budi Santoso"
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
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="username" value="{{ old('username') }}"
                                placeholder="cth. budi.santoso"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('username') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('username')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Telepon
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                placeholder="cth. 08111000001" inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                            @error('phone')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Password --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Password</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('password') ? 'border-red-400' : 'border-gray-200' }}" />
                            @error('password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" placeholder="Ulangi password"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
                        </div>

                    </div>
                </div>

                {{-- Rumah Sakit --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-3.5 w-3.5 text-green-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Rumah Sakit</h3>
                    </div>
                    <div class="p-5">
                        @error('hospital_ids')
                            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($hospitals as $hospital)
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 transition hover:border-primary-200 hover:bg-primary-50">
                                    <input type="checkbox" name="hospital_ids[]" value="{{ $hospital->id }}"
                                        {{ in_array($hospital->id, old('hospital_ids', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $hospital->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $hospital->code }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar Kanan --}}
            <div class="space-y-4">

                {{-- Role --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-3.5 w-3.5 text-purple-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Role</h3>
                    </div>
                    <div class="p-5">
                        @error('role')
                            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="space-y-2">
                            @foreach ($roles as $role)
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 transition hover:border-primary-200 hover:bg-primary-50">
                                    <input type="radio" name="role" value="{{ $role->name }}"
                                        {{ old('role') === $role->name ? 'checked' : '' }}
                                        class="border-gray-300 text-primary-500 focus:ring-primary-400" />
                                    <span class="text-sm font-medium text-gray-900">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Status --}}
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
                                <div class="text-sm font-medium text-gray-900">Pengguna Aktif</div>
                                <div class="text-xs text-gray-400">Dapat login ke sistem</div>
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

                {{-- Tombol Simpan --}}
                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                    <svg class="mr-1.5 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Pengguna
                </button>

            </div>
        </div>
    </form>

</x-layouts.app>
