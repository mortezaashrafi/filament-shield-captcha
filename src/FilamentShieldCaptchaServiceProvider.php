<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use MortezaAshrafi\FilamentShieldCaptcha\Support\GdCapabilities;

final class FilamentShieldCaptchaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-shield-captcha.php', 'filament-shield-captcha');

        $this->app->singleton(GdCapabilities::class, static fn (): GdCapabilities => new GdCapabilities);
        $this->app->singleton(CaptchaManager::class, static fn (Container $app): CaptchaManager => CaptchaManager::fromContainer($app));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-shield-captcha');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-shield-captcha');

        $this->publishes([
            __DIR__.'/../config/filament-shield-captcha.php' => config_path('filament-shield-captcha.php'),
        ], 'filament-shield-captcha-config');

        if ((bool) config('filament-shield-captcha.gd.guard_on_boot', true)) {
            $this->app->make(CaptchaManager::class)->guardGd();
        }
    }
}
