<?php

namespace App\Services\Traps;

use FreeDSx\Snmp\Message\EngineId;
use FreeDSx\Snmp\Module\SecurityModel\Usm\UsmUser;
use FreeDSx\Snmp\Trap\TrapContext;
use FreeDSx\Snmp\Trap\TrapListenerInterface;
use Illuminate\Support\Facades\Log;

class NmsTrapListener implements TrapListenerInterface
{
    public function __construct(
        protected TrapProcessor $processor,
        protected TrapUsmResolver $usmResolver,
    ) {}

    public function accept(string $ip): bool
    {
        return true;
    }

    public function getUsmUser(EngineId $engineId, string $ipAddress, string $user): ?UsmUser
    {
        return $this->usmResolver->resolveUsmUser($engineId, $ipAddress, $user);
    }

    public function receive(TrapContext $context): void
    {
        try {
            $trap = $this->processor->process($context);

            Log::info('SNMP trap received', [
                'id' => $trap->id,
                'source_ip' => $trap->source_ip,
                'trap_name' => $trap->trap_name->value,
            ]);
        } catch (\Throwable $e) {
            Log::error('SNMP trap processing failed', [
                'source_ip' => $context->getIpAddress(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
