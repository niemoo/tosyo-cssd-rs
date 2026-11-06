<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrayRequest;
use App\Http\Requests\UpdateTrayRequest;
use App\Models\Consumable;
use App\Models\DistributionRequest;
use App\Models\InstrumentItem;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use App\Models\TrayItem;
use App\Models\TrayTemplate;
use App\Services\ConsumableUsageService;
use App\Services\TrayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrayController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['code', 'name', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital = $userHospitals->count() > 1;
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
            // Perilaku stasiun-scan: kode/barcode yang cocok PERSIS dengan tepat satu tray
            // langsung lompat ke detailnya, tanpa perlu halaman/menu telusur terpisah.
            $exactMatches = (clone $query)
                ->where(function ($q) use ($request) {
                    $q->where('code', $request->search)
                        ->orWhere('barcode', $request->search);
                })
                ->limit(2)
                ->get();

            if ($exactMatches->count() === 1) {
                return redirect()->route('trays.show', $exactMatches->first()->id);
            }

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

    public function create(): View|RedirectResponse
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        if ($userHospitals->isEmpty()) {
            return redirect()->route('trays.index')
                ->with('error', 'Anda tidak terdaftar di rumah sakit aktif manapun. Hubungi administrator.');
        }

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

        $instrumentOptions = $instrumentItems->map(fn ($i) => [
            'id' => (string) $i->id,
            'label' => $i->code.' — '.$i->instrument->name.' ('.$i->instrument->category->name.')',
        ])->values();

        $templateOptions = $templates->map(fn ($t) => [
            'id' => (string) $t->id,
            'label' => $t->code.' — '.$t->name,
            'items' => $t->templateItems->map(fn ($item) => [
                'instrument_name' => $item->instrument->name,
                'quantity' => $item->quantity,
            ])->toArray(),
        ])->values();

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
                'hospital_id' => $request->hospital_id,
                'template_id' => $request->template_id,
                'code' => $request->code,
                'name' => $request->name,
                'barcode' => $request->barcode,
                'status' => Tray::STATUS_ASSEMBLING,
                'assembled_by' => auth()->id(),
                'assembled_at' => now(),
                'notes' => $request->notes,
                'is_active' => $request->is_active,
            ]);

            TrayService::recordGenesis($tray);

            foreach ($request->items as $item) {
                TrayItem::create([
                    'tray_id' => $tray->id,
                    'instrument_item_id' => $item['instrument_item_id'],
                    'notes' => $item['notes'] ?? null,
                ]);
                // Catatan: total_cycles ditambah saat STERILIZE_PASS (satu siklus autoklaf nyata),
                // bukan saat perakitan. Lihat SterilizationBatchController::updateResult.
            }

            foreach ($request->input('consumable_usages', []) as $row) {
                if (empty($row['consumable_id']) || empty($row['quantity'])) {
                    continue;
                }

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
            'lifecycleEvents.performer', 'lifecycleEvents.reference',
        ]);

        $consumables = Consumable::where('hospital_id', $tray->hospital_id)
            ->where('is_active', true)
            ->with('stock')
            ->orderBy('name')
            ->get();

        // "Siklus Tercatat": berapa kali TRAY ini lulus sterilisasi sejak pencatatan digital ada —
        // akan jauh lebih rendah dari umur pakai sebenarnya untuk tray/instrumen lama, karena itu
        // ditampilkan berdampingan dengan siklus instrumen (lihat di bawah), bukan menggantikannya.
        $recordedCycles = $tray->lifecycleEvents->where('event_type', 'STERILIZE_PASS')->count();

        // "Siklus Instrumen (tertinggi)": keausan fisik instrumen yang sebenarnya, dari
        // instrument_items.total_cycles — sumber ini sudah ada sebelum tray_lifecycle_events dan
        // tidak ikut ter-reset saat instrumen berpindah tray.
        $maxInstrumentCycles = $tray->items->max(fn ($item) => $item->instrumentItem?->total_cycles);

        // Panel "Langkah Berikutnya": konteks tambahan sesuai status tray saat ini.
        $pendingBatch = $tray->status === Tray::STATUS_IN_STERILIZATION
            ? $tray->sterilizationBatchItems->firstWhere('result', SterilizationBatchItem::RESULT_PENDING)?->batch
            : null;

        $issuedTo = null;
        if ($tray->status === Tray::STATUS_IN_USE) {
            $issueEvent = $tray->lifecycleEvents->firstWhere('event_type', 'ISSUE');
            if ($issueEvent?->reference instanceof DistributionRequest) {
                $issuedTo = $issueEvent->reference->loadMissing('unit');
            }
        }

        return view('trays.show', compact(
            'tray', 'consumables', 'recordedCycles', 'maxInstrumentCycles',
            'pendingBatch', 'issuedTo'
        ));
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

        $instrumentOptions = $instrumentItems->map(fn ($i) => [
            'id' => (string) $i->id,
            'label' => $i->code.' — '.$i->instrument->name.' ('.$i->instrument->category->name.')',
        ])->values();

        $existingItems = $tray->items->map(fn ($i) => [
            'instrument_item_id' => (string) $i->instrument_item_id,
            'notes' => $i->notes ?? '',
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
                'name' => $request->name,
                'barcode' => $request->barcode,
                'notes' => $request->notes,
                'is_active' => $request->is_active,
            ]);

            // Hapus semua item lama, insert ulang
            $tray->items()->delete();

            foreach ($request->items as $item) {
                TrayItem::create([
                    'tray_id' => $tray->id,
                    'instrument_item_id' => $item['instrument_item_id'],
                    'notes' => $item['notes'] ?? null,
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

    // Tandai tray selesai dirakit & dikemas → siap masuk sterilisasi.
    public function markReady(Tray $tray): RedirectResponse
    {
        if (! $tray->canBeMarkedReady()) {
            return redirect()->route('trays.show', $tray)
                ->with('error', 'Tray hanya bisa ditandai siap saat berstatus Dirakit.');
        }

        if ($tray->items()->count() === 0) {
            return redirect()->route('trays.show', $tray)
                ->with('error', 'Tray belum berisi instrumen. Lengkapi dulu sebelum ditandai siap.');
        }

        TrayService::transitionTo($tray, Tray::STATUS_READY);

        return redirect()->route('trays.show', $tray)
            ->with('success', 'Tray ditandai siap sterilisasi.');
    }

    // Tray dikembalikan/gagal → kembali ke perakitan untuk diproses ulang (menutup loop).
    public function reprocess(Tray $tray): RedirectResponse
    {
        if (! $tray->canBeReprocessed()) {
            return redirect()->route('trays.show', $tray)
                ->with('error', 'Tray hanya bisa diproses ulang saat berstatus Dikembalikan atau Perlu Proses Ulang.');
        }

        TrayService::transitionTo($tray, Tray::STATUS_ASSEMBLING, attributes: ['current_rack_id' => null]);

        return redirect()->route('trays.show', $tray)
            ->with('success', 'Tray dikembalikan ke perakitan untuk diproses ulang.');
    }

    // Tray salah kemas/kurang instrumen → buka lagi kemasannya untuk dirakit ulang.
    public function unpack(Tray $tray): RedirectResponse
    {
        if (! $tray->canBeUnpacked()) {
            return redirect()->route('trays.show', $tray)
                ->with('error', 'Tray hanya bisa dibuka kemasannya saat berstatus Siap Sterilisasi.');
        }

        TrayService::transitionTo($tray, Tray::STATUS_ASSEMBLING);

        return redirect()->route('trays.show', $tray)
            ->with('success', 'Kemasan tray dibuka, kembali ke perakitan.');
    }

    // Label pak steril — dicetak setelah tray lulus sterilisasi, sebelum masuk rak.
    public function label(Tray $tray): View
    {
        abort_if(! $tray->sterile_at, 403, 'Label hanya bisa dicetak untuk tray yang sudah steril.');

        $tray->load(['hospital', 'template', 'assembler', 'items', 'lifecycleEvents.performer']);

        return view('trays.label', compact('tray'));
    }
}
