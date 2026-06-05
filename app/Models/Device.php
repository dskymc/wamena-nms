<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Enums\DeviceVendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'location_id',
        'snmp_profile_id',
        'created_by',
        'name',
        'management_ip',
        'hostname',
        'vendor',
        'model',
        'serial_number',
        'notes',
        'is_monitored',
        'poll_interval_sec',
        'last_seen_at',
        'last_poll_error',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'vendor' => DeviceVendor::class,
            'status' => DeviceStatus::class,
            'is_monitored' => 'boolean',
            'poll_interval_sec' => 'integer',
            'last_seen_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function snmpProfile(): BelongsTo
    {
        return $this->belongsTo(SnmpProfile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDueForPoll(): bool
    {
        if (! $this->is_monitored || ! $this->snmp_profile_id) {
            return false;
        }

        if (! $this->vendor->supportsPolling()) {
            return false;
        }

        if ($this->last_seen_at === null) {
            return true;
        }

        return $this->last_seen_at->copy()->addSeconds($this->poll_interval_sec)->lte(now());
    }

    public function scopePollable($query)
    {
        return $query
            ->where('is_monitored', true)
            ->whereNotNull('snmp_profile_id')
            ->where('vendor', DeviceVendor::Mikrotik->value);
    }
}
