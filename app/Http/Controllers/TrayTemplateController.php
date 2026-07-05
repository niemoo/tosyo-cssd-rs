<?php

namespace App\Http\Controllers;

use App\Models\TrayTemplate;
use App\Models\TrayTemplateItem;
use App\Models\Instrument;
use App\Models\Hospital;
use App\Http\Requests\StoreTrayTemplateRequest;
use App\Http\Requests\UpdateTrayTemplateRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TrayTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'code', 'is_active', 'is_lockable', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = TrayTemplate::with('hospital')
                             ->withCount(['templateItems as instruments_count' => function ($q) {
                                 $q->whereNull('deleted_at');
                             }])
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

        $templates = $query->paginate(10)->withQueryString();

        $hospitalId = $request->filled('hospital_id') && $multiHospital
            ? $request->hospital_id
            : null;

        $statsQuery = TrayTemplate::query();
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

        return view('tray-templates.index', compact(
            'templates', 'stats',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $instruments = Instrument::where('hospital_id', session('active_hospital_id'))
                                 ->where('is_active', true)
                                 ->with('category')
                                 ->orderBy('name')
                                 ->get();

        return view('tray-templates.create', compact('userHospitals', 'instruments'));
    }

    public function store(StoreTrayTemplateRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $template = TrayTemplate::create([
                'hospital_id' => $request->hospital_id,
                'name'        => $request->name,
                'code'        => $request->code,
                'description' => $request->description,
                'is_lockable' => $request->is_lockable,
                'is_active'   => $request->is_active,
            ]);

            if ($request->filled('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['instrument_id'])) {
                        $template->templateItems()->create([
                            'instrument_id' => $item['instrument_id'],
                            'quantity'      => $item['quantity'] ?? 1,
                            'is_active'     => true,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('tray-templates.index')
                         ->with('success', 'Tray template berhasil ditambahkan.');
    }

    public function show(TrayTemplate $trayTemplate): View
    {
        $trayTemplate->load(['hospital', 'templateItems.instrument.category']);

        return view('tray-templates.show', compact('trayTemplate'));
    }

    public function edit(TrayTemplate $trayTemplate): View
    {
        $trayTemplate->load(['templateItems.instrument.category']);

        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $instruments = Instrument::where('hospital_id', $trayTemplate->hospital_id)
                                 ->where('is_active', true)
                                 ->with('category')
                                 ->orderBy('name')
                                 ->get();

        return view('tray-templates.edit', compact('trayTemplate', 'userHospitals', 'instruments'));
    }

    public function update(UpdateTrayTemplateRequest $request, TrayTemplate $trayTemplate): RedirectResponse
    {
        DB::transaction(function () use ($request, $trayTemplate) {
            $trayTemplate->update([
                'name'        => $request->name,
                'description' => $request->description,
                'is_lockable' => $request->is_lockable,
                'is_active'   => $request->is_active,
            ]);

            // Hapus semua item lama lalu insert ulang
            $trayTemplate->templateItems()->delete();

            if ($request->filled('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['instrument_id'])) {
                        $trayTemplate->templateItems()->create([
                            'instrument_id' => $item['instrument_id'],
                            'quantity'      => $item['quantity'] ?? 1,
                            'is_active'     => true,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('tray-templates.index')
                         ->with('success', 'Tray template berhasil diperbarui.');
    }

    public function destroy(TrayTemplate $trayTemplate): RedirectResponse
    {
        if ($trayTemplate->trays()->count() > 0) {
            return redirect()->route('tray-templates.index')
                             ->with('error', 'Template tidak dapat dihapus karena masih digunakan oleh tray aktif.');
        }

        $trayTemplate->delete();

        return redirect()->route('tray-templates.index')
                         ->with('success', 'Tray template berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $template = TrayTemplate::onlyTrashed()->findOrFail($id);
        $template->restore();

        return redirect()->route('tray-templates.index')
                         ->with('success', 'Tray template berhasil dipulihkan.');
    }

    public function toggleActive(TrayTemplate $trayTemplate): RedirectResponse
    {
        $trayTemplate->update(['is_active' => !$trayTemplate->is_active]);

        $status = $trayTemplate->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Tray template berhasil {$status}.");
    }
}