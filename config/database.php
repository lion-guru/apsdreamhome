<?php
/**
 * Database Configuration
 * Complete database settings for APS Dream Home
 */

return [
    'default' => getenv('DB_CONNECTION') ?: 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'database' => getenv('DB_NAME') ?: 'apsdreamhome',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'port' => getenv('DB_PORT') ?: 3306,
            'socket' => getenv('DB_SOCKET') ?: null,
            'options' => extension_loaded('pdo') ? [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ] : [],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => APP_ROOT . '/database/migrations',
    ],

    'seeds' => [
        'path' => APP_ROOT . '/database/seeds',
    ],

    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'password' => getenv('REDIS_PASSWORD'),
        'port' => getenv('REDIS_PORT') ?: 6379,
        'database' => 0,
    ],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => APP_ROOT . '/database/migrations',
    ],

    'seeds' => [
        'path' => APP_ROOT . '/database/seeds',
    ],

    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'password' => getenv('REDIS_PASSWORD'),
        'port' => getenv('REDIS_PORT') ?: 6379,
        'database' => 0,
    ],
];
