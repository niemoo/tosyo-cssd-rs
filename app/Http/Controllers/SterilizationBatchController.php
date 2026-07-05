<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use App\Models\Sterilizer;
use App\Models\StorageRack;
use App\Http\Requests\StoreSterilizationBatchRequest;
use App\Http\Requests\UpdateSterilizationBatchRequest;
use App\Http\Requests\UpdateBatchResultRequest;
use App\Services\ConsumableUsageService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SterilizationBatchController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['batch_number', 'status', 'started_at', 'completed_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
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
                  ->orWhereHas('sterilizer', fn($s) => $s->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy($sortBy, $sortDir);
        $batches = $query->paginate(10)->withQueryString();

        $statsQuery = SterilizationBatch::whereIn('hospital_id', $userHospitalIds);
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'in_progress' => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_IN_PROGRESS)->count(),
            'completed'   => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_COMPLETED)->count(),
            'failed'      => (clone $statsQuery)->where('status', SterilizationBatch::STATUS_FAILED)->count(),
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

        $hospitalId    = session('active_hospital_id');

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

        $batchNumber = 'BATCH-' . date('Y') . '-' . str_pad(
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
            $batch = SterilizationBatch::create([
                'hospital_id'      => $request->hospital_id,
                'sterilizer_id'    => $request->sterilizer_id,
                'batch_number'     => $request->batch_number,
                'status'           => SterilizationBatch::STATUS_IN_PROGRESS,
                'temperature'      => $request->temperature,
                'pressure'         => $request->pressure,
                'duration_minutes' => $request->duration_minutes,
                'operator_id'      => auth()->id(),
                'started_at'       => $request->started_at ?? now(),
                'notes'            => $request->notes,
            ]);

            foreach ($request->tray_ids as $trayId) {
                SterilizationBatchItem::create([
                    'batch_id' => $batch->id,
                    'tray_id'  => $trayId,
                    'result'   => SterilizationBatchItem::RESULT_PENDING,
                ]);

                Tray::find($trayId)?->update(['status' => Tray::STATUS_IN_STERILIZATION]);
            }

            foreach ($request->input('consumable_usages', []) as $row) {
                if (empty($row['consumable_id']) || empty($row['quantity'])) continue;

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
            $allPassed = true;
            $allDone   = true;

            foreach ($request->results as $resultData) {
                $item = SterilizationBatchItem::where('batch_id', $sterilizationBatch->id)
                                              ->where('tray_id', $resultData['tray_id'])
                                              ->firstOrFail();

                $item->update([
                    'result'        => $resultData['result'],
                    'failure_notes' => $resultData['failure_notes'] ?? null,
                ]);

                if ($resultData['result'] === SterilizationBatchItem::RESULT_FAILED) {
                    $allPassed = false;
                    // Tray gagal → NEEDS_REPROCESSING
                    Tray::find($resultData['tray_id'])?->update([
                        'status' => Tray::STATUS_NEEDS_REPROCESSING,
                    ]);
                } elseif ($resultData['result'] === SterilizationBatchItem::RESULT_PASSED) {
                    // Tray lulus → STERILE, simpan ke rak jika dipilih
                    Tray::find($resultData['tray_id'])?->update([
                        'status'          => Tray::STATUS_STERILE,
                        'current_rack_id' => $request->rack_id ?? null,
                    ]);
                } else {
                    $allDone = false;
                }
            }

            // Update status batch
            if ($allDone) {
                $sterilizationBatch->update([
                    'status'       => $allPassed
                        ? SterilizationBatch::STATUS_COMPLETED
                        : SterilizationBatch::STATUS_FAILED,
                    'completed_at' => now(),
                ]);
            }
        });

        return redirect()->route('sterilization-batches.show', $sterilizationBatch)
                         ->with('success', 'Hasil sterilisasi berhasil disimpan.');
    }
}