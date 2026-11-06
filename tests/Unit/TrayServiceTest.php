<?php

namespace Tests\Unit;

use App\Exceptions\InvalidTrayTransition;
use App\Models\DistributionRequest;
use App\Models\Hospital;
use App\Models\SterilizationBatch;
use App\Models\Tray;
use App\Services\TrayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesCssdData;
use Tests\TestCase;

class TrayServiceTest extends TestCase
{
    use CreatesCssdData;
    use RefreshDatabase;

    private Hospital $hospital;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hospital = $this->makeHospital();
    }

    public static function legalTransitionsProvider(): array
    {
        return [
            'ASSEMBLING -> READY (PACKAGE)' => [Tray::STATUS_ASSEMBLING, Tray::STATUS_READY, 'PACKAGE'],
            'READY -> IN_STERILIZATION (LOAD)' => [Tray::STATUS_READY, Tray::STATUS_IN_STERILIZATION, 'LOAD'],
            'READY -> ASSEMBLING (REPROCESS)' => [Tray::STATUS_READY, Tray::STATUS_ASSEMBLING, 'REPROCESS'],
            'IN_STERILIZATION -> STERILE (STERILIZE_PASS)' => [Tray::STATUS_IN_STERILIZATION, Tray::STATUS_STERILE, 'STERILIZE_PASS'],
            'IN_STERILIZATION -> NEEDS_REPROCESSING (STERILIZE_FAIL)' => [Tray::STATUS_IN_STERILIZATION, Tray::STATUS_NEEDS_REPROCESSING, 'STERILIZE_FAIL'],
            'STERILE -> IN_USE (ISSUE)' => [Tray::STATUS_STERILE, Tray::STATUS_IN_USE, 'ISSUE'],
            'STERILE -> NEEDS_REPROCESSING (EXPIRE)' => [Tray::STATUS_STERILE, Tray::STATUS_NEEDS_REPROCESSING, 'EXPIRE'],
            'IN_USE -> RETURNED (RETURN)' => [Tray::STATUS_IN_USE, Tray::STATUS_RETURNED, 'RETURN'],
            'IN_USE -> NEEDS_REPROCESSING (RETURN_DAMAGED)' => [Tray::STATUS_IN_USE, Tray::STATUS_NEEDS_REPROCESSING, 'RETURN_DAMAGED'],
            'RETURNED -> ASSEMBLING (REPROCESS)' => [Tray::STATUS_RETURNED, Tray::STATUS_ASSEMBLING, 'REPROCESS'],
            'NEEDS_REPROCESSING -> ASSEMBLING (REPROCESS)' => [Tray::STATUS_NEEDS_REPROCESSING, Tray::STATUS_ASSEMBLING, 'REPROCESS'],
        ];
    }

    #[DataProvider('legalTransitionsProvider')]
    public function test_legal_transition_succeeds_and_matches_transitions_map(string $from, string $to, string $expectedEventType): void
    {
        $tray = $this->makeTray($this->hospital, status: $from);

        $this->assertSame($expectedEventType, TrayService::TRANSITIONS[$from][$to]);
        $this->assertTrue(TrayService::canTransition($tray, $to));

        $event = TrayService::transitionTo($tray, $to);

        $tray->refresh();
        $this->assertSame($to, $tray->status);

        $this->assertSame($expectedEventType, $event->event_type);
        $this->assertSame($from, $event->from_status);
        $this->assertSame($to, $event->to_status);

        $this->assertDatabaseCount('tray_lifecycle_events', 1);
        $this->assertDatabaseHas('tray_lifecycle_events', [
            'tray_id' => $tray->id,
            'event_type' => $expectedEventType,
            'from_status' => $from,
            'to_status' => $to,
        ]);
    }

    public static function illegalTransitionsProvider(): array
    {
        return [
            'ASSEMBLING -> IN_USE' => [Tray::STATUS_ASSEMBLING, Tray::STATUS_IN_USE],
            'ASSEMBLING -> STERILE' => [Tray::STATUS_ASSEMBLING, Tray::STATUS_STERILE],
            'STERILE -> READY' => [Tray::STATUS_STERILE, Tray::STATUS_READY],
            'IN_USE -> ASSEMBLING' => [Tray::STATUS_IN_USE, Tray::STATUS_ASSEMBLING],
            'RETURNED -> STERILE' => [Tray::STATUS_RETURNED, Tray::STATUS_STERILE],
            'NEEDS_REPROCESSING -> IN_USE' => [Tray::STATUS_NEEDS_REPROCESSING, Tray::STATUS_IN_USE],
            'IN_STERILIZATION -> IN_USE' => [Tray::STATUS_IN_STERILIZATION, Tray::STATUS_IN_USE],
            'READY -> STERILE' => [Tray::STATUS_READY, Tray::STATUS_STERILE],
        ];
    }

    #[DataProvider('illegalTransitionsProvider')]
    public function test_illegal_transition_throws_and_writes_no_event(string $from, string $to): void
    {
        $tray = $this->makeTray($this->hospital, status: $from);

        $this->assertFalse(TrayService::canTransition($tray, $to));

        try {
            TrayService::transitionTo($tray, $to);
            $this->fail('Expected InvalidTrayTransition to be thrown.');
        } catch (InvalidTrayTransition $e) {
            // expected
        }

        $tray->refresh();
        $this->assertSame($from, $tray->status, 'Tray status must not change on an illegal transition.');
        $this->assertDatabaseCount('tray_lifecycle_events', 0);
    }

    public function test_record_genesis_writes_assemble_start_with_null_from_status(): void
    {
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_ASSEMBLING);

        $event = TrayService::recordGenesis($tray);

        $this->assertSame('ASSEMBLE_START', $event->event_type);
        $this->assertNull($event->from_status);
        $this->assertSame(Tray::STATUS_ASSEMBLING, $event->to_status);
        $this->assertDatabaseHas('tray_lifecycle_events', [
            'tray_id' => $tray->id,
            'event_type' => 'ASSEMBLE_START',
            'from_status' => null,
        ]);
    }

    public function test_transition_applies_attributes_to_tray(): void
    {
        $rack = $this->makeStorageRack($this->hospital);
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_IN_STERILIZATION);

        $sterileAt = now();
        $expiryAt = $sterileAt->copy()->addDays(30);

        TrayService::transitionTo($tray, Tray::STATUS_STERILE, attributes: [
            'sterile_at' => $sterileAt,
            'expiry_at' => $expiryAt,
            'current_rack_id' => $rack->id,
        ]);

        $tray->refresh();
        $this->assertSame(Tray::STATUS_STERILE, $tray->status);
        $this->assertSame($rack->id, $tray->current_rack_id);
        $this->assertNotNull($tray->sterile_at);
        $this->assertNotNull($tray->expiry_at);
        // Compare at second precision: the DB column truncates sub-second precision on write.
        $this->assertSame($sterileAt->format('Y-m-d H:i:s'), $tray->sterile_at->format('Y-m-d H:i:s'));
        $this->assertSame($expiryAt->format('Y-m-d H:i:s'), $tray->expiry_at->format('Y-m-d H:i:s'));
    }

    public function test_transition_stores_polymorphic_reference(): void
    {
        $sterilizer = $this->makeSterilizer($this->hospital);
        $operator = $this->makeUser($this->hospital);
        $batch = $this->makeSterilizationBatch($this->hospital, $sterilizer, $operator);
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_READY);

        $event = TrayService::transitionTo($tray, Tray::STATUS_IN_STERILIZATION, reference: $batch);

        $this->assertSame(SterilizationBatch::class, $event->reference_type);
        $this->assertSame($batch->id, $event->reference_id);
    }

    public function test_transition_stores_context_as_array(): void
    {
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_IN_STERILIZATION);

        $event = TrayService::transitionTo($tray, Tray::STATUS_NEEDS_REPROCESSING, context: [
            'ci_result' => 'FAIL',
            'failure_notes' => 'BI positif',
        ]);

        $this->assertIsArray($event->context);
        $this->assertSame('FAIL', $event->context['ci_result']);

        $event->refresh();
        $this->assertIsArray($event->context);
        $this->assertSame('BI positif', $event->context['failure_notes']);
    }

    public function test_transition_records_performed_by(): void
    {
        $user = $this->makeUser($this->hospital);
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_ASSEMBLING);

        $event = TrayService::transitionTo($tray, Tray::STATUS_READY, performedBy: $user->id);

        $this->assertSame($user->id, $event->performed_by);
    }

    public function test_can_transition_and_allowed_targets_reflect_current_status(): void
    {
        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_STERILE);

        $this->assertTrue(TrayService::canTransition($tray, Tray::STATUS_IN_USE));
        $this->assertTrue(TrayService::canTransition($tray, Tray::STATUS_NEEDS_REPROCESSING));
        $this->assertFalse(TrayService::canTransition($tray, Tray::STATUS_READY));
        $this->assertFalse(TrayService::canTransition($tray, Tray::STATUS_ASSEMBLING));

        $this->assertEqualsCanonicalizing(
            [Tray::STATUS_IN_USE, Tray::STATUS_NEEDS_REPROCESSING],
            TrayService::allowedTargets($tray)
        );
    }

    public function test_allowed_targets_is_empty_for_terminal_like_status_with_no_map_entry(): void
    {
        // ASSEMBLING has a map entry; craft a status with none by using an unmapped value directly.
        $tray = $this->makeTray($this->hospital, status: 'UNKNOWN_STATUS');

        $this->assertSame([], TrayService::allowedTargets($tray));
    }

    public function test_reference_ignored_when_null_for_illegal_transition_reasoning_case(): void
    {
        // Sanity: an ISSUE reference tied to a distribution request should not leak into an
        // unrelated legal transition's event when no reference is passed.
        $unit = $this->makeUnit($this->hospital);
        $requester = $this->makeUser($this->hospital);
        $distributionRequest = $this->makeDistributionRequest($this->hospital, $unit, $requester);

        $tray = $this->makeTray($this->hospital, status: Tray::STATUS_STERILE);

        $event = TrayService::transitionTo($tray, Tray::STATUS_IN_USE, reference: $distributionRequest);
        $this->assertSame(DistributionRequest::class, $event->reference_type);

        $again = $this->makeTray($this->hospital, status: Tray::STATUS_STERILE);
        $eventWithoutRef = TrayService::transitionTo($again, Tray::STATUS_IN_USE);
        $this->assertNull($eventWithoutRef->reference_type);
        $this->assertNull($eventWithoutRef->reference_id);
    }
}
