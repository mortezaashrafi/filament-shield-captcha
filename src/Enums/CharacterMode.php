<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Enums;

enum CharacterMode: string
{
    case Numeric = 'numeric';
    case Alphabetic = 'alphabetic';
    case Alphanumeric = 'alphanumeric';
    case Custom = 'custom';
}
