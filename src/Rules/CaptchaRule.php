<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationStatus;

final class CaptchaRule implements ValidationRule
{
    public function __construct(
        private readonly CaptchaManager $manager,
        private readonly string $contextKey,
    ) {}

    /**
     * @param  mixed  $value
     */
    public function validate(string $attribute, $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $result = $this->manager->verify($this->contextKey, (string) $value);

        if ($result->status === VerificationStatus::Passed) {
            return;
        }

        $fail(__('filament-shield-captcha::messages.validation.failed'));
    }
}
