<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Verification;

enum VerificationStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Expired = 'expired';
    case Locked = 'locked';
    case Missing = 'missing';
}
