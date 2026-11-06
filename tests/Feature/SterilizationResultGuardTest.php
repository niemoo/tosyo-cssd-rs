<?php

namespace Tests\Feature;

use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class SterilizationResultGuardTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function submitResult(SterilizationBatch $batch, array $payload)
    {
        return $this->patch(route('sterilization-batches.result', $batch), $payload);
    }

    private function setUpInProgressBatchWithTray(): array
    {
        $hospital = $this->makeHospital(['sterile_shelf_life_days' => 14]);
        $sterilizer = $this->makeSterilizer($hospital);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, [
            'sterilization-batches.view', 'sterilization-batches.edit',
        ]);

        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $user, SterilizationBatch::STATUS_IN_PROGRESS);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $batchItem = $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PENDING);

        return compact('hospital', 'batch', 'tray', 'batchItem');
    }

    public function test_bi_positive_blocks_any_tray_from_being_passed(): void
    {
        ['batch' => $batch, 'tray' => $tray] = $this->setUpInProgressBatchWithTray();

        $response = $this->submitResult($batch, [
            'bi_result' => 'POSITIVE',
            'results' => [
                ['tray_id' => $tray->id, 'result' => 'PASSED', 'ci_result' => 'PASS'],
            ],
        ]);

        $response->assertSessionHasErrors();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_IN_STERILIZATION, $tray->status, 'BI positive must block passing any tray.');

        $batch->refresh();
        $this->assertSame(SterilizationBatch::STATUS_IN_PROGRESS, $batch->status);
    }

    public function test_ci_not_pass_blocks_tray_from_being_passed(): void
    {
        ['batch' => $batch, 'tray' => $tray] = $this->setUpInProgressBatchWithTray();

        $response = $this->submitResult($batch, [
            'bi_result' => 'NEGATIVE',
            'results' => [
                ['tray_id' => $tray->id, 'result' => 'PASSED', 'ci_result' => 'FAIL'],
            ],
        ]);

        $response->assertSessionHasErrors();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_IN_STERILIZATION, $tray->status, 'CI must be PASS to pass a tray.');
    }

    public function test_passing_result_stamps_sterility_and_increments_instrument_cycles(): void
    {
        ['hospital' => $hospital, 'batch' => $batch, 'tray' => $tray] = $this->setUpInProgressBatchWithTray();

        $trayItem = $tray->items()->first();
        $instrumentItem = $trayItem->instrumentItem;
        $this->assertSame(0, $instrumentItem->total_cycles);

        $response = $this->submitResult($batch, [
            'bi_result' => 'NEGATIVE',
            'results' => [
                ['tray_id' => $tray->id, 'result' => 'PASSED', 'ci_result' => 'PASS'],
            ],
        ]);

        $response->assertSessionHas('success');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
        $this->assertNotNull($tray->sterile_at);
        $this->assertNotNull($tray->expiry_at);
        $this->assertSame(
            $tray->sterile_at->copy()->addDays(14)->toDateString(),
            $tray->expiry_at->toDateString()
        );
        $this->assertSame($batch->load_number, $tray->current_load_number);

        $instrumentItem->refresh();
        $this->assertSame(1, $instrumentItem->total_cycles);

        $batch->refresh();
        $this->assertSame(SterilizationBatch::STATUS_COMPLETED, $batch->status);
    }

    public function test_failing_result_clears_sterility_fields_and_needs_reprocessing(): void
    {
        ['batch' => $batch, 'tray' => $tray] = $this->setUpInProgressBatchWithTray();

        // Simulate a tray that still carries stale sterility data (e.g. reused after a bug),
        // to verify the FAILED path actually clears it rather than leaving it stale.
        $tray->forceFill([
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(5),
            'current_load_number' => 'OLD-LOAD',
        ])->save();

        $response = $this->submitResult($batch, [
            'bi_result' => 'NEGATIVE',
            'results' => [
                ['tray_id' => $tray->id, 'result' => 'FAILED', 'failure_notes' => 'Indikator gagal'],
            ],
        ]);

        $response->assertSessionHas('success');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_NEEDS_REPROCESSING, $tray->status);
        $this->assertNull($tray->sterile_at);
        $this->assertNull($tray->expiry_at);
        $this->assertNull($tray->current_load_number);

        $batch->refresh();
        $this->assertSame(SterilizationBatch::STATUS_FAILED, $batch->status);
    }

    public function test_batch_status_is_failed_when_any_tray_fails_among_multiple(): void
    {
        ['hospital' => $hospital, 'batch' => $batch, 'tray' => $trayA] = $this->setUpInProgressBatchWithTray();

        $trayB = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $this->makeSterilizationBatchItem($batch, $trayB, SterilizationBatchItem::RESULT_PENDING);

        $response = $this->submitResult($batch, [
            'bi_result' => 'NEGATIVE',
            'results' => [
                ['tray_id' => $trayA->id, 'result' => 'PASSED', 'ci_result' => 'PASS'],
                ['tray_id' => $trayB->id, 'result' => 'FAILED', 'failure_notes' => 'Gagal'],
            ],
        ]);

        $response->assertSessionHas('success');

        $trayA->refresh();
        $trayB->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $trayA->status);
        $this->assertSame(Tray::STATUS_NEEDS_REPROCESSING, $trayB->status);

        $batch->refresh();
        $this->assertSame(SterilizationBatch::STATUS_FAILED, $batch->status, 'One failure must fail the whole batch.');
    }
}
