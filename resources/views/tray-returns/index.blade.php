<x-layouts.app title="Riwayat Pengembalian">

    <x-slot name="pageHeader">Riwayat Pengembalian Tray</x-slot>
    <x-slot name="pageSubHeader">Semua pengembalian tray dari unit/bangsal</x-slot>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-4 gap-4">
        <x-stat-card label="Total Pengembalian" :value="$stats['total']" color="teal"
            icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        <x-stat-card label="Kondisi Baik" :value="$stats['good']" color="green"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Rusak" :value="$stats['damaged']" color="red"
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        <x-stat-card label="Tidak Lengkap" :value="$stats['incomplete']" color="amber"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    {{-- Filter --}}
    <div class="mb-4 rounded-xl border border-gray-100 bg-white p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div
                class="flex flex-1 items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 min-w-[200px]">
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode tray atau nomor permintaan..."
                    class="w-full border-none bg-transparent p-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 focus:outline-none" />
            </div>

            @if ($multiHospital)
                <select name="hospital_id"
                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                    <option value="">Semua RS</option>
                    @foreach ($userHospitals as $h)
                        <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="condition"
                class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-400/20">
                <option value="">Semua Kondisi</option>
                @foreach (\App\Models\TrayReturn::CONDITIONS as $key => $val)
                    <option value="{{ $key }}" {{ request('condition') === $key ? 'selected' : '' }}>
                        {{ $val['label'] }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['search', 'condition', 'hospital_id']))
                <a href="{{ route('tray-returns.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div class="border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                Menampilkan <span
                    class="font-semibold text-gray-700">{{ $returns->firstItem() }}–{{ $returns->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $returns->total() }}</span> pengembalian
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Tray</th>
                        @if ($multiHospital)
                            <th
                                class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                RS</th>
                        @endif
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            No. Permintaan</th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Unit Asal</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="condition" label="Kondisi" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                        <th
                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            Diterima oleh</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">
                            <x-sort-header column="returned_at" label="Tanggal" :current-sort="$sortBy" :current-direction="$sortDir" />
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($returns as $return)
                        @php $condInfo = \App\Models\TrayReturn::CONDITIONS[$return->condition] ?? ['label' => $return->condition, 'color' => 'gray']; @endphp
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-semibold text-gray-900">{{ $return->tray->name }}</div>
                                <div class="text-xs text-gray-400">{{ $return->tray->code }}</div>
                            </td>
                            @if ($multiHospital)
                                <td class="px-5 py-3.5">
                                    <div class="text-xs font-medium text-gray-700">{{ $return->hospital->name }}</div>
                                </td>
                            @endif
                            <td class="px-5 py-3.5">
                                <a href="{{ route('distribution-requests.show', $return->distributionRequest) }}"
                                    class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600 hover:bg-gray-200">
                                    {{ $return->distributionRequest->request_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-xs font-medium text-gray-700">
                                    {{ $return->distributionRequest->unit->name }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <x-badge :color="$condInfo['color']" dot>{{ $condInfo['label'] }}</x-badge>
                                @if ($return->missing_items)
                                    <div class="mt-0.5 text-[10px] text-red-500">{{ $return->missing_items }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-600">{{ $return->receiver->name }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-xs text-gray-700">{{ $return->returned_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $return->returned_at->format('H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $multiHospital ? 7 : 6 }}">
                                <x-empty-state title="Belum ada riwayat pengembalian"
                                    description="Riwayat akan muncul setelah CSSD mencatat pengembalian tray dari unit."
                                    icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($returns->hasPages())
            <div class="border-t border-gray-50 px-5 py-3.5">
                {{ $returns->links('components.pagination') }}
            </div>
        @endif
    </div>

</x-layouts.app>
