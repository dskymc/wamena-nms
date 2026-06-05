<?php

namespace App\Services\Snmp;

use App\Enums\SnmpSecurityLevel;
use App\Enums\SnmpVersion;
use App\Models\SnmpProfile;
use FreeDSx\Snmp\Exception\ConnectionException;
use FreeDSx\Snmp\Exception\SnmpRequestException;
use FreeDSx\Snmp\SnmpClient as FreeDsxSnmpClient;

class SnmpClient
{
    public function testConnection(SnmpProfile $profile, string $ip): SnmpTestResult
    {
        try {
            $client = new FreeDsxSnmpClient($this->buildOptions($profile, $ip));
            $oids = $client->get(config('nms.sys_descr_oid'));
            $first = $oids->first();

            if ($first === null) {
                return SnmpTestResult::fail('Tidak ada respons dari perangkat.');
            }

            return SnmpTestResult::ok((string) $first->getValue());
        } catch (ConnectionException $e) {
            return SnmpTestResult::fail('Koneksi gagal: '.$e->getMessage());
        } catch (SnmpRequestException $e) {
            return SnmpTestResult::fail('Permintaan SNMP gagal: '.$e->getMessage());
        } catch (\Throwable $e) {
            return SnmpTestResult::fail('Error: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOptions(SnmpProfile $profile, string $ip): array
    {
        $timeoutSec = max(1, (int) ceil($profile->timeout_ms / 1000));

        $options = [
            'host' => $ip,
            'port' => $profile->port,
            'udp_retry' => $profile->retries,
            'timeout_connect' => $timeoutSec,
            'timeout_read' => $timeoutSec,
        ];

        if ($profile->version === SnmpVersion::V2c) {
            $options['version'] = 2;
            $options['community'] = $profile->community ?? 'public';

            return $options;
        }

        $options['version'] = 3;
        $options['user'] = $profile->username;
        $options['context_name'] = $profile->context_name;

        $level = $profile->security_level ?? SnmpSecurityLevel::NoAuthNoPriv;

        $options['use_auth'] = in_array($level, [SnmpSecurityLevel::AuthNoPriv, SnmpSecurityLevel::AuthPriv], true);
        $options['use_priv'] = $level === SnmpSecurityLevel::AuthPriv;

        if ($options['use_auth']) {
            $options['auth_pwd'] = $profile->auth_passphrase;
            $options['auth_mech'] = config('nms.snmp_auth_protocols')[$profile->auth_protocol] ?? 'sha1';
        }

        if ($options['use_priv']) {
            $options['priv_pwd'] = $profile->priv_passphrase;
            $options['priv_mech'] = config('nms.snmp_priv_protocols')[$profile->priv_protocol] ?? 'aes128';
        }

        return $options;
    }
}
