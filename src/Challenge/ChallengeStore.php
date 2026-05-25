<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Challenge;

interface ChallengeStore
{
    public function get(string $contextKey): ?Challenge;

    public function put(string $contextKey, Challenge $challenge, int $ttlSeconds): void;

    public function forget(string $contextKey): void;

    public function incrementAttempts(string $contextKey): ?Challenge;
}
