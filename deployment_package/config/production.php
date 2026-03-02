<?php
/**
 * 🚀 APS DREAM HOME - PRODUCTION CONFIGURATION
 * Production-ready configuration for co-worker system
 */

return [
    // Database Configuration
    'database' => [
        'host' => 'localhost',
        'name' => 'apsdreamhome',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ],

    // Application Configuration
    'app' => [
        'env' => 'production',
        'debug' => false,
        'url' => 'http://localhost',
        'timezone' => 'Asia/Kolkata',
        'locale' => 'en'
    ],

    // Security Configuration
    'security' => [
        'csrf_protection' => true,
        'rate_limiting' => true,
        'input_sanitization' => true,
        'session_timeout' => 3600
    ],

    // Performance Configuration
    'performance' => [
        'caching' => true,
        'compression' => true,
        'minify_assets' => true
    ],

    // Logging Configuration
    'logging' => [
        'level' => 'error',
        'file' => 'logs/app.log',
        'max_files' => 10,
        'max_size' => '10MB'
    ],

    // API Configuration
    'api' => [
        'rate_limit' => 100,
        'timeout' => 30,
        'cors_enabled' => true
    ],

    // Upload Configuration
    'upload' => [
        'max_size' => '10MB',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
        'path' => 'uploads/'
    ]
];
?>
