<?php

namespace App\Models;

use App\Enums\AlertOperator;
use App\Enums\AlertScopeType;
use App\Enums\AlertSeverity;
use App\Enums\AlertTriggerType;
use App\Enums\MetricType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    protected $fillable = [
        'name',
        'is_enabled',
        'trigger_type',
        'scope_type',
        'location_id',
        'device_id',
        'metric',
        'operator',
        'threshold',
        'consecutive_breaches',
        'severity',
        'cooldown_minutes',
        'channels',
        'notify_on_resolve',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'trigger_type' => AlertTriggerType::class,
            'scope_type' => AlertScopeType::class,
            'metric' => MetricType::class,
            'operator' => AlertOperator::class,
            'severity' => AlertSeverity::class,
            'threshold' => 'decimal:4',
            'consecutive_breaches' => 'integer',
            'cooldown_minutes' => 'integer',
            'channels' => 'array',
            'notify_on_resolve' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AlertEvent::class);
    }

    public function appliesTo(Device $device): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        return match ($this->scope_type) {
            AlertScopeType::Global => true,
            AlertScopeType::Location => $device->location_id === $this->location_id,
            AlertScopeType::Device => $device->id === $this->device_id,
        };
    }

    public function channelEnums(): array
    {
        return array_map(
            fn (string $c) => \App\Enums\NotificationChannel::from($c),
            $this->channels ?? [],
        );
    }
}
