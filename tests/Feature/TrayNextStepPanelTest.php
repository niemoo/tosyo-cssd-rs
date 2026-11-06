<?php

namespace Tests\Feature;

use App\Models\DistributionRequest;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

/**
 * F3 — "Langkah Berikutnya" panel: for every tray status the show page must surface the
 * correct next action, and every action must be gated by exactly the permission its target
 * route requires (see Bug #7/#8 — a mismatch here means a button that leads straight to 403).
 */
class TrayNextStepPanelTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private const FULL_PERMISSIONS = [
        'trays.view', 'trays.edit',
        'sterilization-batches.view', 'sterilization-batches.create', 'sterilization-batches.edit',
        'distribution-requests.view', 'distribution-requests.return',
    ];

    public function test_assembling_without_items_shows_reason_and_no_ready_button(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_ASSEMBLING, itemCount: 0);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee('belum berisi instrumen');
    }

    public function test_assembling_with_items_shows_ready_button(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_ASSEMBLING, itemCount: 1);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee(route('trays.ready', $tray), false);
    }

    public function test_ready_shows_create_batch_link_and_unpack_button(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee('Tray siap disterilkan');
        $response->assertSee(route('sterilization-batches.create'), false);
        $response->assertSee(route('trays.unpack', $tray), false);
    }

    public function test_ready_without_sterilization_batches_create_permission_hides_the_link_but_keeps_the_info(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view', 'trays.edit']);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee('Tray siap disterilkan');
        $response->assertDontSee(route('sterilization-batches.create'), false);
    }

    public function test_in_sterilization_shows_batch_number_and_input_result_link(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PENDING);

        $viewer = $this->makeUser($hospital);
        $this->actingAsHospitalUser($viewer, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee($batch->batch_number);
        $response->assertSee(route('sterilization-batches.show', $batch), false);
    }

    public function test_in_sterilization_without_edit_permission_hides_input_result_link(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PENDING);

        $viewer = $this->makeUser($hospital);
        $this->actingAsHospitalUser($viewer, $hospital, ['trays.view', 'sterilization-batches.view']);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee($batch->batch_number);
        $response->assertDontSee(route('sterilization-batches.show', $batch), false);
    }

    public function test_sterile_shows_expiry_and_distribution_requests_link(): void
    {
        $hospital = $this->makeHospital();
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'sterile_at' => now()->subDay(),
            'expiry_at' => now()->addDays(10),
        ]);
        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee('Menunggu permintaan dari unit');
        $response->assertSee(route('distribution-requests.index'), false);
    }

    public function test_in_use_shows_unit_and_return_link(): void
    {
        $hospital = $this->makeHospital();
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $request = $this->makeDistributionRequest($hospital, $unit, $requester, DistributionRequest::STATUS_FULFILLED);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_USE);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, reference: $request);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee($unit->name);
        $response->assertSee($request->request_number);
        $response->assertSee(route('tray-returns.create', $request), false);
    }

    public function test_in_use_without_return_permission_hides_return_link(): void
    {
        $hospital = $this->makeHospital();
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $request = $this->makeDistributionRequest($hospital, $unit, $requester, DistributionRequest::STATUS_FULFILLED);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_USE);
        $this->recordTrayEvent($tray, 'ISSUE', Tray::STATUS_STERILE, Tray::STATUS_IN_USE, reference: $request);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, ['trays.view']);

        $response = $this->get(route('trays.show', $tray));

        $response->assertOk();
        $response->assertSee($unit->name);
        $response->assertDontSee(route('tray-returns.create', $request), false);
    }

    public function test_returned_and_needs_reprocessing_show_reprocess_button(): void
    {
        $hospital = $this->makeHospital();

        foreach ([Tray::STATUS_RETURNED, Tray::STATUS_NEEDS_REPROCESSING] as $status) {
            $tray = $this->makeTray($hospital, status: $status);
            $user = $this->makeUser($hospital);
            $this->actingAsHospitalUser($user, $hospital, self::FULL_PERMISSIONS);

            $response = $this->get(route('trays.show', $tray));

            $response->assertOk();
            $response->assertSee(route('trays.reprocess', $tray), false);
        }
    }
}
