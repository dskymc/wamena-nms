<?php

namespace App\Services\Alerts;

use App\Enums\NotificationChannel;
use App\Jobs\SendWhatsAppMessage;
use App\Mail\AlertNotificationMail;
use App\Models\AlertEvent;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\WaMessage;
use App\Services\Fonnte\FonnteClient;
use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AlertNotifier
{
    public function __construct(
        protected AlertMessageBuilder $messageBuilder,
        protected FonnteClient $fonnteClient,
        protected NotificationSettingsService $settings,
    ) {}

    public function notify(AlertEvent $event, AlertRule $rule, Device $device, bool $isRecovery = false): void
    {
        if (! $this->shouldNotify($event, $rule)) {
            return;
        }

        $body = $this->messageBuilder->build($event, $device, $isRecovery);
        $log = $event->notification_log ?? [];

        foreach ($rule->channelEnums() as $channel) {
            $log[$channel->value] = match ($channel) {
                NotificationChannel::Telegram => $this->sendTelegram($body),
                NotificationChannel::Email => $this->sendEmail($event, $device, $body),
                NotificationChannel::Whatsapp => $this->sendWhatsApp($event, $body),
            };
        }

        $event->update([
            'last_notified_at' => now(),
            'notification_log' => $log,
        ]);
    }

    protected function shouldNotify(AlertEvent $event, AlertRule $rule): bool
    {
        if ($event->last_notified_at === null) {
            return true;
        }

        return $event->last_notified_at->copy()->addMinutes($rule->cooldown_minutes)->lte(now());
    }

    protected function telegramEnabled(): bool
    {
        return $this->settings->telegramEnabled() && $this->settings->telegramConfigured();
    }

    protected function emailEnabled(): bool
    {
        return $this->settings->emailEnabled() && $this->settings->emailConfigured();
    }

    protected function sendTelegram(string $body): string
    {
        if (! $this->telegramEnabled()) {
            return 'skipped';
        }

        $token = $this->settings->telegramBotToken();
        $chatId = $this->settings->telegramChatId();

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $body,
            ]);

            return $response->successful() ? 'sent' : 'failed: '.$response->body();
        } catch (\Throwable $e) {
            return 'failed: '.$e->getMessage();
        }
    }

    protected function sendEmail(AlertEvent $event, Device $device, string $body): string
    {
        if (! $this->emailEnabled()) {
            return 'skipped';
        }

        $recipients = $this->settings->emailRecipients();
        $subject = $this->messageBuilder->buildEmailSubject($event, $device);

        try {
            $this->settings->applyMailConfig();

            foreach ($recipients as $email) {
                Mail::to($email)->send(new AlertNotificationMail($subject, $body));
            }

            return 'sent';
        } catch (\Throwable $e) {
            return 'failed: '.$e->getMessage();
        }
    }

    protected function sendWhatsApp(AlertEvent $event, string $body): string
    {
        if (! $this->fonnteClient->isEnabled()) {
            return 'skipped';
        }

        $numbers = $this->settings->fonnteNumbers();

        if ($numbers === []) {
            return 'skipped: no numbers';
        }

        $results = [];

        foreach ($numbers as $number) {
            $waMessage = WaMessage::create([
                'alert_event_id' => $event->id,
                'target' => $number,
                'body' => $body,
                'status' => 'queued',
            ]);

            SendWhatsAppMessage::dispatchSync($waMessage->id);
            $waMessage->refresh();
            $results[] = $waMessage->status;
        }

        return implode(',', $results);
    }

    public function sendTestWhatsApp(string $message): array
    {
        $numbers = $this->settings->fonnteNumbers();

        if ($numbers === []) {
            return ['success' => false, 'error' => 'Tidak ada nomor NOC dikonfigurasi.'];
        }

        $target = $numbers[0];
        $waMessage = WaMessage::create([
            'target' => $target,
            'body' => $message,
            'status' => 'queued',
        ]);

        SendWhatsAppMessage::dispatchSync($waMessage->id);
        $waMessage->refresh();

        return [
            'success' => $waMessage->status === 'sent',
            'status' => $waMessage->status,
            'response' => $waMessage->fonnte_response,
        ];
    }

    public function sendTestTelegram(string $message): array
    {
        $result = $this->sendTelegram($message);

        return ['success' => $result === 'sent', 'result' => $result];
    }

    /**
     * @return array{success: bool, error?: string}
     */
    public function sendTestEmail(string $message): array
    {
        $recipients = $this->settings->emailRecipients();

        if ($recipients === []) {
            return ['success' => false, 'error' => 'Tidak ada penerima email dikonfigurasi.'];
        }

        try {
            $this->settings->applyMailConfig();

            foreach ($recipients as $email) {
                Mail::to($email)->send(new AlertNotificationMail('[WAMENA NMS] Uji Email', $message));
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
