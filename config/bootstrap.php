<?php
/**
 * APS Dream Home - Main Configuration Bootstrap
 * Organized configuration structure for better maintainability
 */

// Define application constants FIRST
define('APP_NAME', 'APS Dream Home');
define('APP_VERSION', '2.1');
define('APP_ROOT', dirname(__DIR__)); // Parent directory of config folder

// Environment detection
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// Load environment-specific configuration
$config_file = APP_ROOT . '/config/environments/' . ENVIRONMENT . '.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

// Database configuration
require_once APP_ROOT . '/config/database.php';

// Application configuration (now constants are available)
require_once APP_ROOT . '/config/application.php';

// Security configuration
require_once APP_ROOT . '/config/security.php';

// Initialize core systems
require_once APP_ROOT . '/app/bootstrap.php';

// Auto-load classes
require_once APP_ROOT . '/app/core/Autoloader.php';

// Start session with security
$sessionManagerFile = APP_ROOT . '/app/core/SessionManager.php';
if (file_exists($sessionManagerFile)) {
    require_once $sessionManagerFile;
}

// Initialize error handling
$errorHandlerFile = APP_ROOT . '/app/core/ErrorHandler.php';
if (file_exists($errorHandlerFile)) {
    require_once $errorHandlerFile;
}

// Initialize complete system integration
$systemIntegrationFile = APP_ROOT . '/app/core/SystemIntegration.php';
if (file_exists($systemIntegrationFile)) {
    require_once $systemIntegrationFile;
}$coreFunctionsFile = APP_ROOT . '/app/models/CoreFunctions.php';
if (file_exists($coreFunctionsFile)) {
    require_once $coreFunctionsFile;
}

// Initialize application
$app = new App\Core\Application();
$app->initialize();

/**
 * Global helper function for application instance
 */
function app() {
    return App\Core\Application::getInstance();
}

?>
