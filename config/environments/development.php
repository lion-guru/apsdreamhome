<?php
/**
 * APS Dream Home - Development Environment Configuration
 */

return [
    // Database Configuration
    'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
    'DB_NAME' => getenv('DB_NAME') ?: 'apsdreamhome',
    'DB_USER' => getenv('DB_USER') ?: 'root',
    'DB_PASS' => getenv('DB_PASS') ?: '',

    // Application Settings
    'APP_ENV' => 'development',
    'APP_DEBUG' => true,
    'APP_URL' => getenv('APP_URL') ?: 'http://localhost/apsdreamhome',

    // Security Settings
    'CSRF_PROTECTION' => true,
    'SESSION_TIMEOUT' => 3600,

    // Email Configuration (for development)
    'SMTP_HOST' => getenv('SMTP_HOST') ?: 'localhost',
    'SMTP_PORT' => getenv('SMTP_PORT') ?: 587,
    'SMTP_USER' => getenv('SMTP_USER') ?: '',
    'SMTP_PASS' => getenv('SMTP_PASS') ?: '',

    // Cache Settings
    'CACHE_ENABLED' => false,
    'CACHE_TTL' => 3600,

    // Logging
    'LOG_LEVEL' => 'debug',
    'LOG_FILE' => APP_ROOT . '/logs/development.log',

    // API Settings
    'API_RATE_LIMIT' => 1000,
    'API_VERSION' => 'v1',

    // Development specific settings
    'ENABLE_ERROR_DISPLAY' => true,
    'ENABLE_PROFILING' => true,
];
