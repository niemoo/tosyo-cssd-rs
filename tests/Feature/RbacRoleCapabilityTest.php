<?php

namespace Tests\Feature;

use App\Models\DistributionRequest;
use App\Models\Hospital;
use App\Models\SterilizationBatch;
use App\Models\SterilizationBatchItem;
use App\Models\Tray;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class RbacRoleCapabilityTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Urutan mengikuti DatabaseSeeder: role dulu, baru permission (sync ke role butuh role sudah ada).
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    private function actingAsRole(string $role, Hospital $hospital): User
    {
        $user = $this->makeUser($hospital);
        $user->assignRole($role);
        $this->actingAs($user);
        session(['active_hospital_id' => $hospital->id]);

        return $user;
    }

    public function test_operator_cssd_can_create_batch_and_input_sterilization_result(): void
    {
        $hospital = $this->makeHospital(['sterile_shelf_life_days' => 30]);
        $sterilizer = $this->makeSterilizer($hospital);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);

        $this->actingAsRole('Operator CSSD', $hospital);

        $storeResponse = $this->post(route('sterilization-batches.store'), [
            'hospital_id' => $hospital->id,
            'sterilizer_id' => $sterilizer->id,
            'batch_number' => 'BATCH-OPR-001',
            'tray_ids' => [$tray->id],
        ]);

        $storeResponse->assertRedirect(route('sterilization-batches.index'));

        $batch = SterilizationBatch::where('batch_number', 'BATCH-OPR-001')->firstOrFail();

        $resultResponse = $this->patch(route('sterilization-batches.result', $batch), [
            'bi_result' => SterilizationBatch::BI_NEGATIVE,
            'results' => [
                ['tray_id' => $tray->id, 'result' => SterilizationBatchItem::RESULT_PASSED, 'ci_result' => SterilizationBatchItem::CI_PASS],
            ],
        ]);

        $resultResponse->assertRedirect(route('sterilization-batches.show', $batch));
        $resultResponse->assertSessionHasNoErrors();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
    }

    public function test_supervisor_cssd_can_approve_and_fulfill_distribution_request(): void
    {
        $hospital = $this->makeHospital();
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $template = $this->makeTrayTemplate($hospital);

        $request = $this->makeDistributionRequest($hospital, $unit, $requester, DistributionRequest::STATUS_PENDING_APPROVAL);
        $item = $this->makeDistributionRequestItem($request, $template, 1);

        $this->actingAsRole('Supervisor CSSD', $hospital);

        $approveResponse = $this->patch(route('distribution-requests.approve', $request), [
            'decision' => 'approve',
        ]);
        $approveResponse->assertRedirect(route('distribution-requests.show', $request));

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_APPROVED, $request->status);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id,
            'expiry_at' => now()->addDays(10),
        ]);

        $fulfillResponse = $this->post(route('distribution-requests.process-fulfillment', $request), [
            'assignments' => [$item->id => [$tray->id]],
        ]);

        $fulfillResponse->assertRedirect(route('distribution-requests.show', $request));

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_FULFILLED, $request->status);
    }

    public function test_kepala_cssd_can_view_batches_and_distribution_requests(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);

        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator);
        $request = $this->makeDistributionRequest($hospital, $unit, $requester);

        $this->actingAsRole('Kepala CSSD', $hospital);

        $this->get(route('sterilization-batches.index'))->assertOk();
        $this->get(route('sterilization-batches.show', $batch))->assertOk();
        $this->get(route('distribution-requests.index'))->assertOk();
        $this->get(route('distribution-requests.show', $request))->assertOk();
    }

    public function test_kepala_cssd_cannot_create_sterilization_batch(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY);

        $this->actingAsRole('Kepala CSSD', $hospital);

        $response = $this->post(route('sterilization-batches.store'), [
            'hospital_id' => $hospital->id,
            'sterilizer_id' => $sterilizer->id,
            'batch_number' => 'BATCH-KPL-001',
            'tray_ids' => [$tray->id],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('sterilization_batches', ['batch_number' => 'BATCH-KPL-001']);
    }

    public function test_kepala_cssd_cannot_release_sterilization_result(): void
    {
        $hospital = $this->makeHospital();
        $sterilizer = $this->makeSterilizer($hospital);
        $operator = $this->makeUser($hospital);
        $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, SterilizationBatch::STATUS_IN_PROGRESS);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_IN_STERILIZATION);
        $this->makeSterilizationBatchItem($batch, $tray, SterilizationBatchItem::RESULT_PENDING);

        $this->actingAsRole('Kepala CSSD', $hospital);

        $response = $this->patch(route('sterilization-batches.result', $batch), [
            'bi_result' => SterilizationBatch::BI_NEGATIVE,
            'results' => [
                ['tray_id' => $tray->id, 'result' => SterilizationBatchItem::RESULT_PASSED, 'ci_result' => SterilizationBatchItem::CI_PASS],
            ],
        ]);

        $response->assertForbidden();

        $tray->refresh();
        $this->assertSame(Tray::STATUS_IN_STERILIZATION, $tray->status, 'Kepala CSSD must not be able to release sterility.');
    }

    /**
     * sterilization-batches.delete is Admin-only by design (deleting a batch means
     * deleting sterilization proof) — every other seeded role must be refused.
     */
    public function test_non_admin_roles_cannot_destroy_sterilization_batch(): void
    {
        foreach (['Kepala CSSD', 'Supervisor CSSD', 'Operator CSSD', 'Teknisi'] as $role) {
            $hospital = $this->makeHospital();
            $sterilizer = $this->makeSterilizer($hospital);
            $operator = $this->makeUser($hospital);
            $batch = $this->makeSterilizationBatch($hospital, $sterilizer, $operator, SterilizationBatch::STATUS_COMPLETED);

            $this->actingAsRole($role, $hospital);

            $response = $this->delete(route('sterilization-batches.destroy', $batch));

            $response->assertForbidden("Role '{$role}' must not be able to delete a sterilization batch.");
            $this->assertNotSoftDeleted($batch);
        }
    }
}
