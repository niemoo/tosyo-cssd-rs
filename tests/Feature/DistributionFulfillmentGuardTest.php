<?php

namespace Tests\Feature;

use App\Models\DistributionRequest;
use App\Models\Tray;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class DistributionFulfillmentGuardTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private function fulfill(DistributionRequest $request, array $assignments)
    {
        return $this->post(route('distribution-requests.process-fulfillment', $request), [
            'assignments' => $assignments,
        ]);
    }

    private function setUpApprovedRequestWithOneItem(int $quantity = 1): array
    {
        $hospital = $this->makeHospital();
        $unit = $this->makeUnit($hospital);
        $requester = $this->makeUser($hospital);
        $template = $this->makeTrayTemplate($hospital);

        $user = $this->makeUser($hospital);
        $this->actingAsHospitalUser($user, $hospital, [
            'distribution-requests.view', 'distribution-requests.fulfill',
        ]);

        $request = $this->makeDistributionRequest($hospital, $unit, $requester, DistributionRequest::STATUS_APPROVED);
        $item = $this->makeDistributionRequestItem($request, $template, $quantity);

        return compact('hospital', 'template', 'request', 'item');
    }

    public function test_successful_full_assignment_marks_request_fulfilled_and_tray_in_use(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(1);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id,
            'expiry_at' => now()->addDays(10),
        ]);

        $response = $this->fulfill($request, [$item->id => [$tray->id]]);

        $response->assertRedirect(route('distribution-requests.show', $request));
        $response->assertSessionHas('success');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_IN_USE, $tray->status);

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_FULFILLED, $request->status);

        $this->assertDatabaseHas('distribution_request_item_trays', [
            'item_id' => $item->id,
            'tray_id' => $tray->id,
        ]);
    }

    public function test_partial_assignment_leaves_request_in_process(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(2);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id,
            'expiry_at' => now()->addDays(10),
        ]);

        $this->fulfill($request, [$item->id => [$tray->id]]);

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_IN_PROCESS, $request->status);
    }

    public function test_expired_tray_is_rejected(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(1);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id,
            'expiry_at' => now()->subDay(),
        ]);

        $response = $this->fulfill($request, [$item->id => [$tray->id]]);

        $response->assertSessionHasErrors('assignments');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status, 'Expired tray must not be issued.');

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_APPROVED, $request->status);
    }

    public function test_template_mismatch_is_rejected(): void
    {
        ['hospital' => $hospital, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(1);

        $otherTemplate = $this->makeTrayTemplate($hospital);
        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $otherTemplate->id,
            'expiry_at' => now()->addDays(10),
        ]);

        $response = $this->fulfill($request, [$item->id => [$tray->id]]);

        $response->assertSessionHasErrors('assignments');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
    }

    public function test_non_sterile_tray_is_rejected(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(1);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_READY, attrs: [
            'template_id' => $template->id,
        ]);

        $response = $this->fulfill($request, [$item->id => [$tray->id]]);

        $response->assertSessionHasErrors('assignments');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_READY, $tray->status);
    }

    public function test_exceeding_item_quantity_is_rejected(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(1);

        $trayA = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id, 'expiry_at' => now()->addDays(10),
        ]);
        $trayB = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id, 'expiry_at' => now()->addDays(10),
        ]);

        $response = $this->fulfill($request, [$item->id => [$trayA->id, $trayB->id]]);

        $response->assertSessionHasErrors('assignments');

        $trayA->refresh();
        $trayB->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $trayA->status);
        $this->assertSame(Tray::STATUS_STERILE, $trayB->status);

        $request->refresh();
        $this->assertSame(DistributionRequest::STATUS_APPROVED, $request->status);
    }

    public function test_same_tray_selected_twice_is_rejected(): void
    {
        ['hospital' => $hospital, 'template' => $template, 'request' => $request, 'item' => $item] = $this->setUpApprovedRequestWithOneItem(2);

        $tray = $this->makeTray($hospital, status: Tray::STATUS_STERILE, attrs: [
            'template_id' => $template->id, 'expiry_at' => now()->addDays(10),
        ]);

        $response = $this->fulfill($request, [$item->id => [$tray->id, $tray->id]]);

        $response->assertSessionHasErrors('assignments');

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
    }
}
