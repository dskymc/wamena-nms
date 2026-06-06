<?php

return [

    'enabled' => env('FONNTE_ENABLED', false),

    'token' => env('FONNTE_TOKEN'),

    'api_url' => env('FONNTE_API_URL', 'https://api.fonnte.com/send'),

    'default_country_code' => env('FONNTE_COUNTRY_CODE', '62'),

    'noc_numbers' => array_filter(array_map(
        'trim',
        explode(',', env('NMS_FONNTE_NUMBERS', ''))
    )),

];
