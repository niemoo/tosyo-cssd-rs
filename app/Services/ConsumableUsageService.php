<?php

namespace App\Services;

use App\Models\ConsumableStock;
use App\Models\ConsumableMovement;
use App\Models\ConsumableUsage;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConsumableUsageService
{
    /**
     * Catat pemakaian consumable, kurangi stok, dan buat record movement OUT.
     */
    public static function record(
        int $hospitalId,
        int $consumableId,
        Model $usageable,
        int $quantity,
        ?string $notes,
        int $usedBy,
        $usedAt = null
    ): ConsumableUsage {
        $usedAt = $usedAt ? Carbon::parse($usedAt) : now();

        $usage = ConsumableUsage::create([
            'hospital_id'    => $hospitalId,
            'consumable_id'  => $consumableId,
            'usageable_type' => get_class($usageable),
            'usageable_id'   => $usageable->id,
            'quantity'       => $quantity,
            'notes'          => $notes,
            'used_by'        => $usedBy,
            'used_at'        => $usedAt,
        ]);

        ConsumableMovement::create([
            'hospital_id'   => $hospitalId,
            'consumable_id' => $consumableId,
            'type'          => 'OUT',
            'quantity'      => -abs($quantity),
            'notes'         => $notes ?: ('Pemakaian ' . class_basename($usageable) . ' #' . $usageable->id),
            'handled_by'    => $usedBy,
            'moved_at'      => $usedAt,
        ]);

        $stock = ConsumableStock::firstOrNew([
            'hospital_id'   => $hospitalId,
            'consumable_id' => $consumableId,
        ]);

        $stock->quantity        = max(0, ($stock->quantity ?? 0) - abs($quantity));
        $stock->last_updated_at = $usedAt;
        $stock->save();

        return $usage;
    }
}