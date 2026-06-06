<?php

namespace App\Services\Metrics;

use App\Models\Device;
use App\Models\MetricSample;
use Illuminate\Support\Carbon;

class MetricSampleWriter
{
    /**
     * @param  list<MetricReading>  $readings
     */
    public function store(Device $device, array $readings, ?Carbon $recordedAt = null): void
    {
        if ($readings === []) {
            return;
        }

        $recordedAt ??= now();

        $rows = array_map(function (MetricReading $reading) use ($device, $recordedAt) {
            return [
                'device_id' => $device->id,
                'metric' => $reading->metric->value,
                'source' => $reading->source,
                'source_label' => $reading->sourceLabel,
                'value' => $reading->value,
                'unit' => $reading->unit(),
                'recorded_at' => $recordedAt,
                'created_at' => $recordedAt,
                'updated_at' => $recordedAt,
            ];
        }, $readings);

        MetricSample::insert($rows);
    }
}
