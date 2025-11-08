<?php
/**
 * APS Dream Home - Main Entry Point
 * - Renders homepage at root URL.
 * - Delegates other routes to router.php.
 * - Safe for localhost and subfolder deployments.
 */

// Security constant for downstream includes
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Start secure session early (only once)
if (session_status() === PHP_SESSION_NONE) {
    // Normalize host for cookie domain (strip port)
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    // Avoid setting domain for localhost or empty host (prevents cookie issues on dev)
    $cookieDomain = ($host && !in_array($host, ['localhost', '127.0.0.1'])) ? $host : '';

    // Detect HTTPS (supports proxies)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('APS_DREAM_HOME_SESSID');
    session_set_cookie_params([
        'lifetime' => 86400, // 1 day
        'path'     => '/',
        'domain'   => $cookieDomain ?: null, // null => no Domain attribute
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Build request path without query or fragment
$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
if (false !== ($qPos = strpos($reqUri, '?'))) {
    $reqUri = substr($reqUri, 0, $qPos);
}
if (false !== ($hPos = strpos($reqUri, '#'))) {
    $reqUri = substr($reqUri, 0, $hPos);
}

// Compute base directory for subfolder deployments dynamically.
// Example: http://localhost/apsdreamhomefinal/ -> basePath = '/apsdreamhomefinal'
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir  = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath   = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

// Normalize the path by removing the basePath and trimming slashes
$path = $reqUri;
if ($basePath && stripos($path, $basePath . '/') === 0) {
    $path = substr($path, strlen($basePath));
}
$path = trim($path, '/');
$lowerPath = strtolower($path);

// Determine if homepage should be rendered
$serveHome = ($lowerPath === '' || $lowerPath === 'home' || $lowerPath === 'index.php');

// Route
if ($serveHome) {
    require_once __DIR__ . '/homepage.php';
    exit;
}

// Delegate other routes
$routerFile = __DIR__ . '/router.php';
if (is_file($routerFile)) {
    require_once $routerFile;
    exit;
}

// Fallback 404 if router missing or fails
http_response_code(404);
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>Route not found.</p></body></html>';