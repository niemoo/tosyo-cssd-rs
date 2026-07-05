<x-layouts.app title="Dashboard">

    <x-slot name="pageHeader">Dashboard</x-slot>
    <x-slot name="pageSubHeader">
        {{ now()->translatedFormat('l, d F Y') }} — Selamat datang, {{ auth()->user()->name }}
    </x-slot>
    {{-- <x-slot name="pageActions">
        <div
            class="flex items-center gap-1.5 rounded-full border border-gray-100 bg-gray-50 px-3 py-1.5 text-xs text-gray-400">
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"></span>
            Real-time · Diperbarui barusan
        </div>
    </x-slot> --}}

    {{-- KPI CARDS --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-stat-card label="Tray Steril Siap" :value="$trayStats['sterile']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Sedang Diproses" :value="$trayStats['in_process']" color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Sedang Digunakan" :value="$trayStats['distributed']" color="purple" icon="M5 12h14M12 5l7 7-7 7" />
        <x-stat-card label="Tersimpan di Rak" :value="$trayStats['stored']" color="blue"
            icon="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
    </div>

    {{-- PIPELINE STATUS --}}
    <div class="mb-6 -mx-4 sm:mx-0">
        <div class="overflow-x-auto px-4 sm:px-0 pb-2">
            <div class="flex min-w-max rounded-xl border border-gray-100 bg-white overflow-hidden">
                @foreach (\App\Models\Tray::STATUSES as $key => $val)
                    @php
                        $colorMap = [
                            'blue' => [
                                'bg' => 'hover:bg-blue-50',
                                'text' => 'text-blue-600',
                                'badge' => 'bg-blue-100 text-blue-600',
                            ],
                            'amber' => [
                                'bg' => 'hover:bg-amber-50',
                                'text' => 'text-amber-600',
                                'badge' => 'bg-amber-100 text-amber-600',
                            ],
                            'purple' => [
                                'bg' => 'hover:bg-purple-50',
                                'text' => 'text-purple-600',
                                'badge' => 'bg-purple-100 text-purple-600',
                            ],
                            'green' => [
                                'bg' => 'hover:bg-green-50',
                                'text' => 'text-green-600',
                                'badge' => 'bg-green-100 text-green-600',
                            ],
                            'teal' => [
                                'bg' => 'hover:bg-teal-50',
                                'text' => 'text-teal-600',
                                'badge' => 'bg-teal-100 text-teal-600',
                            ],
                            'gray' => [
                                'bg' => 'hover:bg-gray-50',
                                'text' => 'text-gray-500',
                                'badge' => 'bg-gray-100 text-gray-500',
                            ],
                            'red' => [
                                'bg' => 'hover:bg-red-50',
                                'text' => 'text-red-600',
                                'badge' => 'bg-red-100 text-red-600',
                            ],
                        ];
                        $c = $colorMap[$val['color']] ?? $colorMap['gray'];
                    @endphp
                    <div
                        class="flex flex-1 flex-col items-center gap-1.5 border-r border-gray-100 px-4 py-3 last:border-r-0 transition {{ $c['bg'] }} cursor-pointer">
                        <div class="text-[10px] font-semibold text-gray-400">{{ $val['label'] }}</div>
                        <div class="text-xl font-extrabold {{ $c['text'] }}">
                            {{ $trayByStatus[$key] ?? 0 }}
                        </div>
                        <div class="rounded-full px-2 py-0.5 text-[9px] font-bold {{ $c['badge'] }}">tray</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- GRID: Tray Terbaru + Alert --}}
    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Tray Terbaru --}}
        <div class="lg:col-span-2 rounded-xl border border-gray-100 bg-white">
            <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
                <h3 class="text-sm font-bold text-gray-900">Tray Terbaru</h3>
                @can('trays.view')
                    <a href="{{ route('trays.index') }}"
                        class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat semua →</a>
                @else
                    <span class="text-xs text-gray-300">Lihat semua →</span>
                @endcan
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentTrays as $tray)
                    @php
                        $statusInfo = \App\Models\Tray::STATUSES[$tray->status] ?? [
                            'label' => $tray->status,
                            'color' => 'gray',
                        ];
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                        <div
                            class="h-2 w-2 shrink-0 rounded-full
                            @if ($statusInfo['color'] === 'green') bg-green-500
                            @elseif($statusInfo['color'] === 'blue') bg-blue-500
                            @elseif($statusInfo['color'] === 'amber') bg-amber-500
                            @elseif($statusInfo['color'] === 'purple') bg-purple-500
                            @elseif($statusInfo['color'] === 'teal') bg-teal-500
                            @elseif($statusInfo['color'] === 'red') bg-red-500
                            @else bg-gray-400 @endif">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-900">{{ $tray->name }}</div>
                            <div class="truncate text-xs text-gray-400">{{ $tray->code }} ·
                                {{ $tray->template?->name ?? 'Tray Bebas' }}</div>
                        </div>
                        <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                        <div class="text-xs text-gray-300">{{ $tray->updated_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <x-empty-state title="Belum ada tray" description="Tambahkan tray untuk mulai tracking." />
                @endforelse
            </div>
        </div>

        {{-- Alert Panel --}}
        <div class="flex flex-col gap-4 sm:flex-row lg:flex-col">

            {{-- Low Stock --}}
            <div class="rounded-xl border border-gray-100 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-4 py-3">
                    <h3 class="text-sm font-bold text-gray-900">Stok Menipis</h3>
                    @if ($lowStockConsumables->count() > 0)
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600">
                            {{ $lowStockConsumables->count() }} item
                        </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($lowStockConsumables as $consumable)
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs font-semibold text-gray-900">{{ $consumable->name }}</div>
                                <div class="text-[10px] text-gray-400">Min: {{ $consumable->minimum_stock }}
                                    {{ $consumable->unit }}</div>
                            </div>
                            <span class="ml-2 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-600">
                                {{ $consumable->current_stock }} {{ $consumable->unit }}
                            </span>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-center text-xs text-gray-400">Stok semua aman</div>
                    @endforelse
                </div>
            </div>

            {{-- Maintenance Due --}}
            <div class="rounded-xl border border-gray-100 bg-white">
                <div class="flex items-center justify-between border-b border-gray-50 px-4 py-3">
                    <h3 class="text-sm font-bold text-gray-900">Maintenance Due</h3>
                    @if ($sterilizersDue->count() > 0)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-600">
                            {{ $sterilizersDue->count() }} unit
                        </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($sterilizersDue as $sterilizer)
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs font-semibold text-gray-900">{{ $sterilizer->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $sterilizer->type }}</div>
                            </div>
                            <span
                                class="ml-2 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600">
                                {{ $sterilizer->next_maintenance_at?->format('d M') ?? 'Overdue' }}
                            </span>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-center text-xs text-gray-400">Semua mesin oke</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Consumable Stock Bar --}}
    <div class="rounded-xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3.5">
            <h3 class="text-sm font-bold text-gray-900">Status Stok Consumable</h3>
            @can('consumables.view')
                <a href="{{ route('consumable-stocks.index') }}"
                    class="text-xs font-semibold text-primary-500 hover:text-primary-600">Kelola →</a>
            @else
                <span class="text-xs text-gray-300">Kelola →</span>
            @endcan
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($lowStockConsumables->merge(
                    \App\Models\Consumable::with(['stock', 'category'])
                        ->where('hospital_id', session('active_hospital_id'))
                        ->where('is_active', true)
                        ->get()
                        ->filter(fn($c) => !$c->isLowStock())
                        ->take(3)
                ) as $consumable)
                @php
                    $stock = $consumable->current_stock;
                    $maxStock = max($consumable->minimum_stock * 5, $stock, 1);
                    $pct = min(100, round(($stock / $maxStock) * 100));
                    $barColor = $pct <= 20 ? 'bg-red-500' : ($pct <= 50 ? 'bg-amber-400' : 'bg-green-500');
                @endphp
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="w-40 shrink-0 truncate text-xs font-medium text-gray-700">{{ $consumable->name }}</div>
                    <div class="flex-1">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $barColor }} transition-all"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div
                        class="w-20 text-right text-xs font-bold
                                {{ $pct <= 20 ? 'text-red-600' : ($pct <= 50 ? 'text-amber-600' : 'text-green-600') }}">
                        {{ number_format($stock) }} {{ $consumable->unit }}
                    </div>
                    @if ($consumable->isLowStock())
                        <x-badge color="red">Menipis</x-badge>
                    @else
                        <x-badge color="green">Aman</x-badge>
                    @endif
                </div>
            @empty
                <x-empty-state title="Belum ada data consumable" description="Tambahkan consumable terlebih dahulu." />
            @endforelse
        </div>
    </div>

</x-layouts.app>
