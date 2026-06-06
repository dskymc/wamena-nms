<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessage extends Model
{
    protected $fillable = [
        'alert_event_id',
        'target',
        'body',
        'status',
        'fonnte_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function alertEvent(): BelongsTo
    {
        return $this->belongsTo(AlertEvent::class);
    }
}
