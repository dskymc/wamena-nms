<?php

return [

    'branding' => [
        'name' => 'WAMENA NMS',
        'full_name' => 'Wide Area Monitoring & Enterprise Network Analytics',
        'organization' => 'Diskominfosatik Provinsi Papua Pegunungan',
        'logo' => 'images/logo-papua-pegunungan.png',
    ],

    'vendors' => [
        'mikrotik' => [
            'label' => 'MikroTik',
            'enterprise_oid' => '1.3.6.1.4.1.14988',
        ],
        'ruijie' => [
            'label' => 'Ruijie',
            'enterprise_oid' => '1.3.6.1.4.1.4881',
        ],
        'ubiquiti' => [
            'label' => 'Ubiquiti',
            'enterprise_oid' => '1.3.6.1.4.1.41112',
        ],
        'other' => [
            'label' => 'Lainnya',
            'enterprise_oid' => null,
        ],
    ],

    'snmp_auth_protocols' => [
        'MD5' => 'md5',
        'SHA' => 'sha1',
        'SHA224' => 'sha224',
        'SHA256' => 'sha256',
        'SHA384' => 'sha384',
        'SHA512' => 'sha512',
    ],

    'snmp_priv_protocols' => [
        'DES' => 'des',
        'AES128' => 'aes128',
        'AES192' => 'aes192',
        'AES256' => 'aes256',
    ],

    'sys_descr_oid' => '1.3.6.1.2.1.1.1.0',

    'poll_oids' => [
        'common' => [
            'sysUpTime' => '1.3.6.1.2.1.1.3.0',
            'sysName' => '1.3.6.1.2.1.1.5.0',
        ],
        'mikrotik' => [
            'identity' => '1.3.6.1.4.1.14988.1.1.3.0',
        ],
        'ruijie' => [],
        'ubiquiti' => [],
    ],

    'poll_batch_limit' => 50,

    'metrics' => [
        'retention_days' => 7,
        'max_interfaces' => 8,
    ],

    'metric_oids' => [
        'mikrotik' => [
            'cpu_load' => '1.3.6.1.4.1.14988.1.1.1.1.1.0',
            'memory_total' => '1.3.6.1.4.1.14988.1.1.1.1.2.0',
            'memory_used' => '1.3.6.1.4.1.14988.1.1.1.1.3.0',
        ],
        'ruijie' => [
            'cpu_load' => '1.3.6.1.2.1.25.3.3.1.2',
        ],
        'ubiquiti' => [
            'cpu_load' => '1.3.6.1.2.1.25.3.3.1.2',
        ],
    ],

    'interface_oids' => [
        'if_descr' => '1.3.6.1.2.1.2.2.1.2',
        'if_oper_status' => '1.3.6.1.2.1.2.2.1.8',
        'if_hc_in_octets' => '1.3.6.1.2.1.31.1.1.1.10',
        'if_hc_out_octets' => '1.3.6.1.2.1.31.1.1.1.11',
        'if_in_octets' => '1.3.6.1.2.1.2.2.1.10',
        'if_out_octets' => '1.3.6.1.2.1.2.2.1.16',
    ],

    'memory_storage_types' => [
        'hrStorageRam' => '1.3.6.1.2.1.25.2.1.2',
    ],

    'topology' => [
        'discovery_interval_minutes' => 30,
        'batch_limit' => 50,
        'stale_after_minutes' => 120,
    ],

    'topology_oids' => [
        'lldp' => [
            'loc_port_id' => '1.0.8802.1.1.2.1.3.7.1.3',
            'rem_local_port' => '1.0.8802.1.1.2.1.4.1.1',
            'rem_chassis_id' => '1.0.8802.1.1.2.1.4.1.5',
            'rem_port_id' => '1.0.8802.1.1.2.1.4.1.7',
            'rem_sys_name' => '1.0.8802.1.1.2.1.4.1.9',
            'rem_man_addr' => '1.0.8802.1.1.2.1.4.1.12',
        ],
        'cdp' => [
            'cache_device_id' => '1.3.6.1.4.1.9.9.23.1.2.1.1.6',
            'cache_device_port' => '1.3.6.1.4.1.9.9.23.1.2.1.1.7',
            'cache_platform' => '1.3.6.1.4.1.9.9.23.1.2.1.1.8',
        ],
        'vendors' => [
            'mikrotik' => ['lldp'],
            'ruijie' => ['lldp', 'cdp'],
            'ubiquiti' => ['lldp'],
        ],
    ],

    'alerts' => [
        // Semua pengaturan alert dikelola via menu Admin → Notifikasi (tabel nms_settings).
    ],

    'traps' => [
        'bind_ip' => '0.0.0.0',
        'port' => 1162,
        'auto_alert_link_events' => true,
        'alert_cooldown_minutes' => 15,
        'retention_days' => 30,
        'standard_trap_oids' => [
            'coldStart' => '1.3.6.1.6.3.1.1.5.1',
            'warmStart' => '1.3.6.1.6.3.1.1.5.2',
            'linkDown' => '1.3.6.1.6.3.1.1.5.3',
            'linkUp' => '1.3.6.1.6.3.1.1.5.4',
            'authenticationFailure' => '1.3.6.1.6.3.1.1.5.5',
        ],
        'if_index_oid_prefix' => '1.3.6.1.2.1.2.2.1.1',
    ],

];
