<x-layouts.app title="Log Sterilisasi">

    <x-slot name="breadcrumb">
        <a href="{{ route('reports.index') }}" class="hover:text-gray-600">Laporan</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6" />
        </svg>
        <span class="text-gray-700">Log Sterilisasi</span>
    </x-slot>

    <x-slot name="pageHeader">Log Sterilisasi</x-slot>
    <x-slot name="pageSubHeader">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }} · {{ $hospitalName }}</x-slot>

    <x-slot name="pageActions">
        <a href="{{ route('reports.sterilization-log.print', request()->query()) }}" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </a>
        <a href="{{ route('reports.sterilization-log.csv', request()->query()) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Unduh CSV
        </a>
    </x-slot>

    {{-- Filter --}}
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

            <x-searchable-select name="sterilizer_id" placeholder="Semua Sterilizer"
                :options="$sterilizers->map(fn ($s) => ['id' => (string) $s->id, 'label' => $s->name])->values()"
                :selected="request('sterilizer_id')" />

            <x-searchable-select name="bi_result" placeholder="Semua Hasil BI"
                :options="collect(\App\Models\SterilizationBatch::BI_RESULTS)->map(fn ($val, $key) => ['id' => $key, 'label' => $val['label']])->values()"
                :selected="request('bi_result')" />

            <x-searchable-select name="status" placeholder="Semua Status"
                :options="collect(\App\Models\SterilizationBatch::STATUSES)->map(fn ($val, $key) => ['id' => $key, 'label' => $val['label']])->values()"
                :selected="request('status')" />

            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                Filter
            </button>

            @if (request()->hasAny(['from', 'to', 'sterilizer_id', 'bi_result', 'status', 'hospital_id']))
                <a href="{{ route('reports.sterilization-log') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-50 px-5 py-3.5">
            <p class="text-xs text-gray-400">
                <span class="font-semibold text-gray-700">{{ $batches->count() }}</span> batch dalam periode ini
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px]">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">No. Batch</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">No. Load</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Sterilizer</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Metode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Suhu</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Tekanan</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Durasi</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Bowie-Dick</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Hasil BI</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Operator</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Jml Tray</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Lulus/Gagal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($batches as $batch)
                        @php
                            $biPositive = $batch->bi_result === \App\Models\SterilizationBatch::BI_POSITIVE;
                            $statusInfo = \App\Models\SterilizationBatch::STATUSES[$batch->status] ?? ['label' => $batch->status, 'color' => 'gray'];
                            $biInfo = \App\Models\SterilizationBatch::BI_RESULTS[$batch->bi_result] ?? ['label' => $batch->bi_result, 'color' => 'gray'];
                            $passed = $batch->items->where('result', \App\Models\SterilizationBatchItem::RESULT_PASSED)->count();
                            $failed = $batch->items->where('result', \App\Models\SterilizationBatchItem::RESULT_FAILED)->count();
                        @endphp
                        <tr class="{{ $biPositive ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->started_at?->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-gray-900">{{ $batch->batch_number }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->load_number }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->sterilizer?->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->method ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->temperature ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->pressure ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->duration_minutes ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                {{ \App\Models\SterilizationBatch::BOWIE_DICK[$batch->bowie_dick_result]['label'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <x-badge :color="$biInfo['color']" dot>{{ $biInfo['label'] }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $batch->operator?->name }}</td>
                            <td class="px-4 py-3 text-center text-xs font-semibold text-gray-900">{{ $batch->items->count() }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <span class="text-green-600 font-semibold">{{ $passed }} Lulus</span> /
                                <span class="text-red-600 font-semibold">{{ $failed }} Gagal</span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <x-badge :color="$statusInfo['color']" dot>{{ $statusInfo['label'] }}</x-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-8 text-center text-sm text-gray-400">
                                Tidak ada batch sterilisasi dalam periode/filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
