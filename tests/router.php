<?php
/**
 * PHP Built-in Server Router for CI and local development.
 *
 * Usage:
 *   php -S 0.0.0.0:8000 -t public tests/router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$publicDir = realpath(__DIR__ . '/../public');
$filePath = $publicDir . $uri;

// If file exists and is not a directory, serve it directly
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Forward all other requests to index.php
require_once $publicDir . '/index.php';
