<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationStatus;

final class CaseSensitivityTest extends TestCase
{
    public function test_case_insensitive_verification_accepts_different_casing(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig([
            'mode' => 'custom',
            'charset' => 'aBc',
            'length' => 3,
            'case_sensitive' => false,
        ]);

        $challenge = $manager->refreshChallenge($contextKey, $options);

        $result = $manager->verify($contextKey, mb_strtoupper($challenge->answer));
        $this->assertSame(VerificationStatus::Passed, $result->status);
    }

    public function test_case_sensitive_verification_rejects_different_casing(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig([
            'mode' => 'custom',
            'charset' => 'aBc',
            'length' => 3,
            'case_sensitive' => true,
        ]);

        $challenge = $manager->refreshChallenge($contextKey, $options);

        $result = $manager->verify($contextKey, mb_strtoupper($challenge->answer));
        $this->assertSame(VerificationStatus::Failed, $result->status);
    }
}
