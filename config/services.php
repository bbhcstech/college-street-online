<?php

return [
    // Not used yet — College Street Online is manual-payment only per FR-7.
    // Wire up here when the payment gateway integration (SRS Section 10.2) is built.
    'payment_gateway' => [
        'provider' => env('PAYMENT_GATEWAY_PROVIDER', 'manual'),
        'key' => env('PAYMENT_GATEWAY_KEY'),
        'secret' => env('PAYMENT_GATEWAY_SECRET'),
    ],

];
