<?php

namespace App\Services\Topology;

use App\Models\Device;
use App\Models\TopologyLink;
use Illuminate\Support\Facades\DB;

class TopologySyncService
{
    public function __construct(
        protected TopologyWalker $walker,
        protected TopologyMatcher $matcher,
    ) {}

    /**
     * @return array{links: int, error: ?string}
     */
    public function syncDevice(Device $device): array
    {
        $result = $this->walker->discover($device);

        if ($result['error'] !== null && $result['neighbors'] === []) {
            return ['links' => 0, 'error' => $result['error']];
        }

        $now = now();
        $seenKeys = [];

        DB::transaction(function () use ($device, $result, $now, &$seenKeys) {
            foreach ($result['neighbors'] as $neighbor) {
                $identity = $neighbor->identityKey();

                if ($identity === '') {
                    continue;
                }

                $chassisId = $neighbor->remoteChassisId ?? $identity;
                $target = $this->matcher->match($neighbor);

                $link = TopologyLink::updateOrCreate(
                    [
                        'source_device_id' => $device->id,
                        'source_if_index' => $neighbor->localPortIndex,
                        'remote_chassis_id' => $chassisId,
                        'protocol' => $neighbor->protocol,
                    ],
                    [
                        'source_port_label' => $neighbor->localPortLabel,
                        'target_device_id' => $target?->id,
                        'remote_sys_name' => $neighbor->remoteSysName,
                        'remote_port_label' => $neighbor->remotePortLabel,
                        'remote_mgmt_ip' => $neighbor->remoteMgmtIp,
                        'last_seen_at' => $now,
                    ],
                );

                if ($link->wasRecentlyCreated) {
                    $link->discovered_at = $now;
                    $link->save();
                }

                $seenKeys[] = $link->id;
            }

            $this->pruneStaleLinks($device, $now);
        });

        return ['links' => count($seenKeys), 'error' => $result['error']];
    }

    protected function pruneStaleLinks(Device $device, \DateTimeInterface $now): void
    {
        $staleMinutes = (int) config('nms.topology.stale_after_minutes', 120);
        $cutoff = \Carbon\Carbon::instance($now)->subMinutes($staleMinutes);

        TopologyLink::query()
            ->where('source_device_id', $device->id)
            ->where('last_seen_at', '<', $cutoff)
            ->delete();
    }
}
