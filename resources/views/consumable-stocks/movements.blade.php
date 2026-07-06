<x-layouts.app title="Riwayat Pergerakan Stok">

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
        <span class="text-gray-700">Riwayat Pergerakan</span>
    </x-slot>

    <x-slot name="pageHeader">Riwayat Pergerakan Stok</x-slot>
    <x-slot name="pageSubHeader">Semua pergerakan stok consumable</x-slot>

    <x-slot name="pageActions">
        @can('consumables.create')
            <a href="{{ route('consumable-stocks.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Input Stok
            </a>
        @endcan
    </x-slot>

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari consumable..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS Saya</option>
                    @foreach ($userHospitals as $hospital)
                        <option value="{{ $hospital->id }}"
                            {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                            {{ $hospital->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="consumable_id"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Consumable</option>
                @foreach ($consumables as $consumable)
                    <option value="{{ $consumable->id }}"
                        {{ request('consumable_id') == $consumable->id ? 'selected' : '' }}>
                        {{ $consumable->name }}
                    </option>
                @endforeach
            </select>

            <select name="type"
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Tipe</option>
                <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
            </select>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'type', 'hospital_id', 'consumable_id']))
                <a href="{{ route('consumable-stocks.movements') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $movements->firstItem() }}–{{ $movements->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $movements->total() }}</span> pergerakan
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Consumable
                        </th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Rumah Sakit
                            </th>
                        @endif
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Tipe
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Jumlah
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Catatan
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Ditangani
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Tanggal
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movements as $movement)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $movement->consumable->name }}</div>
                                <div class="text-xs text-gray-400">{{ $movement->consumable->code }}</div>
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $movement->hospital->name }}
                                    </div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5 text-center">
                                @if ($movement->type === 'IN')
                                    <x-badge color="green">IN</x-badge>
                                @else
                                    <x-badge color="red">OUT</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="text-sm font-bold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                                </span>
                                <div class="text-[10px] text-gray-400">{{ $movement->consumable->unit }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="line-clamp-2 text-xs text-gray-500">{{ $movement->notes ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-600">{{ $movement->handler->name ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-xs text-gray-600">{{ $movement->moved_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $movement->moved_at->format('H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 7 : 6 }}">
                                <x-empty-state title="Belum ada riwayat pergerakan"
                                    description="Riwayat akan muncul setelah ada input stok."
                                    action-label="Input Stok" action-route="consumable-stocks.create"
                                    icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $movements->links('components.pagination') }}
            </div>
        @endif
    </div>

</x-layouts.app>
