<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Unit;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Exceptions\MissingGdException;
use MortezaAshrafi\FilamentShieldCaptcha\Support\GdCapabilities;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class GdGuardTest extends TestCase
{
    public function test_it_throws_a_clear_exception_when_gd_capabilities_are_missing(): void
    {
        $this->app->singleton(GdCapabilities::class, static fn (): GdCapabilities => new class extends GdCapabilities
        {
            public function missingCapabilities(): array
            {
                return ['ext-gd (PHP GD extension)', 'imagettftext()'];
            }
        });

        $manager = CaptchaManager::fromContainer($this->app);

        $this->expectException(MissingGdException::class);
        $this->expectExceptionMessage('Filament Shield CAPTCHA requires PHP GD');

        $manager->guardGd();
    }
}
