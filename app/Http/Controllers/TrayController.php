<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\Tray;
use App\Models\TrayItem;
use App\Models\TrayTemplate;
use App\Models\InstrumentItem;
use App\Http\Requests\StoreTrayRequest;
use App\Http\Requests\UpdateTrayRequest;
use App\Services\ConsumableUsageService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TrayController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['code', 'name', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = Tray::with(['hospital', 'template', 'currentRack'])
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
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('template_id')) {
            if ($request->template_id === 'free') {
                $query->whereNull('template_id');
            } else {
                $query->where('template_id', $request->template_id);
            }
        }

        $query->orderBy($sortBy, $sortDir);

        $trays = $query->paginate(10)->withQueryString();

        $statsQuery = Tray::query();
        if ($multiHospital) {
            $statsQuery->whereIn('hospital_id', $userHospitalIds);
        } else {
            $statsQuery->where('hospital_id', session('active_hospital_id'));
        }

        $stats = [];
        foreach (Tray::STATUSES as $key => $val) {
            $stats[$key] = (clone $statsQuery)->where('status', $key)->count();
        }

        $templates = TrayTemplate::whereIn('hospital_id', $userHospitalIds)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        return view('trays.index', compact(
            'trays', 'stats', 'templates',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $templates = TrayTemplate::where('hospital_id', session('active_hospital_id'))
                                ->where('is_active', true)
                                ->with('templateItems.instrument')
                                ->orderBy('name')
                                ->get();

        $instrumentItems = InstrumentItem::where('hospital_id', session('active_hospital_id'))
                                        ->where('is_active', true)
                                        ->where('condition', 'GOOD')
                                        ->with('instrument.category')
                                        ->orderBy('code')
                                        ->get();

        $instrumentOptions = $instrumentItems->map(fn($i) => [
            'id'    => (string) $i->id,
            'label' => $i->code . ' — ' . $i->instrument->name . ' (' . $i->instrument->category->name . ')',
        ])->values()->toJson();

        $templateOptions = $templates->map(fn($t) => [
            'id'    => (string) $t->id,
            'label' => $t->code . ' — ' . $t->name,
            'items' => $t->templateItems->map(fn($item) => [
                'instrument_name' => $item->instrument->name,
                'quantity'        => $item->quantity,
            ])->toArray(),
        ])->values()->toJson();

        $consumables = Consumable::where('hospital_id', session('active_hospital_id'))
                                ->where('is_active', true)
                                ->with('stock')
                                ->orderBy('name')
                                ->get();

        return view('trays.create', compact(
            'userHospitals', 'templates',
            'instrumentOptions', 'templateOptions', 'consumables'
        ));
    }

    public function store(StoreTrayRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $tray = Tray::create([
                'hospital_id'  => $request->hospital_id,
                'template_id'  => $request->template_id,
                'code'         => $request->code,
                'name'         => $request->name,
                'barcode'      => $request->barcode,
                'status'       => Tray::STATUS_ASSEMBLING,
                'assembled_by' => auth()->id(),
                'assembled_at' => now(),
                'notes'        => $request->notes,
                'is_active'    => $request->is_active,
            ]);

            foreach ($request->items as $item) {
                TrayItem::create([
                    'tray_id'            => $tray->id,
                    'instrument_item_id' => $item['instrument_item_id'],
                    'notes'              => $item['notes'] ?? null,
                ]);

                InstrumentItem::find($item['instrument_item_id'])
                    ?->increment('total_cycles');
            }

            foreach ($request->input('consumable_usages', []) as $row) {
                if (empty($row['consumable_id']) || empty($row['quantity'])) continue;

                ConsumableUsageService::record(
                    hospitalId: $tray->hospital_id,
                    consumableId: (int) $row['consumable_id'],
                    usageable: $tray,
                    quantity: (int) $row['quantity'],
                    notes: $row['notes'] ?? null,
                    usedBy: auth()->id(),
                    usedAt: $tray->assembled_at,
                );
            }
        });

        return redirect()->route('trays.index')
                        ->with('success', 'Tray berhasil dibuat.');
    }

    public function show(Tray $tray): View
    {
        $tray->load([
            'hospital', 'template', 'currentRack', 'assembler',
            'items.instrumentItem.instrument.category',
            'sterilizationBatchItems.batch.sterilizer',
            'consumableUsages.consumable', 'consumableUsages.usedBy',
        ]);

        $consumables = Consumable::where('hospital_id', $tray->hospital_id)
                                ->where('is_active', true)
                                ->with('stock')
                                ->orderBy('name')
                                ->get();

        return view('trays.show', compact('tray', 'consumables'));
    }

    public function edit(Tray $tray): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $tray->load(['items.instrumentItem.instrument.category']);

        $instrumentItems = InstrumentItem::where('hospital_id', $tray->hospital_id)
                                         ->where('is_active', true)
                                         ->where('condition', 'GOOD')
                                         ->with('instrument.category')
                                         ->orderBy('code')
                                         ->get();

        $instrumentOptions = $instrumentItems->map(fn($i) => [
            'id'    => (string) $i->id,
            'label' => $i->code . ' — ' . $i->instrument->name . ' (' . $i->instrument->category->name . ')',
        ])->values()->toJson();

        $existingItems = $tray->items->map(fn($i) => [
            'instrument_item_id' => (string) $i->instrument_item_id,
            'notes'              => $i->notes ?? '',
        ])->values()->toJson();

        return view('trays.edit', compact(
            'tray', 'userHospitals',
            'instrumentOptions', 'existingItems'
        ));
    }

    public function update(UpdateTrayRequest $request, Tray $tray): RedirectResponse
    {
        DB::transaction(function () use ($request, $tray) {
            $tray->update([
                'name'      => $request->name,
                'barcode'   => $request->barcode,
                'notes'     => $request->notes,
                'is_active' => $request->is_active,
            ]);

            // Hapus semua item lama, insert ulang
            $tray->items()->delete();

            foreach ($request->items as $item) {
                TrayItem::create([
                    'tray_id'            => $tray->id,
                    'instrument_item_id' => $item['instrument_item_id'],
                    'notes'              => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('trays.index')
                         ->with('success', 'Tray berhasil diperbarui.');
    }

    public function destroy(Tray $tray): RedirectResponse
    {
        $tray->delete();

        return redirect()->route('trays.index')
                         ->with('success', 'Tray berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        Tray::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('trays.index')
                         ->with('success', 'Tray berhasil dipulihkan.');
    }
}