<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Challenge\Challenge;
use MortezaAshrafi\FilamentShieldCaptcha\Challenge\ChallengeStore;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationStatus;

final class ChallengeLifecycleTest extends TestCase
{
    public function test_refresh_invalidates_the_previous_challenge_immediately(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig(['ttl' => 300]);

        $first = $manager->refreshChallenge($contextKey, $options);
        $firstAnswer = $first->answer;

        $second = $manager->refreshChallenge($contextKey, $options);

        $this->assertNotSame($first->id, $second->id);

        $result = $manager->verify($contextKey, $firstAnswer);
        $this->assertSame(VerificationStatus::Failed, $result->status);
    }

    public function test_wrong_attempts_increment_and_lock_after_max_attempts(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig(['ttl' => 300, 'max_attempts' => 2]);
        $manager->refreshChallenge($contextKey, $options);

        $first = $manager->verify($contextKey, 'WRONG');
        $this->assertSame(VerificationStatus::Failed, $first->status);
        $this->assertSame(1, $first->attempts);

        $second = $manager->verify($contextKey, 'WRONG');
        $this->assertSame(VerificationStatus::Locked, $second->status);
    }

    public function test_expired_challenges_fail_safely_and_are_cleared(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig(['ttl' => 300]);
        $store = $this->extractStore($manager);
        $challenge = new Challenge(
            id: 'expired',
            answer: 'ABCDE',
            seed: 'deadbeef',
            expiresAt: (new \DateTimeImmutable)->modify('-1 second'),
            attempts: 0,
            maxAttempts: $options->maxAttempts,
            caseSensitive: $options->caseSensitive,
        );
        $store->put($contextKey, $challenge, 300);

        $result = $manager->verify($contextKey, $challenge->answer);
        $this->assertSame(VerificationStatus::Expired, $result->status);

        $this->assertNull($store->get($contextKey));
    }

    private function extractStore(CaptchaManager $manager): ChallengeStore
    {
        $ref = new \ReflectionClass($manager);
        $prop = $ref->getProperty('store');
        $prop->setAccessible(true);

        /** @var ChallengeStore $store */
        $store = $prop->getValue($manager);

        return $store;
    }
}
