<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ReportCsvExportTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    public function test_sterilization_log_csv_has_bom_correct_content_type_and_matching_row_count(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);

        $inPeriod = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-03-15 10:00:00',
        ]);
        // Di luar periode yang difilter — tidak boleh ikut ter-ekspor.
        $this->makeSterilizationBatch($hospital, $sterilizer, $operator, attrs: [
            'started_at' => '2026-01-15 10:00:00',
        ]);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.sterilization-log.csv', [
            'from' => '2026-03-01',
            'to' => '2026-03-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'CSV must start with a UTF-8 BOM for Excel.');

        $withoutBom = substr($content, 3);
        $lines = array_values(array_filter(explode("\n", trim($withoutBom)), fn ($line) => $line !== ''));

        // 1 header row + 1 data row (only the in-period batch).
        $this->assertCount(2, $lines);
        $this->assertStringContainsString($inPeriod->batch_number, $lines[1]);
    }

    public function test_csv_filename_includes_the_filtered_period(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.sterilization-log.csv', [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]));

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('log-sterilisasi_2026-07-01_2026-07-31.csv', $disposition);
    }
}
