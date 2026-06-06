<?php

namespace App\Services\Snmp;

use App\Enums\DeviceStatus;
use App\Models\Device;
use App\Services\Alerts\AlertEvaluator;
use App\Services\Metrics\MetricSampleWriter;

class DevicePollService
{
    public function __construct(
        protected SnmpClient $snmpClient,
        protected MetricSampleWriter $metricWriter,
        protected AlertEvaluator $alertEvaluator,
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
        $previousStatus = $device->status;

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
            $this->metricWriter->store($device, $result->metrics);
            $this->alertEvaluator->evaluateDevice($device->fresh(), $previousStatus);

            return;
        }

        $device->update([
            'status' => DeviceStatus::Down,
            'last_poll_error' => $result->error,
        ]);

        $this->alertEvaluator->evaluateDevice($device->fresh(), $previousStatus);
    }
}
