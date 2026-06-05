<?php

namespace App\Enums;

enum DeviceVendor: string
{
    case Mikrotik = 'mikrotik';
    case Ruijie = 'ruijie';
    case Ubiquiti = 'ubiquiti';
    case Other = 'other';

    public function label(): string
    {
        return config("nms.vendors.{$this->value}.label", ucfirst($this->value));
    }

    /**
     * @return array<string, string>
     */
    public function pollOids(): array
    {
        $oids = config('nms.poll_oids.common', []);

        $vendorOids = config("nms.poll_oids.{$this->value}", []);

        return array_merge($oids, $vendorOids);
    }

    public function supportsPolling(): bool
    {
        return $this === self::Mikrotik;
    }
}
