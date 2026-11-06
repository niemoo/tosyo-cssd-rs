<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSterilizationBatchRequest;
use App\Http\Requests\UpdateBatchResultRequest;
use App\Http\Requests\UpdateSterilizationBatchRequest;
use App\Models\Consumable;
use App\Models\InstrumentItem;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Sterilizer;
use App\Models\StorageRack;
use App\Models\Tray;
use App\Services\ConsumableUsageService;
use App\Services\TrayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SterilizationBatchController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['batch_number', 'status', 'started_at', 'completed_at', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = SterilizationBatch::with(['hospital', 'sterilizer', 'operator', 'items'])
            ->withTrashed($request->boolean('show_deleted'));

        if ($multiHospital) {
            $request->filled('hospital_id')
                ? $query->where('hospital_id', $request->hospital_id)
                : $query->whereIn('hospital_id', $userHospitalIds);
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('batch_number', 'like', "%{$request->search}%")
                    ->orWhereHas('sterilizer', fn ($s) => $s->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy($sortBy, $sortDir);
        $batches = $query->paginate(10)->withQueryString();

        $statsQuery = SterilizationBatch::whereIn('hospital_id', $userHospitalIds);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'in_progress' => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_COMPLETED)->count(),
            'failed' => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_FAILED)->count(),
        ];

        return view('sterilization-batches.index', compact(
            'batches', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('sterilization-batches.index')
                ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

        $hospitalId = session('active_hospital_id');

        $sterilizers = Sterilizer::where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $availableTrays = Tray::where('hospital_id', $hospitalId)
            ->where('status', Tray::STATUS_READY)
            ->where('is_active', true)
            ->with(['template', 'items'])
            ->orderBy('code')
            ->get();

        $consumables = Consumable::where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->with('stock')
            ->orderBy('name')
            ->get();

        $batchNumber = 'BATCH-'.date('Y').'-'.str_pad(
            SterilizationBatch::where('hospital_id', $hospitalId)->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        return view('sterilization-batches.create', compact(
            'userHospitals', 'sterilizers', 'availableTrays', 'batchNumber', 'consumables'
        ));
    }

    public function store(StoreSterilizationBatchRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $sterilizer = Sterilizer::find($request->sterilizer_id);

            $loadNumber = 'LOAD-'.date('Y').'-'.str_pad(
                SterilizationBatch::where('hospital_id', $request->hospital_id)->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $batch = SterilizationBatch::create([
                'hospital_id' => $request->hospital_id,
                'sterilizer_id' => $request->sterilizer_id,
                'method' => $sterilizer?->type,
                'batch_number' => $request->batch_number,
                'load_number' => $loadNumber,
                'status' => SterilizationBatch::STATUS_IN_PROGRESS,
                'temperature' => $request->temperature,
                'pressure' => $request->pressure,
                'duration_minutes' => $request->duration_minutes,
                'bi_result' => SterilizationBatch::BI_PENDING,
                'bowie_dick_result' => $request->bowie_dick_result,
                'operator_id' => auth()->id(),
                'started_at' => $request->started_at ?? now(),
                'notes' => $request->notes,
            ]);

            foreach ($request->tray_ids as $trayId) {
                SterilizationBatchItem::create([
                    'batch_id' => $batch->id,
                    'tray_id' => $trayId,
                    'result' => SterilizationBatchItem::RESULT_PENDING,
                ]);

                $tray = Tray::find($trayId);
                if ($tray) {
                    TrayService::transitionTo($tray, Tray::STATUS_IN_STERILIZATION, reference: $batch);
                }
            }

            foreach ($request->input('consumable_usages', []) as $row) {
                if (empty($row['consumable_id']) || empty($row['quantity'])) {
                    continue;
                }

                ConsumableUsageService::record(
                    hospitalId: $batch->hospital_id,
                    consumableId: (int) $row['consumable_id'],
                    usageable: $batch,
                    quantity: (int) $row['quantity'],
                    notes: $row['notes'] ?? null,
                    usedBy: auth()->id(),
                    usedAt: $batch->started_at,
                );
            }
        });

        return redirect()->route('sterilization-batches.index')
            ->with('success', 'Batch sterilisasi berhasil dibuat.');
    }

    public function show(SterilizationBatch $sterilizationBatch): View
    {
        $sterilizationBatch->load([
            'hospital', 'sterilizer', 'operator',
            'items.tray.template',
            'items.tray.items.instrumentItem.instrument',
            'consumableUsages.consumable', 'consumableUsages.usedBy',
        ]);

        $racks = StorageRack::where('hospital_id', $sterilizationBatch->hospital_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $consumables = Consumable::where('hospital_id', $sterilizationBatch->hospital_id)
            ->where('is_active', true)
            ->with('stock')
            ->orderBy('name')
            ->get();

        return view('sterilization-batches.show', compact('sterilizationBatch', 'racks', 'consumables'));
    }

    public function edit(SterilizationBatch $sterilizationBatch): View
    {
        abort_if(
            $sterilizationBatch->status !== SterilizationBatch::STATUS_IN_PROGRESS,
            403, 'Batch hanya bisa diedit saat berstatus Berjalan.'
        );

        $sterilizationBatch->load(['items.tray']);

        return view('sterilization-batches.edit', compact('sterilizationBatch'));
    }

    public function update(UpdateSterilizationBatchRequest $request, SterilizationBatch $sterilizationBatch): RedirectResponse
    {
        abort_if(
            $sterilizationBatch->status !== SterilizationBatch::STATUS_IN_PROGRESS,
            403, 'Batch hanya bisa diedit saat berstatus Berjalan.'
        );

        $sterilizationBatch->update($request->validated());

        return redirect()->route('sterilization-batches.show', $sterilizationBatch)
            ->with('success', 'Batch berhasil diperbarui.');
    }

    public function destroy(SterilizationBatch $sterilizationBatch): RedirectResponse
    {
        abort_if(
            $sterilizationBatch->status === SterilizationBatch::STATUS_IN_PROGRESS,
            403, 'Batch yang sedang berjalan tidak bisa dihapus.'
        );

        $sterilizationBatch->delete();

        return redirect()->route('sterilization-batches.index')
            ->with('success', 'Batch berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        SterilizationBatch::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('sterilization-batches.index')
            ->with('success', 'Batch berhasil dipulihkan.');
    }

    // Input hasil sterilisasi per tray
    public function updateResult(UpdateBatchResultRequest $request, SterilizationBatch $sterilizationBatch): RedirectResponse
    {
        abort_if(
            $sterilizationBatch->status !== SterilizationBatch::STATUS_IN_PROGRESS,
            403, 'Hasil hanya bisa diinput saat batch berstatus Berjalan.'
        );

        DB::transaction(function () use ($request, $sterilizationBatch) {
            $sterilizationBatch->update(['bi_result' => $request->bi_result]);

            $shelfDays = $sterilizationBatch->hospital->sterile_shelf_life_days ?? 30;
            $loadNumber = $sterilizationBatch->load_number;

            $allPassed = true;
            $allDone = true;

            foreach ($request->results as $resultData) {
                $item = SterilizationBatchItem::where('batch_id', $sterilizationBatch->id)
                    ->where('tray_id', $resultData['tray_id'])
                    ->firstOrFail();

                $item->update([
                    'result' => $resultData['result'],
                    'ci_result' => $resultData['ci_result'] ?? null,
                    'failure_notes' => $resultData['failure_notes'] ?? null,
                ]);

                $tray = Tray::find($resultData['tray_id']);

                if ($resultData['result'] === SterilizationBatchItem::RESULT_FAILED) {
                    $allPassed = false;
                    // Tray gagal → NEEDS_REPROCESSING, bersihkan status steril.
                    if ($tray) {
                        TrayService::transitionTo(
                            $tray, Tray::STATUS_NEEDS_REPROCESSING,
                            reference: $sterilizationBatch,
                            attributes: ['sterile_at' => null, 'expiry_at' => null, 'current_load_number' => null],
                            context: [
                                'ci_result' => $resultData['ci_result'] ?? null,
                                'failure_notes' => $resultData['failure_notes'] ?? null,
                            ],
                        );
                    }
                } elseif ($resultData['result'] === SterilizationBatchItem::RESULT_PASSED) {
                    // Tray lulus → STERILE dengan tgl steril + kedaluwarsa; simpan ke rak jika dipilih.
                    if ($tray) {
                        $sterileAt = now();
                        $expiryAt = $sterileAt->copy()->addDays($shelfDays);

                        TrayService::transitionTo(
                            $tray, Tray::STATUS_STERILE,
                            reference: $sterilizationBatch,
                            attributes: [
                                'current_rack_id' => $request->rack_id ?? null,
                                'sterile_at' => $sterileAt,
                                'expiry_at' => $expiryAt,
                                'current_load_number' => $loadNumber,
                            ],
                            context: [
                                'sterile_at' => $sterileAt->toDateTimeString(),
                                'expiry_at' => $expiryAt->toDateTimeString(),
                                'load_number' => $loadNumber,
                                'ci_result' => $resultData['ci_result'] ?? null,
                                'bi_result' => $request->bi_result,
                            ],
                        );

                        // Satu siklus autoklaf nyata → tambah hitung siklus tiap instrumen.
                        InstrumentItem::whereIn('id', $tray->items()->pluck('instrument_item_id'))
                            ->increment('total_cycles');
                    }
                } else {
                    $allDone = false;
                }
            }

            // Update status batch
            if ($allDone) {
                $sterilizationBatch->update([
                    'status' => $allPassed
                        ? SterilizationBatch::STATUS_COMPLETED
                        : SterilizationBatch::STATUS_FAILED,
                    'completed_at' => now(),
                ]);
            }
        });

        return redirect()->route('sterilization-batches.show', $sterilizationBatch)
            ->with('success', 'Hasil sterilisasi berhasil disimpan.');
    }

    // Cetak label pak steril untuk semua tray yang LULUS dalam batch ini sekaligus.
    public function labels(SterilizationBatch $sterilizationBatch): View
    {
        $trays = $sterilizationBatch->trays()
            ->wherePivot('result', SterilizationBatchItem::RESULT_PASSED)
            ->with(['hospital', 'template', 'assembler', 'items', 'lifecycleEvents.performer'])
            ->orderBy('code')
            ->get();

        return view('sterilization-batches.labels', compact('sterilizationBatch', 'trays'));
    }
}
