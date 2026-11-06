<?php

namespace Tests\Feature;

use App\Models\Tray;
use App\Models\TrayLifecycleEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class TrayUnpackTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    public function test_ready_tray_can_be_unpacked_back_to_assembling(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view', 'trays.edit']);

        $response = $this->patch(route('trays.unpack', $tray));

        $response->assertRedirect(route('trays.show', $tray));

        $tray->refresh();
        $this->assertSame(Tray::STATUS_ASSEMBLING, $tray->status);

        $event = TrayLifecycleEvent::where('tray_id', $tray->id)->where('event_type', 'REPROCESS')->first();
        $this->assertNotNull($event);
        $this->assertSame(Tray::STATUS_READY, $event->from_status);
        $this->assertSame(Tray::STATUS_ASSEMBLING, $event->to_status);
    }

    public function test_tray_not_in_ready_status_cannot_be_unpacked(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_ASSEMBLING);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view', 'trays.edit']);

        $response = $this->patch(route('trays.unpack', $tray));

        $response->assertRedirect(route('trays.show', $tray));
        $response->assertSessionHas('error');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_ASSEMBLING, $tray->status);
        $this->assertDatabaseMissing('tray_lifecycle_events', ['tray_id' => $tray->id, 'event_type' => 'REPROCESS']);
    }

    public function test_user_without_trays_edit_gets_403(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->patch(route('trays.unpack', $tray));

        $response->assertForbidden();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_READY, $tray->status);
    }
}
