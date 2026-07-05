<?php

namespace App\Http\Controllers;

use App\Models\StorageRack;
use App\Http\Requests\StoreStorageRackRequest;
use App\Http\Requests\UpdateStorageRackRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StorageRackController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'capacity', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = StorageRack::with('hospital')
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
                  ->orWhere('location_desc', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $racks = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = StorageRack::query();
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

        return view('storage-racks.index', compact(
            'racks', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('storage-racks.index')
                             ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

        return view('storage-racks.create', compact('userHospitals'));
    }

    public function store(StoreStorageRackRequest $request): RedirectResponse
    {
        StorageRack::create($request->validated());

        return redirect()->route('storage-racks.index')
                         ->with('success', 'Rak penyimpanan berhasil ditambahkan.');
    }

    public function show(StorageRack $storageRack): View
    {
        $storageRack->load('hospital');

        return view('storage-racks.show', compact('storageRack'));
    }

    public function edit(StorageRack $storageRack): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        return view('storage-racks.edit', compact('storageRack', 'userHospitals'));
    }

    public function update(UpdateStorageRackRequest $request, StorageRack $storageRack): RedirectResponse
    {
        $storageRack->update($request->validated());

        return redirect()->route('storage-racks.index')
                         ->with('success', 'Rak penyimpanan berhasil diperbarui.');
    }

    public function destroy(StorageRack $storageRack): RedirectResponse
    {
        $storageRack->delete();

        return redirect()->route('storage-racks.index')
                         ->with('success', 'Rak penyimpanan berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $rack = StorageRack::onlyTrashed()->findOrFail($id);
        $rack->restore();

        return redirect()->route('storage-racks.index')
                         ->with('success', 'Rak penyimpanan berhasil dipulihkan.');
    }

    public function toggleActive(StorageRack $storageRack): RedirectResponse
    {
        $storageRack->update(['is_active' => !$storageRack->is_active]);

        $status = $storageRack->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Rak penyimpanan berhasil {$status}.");
    }
}