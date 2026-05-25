<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Feature;

use MortezaAshrafi\FilamentShieldCaptcha\CaptchaManager;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\Theme;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class ImageDataUriTest extends TestCase
{
    public function test_it_generates_a_png_data_uri_in_memory(): void
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatetruecolor') || ! function_exists('imagettftext') || ! function_exists('imagepng')) {
            $this->markTestSkipped('GD + FreeType + PNG support is required to run this test.');
        }

        $fontPath = dirname(__DIR__, 2).'/resources/fonts/NotoSans.ttf';
        if (! is_file($fontPath)) {
            $this->markTestSkipped('Bundled fonts are not present in the package yet.');
        }

        $this->app['session']->start();

        $manager = CaptchaManager::fromContainer($this->app);
        $contextKey = $manager->contextKey('lw.test.captcha');

        $options = $manager->optionsFromConfig([
            'format' => 'png',
            'width' => 200,
            'height' => 60,
            'font' => 'noto_sans',
        ]);

        $manager->refreshChallenge($contextKey, $options);

        $dataUri = $manager->imageDataUri($contextKey, $options, Theme::Light);

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        $base64 = substr($dataUri, strlen('data:image/png;base64,'));
        $bytes = base64_decode($base64, true);
        $this->assertIsString($bytes);

        $this->assertSame("\x89PNG\r\n\x1a\n", substr($bytes, 0, 8));
    }
}
