<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class CharacterGenerationTest extends TestCase
{
    public function test_custom_charset_is_used_for_generation(): void
    {
        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig([
            'mode' => 'custom',
            'charset' => 'A',
            'length' => 5,
        ]);

        $challenge = $manager->refreshChallenge($contextKey, $options);

        $this->assertSame('AAAAA', $challenge->answer);
    }
}
