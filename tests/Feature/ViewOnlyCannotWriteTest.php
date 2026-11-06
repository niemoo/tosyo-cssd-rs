<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

/**
 * Proves that {module}.view alone no longer grants write access on any resource
 * group — the gap fixed by adding {module}.create/.edit/.delete middleware to
 * the write routes in routes/web.php.
 */
class ViewOnlyCannotWriteTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    public function test_trays_view_only_cannot_store(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $this->post(route('trays.store'), [])->assertForbidden();
    }

    public function test_tray_templates_view_only_cannot_store(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['tray-templates.view']);

        $this->post(route('tray-templates.store'), [])->assertForbidden();
    }

    public function test_sterilization_batches_view_only_cannot_store(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['sterilization-batches.view']);

        $this->post(route('sterilization-batches.store'), [])->assertForbidden();
    }

    public function test_distribution_requests_view_only_cannot_store(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['distribution-requests.view']);

        $this->post(route('distribution-requests.store'), [])->assertForbidden();
    }

    public function test_instruments_view_only_cannot_store(): void
    {
        $hospital = $this->makeHospital();
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['instruments.view']);

        $this->post(route('instruments.store'), [])->assertForbidden();
    }
}
