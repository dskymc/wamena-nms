<?php

namespace App\Services\Metrics;

use App\Enums\MetricType;
use App\Models\Device;
use App\Models\MetricSample;
use App\Models\SnmpProfile;
use FreeDSx\Snmp\SnmpClient as FreeDsxSnmpClient;

class MetricCollector
{
    /**
     * @return list<MetricReading>
     */
    public function collect(Device $device, FreeDsxSnmpClient $client): array
    {
        $readings = [];

        $readings = array_merge($readings, $this->collectCpu($device, $client));
        $readings = array_merge($readings, $this->collectMemory($device, $client));
        $readings = array_merge($readings, $this->collectInterfaces($device, $client));

        return $readings;
    }

    /**
     * @return list<MetricReading>
     */
    protected function collectCpu(Device $device, FreeDsxSnmpClient $client): array
    {
        $oids = $device->vendor->metricOids();
        $cpuOid = $oids['cpu_load'] ?? null;

        if ($cpuOid === null) {
            return [];
        }

        if ($device->vendor === \App\Enums\DeviceVendor::Mikrotik) {
            $value = $this->getScalar($client, $cpuOid);

            if ($value === null) {
                return [];
            }

            return [new MetricReading(MetricType::Cpu, $value)];
        }

        $values = $this->walkNumericValues($client, $cpuOid);

        if ($values === []) {
            return [];
        }

        return [new MetricReading(MetricType::Cpu, round(array_sum($values) / count($values), 2))];
    }

    /**
     * @return list<MetricReading>
     */
    protected function collectMemory(Device $device, FreeDsxSnmpClient $client): array
    {
        if ($device->vendor === \App\Enums\DeviceVendor::Mikrotik) {
            $oids = $device->vendor->metricOids();
            $total = $this->getScalar($client, $oids['memory_total'] ?? '');
            $used = $this->getScalar($client, $oids['memory_used'] ?? '');

            if ($total === null || $used === null || $total <= 0) {
                return $this->collectMemoryFromHostResources($client);
            }

            return [new MetricReading(MetricType::MemoryPercent, round(($used / $total) * 100, 2))];
        }

        return $this->collectMemoryFromHostResources($client);
    }

    /**
     * @return list<MetricReading>
     */
    protected function collectMemoryFromHostResources(FreeDsxSnmpClient $client): array
    {
        $usedWalk = $this->walkIndexedValues($client, '1.3.6.1.2.1.25.2.3.1.6');
        $sizeWalk = $this->walkIndexedValues($client, '1.3.6.1.2.1.25.2.3.1.5');
        $typeWalk = $this->walkIndexedValues($client, '1.3.6.1.2.1.25.2.3.1.2', asString: true);
        $ramType = config('nms.memory_storage_types.hrStorageRam');

        foreach ($typeWalk as $index => $type) {
            if ($this->normalizeOid((string) $type) !== $this->normalizeOid((string) $ramType)) {
                continue;
            }

            $size = (float) ($sizeWalk[$index] ?? 0);
            $used = (float) ($usedWalk[$index] ?? 0);

            if ($size <= 0) {
                continue;
            }

            return [new MetricReading(MetricType::MemoryPercent, round(($used / $size) * 100, 2))];
        }

        return [];
    }

    /**
     * @return list<MetricReading>
     */
    protected function collectInterfaces(Device $device, FreeDsxSnmpClient $client): array
    {
        $ifOids = config('nms.interface_oids');
        $descriptions = $this->walkIndexedValues($client, $ifOids['if_descr'], asString: true);
        $operStatus = $this->walkIndexedValues($client, $ifOids['if_oper_status']);

        $inCounters = $this->walkIndexedValues($client, $ifOids['if_hc_in_octets']);
        if ($inCounters === []) {
            $inCounters = $this->walkIndexedValues($client, $ifOids['if_in_octets']);
        }

        $outCounters = $this->walkIndexedValues($client, $ifOids['if_hc_out_octets']);
        if ($outCounters === []) {
            $outCounters = $this->walkIndexedValues($client, $ifOids['if_out_octets']);
        }

        $readings = [];
        $now = now();
        $maxInterfaces = (int) config('nms.metrics.max_interfaces', 8);

        $candidates = [];
        foreach ($descriptions as $index => $label) {
            if ($this->shouldSkipInterface($label, (int) ($operStatus[$index] ?? 0))) {
                continue;
            }

            $in = (float) ($inCounters[$index] ?? 0);
            $out = (float) ($outCounters[$index] ?? 0);
            $candidates[] = compact('index', 'label', 'in', 'out');
        }

        usort($candidates, fn ($a, $b) => ($b['in'] + $b['out']) <=> ($a['in'] + $a['out']));
        $candidates = array_slice($candidates, 0, $maxInterfaces);

        foreach ($candidates as $iface) {
            $index = (string) $iface['index'];
            $label = (string) $iface['label'];

            $readings[] = new MetricReading(MetricType::IfInOctets, $iface['in'], $index, $label);
            $readings[] = new MetricReading(MetricType::IfOutOctets, $iface['out'], $index, $label);

            $inBps = $this->calculateRate($device, MetricType::IfInOctets, $index, $iface['in'], $now);
            $outBps = $this->calculateRate($device, MetricType::IfOutOctets, $index, $iface['out'], $now);

            if ($inBps !== null) {
                $readings[] = new MetricReading(MetricType::TrafficInBps, $inBps, $index, $label);
            }

            if ($outBps !== null) {
                $readings[] = new MetricReading(MetricType::TrafficOutBps, $outBps, $index, $label);
            }
        }

        return $readings;
    }

    protected function shouldSkipInterface(string $label, int $operStatus): bool
    {
        $lower = strtolower($label);

        if (str_contains($lower, 'loopback') || str_contains($lower, 'null')) {
            return true;
        }

        return $operStatus !== 1 && $operStatus !== 0;
    }

    protected function calculateRate(
        Device $device,
        MetricType $counterMetric,
        string $source,
        float $currentCounter,
        \Illuminate\Support\Carbon $now,
    ): ?float {
        $previous = MetricSample::query()
            ->where('device_id', $device->id)
            ->where('metric', $counterMetric)
            ->where('source', $source)
            ->orderByDesc('recorded_at')
            ->first();

        if ($previous === null) {
            return null;
        }

        $seconds = $previous->recorded_at->diffInSeconds($now);

        if ($seconds <= 0) {
            return null;
        }

        $delta = $currentCounter - (float) $previous->value;

        if ($delta < 0) {
            $delta = $currentCounter;
        }

        return round(($delta * 8) / $seconds, 2);
    }

    protected function getScalar(FreeDsxSnmpClient $client, string $oid): ?float
    {
        if ($oid === '') {
            return null;
        }

        try {
            $response = $client->get($oid);
            $first = $response->first();

            if ($first === null) {
                return null;
            }

            return $this->toFloat((string) $first->getValue());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<float>
     */
    protected function walkNumericValues(FreeDsxSnmpClient $client, string $oid): array
    {
        return array_values(array_filter(
            $this->walkIndexedValues($client, $oid),
            fn ($value) => $value !== null,
        ));
    }

    /**
     * @return array<string, float|string>
     */
    protected function walkIndexedValues(FreeDsxSnmpClient $client, string $oid, bool $asString = false): array
    {
        try {
            $response = $client->walk($oid);
            $values = [];

            foreach ($response as $item) {
                $index = $this->extractIndex((string) $item->getOid(), $oid);

                if ($index === null) {
                    continue;
                }

                $raw = (string) $item->getValue();
                $values[$index] = $asString ? trim($raw) : $this->toFloat($raw);
            }

            return $values;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function extractIndex(string $fullOid, string $baseOid): ?string
    {
        if (! str_starts_with($fullOid, $baseOid)) {
            return null;
        }

        $suffix = substr($fullOid, strlen($baseOid));

        return ltrim($suffix, '.') ?: null;
    }

    protected function toFloat(string $value): float
    {
        $clean = preg_replace('/[^0-9.\-]/', '', $value) ?? '0';

        return (float) $clean;
    }

    protected function normalizeOid(string $oid): string
    {
        return ltrim(trim($oid), '.');
    }
}
