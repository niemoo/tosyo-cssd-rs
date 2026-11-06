<?php

namespace Tests\Feature;

use App\Http\Controllers\TraceabilityController;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

/**
 * The single most critical recall test (see CLAUDE.md / task brief): one physical tray is used
 * across two separate sterilization loads. Recalling Load A must attribute the issue to Unit X
 * (its own load's issue), and recalling Load B must attribute it to Unit Y — never mixing them up.
 * A naive implementation using "the tray's last ISSUE event" or `current_load_number` would wrongly
 * report Unit Y for both loads.
 */
class TraceabilityCrossLoadAttributionTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function buildImpactRows($batch)
    {
        $method = new ReflectionMethod(TraceabilityController::class, 'buildImpactRows');
        $method->setAccessible(true);

        return $method->invoke(new TraceabilityController, $batch);
    }

    public function test_recall_attributes_each_load_to_its_own_issue_not_the_latest_one(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $requester = $this->makeUser($hospital);

        $unitX = $this->makeUnit($hospital, ['name' => 'Unit X']);
        $unitY = $this->makeUnit($hospital, ['name' => 'Unit Y']);

        $loadA = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: ['load_number' => 'LOAD-A']);
        $loadB = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: ['load_number' => 'LOAD-B']);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_USE);

        $this->makeSterilizationBatchItem($loadA, $tray, SterilizationBatchItem::RESULT_PASSED);
        $this->makeSterilizationBatchItem($loadB, $tray, SterilizationBatchItem::RESULT_PASSED);

        $requestX = $this->makeDistributionRequest($hospital, $unitX, $requester);
        $requestY = $this->makeDistributionRequest($hospital, $unitY, $requester);

        // --- Load A: pass -> issue to Unit X -> return -> reprocess (severs sterility) ---
        $t1 = now()->subDays(10);
        $t2 = now()->subDays(9);
        $t3 = now()->subDays(8);
        $t4 = now()->subDays(7);
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $loadA, $t1);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, $requestX, $t2);
        $this->recordTrayEvent($tray, 'RETURN', Tray::STATUS_IN_USE, Tray::STATUS_RETURNED, $requestX, $t3);
        $this->recordTrayEvent($tray, 'REPROCESS', Tray::STATUS_RETURNED, Tray::STATUS_ASSEMBLING, null, $t4);

        // --- Load B: pass again -> issue to Unit Y (still at unit, not yet returned) ---
        $t5 = now()->subDays(6);
        $t6 = now()->subDays(5);
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $loadB, $t5);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, $requestY, $t6);

        $rowsA = $this->buildImpactRows($loadA->fresh());
        $rowsB = $this->buildImpactRows($loadB->fresh());

        $rowA = $rowsA->first();
        $rowB = $rowsB->first();

        $this->assertSame('RETURNED', $rowA->category);
        $this->assertSame($unitX->id, $rowA->distribution_request->unit->id, 'Load A must attribute Unit X, not the later Unit Y.');

        $this->assertSame('AT_UNIT', $rowB->category);
        $this->assertSame($unitY->id, $rowB->distribution_request->unit->id, 'Load B must attribute Unit Y.');
    }
}
