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
}
