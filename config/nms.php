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

    'alerts' => [
        'telegram' => [
            'enabled' => env('NMS_TELEGRAM_ENABLED', false),
            'bot_token' => env('NMS_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('NMS_TELEGRAM_CHAT_ID'),
        ],
        'email' => [
            'enabled' => env('NMS_ALERT_EMAIL_ENABLED', false),
            'recipients' => array_filter(array_map(
                'trim',
                explode(',', env('NMS_ALERT_EMAIL_RECIPIENTS', ''))
            )),
        ],
    ],

];
