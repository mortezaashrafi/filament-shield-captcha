<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Challenge;

/**
 * @immutable
 */
final readonly class Challenge
{
    public function __construct(
        public string $id,
        public string $answer,
        public string $seed,
        public \DateTimeImmutable $expiresAt,
        public int $attempts = 0,
        public int $maxAttempts = 5,
        public bool $caseSensitive = false,
    ) {}

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= $this->maxAttempts;
    }

    public function normalizedAnswer(): string
    {
        return $this->caseSensitive ? $this->answer : mb_strtoupper($this->answer);
    }
}
