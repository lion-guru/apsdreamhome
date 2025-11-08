<?php

return [
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'apsdreamhome_test',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
];
