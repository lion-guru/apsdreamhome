<?php
namespace App\Services;

use Exception;

class ConfigurationManager {
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
    private function __construct() {
        $this->loadConfigurations();
    }

    /**
     * Singleton getInstance method
     * 
     * @return ConfigurationManager
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load all configuration files
     */
    private function loadConfigurations() {
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
     * Get a configuration value
     * 
     * @param string $configName Configuration file name
     * @param string $key Dot-notated key path
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get(string $configName, string $key = null, $default = null) {
        // Validate configuration exists
        if (!isset($this->config[$configName])) {
            return $default;
        }

        // If no specific key, return entire configuration
        if ($key === null) {
            return $this->config[$configName];
        }

        // Navigate through nested configuration
        $config = $this->config[$configName];
        $keys = explode('.', $key);

        foreach ($keys as $nestedKey) {
            if (!is_array($config) || !array_key_exists($nestedKey, $config)) {
                return $default;
            }
            $config = $config[$nestedKey];
        }

        return $config;
    }

    /**
     * Set a configuration value
     * 
     * @param string $configName Configuration file name
     * @param string $key Dot-notated key path
     * @param mixed $value Value to set
     * @return bool
     */
    public function set(string $configName, string $key, $value): bool {
        // Validate configuration exists
        if (!isset($this->config[$configName])) {
            return false;
        }

        // Navigate through nested configuration
        $keys = explode('.', $key);
        $config = &$this->config[$configName];

        foreach ($keys as $nestedKey) {
            if (!is_array($config)) {
                return false;
            }
            
            if (!array_key_exists($nestedKey, $config)) {
                $config[$nestedKey] = [];
            }
            
            $config = &$config[$nestedKey];
        }

        $config = $value;
        return true;
    }

    /**
     * Reload configurations from files
     */
    public function reload() {
        $this->loadConfigurations();
    }

    /**
     * Save configuration to file
     * 
     * @param string $configName Configuration file name
     * @return bool
     */
    public function save(string $configName): bool {
        if (!isset($this->configPaths[$configName])) {
            return false;
        }

        try {
            $content = "<?php\nreturn " . var_export($this->config[$configName], true) . ";";
            file_put_contents($this->configPaths[$configName], $content);
            return true;
        } catch (Exception $e) {
            error_log("Config Save Error ($configName): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prevent cloning of the singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialize of the singleton
     */
    public function __wakeup() {}
}
