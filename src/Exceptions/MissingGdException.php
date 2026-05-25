<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Exceptions;

use RuntimeException;

final class MissingGdException extends RuntimeException
{
    /**
     * @param  list<string>  $missing
     */
    public static function forMissingCapabilities(array $missing): self
    {
        $missingList = implode(', ', $missing);

        $message = <<<TEXT
Filament Shield CAPTCHA requires PHP GD with FreeType support, but required capabilities are missing: {$missingList}.

How to fix (common Linux distros):
- Debian/Ubuntu: sudo apt-get install php-gd
- Alpine: apk add php82-gd (or your PHP version) and enable it

Then restart PHP-FPM / your web server and ensure the GD extension is enabled.
TEXT;

        return new self($message);
    }
}
