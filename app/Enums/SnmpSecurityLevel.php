<?php

namespace App\Enums;

enum SnmpSecurityLevel: string
{
    case NoAuthNoPriv = 'noAuthNoPriv';
    case AuthNoPriv = 'authNoPriv';
    case AuthPriv = 'authPriv';

    public function label(): string
    {
        return match ($this) {
            self::NoAuthNoPriv => 'No Auth, No Priv',
            self::AuthNoPriv => 'Auth, No Priv',
            self::AuthPriv => 'Auth + Priv',
        };
    }
}
