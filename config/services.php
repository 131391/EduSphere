<?php

return [

    'razorpay' => [
        'key'            => env('RAZORPAY_KEY_ID', ''),
        'secret'         => env('RAZORPAY_KEY_SECRET', ''),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        'currency'       => env('RAZORPAY_CURRENCY', 'INR'),
    ],

];
