<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Unit;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class ColorResolutionTest extends TestCase
{
    public function test_it_resolves_light_and_dark_colors_from_config(): void
    {
        $this->app['config']->set('filament-shield-captcha.colors.light.background', [250, 250, 250]);
        $this->app['config']->set('filament-shield-captcha.colors.light.text', [10, 10, 10]);
        $this->app['config']->set('filament-shield-captcha.colors.dark.background', [20, 20, 25]);
        $this->app['config']->set('filament-shield-captcha.colors.dark.text', [240, 240, 245]);

        $manager = CaptchaManager::fromContainer($this->app);
        $options = $manager->optionsFromConfig(['font' => 'noto_sans']);

        $this->assertSame([250, 250, 250], $options->lightBackground->toRgbArray());
        $this->assertSame([10, 10, 10], $options->lightText->toRgbArray());
        $this->assertSame([20, 20, 25], $options->darkBackground->toRgbArray());
        $this->assertSame([240, 240, 245], $options->darkText->toRgbArray());
    }
}
