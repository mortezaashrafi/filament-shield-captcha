<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Exceptions;

use RuntimeException;

final class InvalidFontException extends RuntimeException
{
    public static function missingFontFile(string $path): self
    {
        return new self("The configured CAPTCHA font file does not exist or is not readable: {$path}");
    }

    public static function unknownFontKey(string $key): self
    {
        return new self("Unknown CAPTCHA font key [{$key}]. Publish the config and set a valid fonts.default value, or provide fonts.custom_path.");
    }
}
