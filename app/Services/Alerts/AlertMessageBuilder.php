<?php

namespace App\Services\Alerts;

use App\Models\AlertEvent;
use App\Models\Device;

class AlertMessageBuilder
{
    public function build(AlertEvent $event, Device $device, bool $isRecovery = false): string
    {
        $device->loadMissing('location');
        $severity = strtoupper($event->severity->value);
        $brand = config('nms.branding.name');
        $title = $isRecovery ? 'RECOVERY — Perangkat UP' : $event->message;

        $lines = [
            "[{$brand}][{$severity}]",
            $title,
            'Nama: '.$device->name,
            'IP: '.$device->management_ip,
            'Lokasi: '.($device->location?->name ?? '—'),
            'Waktu: '.now()->format('d/m/Y H:i'),
        ];

        if ($event->detail) {
            $lines[] = 'Detail: '.$event->detail;
        }

        if ($event->metric_value !== null) {
            $lines[] = 'Nilai: '.$event->metric_value;
        }

        return implode("\n", $lines);
    }

    public function buildEmailSubject(AlertEvent $event, Device $device): string
    {
        return sprintf(
            '[%s][%s] %s — %s',
            config('nms.branding.name'),
            strtoupper($event->severity->value),
            $event->message,
            $device->name,
        );
    }
}
