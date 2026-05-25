<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Unit;

use MortezaAshrafi\FilamentShieldCaptcha\Concerns\HasCaptchaOptions;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\ImageFormat;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class OptionOverridesTest extends TestCase
{
    public function test_it_builds_a_consistent_overrides_array(): void
    {
        $subject = new class
        {
            use HasCaptchaOptions;

            /**
             * @return array<string, mixed>
             */
            public function overrides(): array
            {
                return $this->getCaptchaOptionOverrides();
            }
        };

        $overrides = $subject
            ->width(240)
            ->height(64)
            ->length(6)
            ->fontSize(28)
            ->charset('ABC123')
            ->caseSensitive(false)
            ->noise(level: 2, lines: 4, dots: 50)
            ->ttl(120)
            ->maxAttempts(3)
            ->font('vazirmatn')
            ->format(ImageFormat::Jpeg, jpegQuality: 80)
            ->lightColors(background: [255, 255, 255], text: [10, 10, 10])
            ->darkColors(background: [20, 20, 20], text: [240, 240, 240])
            ->overrides();

        $this->assertSame(240, $overrides['width']);
        $this->assertSame(64, $overrides['height']);
        $this->assertSame(6, $overrides['length']);
        $this->assertSame(28, $overrides['font_size']);
        $this->assertSame('custom', $overrides['mode']);
        $this->assertSame('ABC123', $overrides['charset']);
        $this->assertSame(false, $overrides['case_sensitive']);
        $this->assertSame(['level' => 2, 'lines' => 4, 'dots' => 50], $overrides['noise']);
        $this->assertSame(120, $overrides['ttl']);
        $this->assertSame(3, $overrides['max_attempts']);
        $this->assertSame('vazirmatn', $overrides['font']);
        $this->assertSame('jpeg', $overrides['format']);
        $this->assertSame(80, $overrides['jpeg_quality']);
        $this->assertSame([255, 255, 255], $overrides['light_background']);
        $this->assertSame([10, 10, 10], $overrides['light_text']);
        $this->assertSame([20, 20, 20], $overrides['dark_background']);
        $this->assertSame([240, 240, 240], $overrides['dark_text']);
    }
}
