<?php

namespace App\Core\Config;

/**
 * Configuration Class
 * Provides static access to configuration values
 */
class Config
{
    private static $config = [];
    private static $loaded = false;

    /**
     * Get configuration value
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::loadConfig();
        }
        
        $keys = explode('.', $key);
        $value = self::$config;
        
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
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }

    /**
     * Check if config key exists
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::loadConfig();
        }
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }

    /**
     * Load configuration from files
     */
    private static function loadConfig(): void
    {
        if (self::$loaded) {
            return;
        }

        // Set defaults
        self::$config = [
            'log_to_database' => false,
            'app' => [
                'name' => 'APS Dream Home',
                'env' => 'development',
                'debug' => true,
            ]
        ];

        // Try to load from config files if APP_ROOT is defined
        if (!defined('APP_ROOT')) {
            self::$loaded = true;
            return;
        }

        $configPath = defined('CONFIG_PATH') ? CONFIG_PATH : APP_ROOT . '/config';
        
        // Application config
        if (file_exists($configPath . '/application.php')) {
            $appConfig = require $configPath . '/application.php';
            if (is_array($appConfig)) {
                self::$config = array_merge(self::$config, $appConfig);
            }
        }

        // Database config
        if (file_exists($configPath . '/database.php')) {
            $dbConfig = require $configPath . '/database.php';
            if (is_array($dbConfig)) {
                self::$config['database'] = $dbConfig;
            }
        }

        // Environment specific config - skip if it uses APP_ROOT before defined
        $env = getenv('APP_ENV') ?: 'development';
        $envFile = $configPath . '/environments/' . $env . '.php';
        if (file_exists($envFile)) {
            try {
                $envConfig = require $envFile;
                if (is_array($envConfig)) {
                    self::$config = array_merge(self::$config, $envConfig);
                }
            } catch (\Exception $e) {
                // Skip if env file has issues
            }
        }

        self::$loaded = true;
    }

    /**
     * Get all configuration
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::loadConfig();
        }
        return self::$config;
    }
}
