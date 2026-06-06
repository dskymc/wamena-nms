<?php

namespace App\Jobs;

use App\Models\WaMessage;
use App\Services\Fonnte\FonnteClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $waMessageId,
    ) {}

    public function handle(FonnteClient $client): void
    {
        $waMessage = WaMessage::find($this->waMessageId);

        if ($waMessage === null) {
            return;
        }

        $result = $client->send($waMessage->target, $waMessage->body);

        $waMessage->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'fonnte_response' => $result['response'] ?? $result['error'],
            'sent_at' => now(),
        ]);
    }
}
