<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Telegram = 'telegram';
    case Email = 'email';
    case Whatsapp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::Telegram => 'Telegram',
            self::Email => 'Email',
            self::Whatsapp => 'WhatsApp (Fonnte)',
        };
    }
}
