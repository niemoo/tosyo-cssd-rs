<?php

namespace App\Http\Controllers;

use App\Models\TrayReturn;
use App\Models\Tray;
use App\Models\DistributionRequest;
use App\Models\DistributionRequestItem;
use App\Http\Requests\StoreTrayReturnRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TrayReturnController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'returned_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['returned_at', 'condition', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'returned_at';

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = TrayReturn::with(['hospital', 'tray', 'distributionRequest.unit', 'receiver']);

        if ($multiHospital) {
            $request->filled('hospital_id')
                ? $query->where('hospital_id', $request->hospital_id)
                : $query->whereIn('hospital_id', $userHospitalIds);
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('tray', fn($t) => $t->where('code', 'like', "%{$request->search}%")
                                                  ->orWhere('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('distributionRequest', fn($r) => $r->where('request_number', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $query->orderBy($sortBy, $sortDir);
        $returns = $query->paginate(10)->withQueryString();

        $statsQuery = TrayReturn::whereIn('hospital_id', $userHospitalIds);
        $stats = [
            'total'      => (clone $statsQuery)->count(),
            'good'       => (clone $statsQuery)->where('condition', TrayReturn::CONDITION_GOOD)->count(),
            'damaged'    => (clone $statsQuery)->where('condition', TrayReturn::CONDITION_DAMAGED)->count(),
            'incomplete' => (clone $statsQuery)->where('condition', TrayReturn::CONDITION_INCOMPLETE)->count(),
        ];

        return view('tray-returns.index', compact(
            'returns', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(DistributionRequest $distributionRequest): View
    {
        abort_if(
            !in_array($distributionRequest->status, [DistributionRequest::STATUS_FULFILLED, DistributionRequest::STATUS_CLOSED]),
            403, 'Permintaan ini belum terpenuhi sepenuhnya, belum bisa dicatat pengembaliannya.'
        );

        $distributionRequest->load(['items.tray', 'unit']);

        $returnableItems = $distributionRequest->items
            ->filter(fn($item) => $item->tray && $item->tray->status === Tray::STATUS_IN_USE)
            ->values();

        abort_if($returnableItems->isEmpty(), 403, 'Tidak ada tray yang masih digunakan dari permintaan ini.');

        return view('tray-returns.create', compact('distributionRequest', 'returnableItems'));
    }

    public function store(StoreTrayReturnRequest $request, DistributionRequest $distributionRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $distributionRequest) {
            foreach ($request->returns as $returnData) {
                TrayReturn::create([
                    'hospital_id'             => $distributionRequest->hospital_id,
                    'distribution_request_id' => $distributionRequest->id,
                    'tray_id'                 => $returnData['tray_id'],
                    'received_by'             => auth()->id(),
                    'condition'               => $returnData['condition'],
                    'missing_items'           => $returnData['missing_items'] ?? null,
                    'notes'                   => $returnData['notes'] ?? null,
                    'returned_at'             => $returnData['returned_at'],
                ]);

                $tray = Tray::find($returnData['tray_id']);

                if ($returnData['condition'] === TrayReturn::CONDITION_GOOD) {
                    $tray?->update(['status' => Tray::STATUS_RETURNED]);
                } else {
                    $note = $returnData['condition'] === TrayReturn::CONDITION_DAMAGED
                        ? 'Tray dikembalikan dalam kondisi rusak.'
                        : 'Tray dikembalikan tidak lengkap: ' . ($returnData['missing_items'] ?? '');

                    $tray?->update([
                        'status' => Tray::STATUS_NEEDS_REPROCESSING,
                        'notes'  => $note,
                    ]);
                }
            }

            // Cek apakah semua tray dalam request sudah dikembalikan
            $anyStillInUse = DistributionRequestItem::where('request_id', $distributionRequest->id)
                ->whereHas('tray', fn($q) => $q->where('status', Tray::STATUS_IN_USE))
                ->exists();

            if (!$anyStillInUse && $distributionRequest->status === DistributionRequest::STATUS_FULFILLED) {
                $distributionRequest->update(['status' => DistributionRequest::STATUS_CLOSED]);
            }
        });

        return redirect()->route('distribution-requests.show', $distributionRequest)
                         ->with('success', 'Pengembalian tray berhasil dicatat.');
    }
}