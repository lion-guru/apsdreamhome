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
            'password' => '',  // XAMPP MySQL root has no password by default
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
            'database' => __DIR__ . '/../database/database.sqlite',
            'prefix' => '',
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => __DIR__ . '/../database/migrations',
    ],

    'seeds' => [
        'path' => __DIR__ . '/../database/seeds',
    ],

    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'password' => getenv('REDIS_PASSWORD'),
        'port' => getenv('REDIS_PORT') ?: 6379,
        'database' => 0,
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => __DIR__ . '/../database/migrations',
    ],

    'seeds' => [
        'path' => __DIR__ . '/../database/seeds',
    ],
];
