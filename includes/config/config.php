<?php
/**
 * APS Dream Home - Main Configuration
 * 
 * This file loads the main configuration and sets up the application environment
 */

// Define the secure config file path
define('SECURE_CONFIG_PATH', __DIR__ . '/secure-config.php');

// Check if secure config exists
if (!file_exists(SECURE_CONFIG_PATH)) {
    // Try to create default secure config if it doesn't exist
    $defaultConfig = "<?php\n/**\n * Secure Configuration\n * This file is auto-generated. Please configure your settings below.\n */\n\n";
    $defaultConfig .= "// Database Configuration\n";
    $defaultConfig .= "define('DB_HOST', 'localhost');\n";
    $defaultConfig .= "define('DB_USER', 'root');\n";
    $defaultConfig .= "define('DB_PASS', '');\n";
    $defaultConfig .= "define('DB_NAME', 'apsdreamhomefinal');\n";
    $defaultConfig .= "define('DB_PORT', '3306');\n\n";
    $defaultConfig .= "// Application Security\n";
    $defaultConfig .= "define('APP_KEY', '" . bin2hex(random_bytes(32)) . "');\n";
    $defaultConfig .= "define('ENCRYPTION_KEY', '" . bin2hex(random_bytes(32)) . "');\n";
    
    if (file_put_contents(SECURE_CONFIG_PATH, $defaultConfig) === false) {
        die('Failed to create secure configuration file. Please check directory permissions.');
    }
    // Set secure permissions
    @chmod(SECURE_CONFIG_PATH, 0600);
}

// Load secure configuration
require_once SECURE_CONFIG_PATH;

// Load constants
require_once __DIR__ . '/constants.php';

// Load helper functions
require_once __DIR__ . '/../helpers/url_helpers.php';

// Set error reporting based on environment
$isDevEnv = (getenv('APP_ENV') === 'development') || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);
if ($isDevEnv) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
$appTz = getenv('APP_TIMEZONE');
if (!$appTz && function_exists('defined') && defined('APP_TIMEZONE')) {
    $appTz = constant('APP_TIMEZONE');
}
date_default_timezone_set($appTz ?: 'UTC');

// Initialize configuration array with default values
$config = [
    'app' => [
        'env' => 'development', // Change to 'production' in production
        'debug' => true,
        'timezone' => 'Asia/Kolkata',
        'url' => 'http://localhost/apsdreamhomefinal',
        'name' => 'APS Dream Home',
        'admin_email' => 'admin@apsdreamhome.com',
        'support_email' => 'support@apsdreamhome.com',
        'from_email' => 'noreply@apsdreamhome.com',
        'from_name' => 'APS Dream Home',
    ],
    'db' => [
        'host' => 'localhost',
        'database' => 'apsdreamhomefinal',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    'mail' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
    ],
    'recaptcha' => [
        'site_key' => '',
        'secret_key' => '',
    ],
];

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Optionally load external secure configuration (if present)
$secureConfigPath = 'C:\\xampp\\secure_config\\apsdream_config.php';
if (file_exists($secureConfigPath)) {
    $externalConfig = require $secureConfigPath;
    if (is_array($externalConfig)) {
        // Merge external config into existing defaults
        $config = array_replace_recursive($config, $externalConfig);
    }
} else {
    // Do not hard fail; continue with defaults and generated secure-config.php
    error_log('Warning: External secure configuration not found at ' . $secureConfigPath . '. Using defaults.');
}

// Define application constants
define('APP_ROOT', dirname(__DIR__, 2));
define('INCLUDES_DIR', APP_ROOT . '/includes');
define('UPLOADS_DIR', APP_ROOT . '/uploads');

// Set error reporting based on environment
if ($config['app']['env'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Database connection
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        global $config;
        
        $db = $config['database'];
        
        $this->connection = new mysqli(
            $db['host'],
            $db['username'],
            $db['password'],
            $db['name'],
            $db['port']
        );

        if ($this->connection->connect_error) {
            error_log("Database connection failed: " . $this->connection->connect_error);
            throw new Exception('Database connection failed. Please try again later.');
        }

        $this->connection->set_charset($db['charset']);
        
        // Set strict SQL mode
        $this->connection->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Session configuration
function startSecureSession($sessionName) {
    global $config;
    
    $session = $config['session'];
    
    // Set session cookie parameters
    session_name($session['name']);
    session_set_cookie_params([
        'lifetime' => $session['lifetime'],
        'path' => '/',
        'domain' => parse_url($config['app']['url'], PHP_URL_HOST),
        'secure' => $session['secure'],
        'httponly' => $session['httponly'],
        'samesite' => $session['samesite']
    ]);
    
    // Start the session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $config;
    
    // Ensure config is an array
    if (!is_array($config)) {
        $config = [];
    }
    
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    $message = "$errorType: $errstr in $errfile on line $errline";
    
    error_log($message);
    
    // Default to development mode if config not loaded
    $isDev = isset($config['app']['env']) && $config['app']['env'] === 'development';
    
    if ($isDev) {
        echo "<div style='color:red;padding:10px;margin:10px;border:1px solid #f00;'>$message</div>";
    }
    
    return true;
});

// Exception handler
set_exception_handler(function($e) {
    global $config;
    
    // Ensure config is an array
    if (!is_array($config)) {
        $config = [];
    }
    
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Default to development mode if config not loaded
    $isDev = isset($config['app']['env']) && $config['app']['env'] === 'development';
    
    if ($isDev) {
        echo "<div style='color:red;padding:10px;margin:10px;border:1px solid #f00;'>"
           . "<h3>Uncaught Exception</h3>"
           . "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>"
           . "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>"
           . "<p><strong>Line:</strong> " . $e->getLine() . "</p>"
           . "<h4>Stack Trace:</h4><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>"
           . "</div>";
    } else {
        // In production, show a generic error page
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        if (defined('INCLUDES_DIR') && is_dir(INCLUDES_DIR . '/templates/errors/')) {
            include INCLUDES_DIR . '/templates/errors/500.php';
        } else {
            echo '<h1>500 Internal Server Error</h1>';
            echo '<p>An error occurred while processing your request.</p>';
        }
    }
});

// Shutdown function to catch fatal errors
register_shutdown_function(function() {
    global $config;
    
    // Ensure config is an array
    if (!is_array($config)) {
        $config = [];
    }
    
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        error_log("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        // Default to development mode if config not loaded
        $isDev = isset($config['app']['env']) && $config['app']['env'] === 'development';
        
        if ($isDev) {
            echo "<div style='color:red;padding:10px;margin:10px;border:1px solid #f00;'>"
               . "<h2>Fatal Error</h2>"
               . "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>"
               . "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . "</p>"
               . "<p><strong>Line:</strong> " . $error['line'] . "</p>"
               . "</div>";
        } else {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            include INCLUDES_DIR . '/templates/errors/500.php';
        }
    }
});

// Security headers
function sendSecurityHeaders() {
    global $config;
    
    // Only send headers if headers haven't been sent yet
    if (headers_sent()) {
        return;
    }
    
    // X-Frame-Options
    header('X-Frame-Options: SAMEORIGIN');
    
    // X-Content-Type-Options
    header('X-Content-Type-Options: nosniff');
    
    // X-XSS-Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy
    $csp = [
        "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'",
        "script-src 'self' https: 'unsafe-inline' 'unsafe-eval'",
        "style-src 'self' https: 'unsafe-inline'",
        "img-src 'self' data: https: http: blob:",
        "font-src 'self' https: data:",
        "connect-src 'self' https: wss:",
        "frame-src 'self' https:",
        "media-src 'self' https: data:",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'"
    ];
    
    header("Content-Security-Policy: " . implode("; ", $csp));
    
    // Strict-Transport-Security (HSTS) - Only on HTTPS
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Referrer-Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions-Policy
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// Send security headers
sendSecurityHeaders();

// CSRF Protection
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
        return true;
    }
}

// Initialize CSRF token if not exists
if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['csrf_token'])) {
    generateCSRFToken();
}
