<?php

return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v20.0'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'token' => env('WHATSAPP_TOKEN'),
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '57'),
];

