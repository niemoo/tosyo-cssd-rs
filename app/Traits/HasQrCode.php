<?php

namespace App\Traits;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

trait HasQrCode
{
    /**
     * Generate QR code sebagai SVG inline berdasarkan kolom `code`.
     */
    public function qrCodeSvg(int $size = 160, int $margin = 0): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            data: (string) $this->code,
            size: $size,
            margin: $margin,
        );

        return $builder->build()->getString();
    }
}