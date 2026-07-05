<?php

namespace App\Http\Controllers;

use App\Http\Requests\FulfillDistributionRequestRequest;
use App\Models\DistributionRequest;
use App\Models\DistributionRequestItem;
use App\Models\Tray;
use App\Models\TrayTemplate;
use App\Models\Unit;
use App\Http\Requests\StoreDistributionRequestRequest;
use App\Http\Requests\UpdateDistributionRequestRequest;
use App\Http\Requests\ApproveDistributionRequestRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DistributionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['request_number', 'status', 'requested_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';

        $userHospitals   = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $multiHospital   = $userHospitals->count() > 1;
        $userHospitalIds = $userHospitals->pluck('id')->toArray();

        $query = DistributionRequest::with(['hospital', 'unit', 'requester', 'approver', 'items'])
                                    ->withTrashed($request->boolean('show_deleted'));

        if ($multiHospital) {
            $request->filled('hospital_id')
                ? $query->where('hospital_id', $request->hospital_id)
                : $query->whereIn('hospital_id', $userHospitalIds);
        } else {
            $query->where('hospital_id', session('active_hospital_id'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhereHas('unit', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $query->orderBy($sortBy, $sortDir);
        $requests = $query->paginate(10)->withQueryString();

        $statsQuery = DistributionRequest::whereIn('hospital_id', $userHospitalIds);
        $stats = [
            'total'             => (clone $statsQuery)->count(),
            'pending_approval'  => (clone $statsQuery)->where('status', DistributionRequest::STATUS_PENDING_APPROVAL)->count(),
            'approved'          => (clone $statsQuery)->where('status', DistributionRequest::STATUS_APPROVED)->count(),
            'fulfilled'         => (clone $statsQuery)->where('status', DistributionRequest::STATUS_FULFILLED)->count(),
        ];

        $units = Unit::whereIn('hospital_id', $userHospitalIds)
                     ->where('is_active', true)
                     ->orderBy('name')
                     ->get();

        return view('distribution-requests.index', compact(
            'requests', 'stats', 'units',
            'sortBy', 'sortDir',
            'multiHospital', 'userHospitals'
        ));
    }

    public function create(): View
    {
        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();
        $hospitalId    = session('active_hospital_id');

        $units = Unit::where('hospital_id', $hospitalId)
                     ->where('is_active', true)
                     ->orderBy('name')
                     ->get();

        $templates = TrayTemplate::where('hospital_id', $hospitalId)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        $requestNumber = 'REQ-' . date('Y') . '-' . str_pad(
            DistributionRequest::where('hospital_id', $hospitalId)->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        return view('distribution-requests.create', compact(
            'userHospitals', 'units', 'templates', 'requestNumber'
        ));
    }

    public function store(StoreDistributionRequestRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $status = $request->submit_type === 'submit'
                ? DistributionRequest::STATUS_PENDING_APPROVAL
                : DistributionRequest::STATUS_DRAFT;

            $distributionRequest = DistributionRequest::create([
                'hospital_id'    => $request->hospital_id,
                'unit_id'        => $request->unit_id,
                'request_number' => 'REQ-' . date('Y') . '-' . str_pad(
                    DistributionRequest::where('hospital_id', $request->hospital_id)->count() + 1,
                    3, '0', STR_PAD_LEFT
                ),
                'status'         => $status,
                'requested_by'   => auth()->id(),
                'requested_at'   => now(),
                'notes'          => $request->notes,
            ]);

            foreach ($request->items as $item) {
                DistributionRequestItem::create([
                    'request_id'  => $distributionRequest->id,
                    'template_id' => $item['template_id'] ?? null,
                    'quantity'    => $item['quantity'],
                    'notes'       => $item['notes'] ?? null,
                ]);
            }
        });

        $message = $request->submit_type === 'submit'
            ? 'Permintaan berhasil diajukan dan menunggu approval.'
            : 'Permintaan berhasil disimpan sebagai draft.';

        return redirect()->route('distribution-requests.index')->with('success', $message);
    }

    public function show(DistributionRequest $distributionRequest): View
    {
        $distributionRequest->load([
            'hospital', 'unit', 'requester', 'approver', 'fulfiller',
            'items.template', 'items.tray',
            'trayReturns.tray', 'trayReturns.receiver',
        ]);

        return view('distribution-requests.show', compact('distributionRequest'));
    }

    public function edit(DistributionRequest $distributionRequest): View
    {
        abort_if(
            !in_array($distributionRequest->status, [DistributionRequest::STATUS_DRAFT, DistributionRequest::STATUS_REJECTED]),
            403, 'Permintaan hanya bisa diedit saat status Draft atau Ditolak.'
        );

        $userHospitals = auth()->user()->hospitals()->wherePivot('is_active', true)->get();

        $units = Unit::where('hospital_id', $distributionRequest->hospital_id)
                     ->where('is_active', true)
                     ->orderBy('name')
                     ->get();

        $templates = TrayTemplate::where('hospital_id', $distributionRequest->hospital_id)
                                 ->where('is_active', true)
                                 ->orderBy('name')
                                 ->get();

        $distributionRequest->load('items');

        return view('distribution-requests.edit', compact(
            'distributionRequest', 'userHospitals', 'units', 'templates'
        ));
    }

    public function update(UpdateDistributionRequestRequest $request, DistributionRequest $distributionRequest): RedirectResponse
    {
        abort_if(
            !in_array($distributionRequest->status, [DistributionRequest::STATUS_DRAFT, DistributionRequest::STATUS_REJECTED]),
            403, 'Permintaan hanya bisa diedit saat status Draft atau Ditolak.'
        );

        DB::transaction(function () use ($request, $distributionRequest) {
            $wasRejected = $distributionRequest->status === DistributionRequest::STATUS_REJECTED;
            $status = $request->submit_type === 'submit'
                ? DistributionRequest::STATUS_PENDING_APPROVAL
                : DistributionRequest::STATUS_DRAFT;

            $distributionRequest->update([
                'unit_id'         => $request->unit_id,
                'status'          => $status,
                'notes'           => $request->notes,
                'revision_notes'  => $wasRejected && $request->submit_type === 'submit' ? 'Direvisi dan diajukan ulang' : $distributionRequest->revision_notes,
                'rejection_notes' => $status === DistributionRequest::STATUS_PENDING_APPROVAL ? null : $distributionRequest->rejection_notes,
                'approved_by'     => null,
                'approved_at'     => null,
            ]);

            // Hapus items lama, insert ulang
            $distributionRequest->items()->delete();

            foreach ($request->items as $item) {
                DistributionRequestItem::create([
                    'request_id'  => $distributionRequest->id,
                    'template_id' => $item['template_id'] ?? null,
                    'quantity'    => $item['quantity'],
                    'notes'       => $item['notes'] ?? null,
                ]);
            }
        });

        $message = $request->submit_type === 'submit'
            ? 'Permintaan berhasil diajukan ulang dan menunggu approval.'
            : 'Permintaan berhasil disimpan sebagai draft.';

        return redirect()->route('distribution-requests.index')->with('success', $message);
    }

    public function destroy(DistributionRequest $distributionRequest): RedirectResponse
    {
        abort_if(
            $distributionRequest->status !== DistributionRequest::STATUS_DRAFT,
            403, 'Hanya permintaan dengan status Draft yang bisa dihapus.'
        );

        $distributionRequest->delete();

        return redirect()->route('distribution-requests.index')
                         ->with('success', 'Permintaan berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        DistributionRequest::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('distribution-requests.index')
                         ->with('success', 'Permintaan berhasil dipulihkan.');
    }

    public function approve(ApproveDistributionRequestRequest $request, DistributionRequest $distributionRequest): RedirectResponse
    {
        abort_if(
            !$distributionRequest->canBeApproved(),
            403, 'Permintaan ini tidak dapat diproses (sudah bukan status Menunggu Approval).'
        );

        if ($request->decision === 'approve') {
            $distributionRequest->update([
                'status'      => DistributionRequest::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $message = 'Permintaan berhasil disetujui.';
        } else {
            $distributionRequest->update([
                'status'          => DistributionRequest::STATUS_REJECTED,
                'approved_by'     => auth()->id(),
                'approved_at'     => now(),
                'rejection_notes' => $request->rejection_notes,
            ]);
            $message = 'Permintaan berhasil ditolak.';
        }

        return redirect()->route('distribution-requests.show', $distributionRequest)
                         ->with('success', $message);
    }

    public function fulfill(DistributionRequest $distributionRequest): View
    {
        abort_if(
            !$distributionRequest->canBeFulfilled(),
            403, 'Permintaan ini belum bisa diproses (status harus Disetujui atau Diproses).'
        );

        $distributionRequest->load(['items.template', 'items.tray']);

        // Tray steril yang tersedia (belum di-assign ke request manapun yang masih aktif)
        $availableTrays = Tray::where('hospital_id', $distributionRequest->hospital_id)
                            ->where('status', Tray::STATUS_STERILE)
                            ->where('is_active', true)
                            ->with('template')
                            ->orderBy('code')
                            ->get();

        $trayOptions = $availableTrays->map(fn($t) => [
            'id'          => (string) $t->id,
            'label'       => $t->code . ' — ' . $t->name . ($t->template ? ' (' . $t->template->name . ')' : ' (Bebas)'),
            'template_id' => $t->template_id ? (string) $t->template_id : null,
        ])->values()->toJson();

        return view('distribution-requests.fulfill', compact(
            'distributionRequest', 'availableTrays', 'trayOptions'
        ));
    }

    public function processFulfillment(FulfillDistributionRequestRequest $request, DistributionRequest $distributionRequest): RedirectResponse
    {
        abort_if(
            !$distributionRequest->canBeFulfilled(),
            403, 'Permintaan ini belum bisa diproses.'
        );

        $assignments = $request->input('assignments', []);

        if (empty($assignments)) {
            return redirect()->route('distribution-requests.fulfill', $distributionRequest)
                            ->withErrors(['assignments' => 'Pilih minimal 1 tray untuk di-assign sebelum mengirim.'])
                            ->withInput();
        }

        // Cek duplikasi tray_id (tray yang sama tidak boleh dipilih untuk 2 item berbeda)
        $trayIds = array_column($assignments, 'tray_id');
        if (count($trayIds) !== count(array_unique($trayIds))) {
            return redirect()->route('distribution-requests.fulfill', $distributionRequest)
                            ->withErrors(['assignments' => 'Satu tray tidak boleh di-assign ke lebih dari satu item.'])
                            ->withInput();
        }

        // Validasi semua tray masih STERILE & milik hospital yang sama
        $trays = Tray::whereIn('id', $trayIds)->get();
        foreach ($trays as $tray) {
            if ($tray->status !== Tray::STATUS_STERILE || $tray->hospital_id !== $distributionRequest->hospital_id) {
                return redirect()->route('distribution-requests.fulfill', $distributionRequest)
                                ->withErrors(['assignments' => "Tray {$tray->code} sudah tidak tersedia (bukan berstatus Steril). Silakan pilih tray lain."])
                                ->withInput();
            }
        }

        DB::transaction(function () use ($assignments, $distributionRequest) {
            foreach ($assignments as $assignment) {
                $item = DistributionRequestItem::where('request_id', $distributionRequest->id)
                                                ->findOrFail($assignment['item_id']);

                $item->update(['tray_id' => $assignment['tray_id']]);

                Tray::find($assignment['tray_id'])?->update([
                    'status'          => Tray::STATUS_IN_USE,
                    'current_rack_id' => null,
                ]);
            }

            $allAssigned = $distributionRequest->items()->whereNull('tray_id')->doesntExist();

            $distributionRequest->update([
                'status'       => $allAssigned
                    ? DistributionRequest::STATUS_FULFILLED
                    : DistributionRequest::STATUS_IN_PROCESS,
                'fulfilled_by' => auth()->id(),
                'fulfilled_at' => $allAssigned ? now() : $distributionRequest->fulfilled_at,
            ]);
        });

        $message = $distributionRequest->refresh()->status === DistributionRequest::STATUS_FULFILLED
            ? 'Semua tray berhasil dikirim ke unit. Permintaan selesai.'
            : 'Sebagian tray berhasil di-assign. Lanjutkan untuk item tersisa.';

        return redirect()->route('distribution-requests.show', $distributionRequest)
                        ->with('success', $message);
    }
}