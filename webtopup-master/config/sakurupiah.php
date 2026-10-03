<?php

return [
    'api_id' => env('SAKURUPIAH_API_ID'),
    'api_key' => env('SAKURUPIAH_API_KEY'),
    'is_production' => env('SAKURUPIAH_IS_PRODUCTION', true),
    'merchant_fee' => env('SAKURUPIAH_MERCHANT_FEE', 1),
    'base_url' => env('SAKURUPIAH_BASE_URL', 'https://sakurupiah.id'),
];
