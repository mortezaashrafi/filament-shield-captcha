<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Options;

/**
 * @immutable
 */
final readonly class NoiseOptions
{
    public function __construct(
        public int $level,
        public int $lines,
        public int $dots,
    ) {
        if ($level < 0) {
            throw new \InvalidArgumentException('Noise level must be >= 0.');
        }
        if ($lines < 0) {
            throw new \InvalidArgumentException('Line noise count must be >= 0.');
        }
        if ($dots < 0) {
            throw new \InvalidArgumentException('Dot noise count must be >= 0.');
        }
    }
}
