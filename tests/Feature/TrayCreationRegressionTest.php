<?php

namespace Tests\Feature;

use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

/**
 * Locks down two bugs that previously reached production silently (see CLAUDE.md):
 * (1) the `barcode` column was missing from `trays`, so a tray could not be created at all;
 * (2) new trays defaulted to `is_active = false`, so they vanished from the sterilization
 *     pipeline (queries filter `is_active = true`) right after creation.
 */
class TrayCreationRegressionTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function storeTrayPayload(): array
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view', 'trays.create']);
        $instrumentItem = $this->makeInstrumentItem($hospital);

        return [$hospital, $instrumentItem];
    }

    public function test_tray_can_be_created_with_a_barcode_and_it_is_persisted(): void
    {
        [$hospital, $instrumentItem] = $this->storeTrayPayload();

        $response = $this->post(route('trays.store'), [
            'hospital_id' => $hospital->id,
            'code' => 'TRAY-CODE-001',
            'barcode' => 'BC-000123456',
            'name' => 'Tray Bedah Minor',
            'items' => [
                ['instrument_item_id' => $instrumentItem->id],
            ],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('trays.index'));

        $this->assertDatabaseHas('trays', [
            'code' => 'TRAY-CODE-001',
            'barcode' => 'BC-000123456',
        ]);
    }

    public function test_new_tray_defaults_to_active_even_when_field_is_omitted(): void
    {
        [$hospital, $instrumentItem] = $this->storeTrayPayload();

        $this->post(route('trays.store'), [
            'hospital_id' => $hospital->id,
            'code' => 'TRAY-CODE-002',
            'name' => 'Tray Bedah Mayor',
            'items' => [
                ['instrument_item_id' => $instrumentItem->id],
            ],
        ]);

        $tray = Tray::where('code', 'TRAY-CODE-002')->firstOrFail();

        $this->assertTrue($tray->is_active, 'New trays must default to active or they silently vanish from the sterilization pipeline.');
    }

    public function test_new_tray_is_visible_to_ready_status_query_used_when_starting_a_batch(): void
    {
        [$hospital, $instrumentItem] = $this->storeTrayPayload();

        $this->post(route('trays.store'), [
            'hospital_id' => $hospital->id,
            'code' => 'TRAY-CODE-003',
            'name' => 'Tray Bedah Umum',
            'items' => [
                ['instrument_item_id' => $instrumentItem->id],
            ],
        ]);

        $tray = Tray::where('code', 'TRAY-CODE-003')->firstOrFail();
        $tray->update(['status' => Tray::STATUS_READY]);

        // Same filter SterilizationBatchController::create() uses to list assignable trays.
        $visible = Tray::where('hospital_id', $hospital->id)
            ->where('status', Tray::STATUS_READY)
            ->where('is_active', true)
            ->pluck('id');

        $this->assertContains($tray->id, $visible);
    }
}
