<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\InstrumentItem;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Sterilizer;
use App\Models\TrayLifecycleEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    // ------------------------------------------------------------------
    // Log Sterilisasi — dokumen akreditasi inti. Filter hasil BI di sini
    // ADALAH "Log Indikator Biologis" yang diminta PPI — jangan bikin laporan terpisah.
    // ------------------------------------------------------------------

    public function sterilizationLog(Request $request): View
    {
        $data = $this->buildSterilizationLog($request);

        return view('reports.sterilization-log', $data);
    }

    public function sterilizationLogPrint(Request $request): View
    {
        $data = $this->buildSterilizationLog($request);

        return view('reports.sterilization-log-print', $data);
    }

    public function sterilizationLogCsv(Request $request): StreamedResponse
    {
        $data = $this->buildSterilizationLog($request);

        $filename = $this->csvFilename('log-sterilisasi', $data['from'], $data['to']);

        return $this->streamCsv($filename, function ($out) use ($data) {
            fputcsv($out, [
                'Tanggal', 'No. Batch', 'No. Load', 'Sterilizer', 'Metode',
                'Suhu', 'Tekanan', 'Durasi (menit)', 'Uji Bowie-Dick', 'Hasil BI',
                'Operator', 'Jumlah Tray', 'Lulus', 'Gagal', 'Status Batch',
            ]);

            foreach ($data['batches'] as $batch) {
                fputcsv($out, [
                    $batch->started_at?->format('Y-m-d H:i'),
                    $batch->batch_number,
                    $batch->load_number,
                    $batch->sterilizer?->name,
                    $batch->method,
                    $batch->temperature,
                    $batch->pressure,
                    $batch->duration_minutes,
                    SterilizationBatch::BOWIE_DICK[$batch->bowie_dick_result]['label'] ?? $batch->bowie_dick_result,
                    SterilizationBatch::BI_RESULTS[$batch->bi_result]['label'] ?? $batch->bi_result,
                    $batch->operator?->name,
                    $batch->items->count(),
                    $batch->items->where('result', SterilizationBatchItem::RESULT_PASSED)->count(),
                    $batch->items->where('result', SterilizationBatchItem::RESULT_FAILED)->count(),
                    SterilizationBatch::STATUSES[$batch->status]['label'] ?? $batch->status,
                ]);
            }
        });
    }

    private function buildSterilizationLog(Request $request): array
    {
        [$from, $to] = $this->resolvePeriod($request);
        $scope = $this->resolveHospitalScope($request);

        $query = SterilizationBatch::with(['sterilizer', 'operator', 'items'])
            ->whereIn('hospital_id', $scope['hospitalIds'])
            ->whereBetween('started_at', [$from, $to]);

        if ($request->filled('sterilizer_id')) {
            $query->where('sterilizer_id', $request->sterilizer_id);
        }

        if ($request->filled('bi_result')) {
            $query->where('bi_result', $request->bi_result);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('started_at')->get();

        $sterilizers = Sterilizer::whereIn('hospital_id', $scope['hospitalIds'])
            ->orderBy('name')
            ->get();

        return compact('batches', 'from', 'to', 'sterilizers') + $scope;
    }

    // ------------------------------------------------------------------
    // Produktivitas
    // ------------------------------------------------------------------

    public function productivity(Request $request): View
    {
        $data = $this->buildProductivity($request);

        return view('reports.productivity', $data);
    }

    public function productivityPrint(Request $request): View
    {
        $data = $this->buildProductivity($request);

        return view('reports.productivity-print', $data);
    }

    public function productivityCsv(Request $request): StreamedResponse
    {
        $data = $this->buildProductivity($request);

        $filename = $this->csvFilename('produktivitas', $data['from'], $data['to']);

        return $this->streamCsv($filename, function ($out) use ($data) {
            fputcsv($out, ['Ringkasan Periode']);
            fputcsv($out, ['Total Tray Disterilkan', $data['summary']['total_trays']]);
            fputcsv($out, ['Total Load', $data['summary']['total_loads']]);
            fputcsv($out, ['Tingkat Kegagalan (%)', $data['summary']['failure_rate']]);
            fputcsv($out, ['Tray Kedaluwarsa', $data['summary']['expired_trays']]);
            fputcsv($out, []);

            fputcsv($out, ['Per Operator']);
            fputcsv($out, ['Operator', 'Jumlah Load', 'Jumlah Tray', 'Gagal', 'Tingkat Kegagalan (%)']);
            foreach ($data['byOperator'] as $row) {
                fputcsv($out, [$row['name'], $row['loads'], $row['trays'], $row['failed'], $row['failure_rate']]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Per Sterilizer']);
            fputcsv($out, ['Sterilizer', 'Jumlah Siklus', 'Jumlah Tray', 'Gagal', 'Tingkat Kegagalan (%)']);
            foreach ($data['bySterilizer'] as $row) {
                fputcsv($out, [$row['name'], $row['loads'], $row['trays'], $row['failed'], $row['failure_rate']]);
            }
        });
    }

    private function buildProductivity(Request $request): array
    {
        [$from, $to] = $this->resolvePeriod($request);
        $scope = $this->resolveHospitalScope($request);

        $batches = SterilizationBatch::with(['operator', 'sterilizer', 'items'])
            ->whereIn('hospital_id', $scope['hospitalIds'])
            ->whereBetween('started_at', [$from, $to])
            ->get();

        $resolvedItems = $batches->flatMap->items
            ->whereIn('result', [SterilizationBatchItem::RESULT_PASSED, SterilizationBatchItem::RESULT_FAILED]);

        $totalTrays = $resolvedItems->count();
        $failedTrays = $resolvedItems->where('result', SterilizationBatchItem::RESULT_FAILED)->count();

        $expiredTrays = TrayLifecycleEvent::whereIn('hospital_id', $scope['hospitalIds'])
            ->where('event_type', 'EXPIRE')
            ->whereBetween('performed_at', [$from, $to])
            ->count();

        $summary = [
            'total_trays' => $totalTrays,
            'total_loads' => $batches->count(),
            'failure_rate' => $this->rate($failedTrays, $totalTrays),
            'expired_trays' => $expiredTrays,
        ];

        $byOperator = $batches->groupBy('operator_id')->map(function (Collection $group) {
            $items = $group->flatMap->items
                ->whereIn('result', [SterilizationBatchItem::RESULT_PASSED, SterilizationBatchItem::RESULT_FAILED]);
            $failed = $items->where('result', SterilizationBatchItem::RESULT_FAILED)->count();
            $total = $items->count();

            return [
                'name' => $group->first()->operator?->name ?? '-',
                'loads' => $group->count(),
                'trays' => $total,
                'failed' => $failed,
                'failure_rate' => $this->rate($failed, $total),
            ];
        })->sortByDesc('loads')->values();

        $bySterilizer = $batches->groupBy('sterilizer_id')->map(function (Collection $group) {
            $items = $group->flatMap->items
                ->whereIn('result', [SterilizationBatchItem::RESULT_PASSED, SterilizationBatchItem::RESULT_FAILED]);
            $failed = $items->where('result', SterilizationBatchItem::RESULT_FAILED)->count();
            $total = $items->count();

            return [
                'name' => $group->first()->sterilizer?->name ?? '-',
                'loads' => $group->count(),
                'trays' => $total,
                'failed' => $failed,
                'failure_rate' => $this->rate($failed, $total),
            ];
        })->sortByDesc('loads')->values();

        return compact('from', 'to', 'summary', 'byOperator', 'bySterilizer') + $scope;
    }

    private function rate(int $part, int $total): float
    {
        return $total > 0 ? round($part / $total * 100, 1) : 0.0;
    }

    // ------------------------------------------------------------------
    // Umur Instrumen
    // ------------------------------------------------------------------

    public function instrumentLifespan(Request $request): View
    {
        $data = $this->buildInstrumentLifespan($request);

        return view('reports.instrument-lifespan', $data);
    }

    public function instrumentLifespanPrint(Request $request): View
    {
        $data = $this->buildInstrumentLifespan($request);

        return view('reports.instrument-lifespan-print', $data);
    }

    public function instrumentLifespanCsv(Request $request): StreamedResponse
    {
        $data = $this->buildInstrumentLifespan($request);

        // Laporan ini tidak terikat periode — nama file cukup tanggal cetak.
        $filename = 'umur-instrumen_'.now()->format('Y-m-d').'.csv';

        return $this->streamCsv($filename, function ($out) use ($data) {
            fputcsv($out, ['Kode', 'Nama', 'Kategori', 'Kondisi', 'Total Siklus', 'Umur (siklus)', 'Sisa Siklus', '% Terpakai']);

            foreach ($data['items'] as $item) {
                fputcsv($out, [
                    $item->code,
                    $item->instrument->name,
                    $item->instrument->category->name,
                    InstrumentItem::CONDITIONS[$item->condition]['label'] ?? $item->condition,
                    $item->total_cycles,
                    $item->instrument->lifespan_cycles ?? 'Tidak ditentukan',
                    $item->instrument->lifespan_cycles ? $item->remaining_cycles : '-',
                    $item->instrument->lifespan_cycles ? $this->rate($item->total_cycles, $item->instrument->lifespan_cycles) : '-',
                ]);
            }
        });
    }

    private function buildInstrumentLifespan(Request $request): array
    {
        $scope = $this->resolveHospitalScope($request);
        $showAll = $request->boolean('all');

        $items = InstrumentItem::with(['instrument.category'])
            ->whereIn('hospital_id', $scope['hospitalIds'])
            ->where('is_active', true)
            ->get()
            ->sortByDesc(fn (InstrumentItem $item) => $item->instrument->lifespan_cycles
                ? $item->total_cycles / $item->instrument->lifespan_cycles
                : -1)
            ->values();

        if (! $showAll) {
            $items = $items->filter(fn (InstrumentItem $item) => $item->isNearingLifespan())->values();
        }

        return compact('items', 'showAll') + $scope;
    }

    // ------------------------------------------------------------------
    // Shared helpers
    // ------------------------------------------------------------------

    /** @return array{0: Carbon, 1: Carbon} */
    private function resolvePeriod(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfMonth();

        return [$from, $to];
    }

    private function resolveHospitalScope(Request $request): array
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital = $userHospitals->count() > 1;

        if ($multiHospital) {
            $hospitalId = $request->filled('hospital_id') ? (int) $request->hospital_id : null;
            $hospitalIds = $hospitalId ? [$hospitalId] : $userHospitals->pluck('id')->toArray();
            $hospitalName = $hospitalId
                ? Hospital::find($hospitalId)?->name
                : $userHospitals->pluck('name')->implode(', ');
        } else {
            $hospitalId = session('active_hospital_id');
            $hospitalIds = [$hospitalId];
            $hospitalName = $userHospitals->first()?->name;
        }

        return compact('userHospitals', 'multiHospital', 'hospitalId', 'hospitalIds', 'hospitalName');
    }

    private function csvFilename(string $slug, Carbon $from, Carbon $to): string
    {
        return "{$slug}_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}.csv";
    }

    private function streamCsv(string $filename, \Closure $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 — tanpa ini Excel merusak huruf/aksen Indonesia.
            fwrite($out, "\xEF\xBB\xBF");
            $writer($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
