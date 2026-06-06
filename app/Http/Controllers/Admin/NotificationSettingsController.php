<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NmsSetting;
use App\Models\WaMessage;
use App\Services\Alerts\AlertNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    public function edit(): View
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        $settings = [
            'telegram_enabled' => NmsSetting::getBool('telegram_enabled', config('nms.alerts.telegram.enabled')),
            'email_enabled' => NmsSetting::getBool('email_enabled', config('nms.alerts.email.enabled')),
            'fonnte_enabled' => NmsSetting::getBool('fonnte_enabled', config('fonnte.enabled')),
        ];

        $waMessages = WaMessage::query()->orderByDesc('id')->limit(20)->get();

        return view('admin.notification-settings.edit', compact('settings', 'waMessages'));
    }

    public function update(): RedirectResponse
    {
        abort_unless(auth()->user()->can('notification_settings.update'), 403);

        NmsSetting::set('telegram_enabled', request()->boolean('telegram_enabled'));
        NmsSetting::set('email_enabled', request()->boolean('email_enabled'));
        NmsSetting::set('fonnte_enabled', request()->boolean('fonnte_enabled'));

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
