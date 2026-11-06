<?php

namespace Tests\Feature;

use App\Models\Tray;
use App\Models\TrayLifecycleEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ExpireSterileTraysCommandTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    public function test_expired_sterile_tray_moves_to_needs_reprocessing_with_expire_event(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDays(10),
            'expiry_at' => now()->subDay(),
            'current_load_number' => 'LOAD-2026-0001',
        ]);

        $this->artisan('trays:expire')->assertSuccessful();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_NEEDS_REPROCESSING, $tray->status);
        $this->assertNull($tray->sterile_at);
        $this->assertNull($tray->expiry_at);
        $this->assertNull($tray->current_load_number);

        $event = TrayLifecycleEvent::where('tray_id', $tray->id)->where('event_type', 'EXPIRE')->first();
        $this->assertNotNull($event, 'Expected an EXPIRE lifecycle event to be written.');
        $this->assertSame(Tray::STATUS_STERILE, $event->from_status);
        $this->assertSame(Tray::STATUS_NEEDS_REPROCESSING, $event->to_status);
        $this->assertSame('kedaluwarsa', $event->context['reason']);
        $this->assertNotNull($event->context['expired_at']);
        $this->assertNull($event->performed_by, 'System-triggered expiry has no acting user.');
    }

    public function test_sterile_tray_not_yet_expired_is_untouched(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(5),
        ]);

        $this->artisan('trays:expire')->assertSuccessful();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
        $this->assertNotNull($tray->expiry_at);
        $this->assertDatabaseMissing('tray_lifecycle_events', ['tray_id' => $tray->id, 'event_type' => 'EXPIRE']);
    }

    public function test_non_sterile_tray_with_past_expiry_at_is_untouched(): void
    {
        $hospital = $this->makeHospital();
        // e.g. a tray that was issued (IN_USE) before its sterile shelf life ran out —
        // expiry_at is a leftover from when it was still STERILE and must not be acted on.
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_USE, attrs: [
            'expiry_at' => now()->subDay(),
        ]);

        $this->artisan('trays:expire')->assertSuccessful();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_IN_USE, $tray->status);
        $this->assertDatabaseMissing('tray_lifecycle_events', ['tray_id' => $tray->id, 'event_type' => 'EXPIRE']);
    }

    public function test_running_command_twice_does_not_duplicate_the_expire_event(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'expiry_at' => now()->subDay(),
        ]);

        $this->artisan('trays:expire')->assertSuccessful();
        $this->artisan('trays:expire')->assertSuccessful();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_NEEDS_REPROCESSING, $tray->status);
        $this->assertSame(1, TrayLifecycleEvent::where('tray_id', $tray->id)->where('event_type', 'EXPIRE')->count());
    }
}
