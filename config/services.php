<?php

return [
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY', 'sk-0e90410f898440a6bf935faf0050922f'),
        'key' => env('STRIPE_PUBLISHABLE_KEY', 'sk-0e90410f898440a6bf935faf0050922f'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', 'sk-0e90410f898440a6bf935faf0050922f'),
    ],
];
