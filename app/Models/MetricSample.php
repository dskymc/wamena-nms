<?php

namespace App\Models;

use App\Enums\MetricType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricSample extends Model
{
    protected $fillable = [
        'device_id',
        'metric',
        'source',
        'source_label',
        'value',
        'unit',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'metric' => MetricType::class,
            'value' => 'decimal:4',
            'recorded_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
