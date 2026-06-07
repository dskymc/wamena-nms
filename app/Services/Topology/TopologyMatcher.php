<?php

namespace App\Services\Topology;

use App\Models\Device;
use Illuminate\Support\Collection;

class TopologyMatcher
{
    /**
     * @var Collection<int, Device>|null
     */
    protected ?Collection $devices = null;

    public function match(TopologyNeighbor $neighbor): ?Device
    {
        $devices = $this->devices();

        if ($neighbor->remoteMgmtIp !== null) {
            $byIp = $devices->first(fn (Device $d) => $d->management_ip === $neighbor->remoteMgmtIp);

            if ($byIp !== null) {
                return $byIp;
            }
        }

        if ($neighbor->remoteSysName !== null && $neighbor->remoteSysName !== '') {
            $sysName = strtolower(trim($neighbor->remoteSysName));

            $byName = $devices->first(function (Device $d) use ($sysName) {
                $candidates = array_filter([
                    strtolower($d->hostname ?? ''),
                    strtolower($d->name ?? ''),
                ]);

                return in_array($sysName, $candidates, true);
            });

            if ($byName !== null) {
                return $byName;
            }
        }

        if ($neighbor->remoteChassisId !== null && $neighbor->remoteChassisId !== '') {
            $chassis = strtolower($neighbor->remoteChassisId);

            return $devices->first(function (Device $d) use ($chassis) {
                return $this->normalizeMac($d->serial_number) === $chassis
                    || strtolower($d->management_ip) === $chassis;
            });
        }

        return null;
    }

    /**
     * @return Collection<int, Device>
     */
    protected function devices(): Collection
    {
        if ($this->devices === null) {
            $this->devices = Device::query()
                ->where('is_monitored', true)
                ->get();
        }

        return $this->devices;
    }

    protected function normalizeMac(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $hex = preg_replace('/[^a-fA-F0-9]/', '', $value);

        if ($hex === null || strlen($hex) !== 12) {
            return strtolower(trim($value));
        }

        return implode(':', str_split(strtolower($hex), 2));
    }
}
