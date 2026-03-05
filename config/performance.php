<?php
/**
 * APS Dream Home - Performance Configuration
 * Advanced performance settings merged from ultimate optimization
 */

return [
    // Database Performance Settings
    'database' => [
        'slow_query_threshold' => 1.0, // seconds
        'query_cache_enabled' => true,
        'max_connections' => 100,
        'connection_timeout' => 30,
    ],
    
    // Cache Settings
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data'),
            ],
            'redis' => [
                'driver' => 'redis',
                'connection' => 'cache',
            ],
        ],
        'prefix' => 'apsdreamhome',
    ],
    
    // Session Settings
    'session' => [
        'driver' => 'file',
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => storage_path('framework/sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'apsdreamhome_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    // Performance Monitoring
    'monitoring' => [
        'enabled' => true,
        'log_slow_queries' => true,
        'track_memory_usage' => true,
        'track_execution_time' => true,
        'alert_thresholds' => [
            'memory_usage' => 128, // MB
            'execution_time' => 5.0, // seconds
            'slow_query_time' => 1.0, // seconds
        ],
    ],
    
    // Asset Optimization
    'assets' => [
        'minify_css' => true,
        'minify_js' => true,
        'combine_files' => true,
        'cache_busting' => true,
        'lazy_load_images' => true,
        'webp_support' => true,
    ],
    
    // API Rate Limiting
    'api' => [
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'burst_limit' => 10,
        ],
        'caching' => [
            'enabled' => true,
            'default_ttl' => 300, // seconds
        ],
    ],
    
    // Security Performance
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'sql_injection_protection' => true,
        'input_validation' => true,
        'rate_limiting' => true,
    ],
];
