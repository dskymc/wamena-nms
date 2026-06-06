<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use App\Enums\AlertState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertEvent extends Model
{
    protected $fillable = [
        'alert_rule_id',
        'device_id',
        'severity',
        'state',
        'message',
        'detail',
        'metric_value',
        'fired_at',
        'resolved_at',
        'last_notified_at',
        'notification_log',
    ];

    protected function casts(): array
    {
        return [
            'severity' => AlertSeverity::class,
            'state' => AlertState::class,
            'metric_value' => 'decimal:4',
            'fired_at' => 'datetime',
            'resolved_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'notification_log' => 'array',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function waMessages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('state', AlertState::Open);
    }
}
