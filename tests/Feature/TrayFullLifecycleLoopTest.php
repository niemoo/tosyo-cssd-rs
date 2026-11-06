<?php

namespace Tests\Feature;

use App\Models\Tray;
use App\Models\TrayLifecycleEvent;
use App\Services\TrayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class TrayFullLifecycleLoopTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    public function test_tray_completes_full_loop_with_seven_ordered_lifecycle_events(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_ASSEMBLING);

        TrayService::recordGenesis($tray);
        TrayService::transitionTo($tray, Tray::STATUS_READY);
        TrayService::transitionTo($tray, Tray::STATUS_IN_STERILIZATION);
        TrayService::transitionTo($tray, Tray::STATUS_STERILE);
        TrayService::transitionTo($tray, Tray::STATUS_IN_USE);
        TrayService::transitionTo($tray, Tray::STATUS_RETURNED);
        TrayService::transitionTo($tray, Tray::STATUS_ASSEMBLING);

        $tray->refresh();
        $this->assertSame(Tray::STATUS_ASSEMBLING, $tray->status);

        // Query directly rather than through Tray::lifecycleEvents(), which orders `latest()`
        // (newest first) for UI timelines — we need chronological order here.
        $events = TrayLifecycleEvent::where('tray_id', $tray->id)
            ->orderBy('performed_at')
            ->orderBy('id')
            ->get();

        $this->assertCount(7, $events);

        $expectedSequence = [
            ['ASSEMBLE_START', null, Tray::STATUS_ASSEMBLING],
            ['PACKAGE', Tray::STATUS_ASSEMBLING, Tray::STATUS_READY],
            ['LOAD', Tray::STATUS_READY, Tray::STATUS_IN_STERILIZATION],
            ['STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE],
            ['ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE],
            ['RETURN', Tray::STATUS_IN_USE, Tray::STATUS_RETURNED],
            ['REPROCESS', Tray::STATUS_RETURNED, Tray::STATUS_ASSEMBLING],
        ];

        foreach ($expectedSequence as $i => [$eventType, $from, $to]) {
            $this->assertSame($eventType, $events[$i]->event_type, "event #{$i}");
            $this->assertSame($from, $events[$i]->from_status, "event #{$i} from_status");
            $this->assertSame($to, $events[$i]->to_status, "event #{$i} to_status");
        }
    }
}
