<?php

namespace App\Services\Topology;

use App\Enums\DeviceStatus;
use App\Models\Device;
use App\Models\TopologyLink;

class TopologyGraphService
{
    /**
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>}
     */
    public function buildGraph(?int $locationId = null, ?string $vendor = null, bool $registeredOnly = false): array
    {
        $links = TopologyLink::query()
            ->with(['sourceDevice.location', 'targetDevice.location'])
            ->whereHas('sourceDevice', function ($query) use ($locationId, $vendor) {
                if ($locationId) {
                    $query->where('location_id', $locationId);
                }

                if ($vendor) {
                    $query->where('vendor', $vendor);
                }
            })
            ->get();

        if ($registeredOnly) {
            $links = $links->filter(fn (TopologyLink $link) => $link->target_device_id !== null);
        }

        $nodes = [];
        $edges = [];
        $edgePairs = [];

        foreach ($links as $link) {
            $source = $link->sourceDevice;

            if ($source === null) {
                continue;
            }

            $fromId = $this->deviceNodeId($source->id);
            $nodes[$fromId] = $this->deviceNode($source);

            $toId = $link->target_device_id !== null
                ? $this->deviceNodeId($link->target_device_id)
                : $this->unknownNodeId($link);

            if ($link->target_device_id !== null && $link->targetDevice !== null) {
                $nodes[$toId] = $this->deviceNode($link->targetDevice);
            } else {
                $nodes[$toId] = $this->unknownNode($link);
            }

            $pairKey = $this->pairKey($fromId, $toId);
            $reverseKey = $this->pairKey($toId, $fromId);
            $label = $this->edgeLabel($link);

            if (isset($edgePairs[$reverseKey])) {
                $existingIndex = $edgePairs[$reverseKey];
                $edges[$existingIndex]['label'] = $this->mergeEdgeLabels($edges[$existingIndex]['label'], $label);
                $edges[$existingIndex]['title'] .= "\n".$this->edgeTitle($link);

                continue;
            }

            $edgePairs[$pairKey] = count($edges);
            $edges[] = [
                'id' => $link->id,
                'from' => $fromId,
                'to' => $toId,
                'label' => $label,
                'title' => $this->edgeTitle($link),
                'arrows' => 'to',
                'protocol' => $link->protocol->value,
            ];
        }

        $this->appendIsolatedDevices($nodes, $locationId, $vendor);

        return [
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodes
     */
    protected function appendIsolatedDevices(array &$nodes, ?int $locationId, ?string $vendor): void
    {
        $query = Device::query()
            ->where('is_monitored', true)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($vendor, fn ($q) => $q->where('vendor', $vendor));

        foreach ($query->get() as $device) {
            $nodeId = $this->deviceNodeId($device->id);

            if (! isset($nodes[$nodeId])) {
                $nodes[$nodeId] = $this->deviceNode($device);
            }
        }
    }

    protected function deviceNodeId(int $deviceId): string
    {
        return 'device:'.$deviceId;
    }

    protected function unknownNodeId(TopologyLink $link): string
    {
        $key = $link->remote_chassis_id
            ?? $link->remote_sys_name
            ?? ('link-'.$link->id);

        return 'unknown:'.md5(strtolower((string) $key));
    }

    /**
     * @return array<string, mixed>
     */
    protected function deviceNode(Device $device): array
    {
        return [
            'id' => $this->deviceNodeId($device->id),
            'label' => $device->name,
            'group' => $this->statusGroup($device->status),
            'device_id' => $device->id,
            'title' => implode("\n", array_filter([
                $device->management_ip,
                $device->vendor->label(),
                $device->location?->name,
            ])),
            'url' => route('admin.devices.show', $device),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function unknownNode(TopologyLink $link): array
    {
        $label = $link->remote_sys_name ?: 'Neighbor tidak dikenal';

        return [
            'id' => $this->unknownNodeId($link),
            'label' => $label,
            'group' => 'unknown',
            'device_id' => null,
            'title' => implode("\n", array_filter([
                $link->remote_chassis_id,
                $link->remote_port_label,
                $link->remote_mgmt_ip,
            ])),
            'url' => null,
        ];
    }

    protected function statusGroup(DeviceStatus $status): string
    {
        return match ($status) {
            DeviceStatus::Up => 'up',
            DeviceStatus::Down => 'down',
            DeviceStatus::Unknown => 'unknown_status',
        };
    }

    protected function edgeLabel(TopologyLink $link): string
    {
        $local = $link->source_port_label ?: $link->source_if_index;
        $remote = $link->remote_port_label ?: '?';

        return trim("{$local} → {$remote}");
    }

    protected function edgeTitle(TopologyLink $link): string
    {
        return implode("\n", array_filter([
            'Protokol: '.$link->protocol->label(),
            'Port lokal: '.($link->source_port_label ?: $link->source_if_index),
            'Port remote: '.($link->remote_port_label ?: '—'),
            'Terakhir: '.$link->last_seen_at?->format('d/m/Y H:i'),
        ]));
    }

    protected function pairKey(string $from, string $to): string
    {
        return $from.'|'.$to;
    }

    protected function mergeEdgeLabels(string $existing, string $incoming): string
    {
        if ($existing === $incoming) {
            return $existing;
        }

        return $existing.' / '.$incoming;
    }
}
