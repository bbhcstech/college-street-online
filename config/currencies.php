<?php

return [
    'countries' => [
        'IN' => ['currency' => 'INR', 'symbol' => '₹', 'rate' => (float) env('RATE_INR', 1)],
        'BD' => ['currency' => 'BDT', 'symbol' => '৳', 'rate' => (float) env('RATE_BDT', 1.40)],
        'GB' => ['currency' => 'GBP', 'symbol' => '£', 'rate' => (float) env('RATE_GBP', 0.0088)],
        'US' => ['currency' => 'USD', 'symbol' => '$', 'rate' => (float) env('RATE_USD', 0.012)],
    ],
];
