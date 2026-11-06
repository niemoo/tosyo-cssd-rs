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
 * Exercises TraceabilityController::buildImpactRows() — the recall engine — via reflection
 * since it's private. We do NOT reimplement its logic here; we only assemble
 * tray_lifecycle_events/batch fixtures and assert on the categories the real method returns.
 */
class TraceabilityRecallTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function buildImpactRows($batch)
    {
        $method = new ReflectionMethod(TraceabilityController::class, 'buildImpactRows');
        $method->setAccessible(true);

        return $method->invoke(new TraceabilityController, $batch);
    }

    public function test_on_shelf_when_passed_and_never_issued(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PASSED);

        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $batch);

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('ON_SHELF', $rows->first()->category);
    }

    public function test_at_unit_when_issued_and_not_returned(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_USE);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PASSED);
        $request = $this->makeDistributionRequest($hospital, $unit, $requester);

        $t1 = now()->subDays(2);
        $t2 = now()->subDay();
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $batch, $t1);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, $request, $t2);

        $rows = $this->buildImpactRows($batch->fresh());
        $row = $rows->first();

        $this->assertSame('AT_UNIT', $row->category);
        $this->assertSame($request->id, $row->distribution_request->id);
        $this->assertSame($unit->id, $row->distribution_request->unit->id);
    }

    public function test_returned_when_issued_then_returned(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_RETURNED);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PASSED);
        $request = $this->makeDistributionRequest($hospital, $unit, $requester);

        $t1 = now()->subDays(3);
        $t2 = now()->subDays(2);
        $t3 = now()->subDay();
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $batch, $t1);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, $request, $t2);
        $this->recordTrayEvent($tray, 'RETURN', Tray::STATUS_IN_USE, Tray::STATUS_RETURNED, $request, $t3);

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('RETURNED', $rows->first()->category);
    }

    public function test_severed_when_passed_then_reprocessed_without_issue(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_ASSEMBLING);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PASSED);

        $t1 = now()->subDays(2);
        $t2 = now()->subDay();
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $batch, $t1);
        $this->recordTrayEvent($tray, 'EXPIRE', Tray::STATUS_STERILE, Tray::STATUS_NEEDS_REPROCESSING, null, $t2);

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('SEVERED', $rows->first()->category);
    }

    public function test_failed_when_batch_item_result_is_failed(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_NEEDS_REPROCESSING);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_FAILED);

        $this->recordTrayEvent($tray, 'STERILIZE_FAIL', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_NEEDS_REPROCESSING, $batch);

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('FAILED', $rows->first()->category);
    }

    public function test_pending_when_batch_item_result_not_yet_entered(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PENDING);

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('PENDING', $rows->first()->category);
    }

    /**
     * Regression: a PASSED item with no STERILIZE_PASS event trail for this batch must be
     * flagged UNTRACEABLE — not silently treated as ON_SHELF. This is the patient-safety bug
     * that previously slipped through (a tray with no history was reported as "safe on shelf").
     */
    public function test_untraceable_when_passed_but_no_sterilize_pass_event_exists(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PASSED);

        // No tray_lifecycle_events at all for this tray — simulates pre-tracking legacy data.

        $rows = $this->buildImpactRows($batch->fresh());

        $this->assertSame('UNTRACEABLE', $rows->first()->category);
        $this->assertNotSame('ON_SHELF', $rows->first()->category);
    }

    public function test_untraceable_when_sterilize_pass_event_belongs_to_a_different_batch(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batchA = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $batchB = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE);
        $this->makeSterilizationBatchItem($batchB, $tray, SterilizationBatchItem::RESULT_PASSED);

        // Pass event references batch A, but we're recalling batch B.
        $this->recordTrayEvent($tray, 'STERILIZE_PASS', Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, $batchA);

        $rows = $this->buildImpactRows($batchB->fresh());

        $this->assertSame('UNTRACEABLE', $rows->first()->category);
    }
}
