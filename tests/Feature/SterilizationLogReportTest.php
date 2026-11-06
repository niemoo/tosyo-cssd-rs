<?php

namespace Tests\Feature;

use App\Models\SterilizationBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class SterilizationLogReportTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_only_batches_within_the_filtered_period_are_shown(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);

        $inPeriod = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-03-15 10:00:00',
        ]);
        $outsidePeriod = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-01-15 10:00:00',
        ]);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.sterilization-log', [
            'from' => '2026-03-01',
            'to' => '2026-03-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('batches', function ($batches) use ($inPeriod, $outsidePeriod) {
            return $batches->pluck('id')->contains($inPeriod->id)
                && ! $batches->pluck('id')->contains($outsidePeriod->id);
        });
    }

    public function test_bi_positive_filter_only_shows_bi_positive_batches(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);

        $positive = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-03-10 08:00:00',
            'bi_result' => SterilizationBatch::BI_POSITIVE,
        ]);
        $negative = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-03-11 08:00:00',
            'bi_result' => SterilizationBatch::BI_NEGATIVE,
        ]);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.sterilization-log', [
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'bi_result' => SterilizationBatch::BI_POSITIVE,
        ]));

        $response->assertOk();
        $response->assertViewHas('batches', function ($batches) use ($positive, $negative) {
            return $batches->pluck('id')->contains($positive->id)
                && ! $batches->pluck('id')->contains($negative->id);
        });
    }
}
