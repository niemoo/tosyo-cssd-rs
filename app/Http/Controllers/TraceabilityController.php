<?php

namespace App\Http\Controllers;

use App\Models\DistributionRequest;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\TrayLifecycleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TraceabilityController extends Controller
{
    // Recall per Load: cari batch/load lalu tampilkan dampak distribusi tiap tray di dalamnya.
    public function loadRecall(Request $request): View
    {
        [$multiHospital, $userHospitals, $scopeHospital] = $this->hospitalScope($request);

        $withImpactData = ['hospital', 'sterilizer', 'operator', 'items' => fn ($q) => $q->with([
            'tray' => fn ($t) => $t->withTrashed()->with('template'),
        ])];

        $batch = null;
        $matches = null;

        if ($request->filled('batch_id')) {
            $batch = $scopeHospital(SterilizationBatch::withTrashed())
                ->with($withImpactData)
                ->find($request->batch_id);
        } elseif ($request->filled('search')) {
            $baseQuery = fn () => $scopeHospital(SterilizationBatch::withTrashed())
                ->where(function ($q) use ($request) {
                    $q->where('load_number', 'like', "%{$request->search}%")
                        ->orWhere('batch_number', 'like', "%{$request->search}%");
                });

            // Cukup intip 2 baris untuk tahu "tepat satu" tanpa menarik seluruh hasil.
            $preview = $baseQuery()->limit(2)->get();

            if ($preview->count() === 1) {
                $batch = $scopeHospital(SterilizationBatch::withTrashed())
                    ->with($withImpactData)
                    ->find($preview->first()->id);
            } else {
                $matches = $baseQuery()
                    ->with(['hospital', 'sterilizer'])
                    ->orderByDesc('started_at')
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        $rows = collect();
        $kpi = ['total' => 0, 'at_unit' => 0, 'on_shelf' => 0, 'returned' => 0, 'untraceable' => 0];

        if ($batch) {
            $rows = $this->buildImpactRows($batch);
            $kpi = [
                'total' => $rows->count(),
                'at_unit' => $rows->where('category', 'AT_UNIT')->count(),
                'on_shelf' => $rows->where('category', 'ON_SHELF')->count(),
                'returned' => $rows->where('category', 'RETURNED')->count(),
                'untraceable' => $rows->where('category', 'UNTRACEABLE')->count(),
            ];
        }

        return view('traceability.load-recall', compact(
            'batch', 'rows', 'kpi', 'matches', 'multiHospital', 'userHospitals'
        ));
    }

    // Bukti recall cetak — dokumen yang bisa dilampirkan saat audit.
    public function loadRecallPrint(Request $request): View
    {
        [, , $scopeHospital] = $this->hospitalScope($request);

        $withImpactData = ['hospital', 'sterilizer', 'operator', 'items' => fn ($q) => $q->with([
            'tray' => fn ($t) => $t->withTrashed()->with('template'),
        ])];

        $batch = $scopeHospital(SterilizationBatch::withTrashed())
            ->with($withImpactData)
            ->findOrFail($request->batch_id);

        $rows = $this->buildImpactRows($batch);

        return view('traceability.load-recall-print', compact('batch', 'rows'));
    }

    /**
     * Untuk tiap tray dalam batch, tentukan dampaknya terhadap load ini: masih di unit,
     * sudah kembali, masih di rak, atau sudah tak lagi membawa sterilitas load ini.
     *
     * Atribusi ISSUE tidak boleh sekadar ambil event ISSUE terakhir tray (satu tray dipakai
     * berulang lewat banyak load) — harus ISSUE pertama SETELAH STERILIZE_PASS milik batch
     * ini, dan hanya kalau itu terjadi sebelum event apa pun yang memutus rantai sterilitasnya
     * (REPROCESS/EXPIRE/RETIRE/STERILIZE_FAIL). `trays.current_load_number` sengaja tidak
     * dipakai sebagai sumber di sini karena hanya mencerminkan load TERAKHIR, bukan riwayat.
     */
    private function buildImpactRows(SterilizationBatch $batch): Collection
    {
        $items = $batch->items;
        $trayIds = $items->pluck('tray_id');

        // Urutan performed_at lalu id sebagai penentu — beberapa event bisa tercatat
        // dalam detik yang sama (mis. dari seeder), id menjamin urutan tetap deterministik.
        $eventsByTray = TrayLifecycleEvent::whereIn('tray_id', $trayIds)
            ->orderBy('performed_at')
            ->orderBy('id')
            ->get()
            ->groupBy('tray_id');

        $severingTypes = ['REPROCESS', 'EXPIRE', 'RETIRE', 'STERILIZE_FAIL'];
        $analysis = [];
        $issueRequestIds = [];

        foreach ($items as $item) {
            $trayEvents = $eventsByTray->get($item->tray_id, collect())->values();

            $passIndex = $trayEvents->search(fn ($e) => $e->event_type === 'STERILIZE_PASS'
                && $e->reference_type === SterilizationBatch::class
                && (int) $e->reference_id === $batch->id);

            $issueEvent = null;
            $returnEvent = null;
            $severedEvent = null;

            if ($passIndex !== false) {
                $after = $trayEvents->slice($passIndex + 1)->values();

                $issueIdx = $after->search(fn ($e) => $e->event_type === 'ISSUE');
                $severedIdx = $after->search(fn ($e) => in_array($e->event_type, $severingTypes));

                if ($issueIdx !== false && ($severedIdx === false || $issueIdx < $severedIdx)) {
                    $issueEvent = $after->get($issueIdx);
                    $issueRequestIds[] = $issueEvent->reference_id;

                    $returnEvent = $after->slice($issueIdx + 1)
                        ->first(fn ($e) => in_array($e->event_type, ['RETURN', 'RETURN_DAMAGED']));
                } elseif ($severedIdx !== false) {
                    $severedEvent = $after->get($severedIdx);
                }
            }

            // "Tidak ada jejak event" (passIndex === false) BUKAN berarti "tidak terjadi apa-apa" —
            // itu berarti sistem tidak tahu. Data lama (sebelum tray_lifecycle_events ada) harus
            // ditandai UNTRACEABLE, bukan dianggap ON_SHELF secara diam-diam.
            $analysis[$item->id] = [
                'traced' => $passIndex !== false,
                'issueEvent' => $issueEvent,
                'returnEvent' => $returnEvent,
                'severedEvent' => $severedEvent,
            ];
        }

        $requestsById = DistributionRequest::whereIn('id', array_unique($issueRequestIds))
            ->with('unit')
            ->get()
            ->keyBy('id');

        return $items->map(function ($item) use ($analysis, $requestsById) {
            $a = $analysis[$item->id];
            $distributionRequest = $a['issueEvent'] ? $requestsById->get($a['issueEvent']->reference_id) : null;

            $category = match (true) {
                $item->result === SterilizationBatchItem::RESULT_FAILED => 'FAILED',
                $item->result === SterilizationBatchItem::RESULT_PENDING => 'PENDING',
                ! $a['traced'] => 'UNTRACEABLE',
                $a['issueEvent'] && ! $a['returnEvent'] => 'AT_UNIT',
                $a['issueEvent'] && $a['returnEvent'] => 'RETURNED',
                $a['severedEvent'] !== null => 'SEVERED',
                default => 'ON_SHELF',
            };

            return (object) [
                'item' => $item,
                'tray' => $item->tray,
                'category' => $category,
                'distribution_request' => $distributionRequest,
                'issued_at' => $a['issueEvent']?->performed_at,
                'returned_at' => $a['returnEvent']?->performed_at,
            ];
        })->values();
    }

    /** Pola scoping multi-hospital manual yang dipakai berulang di controller index() lain. */
    private function hospitalScope(Request $request): array
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $scope = function ($query) use ($request, $multiHospital, $userHospitalIds) {
            if ($multiHospital) {
                $request->filled('hospital_id')
                    ? $query->where('hospital_id', $request->hospital_id)
                    : $query->whereIn('hospital_id', $userHospitalIds);
            } else {
                $query->where('hospital_id', session('active_hospital_id'));
            }

            return $query;
        };

        return [$multiHospital, $userHospitals, $scope];
    }
}
