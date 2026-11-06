<?php

namespace App\Exceptions;

use App\Models\Tray;
use RuntimeException;

class InvalidTrayTransition extends RuntimeException
{
    public static function for(Tray $tray, string $to): self
    {
        $from = $tray->status;

        return new self(
            "Transisi tray tidak valid: {$from} → {$to} (tray {$tray->code})."
        );
    }
}
