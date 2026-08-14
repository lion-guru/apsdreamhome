<?php
/**
 * Cache Configuration for APS Dream Home
 *
 * Simple Redis-backed cache with file fallback.
 * Compatible with the App\Core\RedisCache class.
 *
 * Environment overrides:
 *   CACHE_DRIVER    = redis | file
 *   CACHE_FALLBACK  = file  | none
 *   CACHE_PREFIX    = apsdream_
 *   REDIS_HOST      = 127.0.0.1
 *   REDIS_PORT      = 6379
 *   REDIS_PASSWORD  = (null)
 *   REDIS_DB        = 0
 *   REDIS_TIMEOUT   = 2.0
 */

return [
    // 'redis' uses the phpredis extension when available.
    // 'file' skips Redis entirely and always uses the file cache.
    'driver' => getenv('CACHE_DRIVER') ?: 'redis',

    // What to fall back to when Redis is unavailable.
    // 'file' = transparent fallback to file cache. 'none' = throw / no cache.
    'fallback' => getenv('CACHE_FALLBACK') ?: 'file',

    // Prefix applied to every Redis key. Helps avoid collisions
    // when sharing a Redis instance with other apps.
    'prefix' => getenv('CACHE_PREFIX') ?: 'apsdream_',

    'redis' => [
        'host'     => getenv('REDIS_HOST')     ?: '127.0.0.1',
        'port'     => (int)   (getenv('REDIS_PORT')     ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => (int)   (getenv('REDIS_DB')        ?: 0),
        'timeout'  => (float) (getenv('REDIS_TIMEOUT')   ?: 2.0),
    ],

    // Default TTLs used by App\Services\CacheService helpers.
    'ttl' => [
        'admin_menu'        => 3600, // 1 hour
        'header_projects'   => 300,  // 5 minutes
        'unread_count'      => 30,   // 30 seconds
        'admin_dashboard'   => 120,  // 2 minutes
        'property_filters'  => 3600, // 1 hour
    ],
];?>