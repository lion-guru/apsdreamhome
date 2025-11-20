<?php
/**
 * Environment Configuration Helper
 * Reads configuration from .env file and provides easy access
 */

class Env
{
    private static $config = [];
    private static $loaded = false;

    /**
     * Load configuration from .env file
     */
    private static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos($line, '#') === 0) {
                    continue;
                }

                // Parse key=value pairs
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Remove quotes if present
                    if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                        $value = $matches[1];
                    }

                    self::$config[$key] = $value;
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        self::load();
        return self::$config[$key] ?? $default;
    }

    /**
     * Set configuration value
     */
    public static function set($key, $value)
    {
        self::load();
        self::$config[$key] = $value;
    }

    /**
     * Check if key exists
     */
    public static function has($key)
    {
        self::load();
        return isset(self::$config[$key]);
    }

    /**
     * Get all configuration
     */
    public static function all()
    {
        self::load();
        return self::$config;
    }
}

/**
 * Helper function for easy access
 */
function env($key, $default = null)
{
    return Env::get($key, $default);
}

/**
 * Configuration helper for application settings
 */
function config($key, $default = null)
{
    // Handle nested keys like 'app.name'
    if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        $config = Env::get($keys[0], []);

        // If it's a simple string, return it
        if (is_string($config)) {
            return $config;
        }

        // Navigate through nested array
        for ($i = 1; $i < count($keys); $i++) {
            if (isset($config[$keys[$i]])) {
                $config = $config[$keys[$i]];
            } else {
                return $default;
            }
        }

        return $config;
    }

    return Env::get($key, $default);
}

/**
 * Email configuration helper
 */
function email_config()
{
    return [
        'smtp_host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'smtp_port' => env('MAIL_PORT', 587),
        'smtp_username' => env('MAIL_USERNAME', ''),
        'smtp_password' => env('MAIL_PASSWORD', ''),
        'smtp_encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com'),
        'from_name' => env('MAIL_FROM_NAME', 'APS Dream Home'),
        'admin_email' => env('ADMIN_EMAIL', 'admin@apsdreamhome.com')
    ];
}

/**
 * Database configuration helper
 */
function db_config()
{
    return [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'apsdreamhome'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];
}

/**
 * Application configuration helper
 */
function app_config()
{
    return [
        'name' => env('APP_NAME', 'APS Dream Home'),
        'env' => env('APP_ENV', 'development'),
        'debug' => env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://localhost/apsdreamhome'),
        'key' => env('APP_KEY', ''),
    ];
}

/**
 * Security configuration helper
 */
function security_config()
{
    return [
        'session_lifetime' => env('SESSION_LIFETIME', 120),
        'session_encrypt' => env('SESSION_ENCRYPT', true),
        'session_secure_cookie' => env('SESSION_SECURE_COOKIE', false),
        'api_rate_limit' => env('API_RATE_LIMIT', 1000),
    ];
}

/**
 * File upload configuration helper
 */
function upload_config()
{
    return [
        'max_file_size' => env('MAX_FILE_SIZE', 5242880), // 5MB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx')),
    ];
}

/**
 * Payment configuration helper
 */
function payment_config()
{
    return [
        'razorpay_key' => env('RAZORPAY_KEY', ''),
        'razorpay_secret' => env('RAZORPAY_SECRET', ''),
    ];
}

/**
 * SMS configuration helper
 */
function sms_config()
{
    return [
        'api_key' => env('SMS_API_KEY', ''),
        'sender_id' => env('SMS_SENDER_ID', ''),
    ];
}

/**
 * Analytics configuration helper
 */
function analytics_config()
{
    return [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
    ];
}

/**
 * Maintenance mode helper
 */
function is_maintenance_mode()
{
    return env('MAINTENANCE_MODE', false);
}

/**
 * Get cache configuration
 */
function cache_config()
{
    return [
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('CACHE_TTL', 3600),
    ];
}

/**
 * Get queue configuration
 */
function queue_config()
{
    return [
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        'failed' => env('QUEUE_FAILED_DRIVER', 'database'),
    ];
}

/**
 * Get logging configuration
 */
function logging_config()
{
    return [
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'debug'),
    ];
}

/**
 * Get API configuration
 */
function api_config()
{
    return [
        'version' => env('API_VERSION', 'v1'),
        'rate_limit' => env('API_RATE_LIMIT', 1000),
        'throttle' => env('API_THROTTLE', 60),
    ];
}
?>
