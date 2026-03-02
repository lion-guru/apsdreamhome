<?php
/**
 * APS Dream Home - Cache Configuration
 */

return [
    'driver' => 'file',
    'prefix' => 'apsdreamhome_',
    'duration' => 3600,
    'path' => 'C:/xampp/htdocs/apsdreamhome/storage/cache',
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'db' => 0
    ],
    'routes' => [
        'duration' => 1800, // 30 minutes
        'prefix' => 'route_'
    ],
    'api' => [
        'duration' => 300, // 5 minutes
        'prefix' => 'api_'
    ],
    'pages' => [
        'duration' => 3600, // 1 hour
        'prefix' => 'page_'
    ],
    'database' => [
        'duration' => 1800, // 30 minutes
        'prefix' => 'db_'
    ]
];
