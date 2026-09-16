<?php
/**
 * Rate Limit Configuration
 *
 * Defines tiered rate limits and per-endpoint limits.
 * Used by RedisRateLimiter and RateLimitMiddleware.
 */

return [
    // Tier definitions - maps user role/plan to limits
    'tiers' => [
        'free'  => ['requests_per_minute' => 60,  'burst' => 10],
        'basic' => ['requests_per_minute' => 120, 'burst' => 20],
        'pro'   => ['requests_per_minute' => 300, 'burst' => 50],
        'admin' => ['requests_per_minute' => -1,  'burst' => -1], // unlimited
    ],

    // Default tier if user has no assigned tier
    'default_tier' => 'free',

    // Per-endpoint limits (overrides tier limits for specific routes)
    'endpoints' => [
        // Auth endpoints - strict limits
        'auth.login'        => ['limit' => 5,  'window' => 60,  'key_prefix' => 'auth:login'],
        'auth.register'     => ['limit' => 3,  'window' => 3600, 'key_prefix' => 'auth:register'],
        'auth.password'     => ['limit' => 5,  'window' => 300,  'key_prefix' => 'auth:password'],
        'auth.otp'          => ['limit' => 3,  'window' => 300,  'key_prefix' => 'auth:otp'],
        'auth.air_login'    => ['limit' => 5,  'window' => 300,  'key_prefix' => 'auth:air_login'],

        // API endpoints
        'api.default'       => ['limit' => 120, 'window' => 60,  'key_prefix' => 'api'],
        'api.search'        => ['limit' => 30,  'window' => 60,  'key_prefix' => 'api:search'],
        'api.properties'    => ['limit' => 60,  'window' => 60,  'key_prefix' => 'api:properties'],
        'api.bookings'      => ['limit' => 60,  'window' => 60,  'key_prefix' => 'api:bookings'],
        'api.payments'      => ['limit' => 30,  'window' => 60,  'key_prefix' => 'api:payments'],
        'api.profile'       => ['limit' => 60,  'window' => 60,  'key_prefix' => 'api:profile'],
        'api.notifications' => ['limit' => 60,  'window' => 60,  'key_prefix' => 'api:notifications'],

        // Admin endpoints (higher limits)
        'admin.default'     => ['limit' => 300, 'window' => 60,  'key_prefix' => 'admin'],
        'admin.export'      => ['limit' => 10,  'window' => 3600, 'key_prefix' => 'admin:export'],
        'admin.bulk'        => ['limit' => 20,  'window' => 3600, 'key_prefix' => 'admin:bulk'],

        // Web endpoints (page loads)
        'web.default'       => ['limit' => 600, 'window' => 60,  'key_prefix' => 'web'],
    ],

    // Role to tier mapping
    'role_tier_map' => [
        'customer'           => 'free',
        'associate'          => 'basic',
        'agent'              => 'basic',
        'employee'           => 'pro',
        'telecaller'         => 'pro',
        'manager'            => 'pro',
        'sales_director'     => 'pro',
        'marketing_director' => 'pro',
        'admin'              => 'admin',
        'super_admin'        => 'admin',
        'ceo'                => 'admin',
        'cfo'                => 'admin',
        'cto'                => 'admin',
        'coo'                => 'admin',
        'chro'               => 'admin',
        'cmo'                => 'admin',
    ],

    // Redis key prefix for all rate limit keys
    'redis_prefix' => 'apsdream:ratelimit:',

    // Whether to include rate limit headers in responses
    'include_headers' => true,

    // Header names
    'headers' => [
        'limit'     => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset'     => 'X-RateLimit-Reset',
        'retry'     => 'Retry-After',
    ],

    // Graceful degradation settings
    'fallback' => [
        'enabled' => true,
        'driver'  => 'file', // 'file' or 'database'
    ],
];