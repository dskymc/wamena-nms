<?php

namespace App\Services\Traps;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use App\Models\Device;
use App\Models\SnmpProfile;
use App\Models\SnmpTrap;
use FreeDSx\Snmp\Message\EngineId;
use FreeDSx\Snmp\Module\SecurityModel\Usm\UsmUser;
use FreeDSx\Snmp\Trap\TrapContext;
use Illuminate\Support\Facades\Log;

class TrapProcessor
{
    public function __construct(
        protected TrapParser $parser,
        protected TrapAlertHandler $alertHandler,
    ) {}

    public function process(TrapContext $context): SnmpTrap
    {
        $trap = $context->getTrap();
        $parsed = $this->parser->parse($trap);
        $device = $this->matchDevice($context->getIpAddress());
        $summary = $this->parser->buildSummary(
            $parsed['trap_name'],
            $device,
            $parsed['if_index'],
        );

        $record = SnmpTrap::create([
            'source_ip' => $context->getIpAddress(),
            'device_id' => $device?->id,
            'snmp_version' => $this->formatVersion($context->getVersion()),
            'trap_oid' => $parsed['trap_oid'],
            'trap_name' => $parsed['trap_name'],
            'varbinds' => $parsed['varbinds'],
            'summary' => $summary,
            'received_at' => now(),
        ]);

        if ($device !== null && config('nms.traps.auto_alert_link_events', true)) {
            try {
                $this->alertHandler->handle($record, $device, $parsed['if_index']);
            } catch (\Throwable $e) {
                Log::warning('Trap alert handling failed', [
                    'trap_id' => $record->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $record;
    }

    protected function matchDevice(string $sourceIp): ?Device
    {
        return Device::query()
            ->where('management_ip', $sourceIp)
            ->first();
    }

    protected function formatVersion(int $version): string
    {
        return match ($version) {
            1 => '1',
            2 => '2c',
            3 => '3',
            default => (string) $version,
        };
    }
}
