<?php

namespace App\Enums;

enum TopologyProtocol: string
{
    case Lldp = 'lldp';
    case Cdp = 'cdp';

    public function label(): string
    {
        return match ($this) {
            self::Lldp => 'LLDP',
            self::Cdp => 'CDP',
        };
    }
}
