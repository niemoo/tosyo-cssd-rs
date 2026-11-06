<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class InstrumentLifespanReportTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_default_view_only_shows_items_nearing_lifespan_and_all_toggle_shows_everything(): void
    {
        $hospital = $this->makeHospital();
        $category = $this->makeInstrumentCategory($hospital);

        $nearInstrument = $this->makeInstrument($hospital, $category, ['lifespan_cycles' => 100]);
        $nearItem = $this->makeInstrumentItem($hospital, $nearInstrument, ['total_cycles' => 95, 'code' => 'NEAR-001']);

        $farInstrument = $this->makeInstrument($hospital, $category, ['lifespan_cycles' => 100]);
        $farItem = $this->makeInstrumentItem($hospital, $farInstrument, ['total_cycles' => 50, 'code' => 'FAR-001']);

        // Instrumen tanpa lifespan_cycles — tidak boleh menyebabkan error, dan tidak dihitung persentasenya.
        $undefinedInstrument = $this->makeInstrument($hospital, $category, ['lifespan_cycles' => null]);
        $undefinedItem = $this->makeInstrumentItem($hospital, $undefinedInstrument, ['total_cycles' => 10, 'code' => 'UNDEF-001']);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        $default = $this->get(route('reports.instrument-lifespan'));
        $default->assertOk();
        $default->assertSee('NEAR-001');
        $default->assertDontSee('FAR-001');
        $default->assertDontSee('UNDEF-001');

        $all = $this->get(route('reports.instrument-lifespan', ['all' => 1]));
        $all->assertOk();
        $all->assertSee('NEAR-001');
        $all->assertSee('FAR-001');
        $all->assertSee('UNDEF-001');
        $all->assertSee('Tidak ditentukan');
    }
}
