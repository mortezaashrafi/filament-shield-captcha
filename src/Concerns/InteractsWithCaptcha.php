<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Concerns;

use MortezaAshrafi\FilamentShieldCaptcha\Forms\Components\Captcha;

trait InteractsWithCaptcha
{
    protected function getCaptchaFormComponent(): Captcha
    {
        return Captcha::make('captcha')
            ->label(__('filament-shield-captcha::messages.alt'))
            ->required();
    }
}
