<?php

namespace App\Http\Controllers;

use App\Models\Tray;
use App\Models\SterilizationBatch;
use App\Models\Consumable;
use App\Models\ConsumableStock;
use App\Http\Requests\StoreConsumableUsageRequest;
use App\Services\ConsumableUsageService;
use Illuminate\Http\RedirectResponse;

class ConsumableUsageController extends Controller
{
    public function store(StoreConsumableUsageRequest $request): RedirectResponse
    {
        $usageable = match ($request->usageable_type) {
            'tray'                => Tray::findOrFail($request->usageable_id),
            'sterilization_batch' => SterilizationBatch::findOrFail($request->usageable_id),
        };

        if ($usageable instanceof Tray) {
            abort_unless(auth()->user()->can('trays.edit'), 403);
            $redirectRoute = route('trays.show', $usageable->id);
        } else {
            abort_unless(auth()->user()->can('sterilization-batches.edit'), 403);
            $redirectRoute = route('sterilization-batches.show', $usageable->id);
        }

        // Cek stok
        $stock = ConsumableStock::where('hospital_id', $usageable->hospital_id)
                                ->where('consumable_id', $request->consumable_id)
                                ->first();

        $available = $stock?->quantity ?? 0;

        if ($request->quantity > $available) {
            $consumable = Consumable::find($request->consumable_id);
            return back()
                ->withErrors(['quantity' => "Stok {$consumable?->name} tidak cukup (tersedia: {$available} {$consumable?->unit})."])
                ->withInput()
                ->with('open_usage_form', true);
        }

        ConsumableUsageService::record(
            hospitalId: $usageable->hospital_id,
            consumableId: (int) $request->consumable_id,
            usageable: $usageable,
            quantity: (int) $request->quantity,
            notes: $request->notes,
            usedBy: auth()->id(),
            usedAt: $request->used_at,
        );

        return redirect($redirectRoute)->with('success', 'Pemakaian consumable berhasil dicatat.');
    }
}