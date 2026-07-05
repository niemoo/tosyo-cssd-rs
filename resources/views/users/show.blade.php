<x-layouts.app title="{{ $user->name }}">

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
        <span class="text-gray-700">{{ $user->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $user->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $user->username }} · {{ $user->phone ?? 'Telepon belum diisi' }}</x-slot>

    <x-slot name="pageActions">
        @can('users.edit')
            <a href="{{ route('users.edit', $user) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Info Detail --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- Profile Card --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Pengguna</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama Lengkap', 'value' => $user->name], ['label' => 'Username', 'value' => $user->username], ['label' => 'Telepon', 'value' => $user->phone ?? '-'], ['label' => 'Login Terakhir', 'value' => $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah login']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-32 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$user->is_active ? 'green' : 'red'" dot>
                            {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Rumah Sakit --}}
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Rumah Sakit Terdaftar</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($user->hospitals as $hospital)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $hospital->name }}</div>
                                <div class="text-xs text-gray-400">{{ $hospital->code }}</div>
                            </div>
                            <x-badge :color="$hospital->pivot->is_active ? 'green' : 'gray'" dot>
                                {{ $hospital->pivot->is_active ? 'Aktif' : 'Non-aktif' }}
                            </x-badge>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-xs text-gray-400">Belum terdaftar di rumah sakit manapun
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Role --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Role & Akses</h4>
                @if ($user->roles->isNotEmpty())
                    <x-badge color="teal">{{ $user->roles->first()->name }}</x-badge>
                @else
                    <span class="text-xs text-gray-400">Belum ada role</span>
                @endif
            </div>

            {{-- Audit Trail --}}
            <div class="rounded-xl border border-gray-100 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $user->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $user->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $user->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $user->updated_at->format('d M Y, H:i')]] as $audit)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] text-gray-400">{{ $audit['label'] }}</span>
                            <span class="text-xs font-semibold text-gray-700">{{ $audit['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
