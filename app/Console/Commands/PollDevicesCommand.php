<?php

namespace App\Console\Commands;

use App\Enums\DeviceStatus;
use App\Jobs\PollDeviceJob;
use App\Models\Device;
use Illuminate\Console\Command;

class PollDevicesCommand extends Command
{
    protected $signature = 'nms:poll-devices
                            {--limit= : Maksimum perangkat per eksekusi}';

    protected $description = 'Poll perangkat MikroTik/Ruijie/Ubiquiti yang jatuh tempo dan perbarui status + metrik';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?: config('nms.poll_batch_limit', 50));

        $devices = Device::pollable()
            ->with('snmpProfile')
            ->orderBy('name')
            ->get()
            ->filter(fn (Device $device) => $device->isDueForPoll())
            ->take($limit);

        if ($devices->isEmpty()) {
            $this->info('Tidak ada perangkat yang perlu di-poll.');

            return self::SUCCESS;
        }

        $polled = 0;
        $up = 0;
        $down = 0;

        foreach ($devices as $device) {
            PollDeviceJob::dispatchSync($device);
            $device->refresh();
            $polled++;

            if ($device->status === DeviceStatus::Up) {
                $up++;
            } elseif ($device->status === DeviceStatus::Down) {
                $down++;
            }
        }

        $this->info("Poll selesai: {$polled} perangkat ({$up} up, {$down} down).");

        return self::SUCCESS;
    }
}
