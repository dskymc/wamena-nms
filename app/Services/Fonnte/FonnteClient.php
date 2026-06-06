<?php

namespace App\Services\Fonnte;

use Illuminate\Support\Facades\Http;

class FonnteClient
{
    public function __construct(
        protected PhoneNormalizer $normalizer,
    ) {}

    public function isEnabled(): bool
    {
        if (\App\Models\NmsSetting::get('fonnte_enabled') !== null) {
            return \App\Models\NmsSetting::getBool('fonnte_enabled');
        }

        return (bool) config('fonnte.enabled') && ! empty(config('fonnte.token'));
    }

    /**
     * @return array{success: bool, response: ?string, error: ?string}
     */
    public function send(string $target, string $message): array
    {
        $token = config('fonnte.token');

        if (! $this->isEnabled() || empty($token)) {
            return ['success' => false, 'response' => null, 'error' => 'Fonnte tidak aktif atau token kosong.'];
        }

        $normalized = $this->normalizer->normalize($target);

        if ($normalized === '') {
            return ['success' => false, 'response' => null, 'error' => 'Nomor target tidak valid.'];
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->post(config('fonnte.api_url'), [
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
