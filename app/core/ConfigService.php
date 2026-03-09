<?php

namespace App\Core;

use PDO;

/**
 * Configuration Service - APS Dream Home
 * Modern configuration management service
 * Custom MVC implementation without Laravel dependencies
 */
class ConfigService
{
    private static $instance = null;
    private $config = [];

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load configuration from environment and config files
     */
    private function loadConfiguration()
    {
        // Load environment variables
        $this->loadEnvironmentVariables();

        // Set default configuration
        $this->config = [
            'app' => [
                'name' => getenv('APP_NAME') ?: 'APS Dream Home',
                'env' => getenv('APP_ENV') ?: 'production',
                'debug' => getenv('APP_DEBUG') ?: false,
                'url' => getenv('APP_URL') ?: 'http://localhost',
                'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
            ],
            'database' => [
                'host' => getenv('DB_HOST') ?: 'localhost',
                'port' => getenv('DB_PORT') ?: '3306',
                'database' => getenv('DB_DATABASE') ?: 'apsdreamhome',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ],
            'mail' => [
                'host' => getenv('MAIL_HOST') ?: 'localhost',
                'port' => getenv('MAIL_PORT') ?: '587',
                'username' => getenv('MAIL_USERNAME') ?: '',
                'password' => getenv('MAIL_PASSWORD') ?: '',
                'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
                'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@apsdreamhome.com',
                'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home',
                'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com'
            ],
            'security' => [
                'session_lifetime' => getenv('SESSION_LIFETIME') ?: 7200,
                'csrf_token_lifetime' => getenv('CSRF_TOKEN_LIFETIME') ?: 3600,
                'password_min_length' => getenv('PASSWORD_MIN_LENGTH') ?: 8,
                'max_login_attempts' => getenv('MAX_LOGIN_ATTEMPTS') ?: 5,
                'lockout_duration' => getenv('LOCKOUT_DURATION') ?: 900,
            ],
            'storage' => [
                'upload_path' => getenv('UPLOAD_PATH') ?: 'uploads/',
                'max_file_size' => getenv('MAX_FILE_SIZE') ?: 10485760, // 10MB
                'allowed_extensions' => explode(',', getenv('ALLOWED_EXTENSIONS') ?: 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'),
            ],
            'performance' => [
                'cache_enabled' => getenv('CACHE_ENABLED') ?: true,
                'cache_lifetime' => getenv('CACHE_LIFETIME') ?: 3600,
                'slow_query_threshold' => getenv('SLOW_QUERY_THRESHOLD') ?: 100,
                'memory_limit' => getenv('MEMORY_LIMIT') ?: 128,
            ]
        ];
    }

    /**
     * Load environment variables
     */
    private function loadEnvironmentVariables()
    {
        // Load .env file if it exists
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0 || empty($line)) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Remove quotes if present
                    $value = trim($value, '"\'');

                    // Set environment variable
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    /**
     * Get configuration value
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value
     */
    public function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Check if configuration key exists
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * Get database configuration
     */
    public function getDatabaseConfig()
    {
        return $this->get('database');
    }

    /**
     * Get mail configuration
     */
    public function getMailConfig()
    {
        return $this->get('mail');
    }

    /**
     * Get security configuration
     */
    public function getSecurityConfig()
    {
        return $this->get('security');
    }

    /**
     * Get storage configuration
     */
    public function getStorageConfig()
    {
        return $this->get('storage');
    }

    /**
     * Get performance configuration
     */
    public function getPerformanceConfig()
    {
        return $this->get('performance');
    }

    /**
     * Get application configuration
     */
    public function getAppConfig()
    {
        return $this->get('app');
    }
}
