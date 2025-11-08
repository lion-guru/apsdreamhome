<?php
/**
 * Application Bootstrap
 * Initializes core application systems
 */

namespace App\Core;

// Ensure this file is only loaded once
if (class_exists('App\Core\Application')) {
    return;
}

/**
 * Main Application Class
 * Handles initialization and core functionality
 */
class Application {
    private static $instance = null;
    private $config = [];
    private $initialized = false;

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the application
     */
    public function initialize() {
        if ($this->initialized) {
            return;
        }

        // Set timezone
        date_default_timezone_set(config('app.timezone'));

        // Set error reporting
        if (config('app.debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', 0);
        }

        // Initialize logging
        $this->initializeLogging();

        // Initialize caching
        $this->initializeCaching();

        // Initialize database connections
        $this->initializeDatabase();

        // Register shutdown handler
        register_shutdown_function([$this, 'shutdown']);

        $this->initialized = true;
    }

    /**
     * Initialize logging system
     */
    private function initializeLogging() {
        if (!config('logging.enabled')) {
            return;
        }

        // Create log directory if it doesn't exist
        $logDir = config('logging.path');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Set up error logging
        ini_set('log_errors', 1);
        ini_set('error_log', $logDir . 'php_errors.log');
    }

    /**
     * Initialize caching system
     */
    private function initializeCaching() {
        if (!config('cache.enabled', false)) {
            return;
        }

        // Create cache directory if it doesn't exist
        $cacheDir = config('cache.path');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }

    /**
     * Initialize database connections
     */
    private function initializeDatabase() {
        // Database connections are handled in config/database.php
        // This is just a placeholder for additional initialization
    }

    /**
     * Application shutdown handler
     */
    public function shutdown() {
        // Clean up resources, close connections, etc.
        if (isset($GLOBALS['con']) && $GLOBALS['con'] instanceof mysqli) {
            mysqli_close($GLOBALS['con']);
        }
    }

    /**
     * Get application configuration value
     */
    public function config($key, $default = null) {
        return config($key, $default);
    }

    /**
     * Check if application is in debug mode
     */
    public function isDebug() {
        return config('app.debug', false);
    }

    /**
     * Get application environment
     */
    public function environment() {
        return config('app.environment', 'production');
    }
}

/**
 * Global helper function to get configuration values
 */
function config($key, $default = null) {
    global $config;

    if (!isset($config)) {
        $config = [];
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }

    return $value;
}

/**
 * Global helper function for application instance
 */
function app() {
    return Application::getInstance();
}

?>
