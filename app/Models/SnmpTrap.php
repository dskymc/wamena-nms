<?php

namespace App\Models;

use App\Enums\SnmpTrapName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnmpTrap extends Model
{
    protected $fillable = [
        'source_ip',
        'device_id',
        'snmp_version',
        'trap_oid',
        'trap_name',
        'varbinds',
        'summary',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'trap_name' => SnmpTrapName::class,
            'varbinds' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
