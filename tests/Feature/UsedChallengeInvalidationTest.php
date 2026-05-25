<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationStatus;

final class UsedChallengeInvalidationTest extends TestCase
{
    public function test_successful_verification_invalidates_the_challenge(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig(['ttl' => 300]);
        $challenge = $manager->refreshChallenge($contextKey, $options);

        $first = $manager->verify($contextKey, $challenge->answer);
        $this->assertSame(VerificationStatus::Passed, $first->status);

        $second = $manager->verify($contextKey, $challenge->answer);
        $this->assertSame(VerificationStatus::Missing, $second->status);
    }
}
