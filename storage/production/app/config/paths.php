<?php
/**
 * APS Dream Home - Path Configuration
 * Centralized path definitions and detection
 */

// Prevent direct access
if (!defined('APS_DREAM_HOME_PATHS')) {
    define('APS_DREAM_HOME_PATHS', true);
}

/**
 * Define application root path
 */
if (!defined('APP_ROOT')) {
    $possiblePaths = [
        'C:/xampp/htdocs/apsdreamhome',           // Direct path (Windows)
        'C:\\xampp\\htdocs\\apsdreamhome',        // Direct path (Windows backslashes)
        realpath(__DIR__ . '/..'),                // From config directory
        dirname(__DIR__, 2),                       // Two levels up from config
        $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome',  // Server path
        $_SERVER['DOCUMENT_ROOT'] . '\\apsdreamhome', // Server path (Windows)
    ];
    
    foreach ($possiblePaths as $path) {
        if (!empty($path) && is_dir($path) && file_exists($path . '/app/core/autoload.php')) {
            define('APP_ROOT', rtrim($path, '/\\'));
            break;
        }
    }
    
    // Ultimate fallback
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', 'C:/xampp/htdocs/apsdreamhome');
    }
}

/**
 * Define base path (alias for APP_ROOT)
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', APP_ROOT);
}

/**
 * Define public path
 */
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', APP_ROOT . '/public');
}

/**
 * Define config path
 */
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APP_ROOT . '/config');
}

/**
 * Define app path
 */
if (!defined('APP_PATH')) {
    define('APP_PATH', APP_ROOT . '/app');
}

/**
 * Define vendor path
 */
if (!defined('VENDOR_PATH')) {
    define('VENDOR_PATH', APP_ROOT . '/vendor');
}

/**
 * Define logs path
 */
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', APP_ROOT . '/logs');
}

/**
 * Define assets path
 */
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', PUBLIC_PATH . '/assets');
}

/**
 * Debug function to verify paths
 */
if (!function_exists('debug_paths')) {
    function debug_paths() {
        echo "<h3>🔍 Path Debug Information</h3>";
        echo "<p><strong>APP_ROOT:</strong> " . APP_ROOT . "</p>";
        echo "<p><strong>BASE_PATH:</strong> " . BASE_PATH . "</p>";
        echo "<p><strong>PUBLIC_PATH:</strong> " . PUBLIC_PATH . "</p>";
        echo "<p><strong>CONFIG_PATH:</strong> " . CONFIG_PATH . "</p>";
        echo "<p><strong>APP_PATH:</strong> " . APP_PATH . "</p>";
        echo "<p><strong>VENDOR_PATH:</strong> " . VENDOR_PATH . "</p>";
        echo "<p><strong>LOGS_PATH:</strong> " . LOGS_PATH . "</p>";
        echo "<p><strong>ASSETS_PATH:</strong> " . ASSETS_PATH . "</p>";
        
        // Verify critical files
        $criticalFiles = [
            'Autoloader' => APP_PATH . '/core/autoload.php',
            'App Class' => APP_PATH . '/core/App.php',
            'Database Config' => CONFIG_PATH . '/database.php',
            'Composer Autoload' => VENDOR_PATH . '/autoload.php'
        ];
        
        echo "<h4>📁 Critical Files Verification</h4>";
        foreach ($criticalFiles as $name => $file) {
            $exists = file_exists($file) ? 'EXISTS ✅' : 'NOT FOUND ❌';
            $color = file_exists($file) ? 'green' : 'red';
            echo "<p style='color: $color;'><strong>$name:</strong> $file - $exists</p>";
        }
    }
}

/**
 * Get URL base path
 */
if (!function_exists('get_base_url')) {
    function get_base_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $host = rtrim($host, '.');
        
        // Try to determine the base path from script name
        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        // Remove /public if it exists to get app root URL
        if (basename($script) === 'public') {
            $script = dirname($script);
        }
        
        $script = rtrim($script, '/\\');
        $basePath = $script ? ($script . '/') : '/';
        
        return $protocol . '://' . $host . $basePath;
    }
}

/**
 * Define BASE_URL if not already defined
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', get_base_url());
}

// Log path detection for debugging
if (defined('LOGS_PATH') && is_dir(LOGS_PATH)) {
    $logFile = LOGS_PATH . '/path_debug.log';
    $logEntry = "[" . date('Y-m-d H:i:s') . "] APP_ROOT: " . APP_ROOT . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?>
