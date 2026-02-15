<?php
/**
 * APS Dream Home - Modern Entry Point
 * 
 * This is the new main entry point that uses the modern routing system
 * while maintaining backward compatibility
 */

// Security check - prevent direct access
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Start session with enhanced security
if (session_status() === PHP_SESSION_NONE) {
    session_name('APS_DREAM_HOME_SESSID');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Define base paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Define base URL with subfolder support
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $domainName . $scriptPath;

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($base_url, '/'));
}

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Load modern application bootstrap
require_once APP_PATH . '/bootstrap.php';

/**
 * Modern Application Bootstrap
 */
try {
    // Initialize the application
    $app = new App();
    
    // Set up the application
    $app->bootstrap();
    
    // Create enhanced route loader
    require_once ROOT_PATH . '/modern_router.php';
    
    // The modern_router.php will handle the routing
    
} catch (Exception $e) {
    // Handle bootstrap errors
    error_log("Application bootstrap failed: " . $e->getMessage());
    
    // Show user-friendly error page
    http_response_code(500);
    echo '<!DOCTYPE html>
<html>
<head>
    <title>Application Error</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #ff6b6b; 
            margin-bottom: 20px;
        }
        .back-link { 
            color: #4ecdc4; 
            text-decoration: none; 
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #4ecdc4;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: #4ecdc4;
            color: white;
        }
        .error-details {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Application Error</h1>
        <p>We apologize, but the application encountered an error.</p>
        <div class="error-details">' . htmlspecialchars($e->getMessage()) . '</div>
        <p><a href="' . BASE_URL . '" class="back-link">Go back to homepage</a></p>
    </div>
</body>
</html>';
    exit;
}