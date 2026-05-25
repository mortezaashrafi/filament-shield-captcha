<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Support;

/**
 * Performs a defensive runtime check for the minimum GD + FreeType capabilities
 * required to generate image CAPTCHAs using imagettftext().
 *
 * Composer already requires ext-gd, but this class provides a clear, actionable
 * exception for misconfigured servers (e.g. GD installed without FreeType).
 */
class GdCapabilities
{
    /**
     * @return list<string> A list of missing capabilities (empty when OK).
     */
    public function missingCapabilities(): array
    {
        $missing = [];

        if (! \extension_loaded('gd')) {
            $missing[] = 'ext-gd (PHP GD extension)';
        }

        foreach ([
            'imagecreatetruecolor',
            'imagecolorallocate',
            'imagefilledrectangle',
            'imagesetthickness',
            'imageline',
            'imagesetpixel',
            'imagettftext',
        ] as $fn) {
            if (! \function_exists($fn)) {
                $missing[] = $fn.'()';
            }
        }

        if (! \function_exists('imagepng') && ! \function_exists('imagejpeg')) {
            $missing[] = 'imagepng() or imagejpeg()';
        }

        return $missing;
    }
}
