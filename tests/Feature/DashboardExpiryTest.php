<?php

namespace Tests\Feature;

use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class DashboardExpiryTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_reports_expiring_soon_and_already_expired_counts(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['dashboard.view']);

        // Sudah kedaluwarsa.
        $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: ['expiry_at' => now()->subDays(2)]);
        // Akan kedaluwarsa dalam 7 hari.
        $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: ['expiry_at' => now()->addDays(3)]);
        // Steril tapi masih jauh dari kedaluwarsa — tidak boleh dihitung.
        $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: ['expiry_at' => now()->addDays(20)]);
        // IN_USE dengan expiry_at lampau — bukan tray steril, tidak boleh dihitung.
        $this->makeTray($hospital, status: Tray::STATUS_IN_USE, attrs: ['expiry_at' => now()->subDay()]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('expiryStats', function ($stats) {
            return $stats['expired'] === 1 && $stats['expiring_soon'] === 1;
        });
    }

    public function test_dashboard_lists_soonest_expiring_trays_linking_to_tray_detail(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['dashboard.view', 'trays.view']);

        $soonest = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: ['expiry_at' => now()->addDay()]);
        $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: ['expiry_at' => now()->addDays(20)]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($soonest->code);
        $response->assertSee(route('trays.show', $soonest), false);
    }
}
