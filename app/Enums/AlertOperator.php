<?php

namespace App\Enums;

enum AlertOperator: string
{
    case GreaterThan = 'gt';
    case LessThan = 'lt';

    public function label(): string
    {
        return match ($this) {
            self::GreaterThan => '>',
            self::LessThan => '<',
        };
    }

    public function compare(float $value, float $threshold): bool
    {
        return match ($this) {
            self::GreaterThan => $value > $threshold,
            self::LessThan => $value < $threshold,
        };
    }
}
