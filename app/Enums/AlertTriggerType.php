<?php

namespace App\Enums;

enum AlertTriggerType: string
{
    case DeviceDown = 'device_down';
    case DeviceRecovery = 'device_recovery';
    case MetricThreshold = 'metric_threshold';

    public function label(): string
    {
        return match ($this) {
            self::DeviceDown => 'Perangkat Down',
            self::DeviceRecovery => 'Recovery (Up)',
            self::MetricThreshold => 'Threshold Metrik',
        };
    }
}
