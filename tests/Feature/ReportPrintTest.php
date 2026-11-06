<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ReportPrintTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Assert a print page renders the accreditation header and never leaks the app chrome. */
    private function assertIsAnIsolatedPrintPage($response, string $hospitalName): void
    {
        $response->assertOk();
        $response->assertSee($hospitalName);
        $response->assertSee('Dicetak:');
        $response->assertDontSee('Pelacakan Tray');
        $response->assertDontSee('id="dropdown-portal"', false);
    }

    public function test_sterilization_log_print_shows_header_without_app_chrome(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.sterilization-log.print'));

        $this->assertIsAnIsolatedPrintPage($response, $hospital->name);
        $response->assertSee('Log Sterilisasi');
        $response->assertSee('Periode:');
    }

    public function test_productivity_print_shows_header_without_app_chrome(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.productivity.print'));

        $this->assertIsAnIsolatedPrintPage($response, $hospital->name);
        $response->assertSee('Laporan Produktivitas');
    }

    public function test_instrument_lifespan_print_shows_header_without_app_chrome(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $response = $this->get(route('reports.instrument-lifespan.print'));

        $this->assertIsAnIsolatedPrintPage($response, $hospital->name);
        $response->assertSee('Laporan Umur Instrumen');
    }

    public function test_recall_print_shows_header_without_app_chrome(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->get(route('traceability.load-recall.print', ['batch_id' => $batch->id]));

        $this->assertIsAnIsolatedPrintPage($response, $hospital->name);
        $response->assertSee('Bukti Recall per Load');
    }
}
