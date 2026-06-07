<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\Topology\TopologySyncService;
use Illuminate\Console\Command;

class DiscoverTopologyCommand extends Command
{
    protected $signature = 'nms:discover-topology
                            {--device= : ID perangkat tertentu}
                            {--limit= : Maksimum perangkat per eksekusi}';

    protected $description = 'Discovery topologi LLDP/CDP via SNMP walk dan sinkronkan topology_links';

    public function handle(TopologySyncService $syncService): int
    {
        $deviceId = $this->option('device');
        $limit = (int) ($this->option('limit') ?: config('nms.topology.batch_limit', 50));

        $query = Device::discoverable()
            ->with(['snmpProfile', 'location'])
            ->orderBy('name');

        if ($deviceId) {
            $query->whereKey($deviceId);
        }

        $devices = $query->limit($limit)->get();

        if ($devices->isEmpty()) {
            $this->info('Tidak ada perangkat yang eligible untuk discovery topologi.');

            return self::SUCCESS;
        }

        $discovered = 0;
        $links = 0;
        $errors = 0;

        foreach ($devices as $device) {
            $result = $syncService->syncDevice($device);
            $discovered++;

            if ($result['error'] !== null) {
                $errors++;
                $this->warn("{$device->name}: {$result['error']}");
            }

            $links += $result['links'];
        }

        $this->info("Discovery selesai: {$discovered} perangkat, {$links} link, {$errors} error.");

        return self::SUCCESS;
    }
}
