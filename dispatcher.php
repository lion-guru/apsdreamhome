<?php

/**
 * Enhanced Application Dispatcher
 * Main entry point for the new routing system
 */

// Debug: Log that dispatcher is being called
error_log("DEBUG: dispatcher.php called with REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set'));
error_log("DEBUG: dispatcher.php called with REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set'));

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants for the application (only if not already defined)
if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__);
if (!defined('CONFIG_DIR')) define('CONFIG_DIR', APP_ROOT . '/app/config');
if (!defined('CORE_DIR')) define('CORE_DIR', APP_ROOT . '/app/core');
if (!defined('CONTROLLERS_DIR')) define('CONTROLLERS_DIR', APP_ROOT . '/app/controllers');
if (!defined('MODELS_DIR')) define('MODELS_DIR', APP_ROOT . '/app/models');
if (!defined('VIEWS_DIR')) define('VIEWS_DIR', APP_ROOT . '/app/views');
if (!defined('PUBLIC_DIR')) define('PUBLIC_DIR', APP_ROOT . '/public');
if (!defined('UPLOADS_DIR')) define('UPLOADS_DIR', PUBLIC_DIR . '/uploads');

// Define application constants that are used throughout the application (only if not already defined)
if (!defined('APP_NAME')) define('APP_NAME', 'APS Dream Home');
if (!defined('APP_VERSION')) define('APP_VERSION', '2.1');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// Load environment variables
$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Autoloader function
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $class = str_replace('\\', '/', $class);
    $file = APP_ROOT . '/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    
    // Try alternative paths
    $paths = [
        APP_ROOT . '/app/' . $class . '.php',
        APP_ROOT . '/app/core/' . $class . '.php',
        APP_ROOT . '/app/controllers/' . $class . '.php',
        APP_ROOT . '/app/models/' . $class . '.php',
        APP_ROOT . '/app/config/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load helper functions
try {
    require_once APP_ROOT . '/app/Helpers/env.php';
    error_log("DEBUG: Helper functions loaded successfully");
} catch (Exception $e) {
    die("Error loading helper functions: " . $e->getMessage());
}

// Load core files
try {
    error_log("DEBUG: Loading core files from: " . CORE_DIR);
    error_log("DEBUG: Route.php path: " . CORE_DIR . '/Route.php');
    error_log("DEBUG: Route.php exists: " . (file_exists(CORE_DIR . '/Route.php') ? 'yes' : 'no'));
    
    require_once CONFIG_DIR . '/database.php';
    require_once CORE_DIR . '/DatabaseManager.php';
    require_once CORE_DIR . '/Route.php';
    require_once CORE_DIR . '/Router.php';
    require_once CORE_DIR . '/Middleware.php';
    require_once CORE_DIR . '/Middleware/AuthMiddleware.php';
    require_once CORE_DIR . '/Middleware/RoleMiddleware.php';
    require_once CORE_DIR . '/Middleware/CsrfMiddleware.php';
    require_once CORE_DIR . '/Middleware/ErrorMiddleware.php';
    
    error_log("DEBUG: Core files loaded successfully");
    error_log("DEBUG: Route class exists: " . (class_exists('App\Core\Route') ? 'yes' : 'no'));
} catch (Exception $e) {
    die("Error loading core files: " . $e->getMessage());
}

// Initialize error handling - ErrorMiddleware will be used in the middleware pipeline
// Set up basic error reporting
error_reporting(E_ALL);
if (getenv('APP_DEBUG') === 'true') {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}
ini_set('log_errors', 1);

// Initialize error middleware
$errorMiddleware = new App\Core\Middleware\ErrorMiddleware();

// Initialize database
try {
    $dbConfig = App\Config\DatabaseConfig::getInstance();
    $dbManager = App\Core\DatabaseManager::getInstance($dbConfig);
} catch (Exception $e) {
    $errorMiddleware->handleException($e);
    exit;
}

// Create router instance
$router = new App\Core\Router();

// Load route configuration
try {
    $routeConfig = require CORE_DIR . '/routes.php';
    $routeConfig($router);
} catch (Exception $e) {
    $errorMiddleware->handleException($e);
    exit;
}

// Cache routes in production
if (getenv('APP_ENV') === 'production') {
    $router->cacheRoutes(APP_ROOT . '/storage/cache/routes.cache');
}

// Get current request information
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_SERVER['REQUEST_URI'] ?? '/';

// Remove query string and normalize path
$path = strtok($path, '?');
$originalPath = $path;
$path = rtrim($path, '/') ?: '/';

// Remove base path if present (e.g., /apsdreamhome)
$basePath = '/apsdreamhomefinal';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
    $path = rtrim($path, '/') ?: '/';
}

// Debug output
error_log("DEBUG DISPATCHER: Original URI: " . $originalPath);
error_log("DEBUG DISPATCHER: Processed path: " . $path);
error_log("DEBUG DISPATCHER: Method: " . $method);

// Dispatch the request
try {
    error_log("DEBUG DISPATCHER: About to dispatch to router");
    $response = $router->dispatch($method, $path);
    error_log("DEBUG DISPATCHER: Router dispatch completed, response type: " . gettype($response));
    
    // Handle different response types
    if ($response instanceof \App\Core\Http\Response) {
        // Response object - send it directly
        error_log("DEBUG DISPATCHER: Sending Response object");
        $response->send();
    } elseif (is_array($response) || is_object($response)) {
        // JSON response
        error_log("DEBUG DISPATCHER: Sending JSON response");
        header('Content-Type: application/json');
        echo json_encode($response);
    } elseif (is_string($response)) {
        // HTML response
        error_log("DEBUG DISPATCHER: Sending string response");
        if (strpos($response, '<!DOCTYPE') === 0 || strpos($response, '<html') === 0) {
            header('Content-Type: text/html; charset=UTF-8');
        } else {
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo $response;
    } elseif (is_bool($response)) {
        // Boolean response
        error_log("DEBUG DISPATCHER: Sending boolean response");
        header('Content-Type: application/json');
        echo json_encode(['success' => $response]);
    } elseif (is_null($response)) {
        // Null response - 204 No Content
        error_log("DEBUG DISPATCHER: Sending null response (204)");
        http_response_code(204);
    } else {
        // Unknown response type
        error_log("DEBUG DISPATCHER: Sending unknown response type");
        header('Content-Type: application/json');
        echo json_encode(['data' => $response]);
    }
    
} catch (App\Core\Exceptions\NotFoundException $e) {
    // Handle 404 Not Found
    error_log("DEBUG DISPATCHER: NotFoundException caught: " . $e->getMessage());
    http_response_code(404);
    if (strpos($path, '/api/') === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found', 'message' => $e->getMessage()]);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        include VIEWS_DIR . '/errors/404.php';
    }
} catch (App\Core\Exceptions\UnauthorizedException $e) {
    // Handle 401 Unauthorized
    error_log("DEBUG DISPATCHER: UnauthorizedException caught: " . $e->getMessage());
    http_response_code(401);
    if (strpos($path, '/api/') === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized', 'message' => $e->getMessage()]);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        include VIEWS_DIR . '/errors/401.php';
    }
} catch (App\Core\Exceptions\ForbiddenException $e) {
    // Handle 403 Forbidden
    error_log("DEBUG DISPATCHER: ForbiddenException caught: " . $e->getMessage());
    http_response_code(403);
    if (strpos($path, '/api/') === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden', 'message' => $e->getMessage()]);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        include VIEWS_DIR . '/errors/403.php';
    }
} catch (Exception $e) {
    // Handle general exceptions
    error_log("DEBUG DISPATCHER: General Exception caught: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    $errorMiddleware = new App\Core\Middleware\ErrorMiddleware();
    $errorMiddleware->handleException($e);
}

// Log performance metrics
if (getenv('APP_DEBUG') === 'true') {
    $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
    
    error_log(sprintf(
        "Request: %s %s | Time: %.3fs | Memory: %.2fMB | DB Queries: %d",
        $method,
        $path,
        $executionTime,
        $memoryUsage,
        $dbManager->getPerformanceStats()['total_queries'] ?? 0
    ));
}