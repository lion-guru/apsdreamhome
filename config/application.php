<?php
/**
 * Application Configuration
 * General application settings and constants
 */

// Base URL configuration
$config['app'] = [
    'name' => APP_NAME,
    'version' => APP_VERSION,
    'environment' => ENVIRONMENT,
    'debug' => ENVIRONMENT === 'development',
    'url' => getenv('APP_URL') ?: 'http://localhost/apsdreamhome',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',
    'locale' => getenv('APP_LOCALE') ?: 'en',
    'key' => getenv('APP_KEY') ?: 'base64:your-secret-key-here',
    'cipher' => 'AES-256-CBC',
];

// URL and Path helpers
if (!defined('BASE_URL')) define('BASE_URL', rtrim($config['app']['url'], '/') . '/');
if (!defined('ASSET_URL')) define('ASSET_URL', BASE_URL . 'public/assets/');

// File upload settings
$config['upload'] = [
    'max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    'upload_path' => APP_ROOT . '/uploads/',
    'temp_path' => APP_ROOT . '/temp/',
];

// Cache configuration
$config['cache'] = [
    'default' => getenv('CACHE_DRIVER') ?: 'file',
    'ttl' => 3600,
    'path' => APP_ROOT . '/storage/cache/',
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'password' => getenv('REDIS_PASSWORD') ?: null,
    ],
];

// Queue configuration
$config['queue'] = [
    'default' => getenv('QUEUE_CONNECTION') ?: 'sync',
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'password' => getenv('REDIS_PASSWORD') ?: null,
    ],
];

// Email configuration
$config['mail'] = [
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'port' => getenv('MAIL_PORT') ?: 587,
    'username' => getenv('MAIL_USERNAME') ?: 'apsdreamhomes44@gmail.com',
    'password' => getenv('MAIL_PASSWORD') ?: 'Aps@1601',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'apsdreamhomes44@gmail.com',
        'name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home',
    ],
];

// API configuration
$config['api'] = [
    'version' => 'v1',
    'rate_limit' => 1000, // requests per hour
    'throttle' => [
        'enabled' => true,
        'attempts' => 60,
        'decay' => 1, // minute
    ],
];

// Social media and external services
$config['services'] = [
    'google_analytics' => getenv('GOOGLE_ANALYTICS_ID') ?: null,
    'facebook_pixel' => getenv('FACEBOOK_PIXEL_ID') ?: null,
    'whatsapp' => [
        'enabled' => true,
        'number' => getenv('WHATSAPP_NUMBER') ?: '+919876543210',
    ],
];

// Feature flags
$config['features'] = [
    'ai_chatbot' => true,
    'commission_system' => true,
    'mlm_features' => true,
    'property_alerts' => true,
    'virtual_tours' => false,
    'blockchain_integration' => false,
];

// Pagination settings
$config['pagination'] = [
    'per_page' => 12,
    'page_range' => 5,
];

// Security settings
$config['security'] = [
    'csrf_protection' => true,
    'xss_protection' => true,
    'content_security_policy' => true,
    'rate_limiting' => true,
    'password_min_length' => 8,
    'session_lifetime' => 120, // minutes
];

// Logging configuration
$config['logging'] = [
    'enabled' => true,
    'level' => ENVIRONMENT === 'development' ? 'debug' : 'error',
    'path' => APP_ROOT . '/storage/logs/',
    'max_files' => 30,
];

// Performance settings
$config['performance'] = [
    'asset_minification' => ENVIRONMENT === 'production',
    'query_optimization' => true,
    'caching' => ENVIRONMENT === 'production',
    'compression' => true,
];

return $config;

?>
