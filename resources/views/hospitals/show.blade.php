<x-layouts.app title="{{ $hospital->name }}">

    <x-slot name="backButton">
        <a href="{{ route('hospitals.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    <x-slot name="breadcrumb">
        <a href="{{ route('hospitals.index') }}" class="hover:text-gray-600">Rumah Sakit</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">{{ $hospital->name }}</span>
    </x-slot>

    <x-slot name="pageHeader">{{ $hospital->name }}</x-slot>
    <x-slot name="pageSubHeader">{{ $hospital->code }} · {{ $hospital->address ?? 'Alamat belum diisi' }}</x-slot>

    <x-slot name="pageActions">
        @can('hospitals.edit')
            <a href="{{ route('hospitals.edit', $hospital) }}"
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

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5 lg:col-span-3">
            <x-stat-card label="Unit / Bangsal" :value="$hospital->units_count" color="blue"
                icon="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            <x-stat-card label="Pengguna" :value="$hospital->users_count" color="teal"
                icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v2h5M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            <x-stat-card label="Instrumen" :value="$hospital->instruments_count" color="purple"
                icon="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
            <x-stat-card label="Tray" :value="$hospital->trays_count" color="amber"
                icon="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            <x-stat-card label="Sterilizer" :value="$hospital->sterilizers_count" color="green"
                icon="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
        </div>

        {{-- Info Detail --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- Informasi Rumah Sakit --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Rumah Sakit</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ([['label' => 'Nama', 'value' => $hospital->name], ['label' => 'Kode', 'value' => $hospital->code], ['label' => 'Telepon', 'value' => $hospital->phone ?? '-'], ['label' => 'Alamat', 'value' => $hospital->address ?? '-']] as $item)
                        <div class="flex items-start justify-between px-5 py-3">
                            <span class="w-28 shrink-0 text-xs text-gray-400">{{ $item['label'] }}</span>
                            <span class="text-right text-sm font-medium text-gray-700">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Status</span>
                        <x-badge :color="$hospital->is_active ? 'green' : 'red'" dot>
                            {{ $hospital->is_active ? 'Aktif' : 'Non-aktif' }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Pengguna Terdaftar --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                    <h3 class="text-sm font-bold text-gray-900">Pengguna Terdaftar</h3>
                    @can('users.view')
                        {{-- Uncomment setelah step 4 selesai --}}
                        <a href="{{ route('users.index') }}"
                            class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat semua →</a>
                        {{-- <span class="text-xs text-gray-300">Lihat semua →</span> --}}
                    @endcan
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-400 text-[10px] font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="truncate text-xs text-gray-400">{{ $user->username }}</div>
                            </div>
                            @if ($user->roles->isNotEmpty())
                                <x-badge color="teal">{{ $user->roles->first()->name }}</x-badge>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-xs text-gray-400">Belum ada pengguna</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Sidebar Kanan --}}
        <div class="space-y-4">

            {{-- Audit Trail --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h4 class="mb-4 text-[11px] font-bold uppercase tracking-wider text-gray-400">Audit Trail</h4>
                <div class="space-y-3">
                    @foreach ([['label' => 'Dibuat oleh', 'value' => $hospital->creator?->name ?? '-'], ['label' => 'Dibuat pada', 'value' => $hospital->created_at->format('d M Y, H:i')], ['label' => 'Diperbarui oleh', 'value' => $hospital->updater?->name ?? '-'], ['label' => 'Diperbarui pada', 'value' => $hospital->updated_at->format('d M Y, H:i')]] as $audit)
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
