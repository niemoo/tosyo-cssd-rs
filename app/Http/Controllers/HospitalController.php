<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHospitalRequest;
use App\Models\Hospital;
use App\Http\Requests\HospitalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HospitalController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        // Whitelist kolom yang boleh di-sort
        $allowedSorts = ['name', 'code', 'phone', 'units_count', 'users_count', 'is_active', 'created_at'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = Hospital::withCount(['units', 'users'])
                        ->withTrashed($request->boolean('show_deleted'));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $hospitals = $query->paginate(10)->withQueryString();

        $stats = [
            'total'    => Hospital::count(),
            'active'   => Hospital::where('is_active', true)->count(),
            'inactive' => Hospital::where('is_active', false)->count(),
        ];

        return view('hospitals.index', compact('hospitals', 'stats', 'sortBy', 'sortDir'));
    }

    public function create(): View
    {
        return view('hospitals.create');
    }

    public function store(HospitalRequest $request): RedirectResponse
    {
        Hospital::create($request->validated());

        return redirect()->route('hospitals.index')
                         ->with('success', 'Rumah sakit berhasil ditambahkan.');
    }

    public function show(Hospital $hospital): View
    {
        $hospital->loadCount(['units', 'users', 'instruments', 'trays', 'sterilizers']);

        $recentUsers = $hospital->users()
                                ->wherePivot('is_active', true)
                                ->with('roles')
                                ->latest('hospital_users.created_at')
                                ->take(5)
                                ->get();

        return view('hospitals.show', compact('hospital', 'recentUsers'));
    }

    public function edit(Hospital $hospital): View
    {
        return view('hospitals.edit', compact('hospital'));
    }

    public function update(UpdateHospitalRequest $request, Hospital $hospital): RedirectResponse
    {
        $hospital->update($request->validated());

        return redirect()->route('hospitals.index')
                         ->with('success', 'Rumah sakit berhasil diperbarui.');
    }

    public function destroy(Hospital $hospital): RedirectResponse
    {
        // Cek apakah masih ada user aktif
        $activeUsers = $hospital->hospitalUsers()->where('is_active', true)->count();

        if ($activeUsers > 0) {
            return redirect()->route('hospitals.index')
                             ->with('error', "Tidak dapat menghapus rumah sakit yang masih memiliki {$activeUsers} pengguna aktif.");
        }

        $hospital->delete();

        return redirect()->route('hospitals.index')
                         ->with('success', 'Rumah sakit berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $hospital = Hospital::onlyTrashed()->findOrFail($id);
        $hospital->restore();

        return redirect()->route('hospitals.index')
                         ->with('success', 'Rumah sakit berhasil dipulihkan.');
    }

    public function toggleActive(Hospital $hospital): RedirectResponse
    {
        $hospital->update(['is_active' => !$hospital->is_active]);

        $status = $hospital->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Rumah sakit berhasil {$status}.");
    }
}