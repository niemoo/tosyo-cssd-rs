<?php

namespace Tests\Feature;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class ReportsPermissionTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** @return list<string> */
    private function reportRoutes(): array
    {
        return [
            route('reports.index'),
            route('reports.sterilization-log'),
            route('reports.productivity'),
            route('reports.instrument-lifespan'),
        ];
    }

    public function test_user_with_reports_view_can_open_every_report(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['reports.view']);

        foreach ($this->reportRoutes() as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_user_without_reports_view_gets_403(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, []);

        foreach ($this->reportRoutes() as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_operator_cssd_gets_403_on_every_report_route(): void
    {
        // Urutan mengikuti DatabaseSeeder: role dulu, baru permission.
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $user->assignRole('Operator CSSD');
        $this->actingAs($user);
        session(['active_hospital_id' => $hospital->id]);

        foreach ($this->reportRoutes() as $url) {
            $this->get($url)->assertForbidden();
        }
    }
}
