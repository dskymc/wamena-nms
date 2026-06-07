<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notification_settings.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'telegram_enabled' => ['nullable', 'boolean'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_chat_id' => ['nullable', 'string', 'max:64'],
            'email_enabled' => ['nullable', 'boolean'],
            'email_recipients' => ['nullable', 'string'],
            'email_from_address' => ['nullable', 'email', 'max:255'],
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'email_smtp_host' => ['nullable', 'string', 'max:255'],
            'email_smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'email_smtp_username' => ['nullable', 'string', 'max:255'],
            'email_smtp_password' => ['nullable', 'string', 'max:255'],
            'email_smtp_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'fonnte_enabled' => ['nullable', 'boolean'],
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_api_url' => ['nullable', 'url', 'max:255'],
            'fonnte_country_code' => ['nullable', 'string', 'max:4'],
            'fonnte_noc_numbers' => ['nullable', 'string'],
        ];
    }
}
