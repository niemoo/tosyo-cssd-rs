<?php

namespace Tests\Feature;

use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ProductivityReportTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_failure_rate_is_calculated_correctly(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);

        // 1 gagal dari 4 tray -> 25%.
        $this->makeSterilizationBatchItem($batch, $this->makeTray($hospital), SterilizationBatchItem::RESULT_PASSED);
        $this->makeSterilizationBatchItem($batch, $this->makeTray($hospital), SterilizationBatchItem::RESULT_PASSED);
        $this->makeSterilizationBatchItem($batch, $this->makeTray($hospital), SterilizationBatchItem::RESULT_PASSED);
        $this->makeSterilizationBatchItem($batch, $this->makeTray($hospital), SterilizationBatchItem::RESULT_FAILED);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.productivity'));

        $response->assertOk();
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_trays'] === 4
                && $summary['total_loads'] === 1
                && $summary['failure_rate'] === 25.0;
        });
    }

    public function test_expired_tray_count_comes_from_expire_lifecycle_events(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_NEEDS_REPROCESSING);
        $this->recordTrayEvent($tray, 'EXPIRE', Tray::STATUS_STERILE, Tray::STATUS_NEEDS_REPROCESSING);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.productivity'));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['expired_trays'] === 1);
    }
}
