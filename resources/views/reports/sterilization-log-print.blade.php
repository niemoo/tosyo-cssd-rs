<x-layouts.print title="Log Sterilisasi" page-size="A4 landscape" container-class="max-w-none">

    <x-report-print-header title="Log Sterilisasi" :hospital-name="$hospitalName" :from="$from" :to="$to" />

    <table class="w-full border-collapse text-[10px]">
        <thead>
            <tr class="border-b-2 border-gray-800">
                <th class="p-1.5 text-left">Tanggal</th>
                <th class="p-1.5 text-left">No. Batch</th>
                <th class="p-1.5 text-left">No. Load</th>
                <th class="p-1.5 text-left">Sterilizer</th>
                <th class="p-1.5 text-left">Metode</th>
                <th class="p-1.5 text-left">Suhu</th>
                <th class="p-1.5 text-left">Tekanan</th>
                <th class="p-1.5 text-left">Durasi</th>
                <th class="p-1.5 text-left">Bowie-Dick</th>
                <th class="p-1.5 text-left">Hasil BI</th>
                <th class="p-1.5 text-left">Operator</th>
                <th class="p-1.5 text-center">Jml Tray</th>
                <th class="p-1.5 text-left">Lulus/Gagal</th>
                <th class="p-1.5 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($batches as $batch)
                @php
                    $biPositive = $batch->bi_result === \App\Models\SterilizationBatch::BI_POSITIVE;
                    $passed = $batch->items->where('result', \App\Models\SterilizationBatchItem::RESULT_PASSED)->count();
                    $failed = $batch->items->where('result', \App\Models\SterilizationBatchItem::RESULT_FAILED)->count();
                @endphp
                <tr class="border-b border-gray-200 {{ $biPositive ? 'bg-red-50 font-semibold' : '' }}">
                    <td class="p-1.5">{{ $batch->started_at?->format('d/m/Y H:i') }}</td>
                    <td class="p-1.5">{{ $batch->batch_number }}</td>
                    <td class="p-1.5">{{ $batch->load_number }}</td>
                    <td class="p-1.5">{{ $batch->sterilizer?->name }}</td>
                    <td class="p-1.5">{{ $batch->method ?? '-' }}</td>
                    <td class="p-1.5">{{ $batch->temperature ?? '-' }}</td>
                    <td class="p-1.5">{{ $batch->pressure ?? '-' }}</td>
                    <td class="p-1.5">{{ $batch->duration_minutes ?? '-' }}</td>
                    <td class="p-1.5">{{ \App\Models\SterilizationBatch::BOWIE_DICK[$batch->bowie_dick_result]['label'] ?? '-' }}</td>
                    <td class="p-1.5 {{ $biPositive ? 'text-red-700' : '' }}">
                        {{ \App\Models\SterilizationBatch::BI_RESULTS[$batch->bi_result]['label'] ?? $batch->bi_result }}
                    </td>
                    <td class="p-1.5">{{ $batch->operator?->name }}</td>
                    <td class="p-1.5 text-center">{{ $batch->items->count() }}</td>
                    <td class="p-1.5">{{ $passed }} Lulus / {{ $failed }} Gagal</td>
                    <td class="p-1.5">{{ \App\Models\SterilizationBatch::STATUSES[$batch->status]['label'] ?? $batch->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="p-4 text-center text-gray-400">Tidak ada batch dalam periode/filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</x-layouts.print>
