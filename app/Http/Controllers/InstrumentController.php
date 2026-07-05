<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentCategory;
use App\Models\Hospital;
use App\Http\Requests\StoreInstrumentRequest;
use App\Http\Requests\UpdateInstrumentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InstrumentController extends Controller
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

        $query = Instrument::with(['hospital', 'category'])
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

        $query->orderBy($sortBy, $sortDir);

        $instruments = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = Instrument::query();
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

        // Kategori untuk filter — sesuai hospital user
        $categories = InstrumentCategory::whereIn('hospital_id', $userHospitalIds)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('instruments.index', compact(
            'instruments', 'categories', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('instruments.index')
                             ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

        // Kategori sesuai active hospital
        $categories = InstrumentCategory::where('hospital_id', session('active_hospital_id'))
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('instruments.create', compact('userHospitals', 'categories'));
    }

    public function store(StoreInstrumentRequest $request): RedirectResponse
    {
        Instrument::create($request->validated());

        return redirect()->route('instruments.index')
                         ->with('success', 'Instrumen berhasil ditambahkan.');
    }

    public function show(Instrument $instrument): View
    {
        $instrument->load(['hospital', 'category', 'items']);

        return view('instruments.show', compact('instrument'));
    }

    public function edit(Instrument $instrument): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $categories = InstrumentCategory::where('hospital_id', $instrument->hospital_id)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

        return view('instruments.edit', compact('instrument', 'userHospitals', 'categories'));
    }

    public function update(UpdateInstrumentRequest $request, Instrument $instrument): RedirectResponse
    {
        $instrument->update($request->validated());

        return redirect()->route('instruments.index')
                         ->with('success', 'Instrumen berhasil diperbarui.');
    }

    public function destroy(Instrument $instrument): RedirectResponse
    {
        if ($instrument->items()->count() > 0) {
            return redirect()->route('instruments.index')
                             ->with('error', 'Instrumen tidak dapat dihapus karena masih memiliki item.');
        }

        $instrument->delete();

        return redirect()->route('instruments.index')
                         ->with('success', 'Instrumen berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $instrument = Instrument::onlyTrashed()->findOrFail($id);
        $instrument->restore();

        return redirect()->route('instruments.index')
                         ->with('success', 'Instrumen berhasil dipulihkan.');
    }

    public function toggleActive(Instrument $instrument): RedirectResponse
    {
        $instrument->update(['is_active' => !$instrument->is_active]);

        $status = $instrument->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Instrumen berhasil {$status}.");
    }
}