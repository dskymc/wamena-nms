<?php

namespace App\Services\Traps;

use App\Enums\SnmpTrapName;
use App\Models\Device;
use App\Models\DeviceInterface;
use FreeDSx\Snmp\Oid;
use FreeDSx\Snmp\OidList;
use FreeDSx\Snmp\Request\TrapV1Request;
use FreeDSx\Snmp\Request\TrapV2Request;

class TrapParser
{
    /**
     * @return array{trap_oid: ?string, trap_name: SnmpTrapName, varbinds: list<array{oid: string, value: string}>, if_index: ?string}
     */
    public function parse(mixed $trap): array
    {
        if ($trap instanceof TrapV2Request) {
            $trapOid = (string) $trap->getTrapOid()->getValue();
            $varbinds = $this->extractVarbinds($trap->getOids());

            return [
                'trap_oid' => $trapOid,
                'trap_name' => $this->resolveTrapName($trapOid, null),
                'varbinds' => $varbinds,
                'if_index' => $this->extractIfIndex($varbinds),
            ];
        }

        if ($trap instanceof TrapV1Request) {
            $genericType = $trap->getGenericType();
            $varbinds = $this->extractVarbinds($trap->getOids());

            return [
                'trap_oid' => $trap->getEnterprise(),
                'trap_name' => $this->resolveTrapName(null, $genericType),
                'varbinds' => $varbinds,
                'if_index' => $this->extractIfIndex($varbinds),
            ];
        }

        return [
            'trap_oid' => null,
            'trap_name' => SnmpTrapName::Unknown,
            'varbinds' => [],
            'if_index' => null,
        ];
    }

    public function buildSummary(SnmpTrapName $trapName, ?Device $device, ?string $ifIndex): string
    {
        $interfaceLabel = $this->resolveInterfaceLabel($device, $ifIndex);
        $deviceLabel = $device?->name ?? 'Perangkat tidak dikenal';

        return match ($trapName) {
            SnmpTrapName::LinkDown => "{$deviceLabel}: {$interfaceLabel} link down",
            SnmpTrapName::LinkUp => "{$deviceLabel}: {$interfaceLabel} link up",
            SnmpTrapName::ColdStart => "{$deviceLabel}: cold start",
            SnmpTrapName::WarmStart => "{$deviceLabel}: warm start",
            SnmpTrapName::AuthenticationFailure => "{$deviceLabel}: authentication failure",
            SnmpTrapName::Unknown => "{$deviceLabel}: SNMP trap diterima",
        };
    }

    protected function resolveTrapName(?string $trapOid, ?int $genericType): SnmpTrapName
    {
        if ($trapOid !== null) {
            $map = array_flip(config('nms.traps.standard_trap_oids', []));

            if (isset($map[$trapOid])) {
                return SnmpTrapName::from($map[$trapOid]);
            }
        }

        if ($genericType !== null) {
            return match ($genericType) {
                TrapV1Request::GENERIC_COLD_START => SnmpTrapName::ColdStart,
                TrapV1Request::GENERIC_WARM_START => SnmpTrapName::WarmStart,
                TrapV1Request::GENERIC_LINK_DOWN => SnmpTrapName::LinkDown,
                TrapV1Request::GENERIC_LINK_UP => SnmpTrapName::LinkUp,
                TrapV1Request::GENERIC_AUTH_FAILURE => SnmpTrapName::AuthenticationFailure,
                default => SnmpTrapName::Unknown,
            };
        }

        return SnmpTrapName::Unknown;
    }

    /**
     * @return list<array{oid: string, value: string}>
     */
    protected function extractVarbinds(OidList $oidList): array
    {
        $varbinds = [];

        foreach ($oidList->toArray() as $oid) {
            if (! $oid instanceof Oid) {
                continue;
            }

            $value = $oid->getValue();
            $varbinds[] = [
                'oid' => $oid->getOid(),
                'value' => $value !== null ? trim((string) $value->getValue()) : '',
            ];
        }

        return $varbinds;
    }

    /**
     * @param  list<array{oid: string, value: string}>  $varbinds
     */
    protected function extractIfIndex(array $varbinds): ?string
    {
        $prefix = rtrim((string) config('nms.traps.if_index_oid_prefix'), '.');

        foreach ($varbinds as $varbind) {
            $oid = $varbind['oid'];

            if ($oid === $prefix && $varbind['value'] !== '') {
                return $varbind['value'];
            }

            if (str_starts_with($oid, $prefix.'.')) {
                return substr($oid, strlen($prefix) + 1);
            }
        }

        return null;
    }

    protected function resolveInterfaceLabel(?Device $device, ?string $ifIndex): string
    {
        if ($ifIndex === null) {
            return 'interface tidak diketahui';
        }

        if ($device !== null) {
            $iface = DeviceInterface::query()
                ->where('device_id', $device->id)
                ->where('if_index', $ifIndex)
                ->value('name');

            if ($iface) {
                return $iface;
            }
        }

        return "ifIndex {$ifIndex}";
    }
}
