<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\Snmp\DevicePollService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PollDeviceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Device $device,
    ) {}

    public function handle(DevicePollService $pollService): void
    {
        $this->device->loadMissing('snmpProfile');

        $pollService->pollAndUpdate($this->device);
    }
}
