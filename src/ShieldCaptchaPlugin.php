<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class ShieldCaptchaPlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'mortezaashrafi-filament-shield-captcha';
    }

    public function register(Panel $panel): void
    {
        // No panel-level registration required for this package.
        // The field is used directly via Captcha::make('captcha').
    }

    public function boot(Panel $panel): void
    {
        // Intentionally empty.
    }
}
