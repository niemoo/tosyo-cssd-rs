<x-layouts.app title="Laporan Produktivitas">

    <x-slot name="breadcrumb">
        <a href="{{ route('reports.index') }}" class="hover:text-gray-600">Laporan</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Produktivitas</span>
    </x-slot>

    <x-slot name="pageHeader">Produktivitas</x-slot>
    <x-slot name="pageSubHeader">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }} · {{ $hospitalName }}</x-slot>

    <x-slot name="pageActions">
        <a href="{{ route('reports.productivity.print', request()->query()) }}" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </a>
        <a href="{{ route('reports.productivity.csv', request()->query()) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Unduh CSV
        </a>
    </x-slot>

    {{-- Filter periode --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-gray-400">Dari</label>
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sampai</label>
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-primary-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-400/20" />
            </div>

            @if ($multiHospital)
                <x-searchable-select name="hospital_id" placeholder="Semua RS"
                    :options="$userHospitals->map(fn ($h) => ['id' => (string) $h->id, 'label' => $h->name])->values()"
                    :selected="request('hospital_id')" />
            @endif

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card label="Total Tray Disterilkan" :value="$summary['total_trays']" color="blue"
            icon="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
        <x-stat-card label="Total Load" :value="$summary['total_loads']" color="purple"
            icon="M22 12h-4l-3 9L9 3l-3 9H2" />
        <x-stat-card label="Tingkat Kegagalan" :value="$summary['failure_rate'] . '%'"
            :color="$summary['failure_rate'] > 10 ? 'red' : 'green'"
            icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Tray Kedaluwarsa" :value="$summary['expired_trays']" color="amber"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Per Operator --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-50 px-5 py-3.5">
                <h3 class="text-sm font-bold text-gray-900">Per Operator</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-50 bg-gray-50/50">
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Operator</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Load</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tray</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Gagal (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($byOperator as $row)
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-medium text-gray-900">{{ $row['name'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-700">{{ $row['loads'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-700">{{ $row['trays'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs {{ $row['failure_rate'] > 10 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                    {{ $row['failure_rate'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Per Sterilizer --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-50 px-5 py-3.5">
                <h3 class="text-sm font-bold text-gray-900">Per Sterilizer</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-50 bg-gray-50/50">
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sterilizer</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Siklus</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tray</th>
                            <th class="px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Gagal (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($bySterilizer as $row)
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-medium text-gray-900">{{ $row['name'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-700">{{ $row['loads'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-700">{{ $row['trays'] }}</td>
                                <td class="px-4 py-2.5 text-center text-xs {{ $row['failure_rate'] > 10 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                    {{ $row['failure_rate'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>
