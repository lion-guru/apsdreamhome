<?php
/**
 * Cache Configuration
 */

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'table' => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => null,
            'lock_connection' => null,
        ],
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_FILES_PATH', '/tmp/cache'),
        ],
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],
        'dynamodb' => [
            'driver' => 'dynamodb',
            'PLACEHOLDER_SECRET_VALUEPLACEHOLDER_SECRET_VALUEregion' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],
        'octane' => [
            'driver' => 'octane',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'aps_cache'),
];
