<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableCategory;
use App\Http\Requests\StoreConsumableRequest;
use App\Http\Requests\UpdateConsumableRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ConsumableController extends Controller
{
    private const UNITS = [
        'PCS'   => 'Pcs (Satuan)',
        'BOX'   => 'Box (Kotak)',
        'ROLL'  => 'Roll (Gulungan)',
        'LITER' => 'Liter',
    ];

    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'unit', 'minimum_stock', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = Consumable::with(['hospital', 'category', 'stock'])
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
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('stock') && $request->stock === 'low') {
            $query->whereHas('stock', function ($q) {
                $q->whereColumn('quantity', '<=', 'consumables.minimum_stock');
            })->orWhereDoesntHave('stock');
        }

        $query->orderBy($sortBy, $sortDir);

        $consumables = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = Consumable::query();
        if ($multiHospital) {
            $hospitalId
                ? $statsQuery->where('hospital_id', $hospitalId)
                : $statsQuery->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total'     => (clone $statsQuery)->count(),
            'active'    => (clone $statsQuery)->where('is_active', true)->count(),
            'low_stock' => (clone $statsQuery)->where('is_active', true)
                              ->where(function ($q) {
                                  $q->whereHas('stock', fn($s) => $s->whereColumn('quantity', '<=', 'consumables.minimum_stock'))
                                    ->orWhereDoesntHave('stock');
                              })->count(),
        ];

        $categories = ConsumableCategory::whereIn('hospital_id', $userHospitalIds)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('consumables.index', compact(
            'consumables', 'categories', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ))->with('units', self::UNITS);
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $categories = ConsumableCategory::where('hospital_id', session('active_hospital_id'))
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('consumables.create', compact('userHospitals', 'categories'))
               ->with('units', self::UNITS);
    }

    public function store(StoreConsumableRequest $request): RedirectResponse
    {
        Consumable::create($request->validated());

        return redirect()->route('consumables.index')
                         ->with('success', 'Consumable berhasil ditambahkan.');
    }

    public function show(Consumable $consumable): View
    {
        $consumable->load(['hospital', 'category', 'stock', 'movements' => fn($q) => $q->latest()->limit(10)]);

        return view('consumables.show', compact('consumable'))
               ->with('units', self::UNITS);
    }

    public function edit(Consumable $consumable): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $categories = ConsumableCategory::where('hospital_id', $consumable->hospital_id)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('consumables.edit', compact('consumable', 'userHospitals', 'categories'))
               ->with('units', self::UNITS);
    }

    public function update(UpdateConsumableRequest $request, Consumable $consumable): RedirectResponse
    {
        $consumable->update($request->validated());

        return redirect()->route('consumables.index')
                         ->with('success', 'Consumable berhasil diperbarui.');
    }

    public function destroy(Consumable $consumable): RedirectResponse
    {
        $consumable->delete();

        return redirect()->route('consumables.index')
                         ->with('success', 'Consumable berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $consumable = Consumable::onlyTrashed()->findOrFail($id);
        $consumable->restore();

        return redirect()->route('consumables.index')
                         ->with('success', 'Consumable berhasil dipulihkan.');
    }

    public function toggleActive(Consumable $consumable): RedirectResponse
    {
        $consumable->update(['is_active' => !$consumable->is_active]);

        $status = $consumable->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Consumable berhasil {$status}.");
    }
}