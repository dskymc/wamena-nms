<?php

namespace App\Enums;

enum AlertScopeType: string
{
    case Global = 'global';
    case Location = 'location';
    case Device = 'device';

    public function label(): string
    {
        return match ($this) {
            self::Global => 'Semua Perangkat',
            self::Location => 'Per Lokasi',
            self::Device => 'Per Perangkat',
        };
    }
}
