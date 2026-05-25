<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Concerns\HasCaptchaOptions;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\Theme;
use MortezaAshrafi\FilamentShieldCaptcha\Rules\CaptchaRule;

final class Captcha extends Field
{
    use HasCaptchaOptions;

    protected string $view = 'filament-shield-captcha::components.captcha';

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-shield-captcha::messages.alt'));

        $this->registerActions([
            fn (Captcha $component): Action => $component->getRefreshAction(),
        ]);

        $this->afterStateHydrated(function (Captcha $component): void {
            $manager = app(CaptchaManager::class);
            $options = $manager->optionsFromConfig($component->getCaptchaOptionOverrides());
            $manager->ensureChallenge($component->getCaptchaContextKey(), $options);
        });

        $this->rule(function (Captcha $component): CaptchaRule {
            return new CaptchaRule(app(CaptchaManager::class), $component->getCaptchaContextKey());
        });
    }

    public function getCaptchaContextKey(): string
    {
        $livewireKey = $this->getLivewireKey() ?? $this->getKey() ?? $this->getStatePath();

        return app(CaptchaManager::class)->contextKey((string) $livewireKey);
    }

    public function getLightImageDataUri(): string
    {
        $manager = app(CaptchaManager::class);
        $options = $manager->optionsFromConfig($this->getCaptchaOptionOverrides());

        return $manager->imageDataUri($this->getCaptchaContextKey(), $options, Theme::Light);
    }

    public function getDarkImageDataUri(): string
    {
        $manager = app(CaptchaManager::class);
        $options = $manager->optionsFromConfig($this->getCaptchaOptionOverrides());

        return $manager->imageDataUri($this->getCaptchaContextKey(), $options, Theme::Dark);
    }

    public function getRefreshAction(): Action
    {
        return Action::make('refresh')
            ->label(fn (): string => (string) (config('filament-shield-captcha.ui.refresh.label') ?: __('filament-shield-captcha::messages.actions.refresh')))
            ->icon((string) (config('filament-shield-captcha.ui.refresh.icon') ?: 'heroicon-m-arrow-path'))
            ->iconButton()
            ->color('gray')
            ->action(function (Captcha $component): void {
                $manager = app(CaptchaManager::class);
                $options = $manager->optionsFromConfig($component->getCaptchaOptionOverrides());
                $manager->refreshChallenge($component->getCaptchaContextKey(), $options);

                $component->state(null);
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        return [
            ...parent::getViewData(),
            'lightDataUri' => $this->getLightImageDataUri(),
            'darkDataUri' => $this->getDarkImageDataUri(),
        ];
    }
}
