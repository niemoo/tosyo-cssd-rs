<?php

namespace App\Http\Controllers;

use App\Models\InstrumentCategory;
use App\Models\Hospital;
use App\Http\Requests\StoreInstrumentCategoryRequest;
use App\Http\Requests\UpdateInstrumentCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InstrumentCategoryController extends Controller
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

        $query = InstrumentCategory::with('hospital')
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

        $statsQuery = InstrumentCategory::query();
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

        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();

        return view('instrument-categories.index', compact(
            'categories', 'hospitals', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('instrument-categories.index')
                             ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

        return view('instrument-categories.create', compact('userHospitals'));
    }

    public function store(StoreInstrumentCategoryRequest $request): RedirectResponse
    {
        InstrumentCategory::create($request->validated());

        return redirect()->route('instrument-categories.index')
                         ->with('success', 'Kategori instrumen berhasil ditambahkan.');
    }

    public function show(InstrumentCategory $instrumentCategory): View
    {
        $instrumentCategory->load(['hospital', 'instruments']);
        return view('instrument-categories.show', compact('instrumentCategory'));
    }

    public function edit(InstrumentCategory $instrumentCategory): View
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();
        return view('instrument-categories.edit', compact('instrumentCategory', 'hospitals'));
    }

    public function update(UpdateInstrumentCategoryRequest $request, InstrumentCategory $instrumentCategory): RedirectResponse
    {
        $instrumentCategory->update($request->validated());

        return redirect()->route('instrument-categories.index')
                         ->with('success', 'Kategori instrumen berhasil diperbarui.');
    }

    public function destroy(InstrumentCategory $instrumentCategory): RedirectResponse
    {
        if ($instrumentCategory->instruments()->count() > 0) {
            return redirect()->route('instrument-categories.index')
                             ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki instrumen.');
        }

        $instrumentCategory->delete();

        return redirect()->route('instrument-categories.index')
                         ->with('success', 'Kategori instrumen berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $category = InstrumentCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('instrument-categories.index')
                         ->with('success', 'Kategori instrumen berhasil dipulihkan.');
    }

    public function toggleActive(InstrumentCategory $instrumentCategory): RedirectResponse
    {
        $instrumentCategory->update(['is_active' => !$instrumentCategory->is_active]);

        $status = $instrumentCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Kategori berhasil {$status}.");
    }
}