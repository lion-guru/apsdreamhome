<?php
// Unified bootstrap for API endpoints

// 1) Composer autoload (prefer local vendor, then root), otherwise fallback PSR-4 for App\
$__autoload_local = __DIR__ . '/../vendor/autoload.php';
$__autoload_root  = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($__autoload_local)) {
    require_once $__autoload_local;
} elseif (file_exists($__autoload_root)) {
    // Guard against incomplete root vendor (e.g., missing guzzle functions)
    $maybeGuzzle = dirname($__autoload_root) . '/guzzlehttp/guzzle/src/functions_include.php';
    if (file_exists($maybeGuzzle)) {
        require_once $__autoload_root;
    }
}
if (!class_exists('Composer\\Autoload\\ClassLoader')) {
    // Fallback PSR-4 autoloader for App\ mapped to api/includes/
    spl_autoload_register(function ($class) {
        if (strpos($class, 'App\\') !== 0) {
            return;
        }
        $baseDir = __DIR__ . '/'; // points to api/includes/
        $relative = str_replace('App\\', '', $class);
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });
}

// 2) JSON helper (so json_response() is always available)
$__json_helper = __DIR__ . '/Common/Helpers/json.php';
if (file_exists($__json_helper)) {
    require_once $__json_helper;
}

// 3) Error reporting (development-friendly default)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// 4) Timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
}

// 5) Session start (skip for CLI)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// 6) Default JSON header (only set if not already sent)
if (!headers_sent()) {
    header('Content-Type: application/json');
}
