<?php

namespace App\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Throwable;

/**
 * Server-side QR codes (no third-party image APIs or CDNs).
 */
final class QrCodeHelper
{
    /**
     * Data URI suitable for an <img src="..."> (SVG, base64).
     */
    public static function dataUriImageSrc(string $data): ?string
    {
        if ($data === '') {
            return null;
        }

        try {
            $options = new QROptions([
                'scale' => 4,
                'svgAddXmlHeader' => false,
                'outputBase64' => true,
            ]);

            return (new QRCode($options))->render($data);
        } catch (Throwable) {
            return null;
        }
    }
}
