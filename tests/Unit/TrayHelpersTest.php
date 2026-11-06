<?php

namespace Tests\Unit;

use App\Models\Tray;
use Tests\TestCase;

class TrayHelpersTest extends TestCase
{
    private function tray(array $attrs = []): Tray
    {
        return new Tray(array_merge([
            'status' => Tray::STATUS_ASSEMBLING,
        ], $attrs));
    }

    public function test_is_expired_is_false_when_expiry_at_is_null(): void
    {
        $this->assertFalse($this->tray(['expiry_at' => null])->isExpired());
    }

    public function test_is_expired_is_true_when_expiry_at_in_past(): void
    {
        $this->assertTrue($this->tray(['expiry_at' => now()->subDay()])->isExpired());
    }

    public function test_is_expired_is_false_when_expiry_at_in_future(): void
    {
        $this->assertFalse($this->tray(['expiry_at' => now()->addDay()])->isExpired());
    }

    public function test_expires_within_days_is_false_when_expiry_at_is_null(): void
    {
        $this->assertFalse($this->tray(['expiry_at' => null])->expiresWithinDays(5));
    }

    public function test_expires_within_days_is_false_when_already_expired(): void
    {
        $this->assertFalse($this->tray(['expiry_at' => now()->subDay()])->expiresWithinDays(5));
    }

    public function test_expires_within_days_is_true_within_window(): void
    {
        $this->assertTrue($this->tray(['expiry_at' => now()->addDays(2)])->expiresWithinDays(5));
    }

    public function test_expires_within_days_is_false_beyond_window(): void
    {
        $this->assertFalse($this->tray(['expiry_at' => now()->addDays(10)])->expiresWithinDays(5));
    }

    public function test_is_distributable_true_only_when_sterile_and_not_expired(): void
    {
        $this->assertTrue($this->tray([
            'status' => Tray::STATUS_STERILE,
            'expiry_at' => now()->addDay(),
        ])->isDistributable());

        $this->assertFalse($this->tray([
            'status' => Tray::STATUS_STERILE,
            'expiry_at' => now()->subDay(),
        ])->isDistributable(), 'Expired sterile tray must not be distributable.');

        $this->assertFalse($this->tray([
            'status' => Tray::STATUS_READY,
            'expiry_at' => now()->addDay(),
        ])->isDistributable(), 'Non-sterile tray must not be distributable.');
    }

    public function test_can_be_marked_ready_only_from_assembling(): void
    {
        $this->assertTrue($this->tray(['status' => Tray::STATUS_ASSEMBLING])->canBeMarkedReady());

        foreach ([
            Tray::STATUS_READY,
            Tray::STATUS_IN_STERILIZATION,
            Tray::STATUS_STERILE,
            Tray::STATUS_IN_USE,
            Tray::STATUS_RETURNED,
            Tray::STATUS_NEEDS_REPROCESSING,
        ] as $status) {
            $this->assertFalse($this->tray(['status' => $status])->canBeMarkedReady(), "status={$status}");
        }
    }

    public function test_can_be_reprocessed_only_from_returned_or_needs_reprocessing(): void
    {
        $this->assertTrue($this->tray(['status' => Tray::STATUS_RETURNED])->canBeReprocessed());
        $this->assertTrue($this->tray(['status' => Tray::STATUS_NEEDS_REPROCESSING])->canBeReprocessed());

        foreach ([
            Tray::STATUS_ASSEMBLING,
            Tray::STATUS_READY,
            Tray::STATUS_IN_STERILIZATION,
            Tray::STATUS_STERILE,
            Tray::STATUS_IN_USE,
        ] as $status) {
            $this->assertFalse($this->tray(['status' => $status])->canBeReprocessed(), "status={$status}");
        }
    }

    public function test_qr_code_svg_returns_svg_encoding_the_tray_code(): void
    {
        $trayA = $this->tray(['code' => 'TRY-AAA-001']);
        $trayB = $this->tray(['code' => 'TRY-BBB-002']);

        $svgA = $trayA->qrCodeSvg();
        $svgB = $trayB->qrCodeSvg();

        $this->assertStringContainsString('<svg', $svgA);
        $this->assertNotSame($svgA, $svgB, 'Different tray codes must encode to different QR images.');
    }
}
