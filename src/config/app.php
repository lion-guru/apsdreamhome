<?php

// Application configuration
return [
    // Application settings
    'app' => [
        'name' => 'APS Dream Homes',
        'env' => 'development',
        'debug' => true,
        'url' => 'http://localhost/apsdreamhomefinal',
        'timezone' => 'UTC',
        'locale' => 'en',
    ],

    // Session configuration
    'session' => [
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'cookie_name' => 'aps_session',
        'cookie_httponly' => true,
        'secure' => false,
        'same_site' => 'lax',
    ],

    // Upload settings
    'upload' => [
        'max_size' => '10M',
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'path' => __DIR__ . '/../../uploads',
    ],

    // Email configuration
    'mail' => [
        'driver' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'from' => [
            'address' => 'noreply@apsdreamhomefinal.com',
            'name' => 'APS Dream Homes',
        ],
    ],
];