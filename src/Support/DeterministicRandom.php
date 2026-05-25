<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Support;

/**
 * A small deterministic PRNG used to keep CAPTCHA noise stable across Livewire
 * re-renders for the same challenge seed.
 *
 * This is NOT used for security-sensitive randomness. CAPTCHA answers are
 * generated using random_bytes()/random_int() elsewhere.
 */
final class DeterministicRandom
{
    private int $counter = 0;

    public function __construct(string $seed)
    {
        $this->seed = $seed;
    }

    private string $seed;

    public function int(int $min, int $max): int
    {
        if ($min > $max) {
            throw new \InvalidArgumentException('Min must be <= max.');
        }

        $range = $max - $min + 1;
        $value = $this->nextUInt32();

        return $min + (int) ($value % $range);
    }

    public function float(float $min, float $max): float
    {
        if ($min > $max) {
            throw new \InvalidArgumentException('Min must be <= max.');
        }

        $value = $this->nextUInt32();
        $unit = $value / 4_294_967_295;

        return $min + (($max - $min) * $unit);
    }

    private function nextUInt32(): int
    {
        $hash = hash('sha256', $this->seed.':'.$this->counter, true);
        $this->counter++;

        /** @var array{1:int} $unpacked */
        $unpacked = unpack('N', substr($hash, 0, 4));

        return (int) $unpacked[1];
    }
}
