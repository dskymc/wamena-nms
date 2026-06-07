<?php

namespace App\Services\Fonnte;

use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Support\Facades\Http;

class FonnteClient
{
    public function __construct(
        protected PhoneNormalizer $normalizer,
        protected NotificationSettingsService $settings,
    ) {}

    public function isEnabled(): bool
    {
        return $this->settings->fonnteEnabled() && $this->settings->fonnteConfigured();
    }

    /**
     * @return array{success: bool, response: ?string, error: ?string}
     */
    public function send(string $target, string $message): array
    {
        $token = $this->settings->fonnteToken();

        if (! $this->isEnabled() || empty($token)) {
            return ['success' => false, 'response' => null, 'error' => 'Fonnte tidak aktif atau token kosong.'];
        }

        $normalized = $this->normalizer->normalize($target, $this->settings->fonnteCountryCode());

        if ($normalized === '') {
            return ['success' => false, 'response' => null, 'error' => 'Nomor target tidak valid.'];
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->post($this->settings->fonnteApiUrl(), [
                    'target' => $normalized,
                    'message' => $message,
                ]);

            $body = $response->body();

            if ($response->successful()) {
                return ['success' => true, 'response' => $body, 'error' => null];
            }

            return ['success' => false, 'response' => $body, 'error' => 'HTTP '.$response->status()];
        } catch (\Throwable $e) {
            return ['success' => false, 'response' => null, 'error' => $e->getMessage()];
        }
    }
}
