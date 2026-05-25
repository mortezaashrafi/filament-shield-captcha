<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Tests\Unit;

use MortezaAshrafi\FilamentShieldCaptcha\Exceptions\InvalidFontException;
use MortezaAshrafi\FilamentShieldCaptcha\Support\FontResolver;
use MortezaAshrafi\FilamentShieldCaptcha\Tests\TestCase;

final class FontResolverTest extends TestCase
{
    public function test_it_resolves_a_bundled_font_path(): void
    {
        $basePath = dirname(__DIR__, 2);
        $resolver = new FontResolver(
            config: [
                'default' => 'noto_sans',
                'custom_path' => null,
                'bundled' => [
                    'noto_sans' => 'resources/fonts/NotoSans.ttf',
                ],
            ],
            packageBasePath: $basePath,
        );

        $path = $resolver->resolveFontPath();

        if (! is_file($path)) {
            $this->markTestSkipped('Bundled font file is not present yet.');
        }

        $this->assertStringEndsWith('resources/fonts/NotoSans.ttf', str_replace('\\', '/', $path));
    }

    public function test_it_throws_for_unknown_font_key(): void
    {
        $resolver = new FontResolver(
            config: [
                'default' => 'unknown',
                'custom_path' => null,
                'bundled' => [],
            ],
            packageBasePath: dirname(__DIR__, 2),
        );

        $this->expectException(InvalidFontException::class);
        $resolver->resolveFontPath();
    }
}
