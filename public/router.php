<?php
/**
 * Router script for PHP built-in server (CI/testing only).
 * Usage: php -S 0.0.0.0:8000 -t public public/router.php
 *
 * Serves static files directly; routes everything else through index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Serve existing static files directly (return false = let built-in server handle it)
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Route everything else through the app
require __DIR__ . '/index.php';
