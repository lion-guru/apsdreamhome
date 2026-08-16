<?php header('Content-Type: text/html; charset=UTF-8'); error_reporting(E_ALL);

if ((defined('APS_ENV') && APS_ENV === 'development') || getenv('APS_ENV') === 'development') {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Define constants only if not already defined
if (!defined('APP_NAME')) define('APP_NAME', 'APSDreamHome');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');

// Define application constants only if not already defined
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_ROOT . '/config');
if (!defined('APP_PATH')) define('APP_PATH', APP_ROOT . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', APP_ROOT . '/public');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', APP_ROOT . '/storage');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/views');
if (!defined('CORE_PATH')) define('CORE_PATH', APP_PATH . '/core');
if (!defined('CONTROLLER_PATH')) define('CONTROLLER_PATH', APP_PATH . '/controllers');
if (!defined('MODEL_PATH')) define('MODEL_PATH', APP_PATH . '/models');
if (!defined('HELPER_PATH')) define('HELPER_PATH', APP_PATH . '/helpers');
if (!defined('ROUTE_PATH')) define('ROUTE_PATH', APP_PATH . '/routes');
if (!defined('SERVICE_PATH')) define('SERVICE_PATH', APP_PATH . '/services');
if (!defined('MIDDLEWARE_PATH')) define('MIDDLEWARE_PATH', APP_PATH . '/middleware');
if (!defined('CACHE_PATH')) define('CACHE_PATH', STORAGE_PATH . '/cache');
if (!defined('LOG_PATH')) define('LOG_PATH', STORAGE_PATH . '/logs');
if (!defined('SESSION_PATH')) define('SESSION_PATH', STORAGE_PATH . '/sessions');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Session configuration - MUST be before session_start()
if (!ini_get('session.save_handler') || ini_get('session.save_handler') === 'files') {
    ini_set('session.save_path', SESSION_PATH);
}
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

if (!defined('BASE_URL')) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';

    if (php_sapi_name() === 'cli') {
        // CLI mode (cron scripts, artisan): detect from project structure
        $appDir = dirname(__DIR__); // config/ -> project root
        // Check if we're in XAMPP htdocs or similar web root
        $webRoot = dirname($appDir); // project root -> htdocs
        $base = '/' . basename($appDir);
        define('BASE_URL', $protocol . '://localhost' . $base);
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
        $script = str_replace('\\', '/', $script);
        // Remove /public if it exists in the path
        if (substr($script, -7) === '/public') {
            $script = substr($script, 0, -7);
        }
        // Remove /index.php if it exists
        $script = str_replace('/index.php', '', $script);

        // Fallback for test environments where SCRIPT_NAME might be empty
        if (empty($script)) {
            $script = '/apsdreamhome';
        }

        define('BASE_URL', rtrim("$protocol://$host$script", '/'));
    }
}

// Ensure BASE_URL is always defined as a last resort
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome');
}

if (!defined('WHATSAPP_SERVICE_URL')) {
    define('WHATSAPP_SERVICE_URL', getenv('WHATSAPP_SERVICE_URL') ?: 'http://localhost:3001');
}

// Environment detection
$environment = getenv('APP_ENV') ?: 'development';
if (!defined('APP_ENV')) {
    define('APP_ENV', $environment);
}

// Initialize global configuration array
global $config;
$config = [];

// Load environment-specific configuration
if (file_exists(CONFIG_PATH . '/environments/' . APP_ENV . '.php')) {
    $envConfig = require CONFIG_PATH . '/environments/' . APP_ENV . '.php';
    if (!is_array($envConfig)) {
        error_log("Environment config returned non-array: " . gettype($envConfig));
        $envConfig = [];
    }
    $config = array_merge($config, (array)$envConfig);
}

// Load helper functions first
if (file_exists(CONFIG_PATH . '/helpers.php')) {
    require_once CONFIG_PATH . '/helpers.php';
}

// Load database configuration
if (file_exists(CONFIG_PATH . '/database.php')) {
    try {
        $dbConfig = require CONFIG_PATH . '/database.php';
        if (!is_array($dbConfig)) {
            error_log("Database config returned non-array: " . gettype($dbConfig));
            $dbConfig = [];
        }
        $config = array_merge($config, $dbConfig);
    } catch (Exception $e) {
        error_log("Database config failed to load: " . $e->getMessage());
        // Continue without database config
    }
}

// Load application configuration
if (file_exists(CONFIG_PATH . '/application.php')) {
    $appConfig = require CONFIG_PATH . '/application.php';
    if (!is_array($appConfig)) {
        error_log("Application config returned non-array: " . gettype($appConfig));
        $appConfig = [];
    }
    $config = array_merge($config, $appConfig);
}

// Load security configuration
if (file_exists(CONFIG_PATH . '/security.php')) {
    $securityConfig = require CONFIG_PATH . '/security.php';
    if (is_array($securityConfig)) {
        $config = array_merge($config, $securityConfig);
    }
}

// Include vendor autoloader for PSR interfaces FIRST
if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
}

// Fallback for missing PSR log interface
if (!interface_exists('Psr\Log\LoggerInterface')) {
    require_once APP_PATH . '/Core/LoggerInterfaceFallback.php';
}

// Include core system files
require_once CORE_PATH . '/Autoloader.php';
// Register autoloader
\App\Core\Autoloader::getInstance()->register();

// SessionManager is now autoloaded
// require_once CORE_PATH . '/SessionManager.php';
if (file_exists(CORE_PATH . '/ErrorHandler.php')) {
    require_once CORE_PATH . '/ErrorHandler.php';
}
if (file_exists(CORE_PATH . '/SystemIntegration.php')) {
    require_once CORE_PATH . '/SystemIntegration.php';
}
// Database is autoloaded
// require_once CORE_PATH . '/Database.php';

// Include global helper functions
if (file_exists(APP_PATH . '/helpers.php')) {
    require_once APP_PATH . '/helpers.php';
}

// Validate critical configuration after autoloader is ready
if (class_exists('App\Core\ConfigService')) {
    try {
        $configService = \App\Core\ConfigService::getInstance();
        
        // Validate required secrets in production
        if (defined('APP_ENV') && APP_ENV === 'production') {
            $requiredSecrets = [
                'JWT_SECRET' => 'JWT signing secret',
                'APP_KEY' => 'Application encryption key',
                'DB_PASS' => 'Database password',
                'SMTP_PASS' => 'SMTP password',
            ];
            
            foreach ($requiredSecrets as $key => $description) {
                $value = getenv($key) ?: ($_ENV[$key] ?? '');
                if (empty($value) || $value === 'generate-with-php-artisan-key-generate' || $value === 'your-secret-here') {
                    error_log("CRITICAL CONFIG: Missing or default $key ($description) in production!");
                    // Don't die in production - log and continue, but this should be fixed
                }
            }
        }
        
        // Validate database connection
        $dbConfig = $configService->getDatabaseConfig();
        if (empty($dbConfig['host']) || empty($dbConfig['database'])) {
            error_log("WARNING: Database configuration incomplete");
        }
    } catch (\Throwable $e) {
        error_log("Config validation error: " . $e->getMessage());
    }
}?>