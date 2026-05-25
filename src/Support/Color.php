<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Support;

/**
 * @immutable
 */
final readonly class Color
{
    public function __construct(
        public int $r,
        public int $g,
        public int $b,
    ) {
        foreach (['r' => $r, 'g' => $g, 'b' => $b] as $channel => $value) {
            if ($value < 0 || $value > 255) {
                throw new \InvalidArgumentException("Color channel '{$channel}' must be between 0 and 255.");
            }
        }
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    public static function fromRgbArray(array $rgb): self
    {
        return new self($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public function toRgbArray(): array
    {
        return [$this->r, $this->g, $this->b];
    }
}
