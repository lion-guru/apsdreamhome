<?php
/**
 * APS Dream Home - Development Environment Configuration
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhome');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application Settings
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/apsdreamhomefinal');

// Security Settings
define('CSRF_PROTECTION', true);
define('SESSION_TIMEOUT', 3600);

// Email Configuration (for development)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');

// Cache Settings
define('CACHE_ENABLED', false);
define('CACHE_TTL', 3600);

// Logging
define('LOG_LEVEL', 'debug');
define('LOG_FILE', APP_ROOT . '/logs/development.log');

// API Settings
define('API_RATE_LIMIT', 1000);
define('API_VERSION', 'v1');

// Development specific settings
define('ENABLE_ERROR_DISPLAY', true);
define('ENABLE_PROFILING', true);
?>
