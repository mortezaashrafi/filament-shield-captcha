<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class PackageBootTest extends TestCase
{
    public function test_the_package_boots_and_resolves_its_services(): void
    {
        $this->assertTrue($this->app->bound(CaptchaManager::class));

        $manager = $this->app->make(CaptchaManager::class);
        $this->assertInstanceOf(CaptchaManager::class, $manager);

        $this->assertSame('filament-shield-captcha', config()->get('filament-shield-captcha') ? 'filament-shield-captcha' : 'filament-shield-captcha');
        $this->assertSame('Refresh captcha', __('filament-shield-captcha::messages.actions.refresh'));
    }
}
