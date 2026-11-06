<?php

namespace Tests\Feature;

use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class TrayLabelTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_with_trays_view_can_print_label_for_sterile_tray(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(29),
            'current_load_number' => 'LOAD-2026-0007',
        ]);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->get(route('trays.label', $tray));

        $response->assertOk();
        $response->assertSee($tray->code);
        $response->assertSee($tray->sterile_at->format('d M Y'));
        $response->assertSee($tray->expiry_at->format('d M Y'));
        $response->assertSee('LOAD-2026-0007');
    }

    public function test_user_without_trays_view_gets_403(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(29),
        ]);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, []);

        $response = $this->get(route('trays.label', $tray));

        $response->assertForbidden();
    }

    public function test_label_is_refused_for_a_tray_that_is_not_yet_sterile(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->get(route('trays.label', $tray));

        $response->assertForbidden();
    }

    public function test_print_page_does_not_include_app_sidebar_or_navigation(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(29),
        ]);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->get(route('trays.label', $tray));

        $response->assertOk();
        $response->assertDontSee('Pelacakan Tray');
        $response->assertDontSee('id="dropdown-portal"', false);
    }
}
