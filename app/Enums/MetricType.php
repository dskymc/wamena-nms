<?php

namespace App\Enums;

enum MetricType: string
{
    case Cpu = 'cpu';
    case MemoryPercent = 'memory_percent';
    case TrafficInBps = 'traffic_in_bps';
    case TrafficOutBps = 'traffic_out_bps';
    case IfInOctets = 'if_in_octets';
    case IfOutOctets = 'if_out_octets';

    public function label(): string
    {
        return match ($this) {
            self::Cpu => 'CPU',
            self::MemoryPercent => 'Memori',
            self::TrafficInBps => 'Traffic In',
            self::TrafficOutBps => 'Traffic Out',
            self::IfInOctets => 'Counter In',
            self::IfOutOctets => 'Counter Out',
        };
    }

    public function unit(): string
    {
        return match ($this) {
            self::Cpu, self::MemoryPercent => 'percent',
            self::TrafficInBps, self::TrafficOutBps => 'bps',
            self::IfInOctets, self::IfOutOctets => 'bytes',
        };
    }

    public function isChartable(): bool
    {
        return in_array($this, [self::Cpu, self::MemoryPercent, self::TrafficInBps, self::TrafficOutBps], true);
    }
}
