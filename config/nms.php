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

];
