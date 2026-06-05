<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case Unknown = 'unknown';
    case Up = 'up';
    case Down = 'down';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Unknown',
            self::Up => 'Up',
            self::Down => 'Down',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unknown => 'gray',
            self::Up => 'green',
            self::Down => 'red',
        };
    }
}
