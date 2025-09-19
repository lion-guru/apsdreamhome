<?php
// Session Configuration
define('SESSION_NAME', 'APS_DREAM_HOMES_SESSION');
define('MAINTENANCE_MODE', false);
define('SITE_URL', 'http://localhost/apsdreamhomefinal');

// Global Configuration Management

class AppConfig {
    private static $instance = null;
    private $config = [];
    private $db_connection = null;

    private function __construct() {
        // Default configuration
        $this->config = [
            'app' => [
                'name' => 'APS Dream Homes',
                'version' => '1.0.0',
                'environment' => getenv('APP_ENV') ?: 'production',
                'debug' => getenv('APP_DEBUG') === 'true',
            ],
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'apsdreamhomefinal',
                'port' => getenv('DB_PORT') ?: '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'auth' => [
                'jwt_secret' => getenv('JWT_SECRET') ?: 'default_jwt_secret',
                'password_hash_algo' => PASSWORD_ARGON2ID,
                'password_hash_options' => [
                    'memory_cost' => 1024 * 64,
                    'time_cost' => 4,
                    'threads' => 3
                ],
            ],
            'google' => [
                'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
                'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
                'oauth_scopes' => getenv('GOOGLE_OAUTH_SCOPES') ? explode(' ', getenv('GOOGLE_OAUTH_SCOPES')) : ['email', 'profile'],
                'oauth_prompt' => getenv('GOOGLE_OAUTH_PROMPT') ?: 'select_account consent',
            ],
            'ai' => [
                'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
                'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
            ],
            'security' => [
                'csrf_protection' => true,
                'password_hash_algo' => PASSWORD_ARGON2ID,
                'password_hash_options' => [
                    'memory_cost' => 1024 * 64,
                    'time_cost' => 4,
                    'threads' => 3
                ],
            ],
            'performance' => [
                'cache_enabled' => true,
                'cache_lifetime' => 3600, // 1 hour
                'opcache_enabled' => true,
            ],
            'email' => [
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_secure' => 'tls',
                'smtp_username' => 'your_email@gmail.com',
                'smtp_password' => 'app_password_here',
            ],
            'logging' => [
                'log_level' => 'warning',
                'log_channels' => ['error', 'security', 'performance'],
            ],
            'features' => [
                'property_search' => true,
                'lead_management' => true,
                'visit_scheduling' => true,
                'notification_system' => true,
            ]
        ];
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null) {
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

    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    public function loadFromEnvironment() {
        // Override config with environment variables
        $env_mappings = [
            'DB_HOST' => 'database.host',
            'DB_USER' => 'database.user',
            'DB_PASS' => 'database.pass',
            'DB_NAME' => 'database.name',
            'APP_ENV' => 'app.environment',
            'APP_DEBUG' => 'app.debug',
        ];

        foreach ($env_mappings as $env_var => $config_key) {
            $value = getenv($env_var);
            if ($value !== false) {
                $this->set($config_key, $value);
            }
        }
    }

    public function validateConfiguration() {
        $errors = [];

        // Validate database configuration
        $db_config = $this->get('database');
        if (empty($db_config['host']) || empty($db_config['user']) || empty($db_config['name'])) {
            $errors[] = 'Incomplete database configuration';
        }

        // Validate security settings
        $security_config = $this->get('security');
        if (!function_exists('password_hash') || !defined($security_config['password_hash_algo'])) {
            $errors[] = 'Unsupported password hashing algorithm';
        }

        return $errors;
    }

    public function exportToFile($path = null) {
        if ($path === null) {
            $path = __DIR__ . '/../config/app_config.json';
        }

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Export configuration (excluding sensitive data)
        $safe_config = $this->config;
        unset($safe_config['database']['pass']);
        unset($safe_config['email']['smtp_password']);

        file_put_contents($path, json_encode($safe_config, JSON_PRETTY_PRINT));
    }

    public function getDatabaseConnection() {
        if ($this->db_connection === null) {
            $db_config = $this->get('database');
            $this->db_connection = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);

            if ($this->db_connection->connect_error) {
                die("Connection failed: " . $this->db_connection->connect_error);
            }
        }

        return $this->db_connection;
    }
}

// Initialize and load configuration
$config = AppConfig::getInstance();
$config->loadFromEnvironment();

// Validate configuration
$config_errors = $config->validateConfiguration();
if (!empty($config_errors)) {
    // Log configuration errors
    error_log('Configuration Errors: ' . implode(', ', $config_errors));
}

// Export configuration for reference
$config->exportToFile();

// Get the database connection
$db_connection = $config->getDatabaseConnection();

// Return the configuration instance and database connection for use in other files
return [$config, $db_connection];
