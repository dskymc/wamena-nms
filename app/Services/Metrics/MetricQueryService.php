<?php

namespace App\Services\Metrics;

use App\Enums\MetricType;
use App\Models\Device;
use App\Models\MetricSample;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MetricQueryService
{
    public function chartData(Device $device, string $range = '24h'): array
    {
        $since = $this->rangeStart($range);

        $cpu = $this->series($device, MetricType::Cpu, $since);
        $memory = $this->series($device, MetricType::MemoryPercent, $since);
        $interfaces = $this->interfaceOptions($device, $since);
        $traffic = $this->trafficSeries($device, $since, $interfaces->keys()->first());

        return [
            'range' => $range,
            'cpu' => $cpu,
            'memory' => $memory,
            'interfaces' => $interfaces->all(),
            'traffic' => $traffic,
        ];
    }

    protected function rangeStart(string $range): Carbon
    {
        return match ($range) {
            '1h' => now()->subHour(),
            '7d' => now()->subDays(7),
            default => now()->subDay(),
        };
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function series(Device $device, MetricType $metric, Carbon $since): array
    {
        $samples = MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', $metric)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'value']);

        return [
            'labels' => $samples->map(fn ($s) => $s->recorded_at->format('d/m H:i'))->all(),
            'values' => $samples->map(fn ($s) => (float) $s->value)->all(),
        ];
    }

    protected function interfaceOptions(Device $device, Carbon $since): Collection
    {
        $options = $this->distinctInterfaceSources($device, $since, MetricType::TrafficInBps);

        if ($options->isNotEmpty()) {
            return $options;
        }

        return $this->distinctInterfaceSources($device, $since, MetricType::IfInOctets);
    }

    protected function distinctInterfaceSources(Device $device, Carbon $since, MetricType $metric): Collection
    {
        return MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', $metric)
            ->where('recorded_at', '>=', $since)
            ->whereNotNull('source')
            ->select('source', 'source_label')
            ->distinct()
            ->orderBy('source_label')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->source => $row->source_label ?? $row->source]);
    }

    /**
     * @return array{labels: list<string>, in: list<float>, out: list<float>}
     */
    protected function trafficSeries(Device $device, Carbon $since, ?string $source): array
    {
        if ($source === null) {
            return ['labels' => [], 'in' => [], 'out' => []];
        }

        $inSamples = MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', MetricType::TrafficInBps)
            ->where('source', $source)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'value']);

        $outSamples = MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', MetricType::TrafficOutBps)
            ->where('source', $source)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'value']);

        $labels = $inSamples->map(fn ($s) => $s->recorded_at->format('d/m H:i'))->all();

        return [
            'labels' => $labels,
            'in' => $inSamples->map(fn ($s) => (float) $s->value)->all(),
            'out' => $outSamples->map(fn ($s) => (float) $s->value)->all(),
        ];
    }

    public function trafficForInterface(Device $device, string $source, string $range = '24h'): array
    {
        $since = $this->rangeStart($range);

        return $this->trafficSeries($device, $since, $source);
    }
}
