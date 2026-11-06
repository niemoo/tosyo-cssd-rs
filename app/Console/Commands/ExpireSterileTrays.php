<?php

namespace App\Console\Commands;

use App\Models\Tray;
use App\Services\TrayService;
use Illuminate\Console\Command;

/**
 * Tanpa ini, tray STERILE yang expiry_at-nya sudah lewat tetap mengaku steril
 * selamanya — tidak ada yang pernah memicu transisi EXPIRE (STERILE -> NEEDS_REPROCESSING)
 * yang sudah ada di TrayService::TRANSITIONS.
 */
class ExpireSterileTrays extends Command
{
    protected $signature = 'trays:expire';

    protected $description = 'Pindahkan tray STERILE yang sudah kedaluwarsa ke NEEDS_REPROCESSING';

    public function handle(): int
    {
        $trays = Tray::where('status', Tray::STATUS_STERILE)
            ->where('expiry_at', '<', now())
            ->get();

        foreach ($trays as $tray) {
            TrayService::transitionTo(
                $tray,
                Tray::STATUS_NEEDS_REPROCESSING,
                attributes: ['sterile_at' => null, 'expiry_at' => null, 'current_load_number' => null],
                context: ['reason' => 'kedaluwarsa', 'expired_at' => $tray->expiry_at?->toDateTimeString()],
            );
        }

        $this->info("{$trays->count()} tray kedaluwarsa dipindahkan ke Perlu Diproses Ulang.");

        return self::SUCCESS;
    }
}
