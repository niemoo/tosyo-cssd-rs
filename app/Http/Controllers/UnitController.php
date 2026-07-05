<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Hospital;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'type', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = Unit::with('hospital')
                    ->withTrashed($request->boolean('show_deleted'));

        if ($multiHospital) {
            if ($request->filled('hospital_id')) {
                // Filter ke RS tertentu
                $query->where('hospital_id', $request->hospital_id);
            } else {
                // Tampilkan semua RS yang user terdaftar
                $query->whereIn('hospital_id', $userHospitalIds);
            }
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
                ->orWhere('type', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $units     = $query->paginate(10)->withQueryString();
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = Unit::query();

        if ($multiHospital) {
            if ($hospitalId) {
                $statsQuery->where('hospital_id', $hospitalId);
            } else {
                $statsQuery->whereIn('hospital_id', $userHospitalIds);
            }
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'active'   => (clone $statsQuery)->where('is_active', true)->count(),
            'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
        ];

        return view('units.index', compact(
            'units', 'hospitals', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $types = ['IGD', 'ICU', 'ICCU', 'PICU', 'NICU', 'Bedah', 'Rawat Inap', 'Rawat Jalan', 'Laboratorium', 'Radiologi', 'Farmasi', 'Lainnya'];

        return view('units.create', compact('userHospitals', 'types'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()->route('units.index')
                         ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function show(Unit $unit): View
    {
        $unit->load('hospital');

        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit): View
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();
        $types     = ['IGD', 'ICU', 'ICCU', 'PICU', 'NICU', 'Bedah', 'Rawat Inap', 'Rawat Jalan', 'Laboratorium', 'Radiologi', 'Farmasi', 'Lainnya'];

        return view('units.edit', compact('unit', 'hospitals', 'types'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('units.index')
                         ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('units.index')
                         ->with('success', 'Unit berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        $unit->restore();

        return redirect()->route('units.index')
                         ->with('success', 'Unit berhasil dipulihkan.');
    }

    public function toggleActive(Unit $unit): RedirectResponse
    {
        $unit->update(['is_active' => !$unit->is_active]);

        $status = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Unit berhasil {$status}.");
    }
}