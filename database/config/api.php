<?php
return [
    'version' => 'v1',
    'prefix' => 'api',
    'middleware' => ['api'],
    'throttle' => [
        'attempts' => env('API_RATE_LIMIT', 60),
        'expires' => env('API_RATE_LIMIT_EXPIRES', 1),
    ],
    'authentication' => [
        'jwt' => [
            'secret' => env('JWT_SECRET'),
            'ttl' => env('JWT_TTL', 60 * 24), // 24 hours
            'refresh_ttl' => env('JWT_REFRESH_TTL', 60 * 24 * 7), // 7 days
        ],
        'oauth' => [
            'google' => [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            ],
            'facebook' => [
                'client_id' => env('FACEBOOK_CLIENT_ID'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
                'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
            ],
        ],
    ],
    'cors' => [
        'allowed_origins' => explode(',', env('API_CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],
    'documentation' => [
        'enabled' => env('API_DOCUMENTATION_ENABLED', true),
        'path' => '/docs',
        'title' => 'APS Dream Home API',
        'description' => 'Real Estate Management System API',
        'version' => '1.0.0',
    ],
];