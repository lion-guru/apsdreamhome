<?php

namespace App\Services;

use Exception;

class ConfigurationManager
{
    // Singleton instance
    private static $instance = null;

    // Configuration cache
    private $config = [];

    // Configuration file paths
    private $configPaths = [
        'logging' => __DIR__ . '/../../config/logging.php',
        'database' => __DIR__ . '/../../config/database.php',
        'app' => __DIR__ . '/../../config/app.php'
    ];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->loadConfigurations();
    }

    /**
     * Singleton getInstance method
     * 
     * @return ConfigurationManager
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load all configuration files
     */
    private function loadConfigurations()
    {
        foreach ($this->configPaths as $configName => $path) {
            try {
                if (file_exists($path)) {
                    $this->config[$configName] = require $path;
                } else {
                    throw new Exception("Configuration file not found: $path");
                }
            } catch (Exception $e) {
                // Log configuration loading error
                error_log("Config Load Error ($configName): " . $e->getMessage());
                $this->config[$configName] = [];
            }
        }
    }

    /**
     * Get configuration value
     * 
     * @param string $configName Configuration file name
     * @param string $key Dot-notated key path
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get(string $configName, string $key = null, $default = null)
    {
        if (!isset($this->config[$configName])) {
            return $default;
        }

        if ($key === null) {
            return $this->config[$configName];
        }

        $value = $this->config[$configName];
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    private function __clone() {}
    public function __wakeup() {}
}
