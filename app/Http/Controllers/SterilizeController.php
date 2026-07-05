<?php

namespace App\Http\Controllers;

use App\Models\Sterilizer;
use App\Models\Hospital;
use App\Http\Requests\StoreSterilizeRequest;
use App\Http\Requests\UpdateSterilizeRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SterilizeController extends Controller
{
    private const TYPES = [
        'STEAM'  => ['label' => 'Steam (Uap)',     'color' => 'blue'],
        'PLASMA' => ['label' => 'Plasma',           'color' => 'purple'],
        'EO'     => ['label' => 'Ethylene Oxide',   'color' => 'amber'],
    ];

    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'type', 'is_active', 'next_maintenance_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = Sterilizer::with('hospital')
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
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $sterilizers = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = Sterilizer::query();
        if ($multiHospital) {
            $hospitalId
                ? $statsQuery->where('hospital_id', $hospitalId)
                : $statsQuery->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'active'      => (clone $statsQuery)->where('is_active', true)->count(),
            'maintenance' => (clone $statsQuery)->where('is_active', true)
                                ->where(function ($q) {
                                    $q->whereDate('next_maintenance_at', '<=', now()->addDays(7))
                                      ->orWhereDate('next_maintenance_at', '<', now());
                                })->count(),
        ];

        return view('sterilizers.index', compact(
            'sterilizers', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ))->with('types', self::TYPES);
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        return view('sterilizers.create', compact('userHospitals'))
               ->with('types', self::TYPES);
    }

    public function store(StoreSterilizeRequest $request): RedirectResponse
    {
        Sterilizer::create($request->validated());

        return redirect()->route('sterilizers.index')
                         ->with('success', 'Sterilizer berhasil ditambahkan.');
    }

    public function show(Sterilizer $sterilizer): View
    {
        $sterilizer->load('hospital');

        return view('sterilizers.show', compact('sterilizer'))
               ->with('types', self::TYPES);
    }

    public function edit(Sterilizer $sterilizer): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        return view('sterilizers.edit', compact('sterilizer', 'userHospitals'))
               ->with('types', self::TYPES);
    }

    public function update(UpdateSterilizeRequest $request, Sterilizer $sterilizer): RedirectResponse
    {
        $sterilizer->update($request->validated());

        return redirect()->route('sterilizers.index')
                         ->with('success', 'Sterilizer berhasil diperbarui.');
    }

    public function destroy(Sterilizer $sterilizer): RedirectResponse
    {
        $sterilizer->delete();

        return redirect()->route('sterilizers.index')
                         ->with('success', 'Sterilizer berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $sterilizer = Sterilizer::onlyTrashed()->findOrFail($id);
        $sterilizer->restore();

        return redirect()->route('sterilizers.index')
                         ->with('success', 'Sterilizer berhasil dipulihkan.');
    }

    public function toggleActive(Sterilizer $sterilizer): RedirectResponse
    {
        $sterilizer->update(['is_active' => !$sterilizer->is_active]);

        $status = $sterilizer->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Sterilizer berhasil {$status}.");
    }
}