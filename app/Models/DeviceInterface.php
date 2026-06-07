<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceInterface extends Model
{
    protected $fillable = [
        'device_id',
        'if_index',
        'name',
        'oper_status',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'oper_status' => 'integer',
            'last_seen_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
