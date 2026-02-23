<?php

/**
 * Production Deployment Configuration
 * Environment-specific settings for deployment
 */

return [
    // Project paths
    'project_root' => dirname(__DIR__),
    'backup_path' => dirname(__DIR__) . '/backups',
    'log_path' => dirname(__DIR__) . '/logs',

    // Application settings
    'app_name' => 'APS Dream Home',
    'app_url' => getenv('APP_URL') ?: 'https://apsdreamhome.com',
    'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com',

    // Environment settings
    'environments' => [
        'staging' => [
            'debug' => true,
            'log_level' => 'info',
            'cache_driver' => 'file',
            'session_driver' => 'file',
        ],
        'production' => [
            'debug' => false,
            'log_level' => 'warning',
            'cache_driver' => 'redis',
            'session_driver' => 'redis',
        ]
    ],

    // Database configuration
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_DATABASE') ?: 'apsdreamhome',
        'username' => getenv('DB_USERNAME') ?: 'apsdreamhome_user',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    // Redis configuration (for production caching)
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => getenv('REDIS_DB') ?: 0,
    ],

    // Email configuration
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@apsdreamhome.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home',
    ],

    // File storage configuration
    'storage' => [
        'disk' => getenv('FILESYSTEM_DISK') ?: 'local',
        'local' => [
            'root' => dirname(__DIR__) . '/storage/app',
        ],
        'public' => [
            'root' => dirname(__DIR__) . '/storage/app/public',
            'url' => getenv('APP_URL') . '/storage',
        ],
    ],

    // Security settings
    'security' => [
        'app_key' => getenv('APP_KEY') ?: 'base64:YOUR_APP_KEY_HERE',
        'cipher' => 'AES-256-CBC',
        'key_length' => 32,
    ],

    // Performance settings
    'performance' => [
        'opcache_enabled' => true,
        'opcache_preload' => dirname(__DIR__) . '/vendor/autoload.php',
        'query_cache_enabled' => true,
        'response_cache_ttl' => 3600, // 1 hour
    ],

    // Monitoring settings
    'monitoring' => [
        'enabled' => true,
        'health_check_interval' => 300, // 5 minutes
        'log_retention_days' => 30,
        'alert_email' => getenv('ALERT_EMAIL') ?: 'alerts@apsdreamhome.com',
        'metrics_retention_days' => 90,
    ],

    // Deployment settings
    'deployment' => [
        'run_seeders' => false, // Set to true for initial deployment
        'maintenance_mode' => true,
        'backup_before_deploy' => true,
        'rollback_on_failure' => true,
        'max_execution_time' => 300, // 5 minutes
        'memory_limit' => '256M',
    ],

    // CDN and external services
    'cdn' => [
        'enabled' => false,
        'provider' => 'cloudflare', // cloudflare, aws_cloudfront, etc.
        'url' => getenv('CDN_URL'),
    ],

    // Third-party integrations
    'integrations' => [
        'google_analytics' => [
            'enabled' => false,
            'tracking_id' => getenv('GA_TRACKING_ID'),
        ],
        'facebook_pixel' => [
            'enabled' => false,
            'pixel_id' => getenv('FB_PIXEL_ID'),
        ],
        'recaptcha' => [
            'enabled' => true,
            'site_key' => getenv('RECAPTCHA_SITE_KEY'),
            'secret_key' => getenv('RECAPTCHA_SECRET_KEY'),
        ],
    ],
    'maintenance' => [
        'contact_email' => 'support@apsdreamhome.com',
        'emergency_contact' => '+91-XXXXXXXXXX',
        'documentation_url' => 'https://docs.apsdreamhome.com',
        'status_page_url' => 'https://status.apsdreamhome.com',
    ],
];
