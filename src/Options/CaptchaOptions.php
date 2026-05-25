<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Options;

use MortezaAshrafi\FilamentShieldCaptcha\Enums\CharacterMode;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\ImageFormat;
use MortezaAshrafi\FilamentShieldCaptcha\Support\Color;

/**
 * @immutable
 */
final readonly class CaptchaOptions
{
    public function __construct(
        public int $width,
        public int $height,
        public int $length,
        public int $fontSize,
        public CharacterMode $mode,
        public ?string $charset,
        public bool $caseSensitive,
        public NoiseOptions $noise,
        public Color $lightBackground,
        public Color $lightText,
        public Color $darkBackground,
        public Color $darkText,
        public string $fontPath,
        public ImageFormat $format,
        public int $jpegQuality,
        public int $ttlSeconds,
        public int $maxAttempts,
    ) {
        if ($width < 80 || $width > 800) {
            throw new \InvalidArgumentException('CAPTCHA width must be between 80 and 800.');
        }

        if ($height < 30 || $height > 300) {
            throw new \InvalidArgumentException('CAPTCHA height must be between 30 and 300.');
        }

        if ($length < 3 || $length > 12) {
            throw new \InvalidArgumentException('CAPTCHA length must be between 3 and 12.');
        }

        if ($fontSize < 10 || $fontSize > 80) {
            throw new \InvalidArgumentException('CAPTCHA font size must be between 10 and 80.');
        }

        if ($ttlSeconds < 30) {
            throw new \InvalidArgumentException('CAPTCHA ttlSeconds must be at least 30.');
        }

        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('CAPTCHA maxAttempts must be at least 1.');
        }

        if ($mode === CharacterMode::Custom && blank($charset)) {
            throw new \InvalidArgumentException('CAPTCHA charset must be provided when mode is custom.');
        }
    }
}
