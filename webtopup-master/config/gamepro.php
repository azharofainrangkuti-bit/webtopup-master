<?php

return [
    'api_url' => env('VIPAYMENT_BASE_URL', 'https://vip-reseller.co.id'),
    'merchant_id' => env('VIPAYMENT_API_ID', null),
    'secret_key' => env('VIPAYMENT_API_KEY', null),
];
