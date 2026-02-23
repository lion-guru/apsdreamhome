<?php

// APS Dream Home - Custom PHP Framework Bootstrap
// This is not a Laravel application, it's a custom PHP framework

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Define application constants
define('APP_ROOT', dirname(__DIR__));

// Define Laravel helper functions for compatibility
if (!function_exists('resource_path')) {
    function resource_path($path = '') {
        return APP_ROOT . '/resources' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'APS Dream Home');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
define('ENVIRONMENT', $_ENV['APP_ENV'] ?? 'production');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
define('APP_KEY', $_ENV['APP_KEY'] ?? '');

// Include configuration files
$configFiles = glob(APP_ROOT . '/config/*.php');
foreach ($configFiles as $configFile) {
    require_once $configFile;
}

// Include helper functions
if (file_exists(APP_ROOT . '/app/helpers.php')) {
    require_once APP_ROOT . '/app/helpers.php';
}

// Initialize application
class App {
    private static $instance = null;
    private $config = [];

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->loadConfig();
    }

    private function loadConfig() {
        // Load all config files
        $configPath = APP_ROOT . '/config';
        if (is_dir($configPath)) {
            $files = scandir($configPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $configName = pathinfo($file, PATHINFO_FILENAME);
                    $this->config[$configName] = require $configPath . '/' . $file;
                }
            }
        }
    }

    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }

    public function run() {
        // Application startup logic would go here
        // For now, just return true to indicate successful initialization
        return true;
    }
}

// Return the application instance
return App::getInstance();
