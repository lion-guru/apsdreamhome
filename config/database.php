<?php
/**
 * Database Configuration
 * Simple database settings for APS Dream Home
 */

return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => (getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: '')),
        'database' => getenv('DB_NAME') ?: 'apsdreamhome',
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
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'timeout' => 30,
        ],
        'cache' => [
            'enabled' => true,
            'ttl' => 3600,
            'queries' => ['SELECT'],
        ],
        'migrations' => [
            'table' => 'migrations',
            'path' => APP_ROOT . '/database/migrations',
        ],
        'seeds' => [
            'path' => APP_ROOT . '/database/seeds',
        ],
    ]
];
