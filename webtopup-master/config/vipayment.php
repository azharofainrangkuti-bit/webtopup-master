<?php

return [
    'api_id' => env('VIPAYMENT_API_ID'),
    'api_key' => env('VIPAYMENT_API_KEY'),
    'base_url' => env('VIPAYMENT_BASE_URL', 'https://vip-reseller.co.id'),
    'webhook_ip' => env('VIPAYMENT_WEBHOOK_IP', '178.248.73.218'),

    // Route every outbound request through one fixed-IP proxy/NAT so only
    // that single IP ever needs to be whitelisted in the ViPayment dashboard,
    // instead of re-whitelisting a new IP whenever the app server changes.
    // Format: http://user:pass@proxy-host:port
    'http_proxy' => env('VIPAYMENT_HTTP_PROXY'),
];
