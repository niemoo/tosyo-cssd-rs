<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableStock;
use App\Models\ConsumableMovement;
use App\Http\Requests\StoreConsumableMovementRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ConsumableStockController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'updated_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['quantity', 'last_updated_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        // Index stocks — join dengan consumables
        $query = ConsumableStock::with(['consumable.category', 'hospital'])
                                ->whereHas('consumable', fn($q) => $q->whereNull('deleted_at'));

        if ($multiHospital) {
            if ($request->filled('hospital_id')) {
                $query->where('hospital_id', $request->hospital_id);
            } else {
                $query->whereIn('hospital_id', $userHospitalIds);
            }
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->whereHas('consumable', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('consumable', fn($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->filled('stock') && $request->stock === 'low') {
            $query->whereHas('consumable', function ($q) {
                $q->whereColumn('consumable_stocks.quantity', '<=', 'consumables.minimum_stock');
            });
        }

        $query->orderBy($sortBy, $sortDir);

        $stocks = $query->paginate(15)->withQueryString();

        // Stats
        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsBase = ConsumableStock::query();
        if ($multiHospital) {
            $hospitalId
                ? $statsBase->where('hospital_id', $hospitalId)
                : $statsBase->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsBase->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total_items' => (clone $statsBase)->count(),
            'low_stock'   => (clone $statsBase)
                ->whereHas('consumable', fn($q) =>
                    $q->whereColumn('consumable_stocks.quantity', '<=', 'consumables.minimum_stock')
                )->count(),
            'out_of_stock' => (clone $statsBase)->where('quantity', 0)->count(),
        ];

        // Categories for filter
        $categories = \App\Models\ConsumableCategory::whereIn('hospital_id', $userHospitalIds)
                                                    ->where('is_active', true)
                                                    ->orderBy('name')
                                                    ->get();

        return view('consumable-stocks.index', compact(
            'stocks', 'categories', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function movements(Request $request): View
    {
        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = ConsumableMovement::with(['consumable.category', 'hospital', 'handler'])
                                   ->withTrashed($request->boolean('show_deleted'));

        if ($multiHospital) {
            if ($request->filled('hospital_id')) {
                $query->where('hospital_id', $request->hospital_id);
            } else {
                $query->whereIn('hospital_id', $userHospitalIds);
            }
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->whereHas('consumable', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('consumable_id')) {
            $query->where('consumable_id', $request->consumable_id);
        }

        $query->orderBy('moved_at', 'desc');

        $movements = $query->paginate(15)->withQueryString();

        $consumables = Consumable::whereIn('hospital_id', $userHospitalIds)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        return view('consumable-stocks.movements', compact(
            'movements', 'consumables',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('consumable-stocks.index')
                             ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

        $consumables = Consumable::where('hospital_id', session('active_hospital_id'))
                                 ->where('is_active', true)
                                 ->with('category')
                                 ->orderBy('name')
                                 ->get();

        return view('consumable-stocks.create', compact('userHospitals', 'consumables'));
    }

    public function store(StoreConsumableMovementRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            // Buat movement record
            ConsumableMovement::create([
                'hospital_id'   => $request->hospital_id,
                'consumable_id' => $request->consumable_id,
                'type'          => $request->type,
                'quantity'      => $request->type === 'OUT'
                    ? -abs($request->quantity)
                    :  abs($request->quantity),
                'notes'         => $request->notes,
                'handled_by'    => auth()->id(),
                'moved_at'      => $request->moved_at,
            ]);

            // Upsert consumable stock
            $stock = ConsumableStock::firstOrNew([
                'hospital_id'   => $request->hospital_id,
                'consumable_id' => $request->consumable_id,
            ]);

            if ($request->type === 'IN') {
                $stock->quantity = ($stock->quantity ?? 0) + abs($request->quantity);
            } else {
                $stock->quantity = max(0, ($stock->quantity ?? 0) - abs($request->quantity));
            }

            $stock->last_updated_at = now();
            $stock->save();
        });

        return redirect()->route('consumable-stocks.index')
                         ->with('success', 'Pergerakan stok berhasil dicatat.');
    }
}