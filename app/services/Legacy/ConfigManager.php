<?php

namespace App\Services\Legacy;

use Exception;

/**
 * Centralized Configuration Management
 * Provides a robust and secure way to manage application configurations
 */

class ConfigManager {
    private static $instance = null;
    private $configurations = [];
    private $sensitiveKeys = [
        'DB_PASS',
        'APP_SECRET_KEY',
        'SECURITY_SALT',
        'S3_SECRET',
        'INCIDENT_WEBHOOK_URL'
    ];

    /**
     * Role-based configuration access
     *
     * @param string $role User role
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value or default
     */
    public function getRoleConfig(string $role, string $key, $default = null) {
        // Role-specific configuration overrides
        $roleConfigs = [
            'admin' => [
                'APP_DEBUG' => true,
                'LOG_LEVEL' => 'debug',
                'SECURITY_MODE' => 'strict'
            ],
            'manager' => [
                'APP_DEBUG' => false,
                'LOG_LEVEL' => 'warning',
                'SECURITY_MODE' => 'moderate'
            ],
            'user' => [
                'APP_DEBUG' => false,
                'LOG_LEVEL' => 'error',
                'SECURITY_MODE' => 'basic'
            ]
        ];

        // Check if role-specific config exists
        if (isset($roleConfigs[$role][$key])) {
            return $roleConfigs[$role][$key];
        }

        // Fallback to default configuration
        return $this->get($key, $default);
    }

    /**
     * Validate configuration access based on role
     *
     * @param string $role User role
     * @param string $key Configuration key
     * @return bool True if access is allowed, false otherwise
     */
    public function canAccessConfig(string $role, string $key): bool {
        $restrictedKeys = [
            'admin' => ['*'],  // Admins can access everything
            'manager' => ['APP_SECRET_KEY', 'SECURITY_SALT', 'DB_PASS'],
            'user' => []
        ];

        // Check if role has full access
        if (\in_array('*', $restrictedKeys[$role] ?? [])) {
            return true;
        }

        // Check if key is restricted for this role
        return !\in_array($key, $restrictedKeys[$role] ?? []);
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->loadConfigurations();
    }

    /**
     * Singleton instance method
     *
     * @return ConfigManager
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load configurations from .env file
     */
    private function loadConfigurations() {
        // require_once __DIR__ . '/validator.php';

        $rootEnv = dirname(dirname(dirname(__DIR__))) . '/.env';
        $legacyEnv = dirname(dirname(dirname(__DIR__))) . '/includes/config/.env';

        $config = $this->getDefaultConfigurations();

        // Load root .env
        if (file_exists($rootEnv)) {
            try {
                $rootConfig = $this->parseEnvFile($rootEnv);
                $config = array_merge($config, $rootConfig);
            } catch (Exception $e) {
                error_log("Error parsing root .env: " . $e->getMessage());
            }
        }

        // Load legacy .env (and overwrite/merge)
        if (file_exists($legacyEnv)) {
            try {
                $legacyConfig = $this->parseEnvFile($legacyEnv);
                $config = array_merge($config, $legacyConfig);
            } catch (Exception $e) {
                error_log("Error parsing legacy .env: " . $e->getMessage());
            }
        }

        // Validate and sanitize configurations
        foreach ($config as $key => $value) {
            // Sanitize sensitive keys
            if (\in_array($key, $this->sensitiveKeys)) {
                $value = $this->sanitizeSensitiveValue($value);
            }

            // Validate configuration values
            if ($this->validateConfigValue($key, $value)) {
                $this->configurations[$key] = $value;
            }
        }

        // Log if no configurations were loaded
        if (empty($this->configurations)) {
            \error_log('No valid configurations found in: ' . $envPath);
        }

        // Optional: Add default fallback values for critical configurations
        $this->setDefaultConfigurations();
    }

    /**
     * Sanitize sensitive configuration values
     */
    private function sanitizeSensitiveValue($value) {
        // Basic sanitization for sensitive values
        return \trim(\strip_tags($value));
    }

    /**
     * Validate configuration values
     */
    private function validateConfigValue($key, $value) {
        // Basic validation rules
        if (empty($value)) {
            \error_log("Empty value for configuration key: $key");
            return false;
        }

        // Add more specific validation as needed
        switch ($key) {
            case 'DB_HOST':
                return \preg_match('/^[a-zA-Z0-9.-]+$/', $value) === 1;
            case 'DB_USER':
                return \preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
            case 'DB_NAME':
                return \preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
            default:
                return true;
        }
    }

    private function setDefaultConfigurations() {
        // Add default fallback values for critical configurations
        $defaults = $this->getDefaultConfigurations();
        foreach ($defaults as $key => $value) {
            if (!isset($this->configurations[$key])) {
                $this->configurations[$key] = $value;
            }
        }
    }

    private function parseEnvFile(string $envPath): array {
        // Validate file existence and readability
        if (!is_readable($envPath)) {
            throw new Exception('Configuration file is not readable: ' . $envPath);
        }

        // Read file contents
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new Exception('Failed to read configuration file');
        }

        $config = [];
        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Split line into key and value
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue; // Invalid line format
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove quotes if present
            $value = trim($value, '\'"');

            $config[$key] = $value;
        }

        return $config;
    }

    private function getDefaultConfigurations(): array {
                return [
                    // Database Defaults
                    'DB_HOST' => 'localhost',
                    'DB_USER' => 'root',
                    'DB_PASS' => '',
                    'DB_NAME' => 'apsdreamhome',

            // Application Defaults
            'APP_DEBUG' => 'false',
            'APP_ENV' => 'production',
            'APP_NAME' => 'Default Application',

            // Security Defaults
            'SECURITY_MODE' => 'strict',
            'SECURITY_SALT' => \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(16)),

            // Logging Defaults
            'LOG_LEVEL' => 'warning',
            'LOG_CHANNEL' => 'daily',

            // Performance Defaults
            'MAX_EXECUTION_TIME' => '300',
            'MEMORY_LIMIT' => '256M'
        ];
    }

    /**
     * Get a configuration value with optional default
     *
     * @param string $key Configuration key to retrieve
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function get(string $key, $default = null) {
        // Ensure configurations are loaded
        if (empty($this->configurations)) {
            $this->loadConfigurations();
        }

        // Check if key exists
        if (!isset($this->configurations[$key])) {
            // Log missing configuration
            error_log("Configuration key not found: $key. Using default value.");
            return $default;
        }

        // Return configuration value
        return $this->configurations[$key];
    }

    /**
     * Check if a configuration key exists with enhanced logging
     *
     * @param string $key Configuration key to check
     * @return bool True if key exists, false otherwise
     */
    public function has(string $key): bool {
        // Ensure configurations are loaded
        if (empty($this->configurations)) {
            $this->loadConfigurations();
        }

        // Check and log configuration status
        $exists = isset($this->configurations[$key]);
        if (!$exists) {
            error_log("Configuration key does not exist: $key");
        }

        return $exists;
    }

    /**
     * Sanitize and validate configuration values
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return mixed Sanitized value
     */
    private function sanitizeConfigValue($key, $value) {
        $validator = new Validator();

        // Trim whitespace
        $value = trim($value);

        // Handle sensitive keys
        if (in_array($key, $this->sensitiveKeys)) {
            // Mask sensitive values in logs
            error_log("Sensitive config loaded: $key");
            $value = $this->maskSensitiveValue($value);
        }

        // Validate and convert configuration values
        $validationRules = [
            'APP_DEBUG' => 'required|bool',
            'MAX_LOGIN_ATTEMPTS' => 'required|numeric|min:1|max:10',
            'LOG_MAX_FILES' => 'required|numeric|min:1|max:50',
            'DB_MAX_CONNECTIONS' => 'required|numeric|min:1|max:100',
            'LOG_MAX_SIZE' => 'required|numeric|min:1|max:1000'
        ];

        // Apply validation if rule exists
        if (isset($validationRules[$key])) {
            if (!$validator->validate([$key => $value], [$key => $validationRules[$key]])) {
                // Removed $this->logger->warning call
                return null; // Invalidate configuration
            }
        }

        // Type conversion
        return $validator->convert($value, match($key) {
            'APP_DEBUG' => 'bool',
            'MAX_LOGIN_ATTEMPTS', 'LOG_MAX_FILES', 'DB_MAX_CONNECTIONS' => 'int',
            'LOG_MAX_SIZE' => 'float',
            default => 'string'
        });
    }

    /**
     * Mask sensitive values
     *
     * @param string $value Sensitive value
     * @return string Masked value
     */
    private function maskSensitiveValue($value) {
        if (empty($value)) return $value;
        return str_repeat('*', min(strlen($value), 8));
    }

    /**
     * Validate critical configurations
     *
     * @throws Exception If critical configurations are missing
     */
    public function validateConfigurations() {
        $criticalKeys = [
            'DB_HOST',
            'DB_USER',
            'DB_NAME',
            'APP_SECRET_KEY'
        ];

        foreach ($criticalKeys as $key) {
            if (!$this->has($key)) {
                throw new Exception("Critical configuration missing: $key");
            }
        }
    }

    /**
     * Reload configurations
     */
    public function reload() {
        $this->loadConfigurations();
    }
}

// Convenience function to get config
function config($key, $default = null) {
    return ConfigManager::getInstance()->get($key, $default);
}

// Validate configurations on load
try {
    $configManager = ConfigManager::getInstance();
    $configManager->validateConfigurations();
} catch (Exception $e) {
    // Log configuration error
    error_log("Configuration Validation Error: " . $e->getMessage());
    die("Application configuration error. Please contact support.");
}
