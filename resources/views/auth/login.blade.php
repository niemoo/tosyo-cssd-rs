<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — TosyoCSSD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-full items-center justify-center bg-gray-50 font-sans">

    {{-- Background grid --}}
    <div class="pointer-events-none fixed inset-0"
        style="background-image: linear-gradient(rgba(15,110,86,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(15,110,86,0.04) 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <div class="relative z-10 w-full max-w-sm px-4">
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-primary-600 via-primary-500 to-primary-400 px-9 pt-8 pb-7">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/15">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-base font-extrabold tracking-tight text-white">TosyoCSSD</div>
                        <div class="text-[10px] font-normal uppercase tracking-widest text-white/60">Enterprise Smart
                            CSSD</div>
                    </div>
                </div>
                <h1 class="text-xl font-bold text-white">Selamat Datang</h1>
                <p class="mt-1 text-sm text-white/60">Masuk untuk mengakses sistem CSSD</p>
            </div>

            {{-- Form --}}
            <div class="px-9 py-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                            Username
                        </label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input type="text" name="username" value="{{ old('username') }}" autofocus
                                placeholder="Masukkan username"
                                class="w-full rounded-lg border bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('username') ? 'border-red-400' : 'border-gray-200' }}" />
                        </div>
                        @error('username')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                            Password
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input :type="show ? 'text' : 'password'" name="password" placeholder="Masukkan password"
                                class="w-full rounded-lg border bg-gray-50 py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-300 transition
                                          focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20
                                          {{ $errors->has('password') ? 'border-red-400' : 'border-gray-200' }}" />
                            <button type="button" @click="show = !show"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path x-show="!show" stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path x-show="show" stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-500">
                            <input type="checkbox" name="remember"
                                class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 hover:-translate-y-0.5 active:translate-y-0">
                        Masuk ke Sistem
                    </button>

                </form>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
