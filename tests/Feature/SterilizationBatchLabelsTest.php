<?php

namespace Tests\Feature;

use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class SterilizationBatchLabelsTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_labels_page_only_includes_passed_trays_not_failed_ones(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);

        $passedTray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(29),
        ]);
        $failedTray = $this->makeTray($hospital, status: Tray::STATUS_NEEDS_REPROCESSING);

        $this->makeSterilizationBatchItem($batch, $passedTray, SterilizationBatchItem::RESULT_PASSED);
        $this->makeSterilizationBatchItem($batch, $failedTray, SterilizationBatchItem::RESULT_FAILED);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['sterilization-batches.view']);

        $response = $this->get(route('sterilization-batches.labels', $batch));

        $response->assertOk();
        $response->assertSee($passedTray->code);
        $response->assertDontSee($failedTray->code);
    }

    public function test_user_without_sterilization_batches_view_gets_403(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, []);

        $response = $this->get(route('sterilization-batches.labels', $batch));

        $response->assertForbidden();
    }
}
