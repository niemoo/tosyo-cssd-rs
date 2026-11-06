<?php

namespace App\Services;

use App\Exceptions\InvalidTrayTransition;
use App\Models\Tray;
use App\Models\TrayLifecycleEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu untuk mengubah status tray.
 *
 * Setiap transisi: (1) divalidasi terhadap TRANSITIONS, (2) menulis status baru,
 * (3) mencatat satu baris tray_lifecycle_events. Jangan pernah menulis
 * `$tray->status` langsung dari controller — lewat sini agar loop selalu sah
 * dan riwayat selalu lengkap.
 */
class TrayService
{
    /**
     * Peta transisi legal: [status_asal => [status_tujuan => event_type]].
     * Pasangan (asal → tujuan) menentukan event_type secara unik.
     */
    public const TRANSITIONS = [
        Tray::STATUS_ASSEMBLING => [
            Tray::STATUS_READY => 'PACKAGE',
        ],
        Tray::STATUS_READY => [
            Tray::STATUS_IN_STERILIZATION => 'LOAD',
            Tray::STATUS_ASSEMBLING => 'REPROCESS', // buka kemasan, rakit ulang
        ],
        Tray::STATUS_IN_STERILIZATION => [
            Tray::STATUS_STERILE => 'STERILIZE_PASS',
            Tray::STATUS_NEEDS_REPROCESSING => 'STERILIZE_FAIL',
        ],
        Tray::STATUS_STERILE => [
            Tray::STATUS_IN_USE => 'ISSUE',
            Tray::STATUS_NEEDS_REPROCESSING => 'EXPIRE',
        ],
        Tray::STATUS_IN_USE => [
            Tray::STATUS_RETURNED => 'RETURN',
            Tray::STATUS_NEEDS_REPROCESSING => 'RETURN_DAMAGED',
        ],
        Tray::STATUS_RETURNED => [
            Tray::STATUS_ASSEMBLING => 'REPROCESS',
        ],
        Tray::STATUS_NEEDS_REPROCESSING => [
            Tray::STATUS_ASSEMBLING => 'REPROCESS',
        ],
    ];

    /**
     * Pindahkan tray ke status baru, catat peristiwanya.
     *
     * @param  array  $attributes  kolom tray lain yang ikut di-set (mis. current_rack_id)
     * @param  array  $context  bukti bebas-bentuk yang disimpan di event
     */
    public static function transitionTo(
        Tray $tray,
        string $to,
        ?Model $reference = null,
        array $attributes = [],
        array $context = [],
        ?string $notes = null,
        ?int $performedBy = null,
        ?string $eventType = null,
    ): TrayLifecycleEvent {
        $from = $tray->status;

        if (! static::canTransition($tray, $to)) {
            throw InvalidTrayTransition::for($tray, $to);
        }

        $eventType ??= static::TRANSITIONS[$from][$to];

        return DB::transaction(function () use (
            $tray, $from, $to, $reference, $attributes, $context, $notes, $performedBy, $eventType
        ) {
            $tray->update(array_merge($attributes, ['status' => $to]));

            return static::writeEvent(
                $tray, $eventType, $from, $to,
                $reference, $context, $notes, $performedBy
            );
        });
    }

    /** Catat peristiwa lahirnya tray (belum ada status asal). */
    public static function recordGenesis(Tray $tray, ?int $performedBy = null): TrayLifecycleEvent
    {
        return static::writeEvent(
            $tray, 'ASSEMBLE_START', null, $tray->status,
            null, [], null, $performedBy
        );
    }

    public static function canTransition(Tray $tray, string $to): bool
    {
        return isset(static::TRANSITIONS[$tray->status][$to]);
    }

    /** Daftar status tujuan yang sah dari status tray saat ini. */
    public static function allowedTargets(Tray $tray): array
    {
        return array_keys(static::TRANSITIONS[$tray->status] ?? []);
    }

    private static function writeEvent(
        Tray $tray,
        string $eventType,
        ?string $from,
        string $to,
        ?Model $reference,
        array $context,
        ?string $notes,
        ?int $performedBy,
    ): TrayLifecycleEvent {
        return TrayLifecycleEvent::create([
            'hospital_id' => $tray->hospital_id,
            'tray_id' => $tray->id,
            'event_type' => $eventType,
            'from_status' => $from,
            'to_status' => $to,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'context' => $context ?: null,
            'performed_by' => $performedBy ?? auth()->id(),
            'performed_at' => now(),
            'notes' => $notes,
        ]);
    }
}
