<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Support;

use MortezaAshrafi\FilamentShieldCaptcha\Exceptions\InvalidFontException;

final class FontResolver
{
    /**
     * @param array{
     *   default: string,
     *   custom_path: string|null,
     *   bundled: array<string, string>
     * } $config
     */
    public function __construct(
        private readonly array $config,
        private readonly string $packageBasePath,
    ) {}

    public function resolveFontPath(): string
    {
        $customPath = $this->config['custom_path'] ?? null;
        if (filled($customPath)) {
            return $this->assertReadable($customPath);
        }

        $key = $this->config['default'];
        $bundled = $this->config['bundled'];

        if (! array_key_exists($key, $bundled)) {
            throw InvalidFontException::unknownFontKey($key);
        }

        $relativePath = $bundled[$key];

        return $this->assertReadable($this->packageBasePath.'/'.ltrim($relativePath, '/\\'));
    }

    private function assertReadable(string $path): string
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw InvalidFontException::missingFontFile($path);
        }

        return $path;
    }
}
