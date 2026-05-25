<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Challenge;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class CacheChallengeStore implements ChallengeStore
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $keyPrefix = 'filament_shield_captcha:',
    ) {}

    public function get(string $contextKey): ?Challenge
    {
        /** @var array<string, mixed>|null $payload */
        $payload = $this->cache->get($this->toCacheKey($contextKey));
        if (! is_array($payload)) {
            return null;
        }

        $expiresAt = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, (string) ($payload['expires_at'] ?? ''));
        if (! $expiresAt instanceof \DateTimeImmutable) {
            return null;
        }

        return new Challenge(
            id: (string) ($payload['id'] ?? ''),
            answer: (string) ($payload['answer'] ?? ''),
            seed: (string) ($payload['seed'] ?? ''),
            expiresAt: $expiresAt,
            attempts: (int) ($payload['attempts'] ?? 0),
            maxAttempts: (int) ($payload['max_attempts'] ?? 5),
            caseSensitive: (bool) ($payload['case_sensitive'] ?? false),
        );
    }

    public function put(string $contextKey, Challenge $challenge, int $ttlSeconds): void
    {
        $payload = [
            'id' => $challenge->id,
            'answer' => $challenge->answer,
            'seed' => $challenge->seed,
            'expires_at' => $challenge->expiresAt->format(\DateTimeInterface::ATOM),
            'attempts' => $challenge->attempts,
            'max_attempts' => $challenge->maxAttempts,
            'case_sensitive' => $challenge->caseSensitive,
        ];

        $this->cache->put($this->toCacheKey($contextKey), $payload, $ttlSeconds);
    }

    public function forget(string $contextKey): void
    {
        $this->cache->forget($this->toCacheKey($contextKey));
    }

    public function incrementAttempts(string $contextKey): ?Challenge
    {
        $challenge = $this->get($contextKey);
        if (! $challenge instanceof Challenge) {
            return null;
        }

        $updated = new Challenge(
            id: $challenge->id,
            answer: $challenge->answer,
            seed: $challenge->seed,
            expiresAt: $challenge->expiresAt,
            attempts: $challenge->attempts + 1,
            maxAttempts: $challenge->maxAttempts,
            caseSensitive: $challenge->caseSensitive,
        );

        $ttl = max(1, $challenge->expiresAt->getTimestamp() - time());
        $this->put($contextKey, $updated, $ttl);

        return $updated;
    }

    private function toCacheKey(string $contextKey): string
    {
        return $this->keyPrefix.hash('sha256', $contextKey);
    }
}
