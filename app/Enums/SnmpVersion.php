<?php

namespace App\Enums;

enum SnmpVersion: string
{
    case V2c = '2c';
    case V3 = '3';

    public function label(): string
    {
        return match ($this) {
            self::V2c => 'SNMPv2c',
            self::V3 => 'SNMPv3',
        };
    }
}
