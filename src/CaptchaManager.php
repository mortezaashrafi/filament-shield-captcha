<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\SessionManager;
use MortezaAshrafi\FilamentShieldCaptcha\Challenge\CacheChallengeStore;
use MortezaAshrafi\FilamentShieldCaptcha\Challenge\Challenge;
use MortezaAshrafi\FilamentShieldCaptcha\Challenge\ChallengeStore;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\CharacterMode;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\ImageFormat;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\Theme;
use MortezaAshrafi\FilamentShieldCaptcha\Exceptions\MissingGdException;
use MortezaAshrafi\FilamentShieldCaptcha\Options\CaptchaOptions;
use MortezaAshrafi\FilamentShieldCaptcha\Options\NoiseOptions;
use MortezaAshrafi\FilamentShieldCaptcha\Support\Color;
use MortezaAshrafi\FilamentShieldCaptcha\Support\FontResolver;
use MortezaAshrafi\FilamentShieldCaptcha\Support\GdCapabilities;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationResult;
use MortezaAshrafi\FilamentShieldCaptcha\Verification\VerificationStatus;

final class CaptchaManager
{
    private function __construct(
        private readonly ChallengeStore $store,
        private readonly GdCapabilities $gdCapabilities,
        private readonly SessionManager $session,
        private readonly string $packageBasePath,
    ) {}

    public static function fromContainer(Container $app): self
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $app->make(CacheFactory::class);

        $cacheStore = config('filament-shield-captcha.store.cache_store');
        $cache = filled($cacheStore) ? $cacheFactory->store($cacheStore) : $cacheFactory->store();

        return new self(
            store: new CacheChallengeStore($cache),
            gdCapabilities: $app->make(GdCapabilities::class),
            session: $app->make(SessionManager::class),
            packageBasePath: dirname(__DIR__),
        );
    }

    public function guardGd(): void
    {
        $missing = $this->gdCapabilities->missingCapabilities();

        if ($missing !== []) {
            throw MissingGdException::forMissingCapabilities($missing);
        }
    }

    public function contextKey(string $livewireKey): string
    {
        $sessionId = $this->session->getId();
        $prefix = filled($sessionId) ? "sid:{$sessionId}|" : '';

        return $prefix."lw:{$livewireKey}";
    }

    public function ensureChallenge(string $contextKey, CaptchaOptions $options): Challenge
    {
        $existing = $this->store->get($contextKey);
        $now = new \DateTimeImmutable;

        if ($existing instanceof Challenge) {
            if (! $existing->isExpired($now) && ! $existing->hasExceededAttempts()) {
                return $existing;
            }
        }

        return $this->refreshChallenge($contextKey, $options);
    }

    public function refreshChallenge(string $contextKey, CaptchaOptions $options): Challenge
    {
        $id = bin2hex(random_bytes(16));
        $seed = bin2hex(random_bytes(16));
        $answer = $this->generateAnswer($options);

        $challenge = new Challenge(
            id: $id,
            answer: $answer,
            seed: $seed,
            expiresAt: (new \DateTimeImmutable)->modify("+{$options->ttlSeconds} seconds"),
            attempts: 0,
            maxAttempts: $options->maxAttempts,
            caseSensitive: $options->caseSensitive,
        );

        $this->store->put($contextKey, $challenge, $options->ttlSeconds);

        return $challenge;
    }

    public function imageDataUri(string $contextKey, CaptchaOptions $options, Theme $theme): string
    {
        $this->guardGd();

        $challenge = $this->ensureChallenge($contextKey, $options);

        $generator = new CaptchaGenerator($options);

        return $generator->toDataUri($challenge->answer, $challenge->seed, $theme);
    }

    public function verify(string $contextKey, ?string $value): VerificationResult
    {
        $challenge = $this->store->get($contextKey);
        if (! $challenge instanceof Challenge) {
            return new VerificationResult(VerificationStatus::Missing);
        }

        $now = new \DateTimeImmutable;
        if ($challenge->isExpired($now)) {
            $this->store->forget($contextKey);

            return new VerificationResult(VerificationStatus::Expired, attempts: $challenge->attempts, maxAttempts: $challenge->maxAttempts);
        }

        if ($challenge->hasExceededAttempts()) {
            $this->store->forget($contextKey);

            return new VerificationResult(VerificationStatus::Locked, attempts: $challenge->attempts, maxAttempts: $challenge->maxAttempts);
        }

        $given = (string) ($value ?? '');
        $given = trim($given);

        if ($given === '') {
            return new VerificationResult(VerificationStatus::Failed, attempts: $challenge->attempts, maxAttempts: $challenge->maxAttempts);
        }

        $normalizedGiven = $challenge->caseSensitive ? $given : mb_strtoupper($given);

        if (hash_equals($challenge->normalizedAnswer(), $normalizedGiven)) {
            $this->store->forget($contextKey);

            return new VerificationResult(VerificationStatus::Passed, attempts: $challenge->attempts, maxAttempts: $challenge->maxAttempts);
        }

        $updated = $this->store->incrementAttempts($contextKey);
        if ($updated instanceof Challenge && $updated->hasExceededAttempts()) {
            $this->store->forget($contextKey);

            return new VerificationResult(VerificationStatus::Locked, attempts: $updated->attempts, maxAttempts: $updated->maxAttempts);
        }

        return new VerificationResult(
            VerificationStatus::Failed,
            attempts: $updated?->attempts ?? ($challenge->attempts + 1),
            maxAttempts: $challenge->maxAttempts,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function optionsFromConfig(array $overrides = []): CaptchaOptions
    {
        $defaults = (array) config('filament-shield-captcha.defaults', []);
        $colors = (array) config('filament-shield-captcha.colors', []);
        $store = (array) config('filament-shield-captcha.store', []);
        $fonts = (array) config('filament-shield-captcha.fonts', []);

        $modeString = (string) ($overrides['mode'] ?? $defaults['mode'] ?? CharacterMode::Alphanumeric->value);
        $mode = CharacterMode::tryFrom($modeString) ?? CharacterMode::Alphanumeric;

        $charset = $overrides['charset'] ?? $defaults['charset'] ?? null;

        $noise = $overrides['noise'] ?? $defaults['noise'] ?? [];
        $noiseOptions = new NoiseOptions(
            level: (int) ($noise['level'] ?? 2),
            lines: (int) ($noise['lines'] ?? 3),
            dots: (int) ($noise['dots'] ?? 40),
        );

        $isRtl = $overrides['is_rtl'] ?? false;
        $isLtr = $overrides['is_ltr'] ?? false;

        if (! $isRtl && ! $isLtr) {
            $direction = __('filament-shield-captcha::messages.direction');
            $isRtl = $direction === 'rtl';
        }

        $defaultFont = (string) ($overrides['font'] ?? $fonts['default'] ?? 'noto_sans');
        if (! isset($overrides['font'])) {
            if ($isRtl) {
                $defaultFont = (string) ($fonts['rtl'] ?? 'vazirmatn');
            } elseif ($isLtr) {
                $defaultFont = (string) ($fonts['ltr'] ?? 'noto_sans');
            }
        }

        $fontPath = (new FontResolver(
            config: [
                'default' => $defaultFont,
                'custom_path' => $overrides['font_path'] ?? $fonts['custom_path'] ?? null,
                'bundled' => (array) ($fonts['bundled'] ?? []),
            ],
            packageBasePath: $this->packageBasePath,
        ))->resolveFontPath();

        $formatString = (string) ($overrides['format'] ?? $defaults['format'] ?? ImageFormat::Png->value);
        $format = ImageFormat::tryFrom($formatString) ?? ImageFormat::Png;

        $jpegQuality = (int) ($overrides['jpeg_quality'] ?? $defaults['jpeg_quality'] ?? 85);

        return new CaptchaOptions(
            width: (int) ($overrides['width'] ?? $defaults['width'] ?? 220),
            height: (int) ($overrides['height'] ?? $defaults['height'] ?? 60),
            length: (int) ($overrides['length'] ?? $defaults['length'] ?? 5),
            fontSize: (int) ($overrides['font_size'] ?? $defaults['font_size'] ?? 26),
            mode: $mode,
            charset: is_string($charset) ? $charset : null,
            caseSensitive: (bool) ($overrides['case_sensitive'] ?? $defaults['case_sensitive'] ?? false),
            noise: $noiseOptions,
            lightBackground: Color::fromRgbArray((array) ($overrides['light_background'] ?? data_get($colors, 'light.background', [255, 255, 255]))),
            lightText: Color::fromRgbArray((array) ($overrides['light_text'] ?? data_get($colors, 'light.text', [30, 30, 30]))),
            darkBackground: Color::fromRgbArray((array) ($overrides['dark_background'] ?? data_get($colors, 'dark.background', [24, 24, 27]))),
            darkText: Color::fromRgbArray((array) ($overrides['dark_text'] ?? data_get($colors, 'dark.text', [245, 245, 245]))),
            fontPath: $fontPath,
            format: $format,
            jpegQuality: $jpegQuality,
            ttlSeconds: (int) ($overrides['ttl'] ?? $store['ttl'] ?? 300),
            maxAttempts: (int) ($overrides['max_attempts'] ?? $store['max_attempts'] ?? 5),
        );
    }

    private function generateAnswer(CaptchaOptions $options): string
    {
        $charset = match ($options->mode) {
            CharacterMode::Numeric => '0123456789',
            CharacterMode::Alphabetic => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            CharacterMode::Alphanumeric => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
            CharacterMode::Custom => (string) $options->charset,
        };

        $chars = mb_str_split($charset);
        $maxIndex = count($chars) - 1;

        $answer = '';
        for ($i = 0; $i < $options->length; $i++) {
            $answer .= $chars[random_int(0, $maxIndex)];
        }

        return $answer;
    }
}
