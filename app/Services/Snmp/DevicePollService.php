<?php

namespace App\Services\Snmp;

use App\Enums\DeviceStatus;
use App\Models\Device;

class DevicePollService
{
    public function __construct(
        protected SnmpClient $snmpClient,
    ) {}

    public function pollAndUpdate(Device $device): PollResult
    {
        if (! $device->snmpProfile) {
            return PollResult::fail('Perangkat belum memiliki profil SNMP.');
        }

        if (! $device->is_monitored) {
            return PollResult::fail('Monitoring tidak aktif untuk perangkat ini.');
        }

        $result = $this->snmpClient->poll($device);
        $this->applyResult($device, $result);

        return $result;
    }

    protected function applyResult(Device $device, PollResult $result): void
    {
        if ($result->success) {
            $updates = [
                'status' => DeviceStatus::Up,
                'last_seen_at' => now(),
                'last_poll_error' => null,
            ];

            if (empty($device->hostname) && $result->sysName) {
                $updates['hostname'] = $result->sysName;
            }

            $device->update($updates);

            return;
        }

        $device->update([
            'status' => DeviceStatus::Down,
            'last_poll_error' => $result->error,
        ]);
    }
}
