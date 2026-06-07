<?php

namespace App\Services\Notifications;

use App\Models\NmsSetting;

class NotificationSettingsService
{
    public function telegramEnabled(): bool
    {
        return NmsSetting::getBool('telegram_enabled', false);
    }

    public function telegramBotToken(): ?string
    {
        return NmsSetting::getEncrypted('telegram_bot_token');
    }

    public function telegramChatId(): ?string
    {
        $value = NmsSetting::get('telegram_chat_id');

        return $value !== null && $value !== '' ? $value : null;
    }

    public function telegramConfigured(): bool
    {
        return $this->telegramBotToken() !== null
            && $this->telegramBotToken() !== ''
            && $this->telegramChatId() !== null;
    }

    public function emailEnabled(): bool
    {
        return NmsSetting::getBool('email_enabled', false);
    }

    /**
     * @return list<string>
     */
    public function emailRecipients(): array
    {
        return NmsSetting::getList('email_recipients');
    }

    public function emailFromAddress(): ?string
    {
        $value = NmsSetting::get('email_from_address');

        return $value !== null && $value !== '' ? $value : null;
    }

    public function emailFromName(): string
    {
        return NmsSetting::get('email_from_name') ?: config('app.name', 'WAMENA NMS');
    }

    public function emailSmtpHost(): ?string
    {
        $value = NmsSetting::get('email_smtp_host');

        return $value !== null && $value !== '' ? $value : null;
    }

    public function emailSmtpPort(): int
    {
        return (int) (NmsSetting::get('email_smtp_port') ?: 587);
    }

    public function emailSmtpUsername(): ?string
    {
        $value = NmsSetting::get('email_smtp_username');

        return $value !== null && $value !== '' ? $value : null;
    }

    public function emailSmtpPassword(): ?string
    {
        return NmsSetting::getEncrypted('email_smtp_password');
    }

    public function emailSmtpEncryption(): ?string
    {
        $value = NmsSetting::get('email_smtp_encryption');

        if ($value === null || $value === '' || $value === 'none') {
            return null;
        }

        return $value;
    }

    public function emailConfigured(): bool
    {
        return $this->emailSmtpHost() !== null
            && $this->emailRecipients() !== [];
    }

    public function fonnteEnabled(): bool
    {
        return NmsSetting::getBool('fonnte_enabled', false);
    }

    public function fonnteToken(): ?string
    {
        return NmsSetting::getEncrypted('fonnte_token');
    }

    public function fonnteApiUrl(): string
    {
        return NmsSetting::get('fonnte_api_url') ?: 'https://api.fonnte.com/send';
    }

    public function fonnteCountryCode(): string
    {
        return NmsSetting::get('fonnte_country_code') ?: '62';
    }

    /**
     * @return list<string>
     */
    public function fonnteNumbers(): array
    {
        return NmsSetting::getList('fonnte_noc_numbers');
    }

    public function fonnteConfigured(): bool
    {
        return $this->fonnteToken() !== null && $this->fonnteToken() !== '';
    }

    public function applyMailConfig(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.from.address' => $this->emailFromAddress() ?: config('mail.from.address'),
            'mail.from.name' => $this->emailFromName(),
            'mail.mailers.smtp.host' => $this->emailSmtpHost(),
            'mail.mailers.smtp.port' => $this->emailSmtpPort(),
            'mail.mailers.smtp.username' => $this->emailSmtpUsername(),
            'mail.mailers.smtp.password' => $this->emailSmtpPassword(),
            'mail.mailers.smtp.encryption' => $this->emailSmtpEncryption(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function formValues(): array
    {
        return [
            'telegram_enabled' => $this->telegramEnabled(),
            'telegram_chat_id' => $this->telegramChatId() ?? '',
            'email_enabled' => $this->emailEnabled(),
            'email_recipients' => implode("\n", $this->emailRecipients()),
            'email_from_address' => $this->emailFromAddress() ?? '',
            'email_from_name' => $this->emailFromName(),
            'email_smtp_host' => $this->emailSmtpHost() ?? '',
            'email_smtp_port' => $this->emailSmtpPort(),
            'email_smtp_username' => $this->emailSmtpUsername() ?? '',
            'email_smtp_encryption' => $this->emailSmtpEncryption() ?? 'tls',
            'fonnte_enabled' => $this->fonnteEnabled(),
            'fonnte_api_url' => $this->fonnteApiUrl(),
            'fonnte_country_code' => $this->fonnteCountryCode(),
            'fonnte_noc_numbers' => implode("\n", $this->fonnteNumbers()),
            'has_telegram_bot_token' => $this->telegramBotToken() !== null && $this->telegramBotToken() !== '',
            'has_fonnte_token' => $this->fonnteConfigured(),
            'has_email_smtp_password' => $this->emailSmtpPassword() !== null && $this->emailSmtpPassword() !== '',
        ];
    }
}
