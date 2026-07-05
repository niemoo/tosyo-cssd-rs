<?php

namespace App\Http\Controllers;

use App\Models\InstrumentItem;
use App\Models\Instrument;
use App\Http\Requests\StoreInstrumentItemRequest;
use App\Http\Requests\UpdateInstrumentItemRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InstrumentItemController extends Controller
{
    private const CONDITIONS = [
        'GOOD'         => ['label' => 'Baik',            'color' => 'green'],
        'DAMAGED'      => ['label' => 'Rusak',           'color' => 'red'],
        'UNDER_REPAIR' => ['label' => 'Sedang Perbaikan','color' => 'amber'],
        'RETIRED'      => ['label' => 'Pensiun',         'color' => 'gray'],
    ];

    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['code', 'serial_number', 'condition', 'total_cycles', 'purchased_at', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = InstrumentItem::with(['instrument.category', 'hospital'])
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
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%")
                  ->orWhereHas('instrument', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('instrument_id')) {
            $query->where('instrument_id', $request->instrument_id);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy($sortBy, $sortDir);

        $items = $query->paginate(15)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = InstrumentItem::query();
        if ($multiHospital) {
            $hospitalId
                ? $statsQuery->where('hospital_id', $hospitalId)
                : $statsQuery->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [
            'total'        => (clone $statsQuery)->count(),
            'good'         => (clone $statsQuery)->where('condition', 'GOOD')->count(),
            'damaged'      => (clone $statsQuery)->where('condition', 'DAMAGED')->count(),
            'under_repair' => (clone $statsQuery)->where('condition', 'UNDER_REPAIR')->count(),
            'retired'      => (clone $statsQuery)->where('condition', 'RETIRED')->count(),
        ];

        $instruments = Instrument::whereIn('hospital_id', $userHospitalIds)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        return view('instrument-items.index', compact(
            'items', 'instruments', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ))->with('conditions', self::CONDITIONS);
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $instruments = Instrument::where('hospital_id', session('active_hospital_id'))
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        return view('instrument-items.create', compact('userHospitals', 'instruments'))
               ->with('conditions', self::CONDITIONS);
    }

    public function store(StoreInstrumentItemRequest $request): RedirectResponse
    {
        InstrumentItem::create($request->validated());

        return redirect()->route('instrument-items.index')
                         ->with('success', 'Item instrumen berhasil ditambahkan.');
    }

    public function show(InstrumentItem $instrumentItem): View
    {
        $instrumentItem->load(['instrument.category', 'hospital', 'currentTray']);

        return view('instrument-items.show', compact('instrumentItem'))
               ->with('conditions', self::CONDITIONS);
    }

    public function edit(InstrumentItem $instrumentItem): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $instruments = Instrument::where('hospital_id', $instrumentItem->hospital_id)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        return view('instrument-items.edit', compact('instrumentItem', 'userHospitals', 'instruments'))
               ->with('conditions', self::CONDITIONS);
    }

    public function update(UpdateInstrumentItemRequest $request, InstrumentItem $instrumentItem): RedirectResponse
    {
        $instrumentItem->update($request->validated());

        return redirect()->route('instrument-items.index')
                         ->with('success', 'Item instrumen berhasil diperbarui.');
    }

    public function destroy(InstrumentItem $instrumentItem): RedirectResponse
    {
        $instrumentItem->delete();

        return redirect()->route('instrument-items.index')
                         ->with('success', 'Item instrumen berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $item = InstrumentItem::onlyTrashed()->findOrFail($id);
        $item->restore();

        return redirect()->route('instrument-items.index')
                         ->with('success', 'Item instrumen berhasil dipulihkan.');
    }

    public function toggleActive(InstrumentItem $instrumentItem): RedirectResponse
    {
        $instrumentItem->update(['is_active' => !$instrumentItem->is_active]);

        $status = $instrumentItem->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Item instrumen berhasil {$status}.");
    }
}