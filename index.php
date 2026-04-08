<?php
/**
 * APS Dream Home - Entry Point
 */

// Check if running PHP built-in server (no Apache)
if (php_sapi_name() === 'cli-server') {
    // Directly serve from public directory
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    
    // If file exists in public, serve it
    if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
        return false;
    }
    
    // Otherwise include public/index.php
    require_once __DIR__ . '/public/index.php';
    return;
}

// For Apache - redirect to public/
header("Location: public/");
exit();
?>