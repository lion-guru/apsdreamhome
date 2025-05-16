<?php
return [
    'name' => 'APS Dream Homes',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost',
    'timezone' => 'Asia/Kolkata',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => env('APP_KEY', 'base64:' . base64_encode(random_bytes(32))),
    'cipher' => 'AES-256-CBC',
    
    'providers' => [
        // List of service providers
    ],
    
    'aliases' => [
        // List of class aliases
    ],
];
