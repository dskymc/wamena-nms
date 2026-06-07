<?php

namespace App\Models;

use App\Enums\TopologyProtocol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopologyLink extends Model
{
    protected $fillable = [
        'source_device_id',
        'source_if_index',
        'source_port_label',
        'target_device_id',
        'remote_sys_name',
        'remote_chassis_id',
        'remote_port_label',
        'remote_mgmt_ip',
        'protocol',
        'discovered_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'protocol' => TopologyProtocol::class,
            'discovered_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function sourceDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'source_device_id');
    }

    public function targetDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'target_device_id');
    }
}
