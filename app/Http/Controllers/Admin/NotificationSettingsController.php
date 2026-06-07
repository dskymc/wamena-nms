<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNotificationSettingsRequest;
use App\Models\NmsSetting;
use App\Models\WaMessage;
use App\Services\Alerts\AlertNotifier;
use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    public function edit(NotificationSettingsService $settings): View
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        $waMessages = WaMessage::query()->orderByDesc('id')->limit(20)->get();

        return view('admin.notification-settings.edit', [
            'settings' => $settings->formValues(),
            'waMessages' => $waMessages,
        ]);
    }

    public function update(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        NmsSetting::set('telegram_enabled', $request->boolean('telegram_enabled'));
        NmsSetting::set('telegram_chat_id', trim((string) $request->input('telegram_chat_id', '')));

        if ($request->filled('telegram_bot_token')) {
            NmsSetting::setEncrypted('telegram_bot_token', $request->string('telegram_bot_token')->toString());
        }

        NmsSetting::set('email_enabled', $request->boolean('email_enabled'));
        NmsSetting::set('email_recipients', trim((string) $request->input('email_recipients', '')));
        NmsSetting::set('email_from_address', trim((string) $request->input('email_from_address', '')));
        NmsSetting::set('email_from_name', trim((string) $request->input('email_from_name', '')));
        NmsSetting::set('email_smtp_host', trim((string) $request->input('email_smtp_host', '')));
        NmsSetting::set('email_smtp_port', (string) ($request->input('email_smtp_port') ?: 587));
        NmsSetting::set('email_smtp_username', trim((string) $request->input('email_smtp_username', '')));
        NmsSetting::set('email_smtp_encryption', $request->input('email_smtp_encryption', 'tls'));

        if ($request->filled('email_smtp_password')) {
            NmsSetting::setEncrypted('email_smtp_password', $request->string('email_smtp_password')->toString());
        }

        NmsSetting::set('fonnte_enabled', $request->boolean('fonnte_enabled'));
        NmsSetting::set('fonnte_api_url', trim((string) ($request->input('fonnte_api_url') ?: 'https://api.fonnte.com/send')));
        NmsSetting::set('fonnte_country_code', trim((string) ($request->input('fonnte_country_code') ?: '62')));
        NmsSetting::set('fonnte_noc_numbers', trim((string) $request->input('fonnte_noc_numbers', '')));

        if ($request->filled('fonnte_token')) {
            NmsSetting::setEncrypted('fonnte_token', $request->string('fonnte_token')->toString());
        }

        return back()->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }

    public function testTelegram(AlertNotifier $notifier): RedirectResponse
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        $result = $notifier->sendTestTelegram('[WAMENA NMS] Pesan uji Telegram — '.now()->format('d/m/Y H:i'));

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Pesan uji Telegram terkirim.' : ('Telegram gagal: '.($result['result'] ?? ''))
        );
    }

    public function testEmail(AlertNotifier $notifier): RedirectResponse
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        $result = $notifier->sendTestEmail('[WAMENA NMS] Pesan uji Email — '.now()->format('d/m/Y H:i'));

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Pesan uji Email terkirim.' : ('Email gagal: '.($result['error'] ?? ''))
        );
    }

    public function testWhatsApp(AlertNotifier $notifier): RedirectResponse
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        $result = $notifier->sendTestWhatsApp('[WAMENA NMS] Pesan uji WhatsApp — '.now()->format('d/m/Y H:i'));

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success'] ? 'Pesan uji WhatsApp terkirim.' : ('WhatsApp gagal: '.($result['error'] ?? $result['response'] ?? ''))
        );
    }
}
