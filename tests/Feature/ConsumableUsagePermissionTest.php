<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\ConsumableCategory;
use App\Models\ConsumableMovement;
use App\Models\ConsumableStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ConsumableUsagePermissionTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function makeConsumableWithStock($hospital, int $quantity = 10): Consumable
    {
        $category = ConsumableCategory::create([
            'hospital_id' => $hospital->id,
            'name' => 'Kategori Consumable Test',
            'code' => 'CCAT-'.Str::upper(Str::random(8)),
            'is_active' => true,
        ]);

        $consumable = Consumable::create([
            'hospital_id' => $hospital->id,
            'category_id' => $category->id,
            'name' => 'Consumable Test',
            'code' => 'CONS-'.Str::upper(Str::random(8)),
            'unit' => 'PCS',
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        ConsumableStock::create([
            'hospital_id' => $hospital->id,
            'consumable_id' => $consumable->id,
            'quantity' => $quantity,
            'last_updated_at' => now(),
        ]);

        return $consumable;
    }

    public function test_user_without_permission_cannot_record_consumable_usage(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $consumable = $this->makeConsumableWithStock($hospital, 10);
        $tray = $this->makeTray($hospital);

        // No consumable-movements.create permission granted.
        $this->actingAsHospitalUser($user, $hospital, ['trays.edit']);

        $response = $this->post(route('consumable-usages.store'), [
            'usageable_type' => 'tray',
            'usageable_id' => $tray->id,
            'consumable_id' => $consumable->id,
            'quantity' => 2,
            'used_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertForbidden();

        $this->assertSame(10, ConsumableStock::where('consumable_id', $consumable->id)->first()->quantity);
        $this->assertDatabaseCount('consumable_movements', 0);
    }

    public function test_user_with_permission_can_record_consumable_usage(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $consumable = $this->makeConsumableWithStock($hospital, 10);
        $tray = $this->makeTray($hospital);

        $this->actingAsHospitalUser($user, $hospital, [
            'consumable-movements.create', 'trays.view', 'trays.edit',
        ]);

        $response = $this->post(route('consumable-usages.store'), [
            'usageable_type' => 'tray',
            'usageable_id' => $tray->id,
            'consumable_id' => $consumable->id,
            'quantity' => 3,
            'used_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('trays.show', $tray->id));

        $this->assertSame(7, ConsumableStock::where('consumable_id', $consumable->id)->first()->quantity);

        $movement = ConsumableMovement::where('consumable_id', $consumable->id)->first();
        $this->assertNotNull($movement, 'Expected a ConsumableMovement to be recorded.');
        $this->assertSame(ConsumableMovement::TYPE_OUT, $movement->type);
        $this->assertSame(-3, $movement->quantity);
    }

    public function test_user_with_trays_view_but_without_trays_edit_can_record_consumable_usage(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $consumable = $this->makeConsumableWithStock($hospital, 10);
        $tray = $this->makeTray($hospital);

        // No trays.edit granted — "mencatat pemakaian" bukan "mengedit tray".
        $this->actingAsHospitalUser($user, $hospital, [
            'consumable-movements.create', 'trays.view',
        ]);

        $response = $this->post(route('consumable-usages.store'), [
            'usageable_type' => 'tray',
            'usageable_id' => $tray->id,
            'consumable_id' => $consumable->id,
            'quantity' => 2,
            'used_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('trays.show', $tray->id));

        $this->assertSame(8, ConsumableStock::where('consumable_id', $consumable->id)->first()->quantity);
    }
}
