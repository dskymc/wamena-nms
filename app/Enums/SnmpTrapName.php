<?php

namespace App\Enums;

enum SnmpTrapName: string
{
    case LinkDown = 'linkDown';
    case LinkUp = 'linkUp';
    case ColdStart = 'coldStart';
    case WarmStart = 'warmStart';
    case AuthenticationFailure = 'authenticationFailure';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::LinkDown => 'Link Down',
            self::LinkUp => 'Link Up',
            self::ColdStart => 'Cold Start',
            self::WarmStart => 'Warm Start',
            self::AuthenticationFailure => 'Authentication Failure',
            self::Unknown => 'Unknown',
        };
    }
}
