<?php

namespace App\Services\Traps;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use App\Models\Device;
use App\Models\SnmpProfile;
use FreeDSx\Snmp\Message\EngineId;
use FreeDSx\Snmp\Module\SecurityModel\Usm\UsmUser;

class TrapUsmResolver
{
    public function resolveUsmUser(EngineId $engineId, string $ipAddress, string $user): ?UsmUser
    {
        $device = Device::query()
            ->with('snmpProfile')
            ->where('management_ip', $ipAddress)
            ->first();

        if ($device?->snmpProfile === null) {
            return null;
        }

        $profile = $device->snmpProfile;

        if ($profile->version !== SnmpVersion::V3 || $profile->username !== $user) {
            return null;
        }

        return $this->buildUsmUser($profile);
    }

    public function buildUsmUser(SnmpProfile $profile): ?UsmUser
    {
        if ($profile->version !== SnmpVersion::V3 || $profile->username === null) {
            return null;
        }

        $level = $profile->security_level ?? SnmpSecurityLevel::NoAuthNoPriv;
        $authMech = config('nms.snmp_auth_protocols')[$profile->auth_protocol] ?? 'sha1';
        $privMech = config('nms.snmp_priv_protocols')[$profile->priv_protocol] ?? 'aes128';

        if ($level === SnmpSecurityLevel::AuthPriv) {
            return UsmUser::withPrivacy(
                $profile->username,
                $profile->auth_passphrase ?? '',
                $authMech,
                $profile->priv_passphrase ?? '',
                $privMech,
            );
        }

        if ($level === SnmpSecurityLevel::AuthNoPriv) {
            return UsmUser::withAuthentication(
                $profile->username,
                $profile->auth_passphrase ?? '',
                $authMech,
            );
        }

        return new UsmUser($profile->username, false, false);
    }
}
