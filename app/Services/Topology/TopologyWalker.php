<?php

namespace App\Services\Topology;

use App\Enums\TopologyProtocol;
use App\Models\Device;
use App\Models\DeviceInterface;
use App\Services\Snmp\SnmpClient;
use FreeDSx\Snmp\Exception\ConnectionException;
use FreeDSx\Snmp\Exception\SnmpRequestException;
use FreeDSx\Snmp\SnmpClient as FreeDsxSnmpClient;
use Illuminate\Support\Facades\Log;

class TopologyWalker
{
    public function __construct(
        protected SnmpClient $snmpClient,
    ) {}

    /**
     * @return array{neighbors: list<TopologyNeighbor>, error: ?string}
     */
    public function discover(Device $device): array
    {
        if ($device->snmpProfile === null) {
            return ['neighbors' => [], 'error' => 'Profil SNMP tidak ditemukan.'];
        }

        try {
            $client = $this->snmpClient->makeClient($device);
            $this->syncInterfaces($device, $client);

            $protocols = config('nms.topology_oids.vendors.'.$device->vendor->value, ['lldp']);
            $neighbors = [];

            foreach ($protocols as $protocolName) {
                $protocol = TopologyProtocol::tryFrom($protocolName);

                if ($protocol === null) {
                    continue;
                }

                $found = match ($protocol) {
                    TopologyProtocol::Lldp => $this->walkLldp($client, $device),
                    TopologyProtocol::Cdp => $this->walkCdp($client, $device),
                };

                $neighbors = array_merge($neighbors, $found);
            }

            return ['neighbors' => $neighbors, 'error' => null];
        } catch (ConnectionException $e) {
            return ['neighbors' => [], 'error' => 'Koneksi gagal: '.$e->getMessage()];
        } catch (SnmpRequestException $e) {
            return ['neighbors' => [], 'error' => 'Permintaan SNMP gagal: '.$e->getMessage()];
        } catch (\Throwable $e) {
            Log::warning('Topology discovery failed', [
                'device_id' => $device->id,
                'message' => $e->getMessage(),
            ]);

            return ['neighbors' => [], 'error' => 'Error: '.$e->getMessage()];
        }
    }

    protected function syncInterfaces(Device $device, FreeDsxSnmpClient $client): void
    {
        $oids = config('nms.interface_oids');
        $now = now();

        try {
            $descriptions = $this->walkIndexedValues($client, $oids['if_descr']);
            $operStatuses = $this->walkIndexedValues($client, $oids['if_oper_status']);

            foreach ($descriptions as $ifIndex => $name) {
                DeviceInterface::updateOrCreate(
                    [
                        'device_id' => $device->id,
                        'if_index' => (string) $ifIndex,
                    ],
                    [
                        'name' => trim((string) $name),
                        'oper_status' => (int) ($operStatuses[$ifIndex] ?? 0),
                        'last_seen_at' => $now,
                    ],
                );
            }
        } catch (\Throwable $e) {
            Log::debug('Interface sync during topology skipped', [
                'device_id' => $device->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return list<TopologyNeighbor>
     */
    protected function walkLldp(FreeDsxSnmpClient $client, Device $device): array
    {
        $oids = config('nms.topology_oids.lldp');
        $locPorts = $this->walkIndexedValues($client, $oids['loc_port_id']);
        $remLocalPorts = $this->walkIndexedValues($client, $oids['rem_local_port']);
        $chassisIds = $this->walkIndexedValues($client, $oids['rem_chassis_id']);
        $portIds = $this->walkIndexedValues($client, $oids['rem_port_id']);
        $sysNames = $this->walkIndexedValues($client, $oids['rem_sys_name']);
        $mgmtAddrs = $this->walkIndexedValues($client, $oids['rem_man_addr']);

        $neighbors = [];

        foreach ($remLocalPorts as $index => $localPortNum) {
            $localPortIndex = $this->extractLocalPortIndex($index, (string) $localPortNum);
            $localPortLabel = $locPorts[$localPortIndex] ?? $locPorts[(string) $localPortNum] ?? null;

            $neighbors[] = new TopologyNeighbor(
                localPortIndex: $localPortIndex,
                localPortLabel: $localPortLabel !== null ? trim((string) $localPortLabel) : null,
                remoteSysName: isset($sysNames[$index]) ? trim((string) $sysNames[$index]) : null,
                remoteChassisId: isset($chassisIds[$index]) ? $this->normalizeChassisId($chassisIds[$index]) : null,
                remotePortLabel: isset($portIds[$index]) ? trim((string) $portIds[$index]) : null,
                remoteMgmtIp: isset($mgmtAddrs[$index]) ? $this->parseMgmtAddress($mgmtAddrs[$index]) : null,
                protocol: TopologyProtocol::Lldp,
            );
        }

        return $neighbors;
    }

    /**
     * @return list<TopologyNeighbor>
     */
    protected function walkCdp(FreeDsxSnmpClient $client, Device $device): array
    {
        $oids = config('nms.topology_oids.cdp');
        $deviceIds = $this->walkIndexedValues($client, $oids['cache_device_id']);
        $devicePorts = $this->walkIndexedValues($client, $oids['cache_device_port']);

        $neighbors = [];

        foreach ($deviceIds as $index => $deviceId) {
            $parts = explode('.', (string) $index);
            $localIfIndex = $parts[0] ?? $index;

            $neighbors[] = new TopologyNeighbor(
                localPortIndex: (string) $localIfIndex,
                localPortLabel: $device->interfaces()->where('if_index', (string) $localIfIndex)->value('name'),
                remoteSysName: trim((string) $deviceId) ?: null,
                remoteChassisId: trim((string) $deviceId) ?: null,
                remotePortLabel: isset($devicePorts[$index]) ? trim((string) $devicePorts[$index]) : null,
                remoteMgmtIp: null,
                protocol: TopologyProtocol::Cdp,
            );
        }

        return $neighbors;
    }

    /**
     * @return array<string, mixed>
     */
    protected function walkIndexedValues(FreeDsxSnmpClient $client, string $oid): array
    {
        $values = [];

        foreach ($client->walk($oid) as $oidObj) {
            $oidString = (string) $oidObj->getOid();
            $suffix = $this->indexSuffix($oidString, $oid);

            if ($suffix === null) {
                continue;
            }

            $values[$suffix] = $oidObj->getValue();
        }

        return $values;
    }

    protected function indexSuffix(string $fullOid, string $baseOid): ?string
    {
        $base = rtrim($baseOid, '.');
        $full = rtrim($fullOid, '.');

        if (! str_starts_with($full, $base.'.')) {
            return null;
        }

        return substr($full, strlen($base) + 1);
    }

    protected function extractLocalPortIndex(string $remIndex, string $localPortNum): string
    {
        $parts = explode('.', $remIndex);

        if (count($parts) >= 2) {
            return $parts[1];
        }

        return $localPortNum;
    }

    protected function normalizeChassisId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && str_contains($value, ':')) {
            return strtolower(str_replace([' ', '-'], '', $value));
        }

        if (is_string($value)) {
            $hex = bin2hex($value);

            if (strlen($hex) === 12) {
                return implode(':', str_split($hex, 2));
            }

            return trim($value) !== '' ? trim($value) : null;
        }

        return trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    protected function parseMgmtAddress(mixed $value): ?string
    {
        if (! is_string($value) || strlen($value) < 5) {
            return null;
        }

        // LLDP management address: subtype(1) + len(1) + addr bytes
        $subtype = ord($value[0]);

        if ($subtype === 1 && strlen($value) >= 5) {
            return inet_ntop(substr($value, 2, 4)) ?: null;
        }

        if ($subtype === 2 && strlen($value) >= 18) {
            return inet_ntop(substr($value, 2, 16)) ?: null;
        }

        return null;
    }
}
