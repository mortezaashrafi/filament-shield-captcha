<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Verification;

/**
 * @immutable
 */
final readonly class VerificationResult
{
    public function __construct(
        public VerificationStatus $status,
        public int $attempts = 0,
        public int $maxAttempts = 0,
    ) {}

    public function passed(): bool
    {
        return $this->status === VerificationStatus::Passed;
    }
}
