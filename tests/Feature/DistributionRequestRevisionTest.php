<?php

namespace Tests\Feature;

use App\Models\DistributionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

/**
 * Closes Bug #7: distribution-requests.edit didn't exist in the permission catalog, so
 * the "Revisi"/"Edit" button never rendered for anyone and REJECTED requests could never
 * be reopened through the UI even though the controller/model already support it.
 */
class DistributionRequestRevisionTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function setUpRejectedRequest(): array
    {
        $hospital = $this->makeHospital();
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $template = $this->makeTrayTemplate($hospital);

        $request = $this->makeDistributionRequest($hospital, $unit, $requester, DistributionRequest::STATUS_REJECTED, [
            'rejection_notes' => 'Kuantitas tidak sesuai kebutuhan unit.',
        ]);
        $item = $this->makeDistributionRequestItem($request, $template, 1);

        return compact('hospital', 'unit', 'template', 'request', 'item');
    }

    public function test_user_with_edit_permission_can_open_and_save_revision_of_rejected_request(): void
    {
        ['hospital' => $hospital, 'unit' => $unit, 'template' => $template, 'request' => $request] = $this->setUpRejectedRequest();

        $this->assertTrue($request->canBeRevised());

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, [
            'distribution-requests.view', 'distribution-requests.edit',
        ]);

        $this->get(route('distribution-requests.edit', $request))->assertOk();

        $response = $this->put(route('distribution-requests.update', $request), [
            'unit_id' => $unit->id,
            'notes' => 'Direvisi sesuai catatan penolakan.',
            'submit_type' => 'submit',
            'items' => [
                ['template_id' => $template->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('distribution-requests.index'));

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_PENDING_APPROVAL, $request->status);
        $this->assertSame(2, $request->items()->first()->quantity);
    }

    public function test_user_without_edit_permission_cannot_open_or_save_revision(): void
    {
        ['hospital' => $hospital, 'unit' => $unit, 'template' => $template, 'request' => $request] = $this->setUpRejectedRequest();

        $user = $this->makeUser($hospital);
        // Only .view — no distribution-requests.edit.
        $this->actingAsHospitalUser($user, $hospital, ['distribution-requests.view']);

        $this->get(route('distribution-requests.edit', $request))->assertForbidden();

        $response = $this->put(route('distribution-requests.update', $request), [
            'unit_id' => $unit->id,
            'submit_type' => 'submit',
            'items' => [
                ['template_id' => $template->id, 'quantity' => 2],
            ],
        ]);

        $response->assertForbidden();

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_REJECTED, $request->status);
    }
}
