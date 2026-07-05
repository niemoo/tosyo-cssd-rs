<?php

namespace App\Http\Controllers;

use App\Models\ConsumableCategory;
use App\Http\Requests\StoreConsumableCategoryRequest;
use App\Http\Requests\UpdateConsumableCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ConsumableCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = ConsumableCategory::with('hospital')
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

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $categories = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = ConsumableCategory::query();
        if ($multiHospital) {
            $hospitalId
                ? $statsQuery->where('hospital_id', $hospitalId)
                : $statsQuery->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'active'   => (clone $statsQuery)->where('is_active', true)->count(),
            'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
        ];

        return view('consumable-categories.index', compact(
            'categories', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        return view('consumable-categories.create', compact('userHospitals'));
    }

    public function store(StoreConsumableCategoryRequest $request): RedirectResponse
    {
        ConsumableCategory::create($request->validated());

        return redirect()->route('consumable-categories.index')
                         ->with('success', 'Kategori consumable berhasil ditambahkan.');
    }

    public function show(ConsumableCategory $consumableCategory): View
    {
        $consumableCategory->load(['hospital', 'consumables']);
        return view('consumable-categories.show', compact('consumableCategory'));
    }

    public function edit(ConsumableCategory $consumableCategory): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        return view('consumable-categories.edit', compact('consumableCategory', 'userHospitals'));
    }

    public function update(UpdateConsumableCategoryRequest $request, ConsumableCategory $consumableCategory): RedirectResponse
    {
        $consumableCategory->update($request->validated());

        return redirect()->route('consumable-categories.index')
                         ->with('success', 'Kategori consumable berhasil diperbarui.');
    }

    public function destroy(ConsumableCategory $consumableCategory): RedirectResponse
    {
        if ($consumableCategory->consumables()->count() > 0) {
            return redirect()->route('consumable-categories.index')
                             ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki consumable.');
        }

        $consumableCategory->delete();

        return redirect()->route('consumable-categories.index')
                         ->with('success', 'Kategori consumable berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $category = ConsumableCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('consumable-categories.index')
                         ->with('success', 'Kategori consumable berhasil dipulihkan.');
    }

    public function toggleActive(ConsumableCategory $consumableCategory): RedirectResponse
    {
        $consumableCategory->update(['is_active' => !$consumableCategory->is_active]);

        $status = $consumableCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Kategori berhasil {$status}.");
    }
}