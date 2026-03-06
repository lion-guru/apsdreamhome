<?php
/**
 * APS Dream Home - Base New System
 * Unified base system for all project functionality
 * Created: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Define constants
define('APS_ROOT', dirname(__FILE__));
define('APS_APP', APS_ROOT . '/app');
define('APS_PUBLIC', APS_ROOT . '/public');
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_VENDOR', APS_ROOT . '/vendor');
define('APS_ASSETS', APS_PUBLIC . '/assets');

// Environment detection
$environment = $_ENV['APP_ENV'] ?? 'development';
define('APS_ENV', $environment);

// Base URL configuration
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/apsdreamhome/public';
define('BASE_URL', $baseUrl);

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'apsdreamhome');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_PATH', '/');

// Error reporting based on environment
if (APS_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Autoloader
if (file_exists(APS_VENDOR . '/autoload.php')) {
    require_once APS_VENDOR . '/autoload.php';
} else {
    // Fallback autoloader
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = APS_APP . '/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

// Core functions
function aps_log($message, $level = 'info') {
    $logFile = APS_ROOT . '/logs/aps_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

function aps_config($key, $default = null) {
    $configFile = APS_CONFIG . '/app.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
        return $config[$key] ?? $default;
    }
    return $default;
}

function aps_asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function aps_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function aps_redirect($path, $statusCode = 302) {
    $url = aps_url($path);
    header("Location: {$url}", true, $statusCode);
    exit;
}

function aps_view($view, $data = [], $layout = 'base_new') {
    $viewPath = APS_APP . '/views/' . str_replace('.', '/', $view) . '.php';
    $layoutPath = APS_APP . '/views/layouts/' . $layout . '.php';
    
    if (!file_exists($viewPath)) {
        throw new Exception("View not found: {$view}");
    }
    
    // Extract data for view
    extract($data);
    
    // Capture view content
    ob_start();
    include $viewPath;
    $content = ob_get_clean();
    
    // Apply layout if exists
    if (file_exists($layoutPath)) {
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
    
    return $content;
}

// Database connection
function aps_db() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            aps_log("Database connection failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    return $pdo;
}

// Security functions
function aps_sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function aps_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function aps_verify_csrf($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => SESSION_PATH,
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => APS_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Load configuration
if (file_exists(APS_CONFIG . '/app.php')) {
    $config = include APS_CONFIG . '/app.php';
    
    // Override constants with config values
    if (isset($config['timezone'])) {
        date_default_timezone_set($config['timezone']);
    }
    
    if (isset($config['base_url'])) {
        define('BASE_URL', $config['base_url']);
    }
}

// Error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    aps_log($message, 'error');
    
    if (APS_ENV !== 'production') {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>{$message}</div>";
    }
    
    return true;
});

// Exception handler
set_exception_handler(function ($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    aps_log($message, 'critical');
    
    if (APS_ENV === 'production') {
        aps_redirect('error/500');
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'><h3>Fatal Error</h3><pre>{$message}</pre></div>";
    }
});

// Log initialization
aps_log("Base system initialized - Environment: " . APS_ENV);

// Return base system info
return [
    'version' => '2.0.0',
    'environment' => APS_ENV,
    'base_url' => BASE_URL,
    'initialized_at' => date('Y-m-d H:i:s'),
    'paths' => [
        'root' => APS_ROOT,
        'app' => APS_APP,
        'public' => APS_PUBLIC,
        'config' => APS_CONFIG,
        'storage' => APS_STORAGE,
        'vendor' => APS_VENDOR,
        'assets' => APS_ASSETS
    ]
];

?>
