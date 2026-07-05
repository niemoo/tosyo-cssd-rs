<x-layouts.app title="Stok Consumable">

    <x-slot name="pageHeader">Stok Consumable</x-slot>
    <x-slot name="pageSubHeader">Pantau stok consumable CSSD secara real-time</x-slot>
    <x-slot name="pageActions">
        @can('consumables.create')
            <a href="{{ route('consumable-stocks.movements') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Riwayat
            </a>
            <a href="{{ route('consumable-stocks.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Input Stok
            </a>
        @endcan
    </x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <x-stat-card label="Total Item" :value="$stats['total_items']" color="teal"
            icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        <x-stat-card label="Stok Menipis" :value="$stats['low_stock']" color="amber"
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        <x-stat-card label="Stok Habis" :value="$stats['out_of_stock']" color="red"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gray-100 bg-white p-4">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau kode consumable..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS Saya</option>
                    @foreach ($userHospitals as $hospital)
                        <option value="{{ $hospital->id }}"
                            {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                            {{ $hospital->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="category_id"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="stock"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Stok</option>
                <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Stok Menipis</option>
            </select>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'category_id', 'hospital_id', 'stock']))
                <a href="{{ route('consumable-stocks.index') }}"
                    class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $stocks->firstItem() }}–{{ $stocks->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $stocks->total() }}</span> item
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
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Kategori
                        </th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="quantity" label="Stok" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Min. Stok
                        </th>
                        <th
                            class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Kondisi
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="last_updated_at" label="Update Terakhir" :current-sort="$sortBy"
                                :current-direction="$sortDir" />
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($stocks as $stock)
                        @php
                            $isLow = $stock->quantity <= $stock->consumable->minimum_stock;
                            $isEmpty = $stock->quantity === 0;
                        @endphp
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-900">{{ $stock->consumable->name }}</div>
                                <div class="text-xs text-gray-400">{{ $stock->consumable->code }}</div>
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $stock->hospital->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $stock->hospital->code }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                <x-badge color="blue">{{ $stock->consumable->category->name }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="text-sm font-bold {{ $isEmpty ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-gray-900') }}">
                                    {{ number_format($stock->quantity) }}
                                </span>
                                <div class="text-[10px] text-gray-400">{{ $stock->consumable->unit }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="text-sm text-gray-500">{{ number_format($stock->consumable->minimum_stock) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($isEmpty)
                                    <x-badge color="red" dot>Habis</x-badge>
                                @elseif($isLow)
                                    <x-badge color="amber" dot>Menipis</x-badge>
                                @else
                                    <x-badge color="green" dot>Aman</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($stock->last_updated_at)
                                    <div class="text-xs text-gray-600">{{ $stock->last_updated_at->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $stock->last_updated_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @can('consumables.create')
                                    <a href="{{ route('consumable-stocks.create', ['consumable_id' => $stock->consumable_id]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Input
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 8 : 7 }}">
                                <x-empty-state title="Belum ada data stok"
                                    description="Stok akan muncul setelah ada pergerakan consumable."
                                    action-label="Input Stok" action-route="consumable-stocks.create"
                                    icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stocks->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $stocks->links('components.pagination') }}
            </div>
        @endif
    </div>

</x-layouts.app>
