<?php
/**
 * APS Dream Home - PSR-4 Autoloader
 *
 * Modern autoloader implementation following PSR-4 standards
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables with error handling
if (file_exists(__DIR__ . '/.env')) {
    try {
        if (class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        } else {
            // Fallback: load environment variables manually
            $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log('Environment loading error: ' . $e->getMessage());
    }
}

// Custom autoloader for legacy code (backwards compatibility)
spl_autoload_register(function ($className) {
    // Convert namespace to file path (PSR-4)
    $className = ltrim($className, '\\');
    $fileName = '';
    $namespace = '';

    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    // Check in src directory first (new PSR-4 structure)
    $srcPath = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($srcPath)) {
        require $srcPath;
        return;
    }

    // Fallback to legacy paths
    $legacyPaths = [
        __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $className . '.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . $className . '.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $className . '.php',
        __DIR__ . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . $className . '.php',
    ];

    foreach ($legacyPaths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
});

// Set error reporting based on environment
$environment = getenv('APP_ENV') ?: 'production';
if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Kolkata');

// Initialize application constants
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', BASE_PATH . '/src');
}

if (!defined('APP_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_URL', $protocol . $host . '/apsdreamhome/');
}

if (!defined('APP_ENV')) {
    define('APP_ENV', $environment);
}

// Load legacy configuration (backwards compatibility)
require_once __DIR__ . '/config.php';

// Initialize logging
if (!function_exists('initializeLogging')) {
    function initializeLogging() {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        if (class_exists('Monolog\Logger') && class_exists('Monolog\Handler\StreamHandler')) {
            global $logger;
            $logger = new Monolog\Logger('aps-dream-home');

            $logLevel = APP_ENV === 'development' ? Monolog\Logger::DEBUG : Monolog\Logger::WARNING;
            $logFile = BASE_PATH . '/logs/app.log';

            $streamHandler = new Monolog\Handler\StreamHandler($logFile, $logLevel);
            $logger->pushHandler($streamHandler);

            $initialized = true;
        }
    }
}

// Initialize application
initializeLogging();
