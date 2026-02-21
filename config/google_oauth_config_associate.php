<?php
/**
 * Google OAuth Configuration for Associates
 * This file contains specific configuration for associate authentication
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $envVars = parse_ini_file(__DIR__ . '/.env');
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Get credentials from environment variables (only if not already defined)
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
}

// Set appropriate redirect URL based on environment
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = ($http_host === 'localhost' || strpos($http_host, '127.0.0.1') !== false) ? 'http' : 'https';
$host = $http_host;
$path = '/march2025apssite/google_callback_associate.php';

// Set the redirect URL for associates
if (!defined('GOOGLE_REDIRECT_URL_ASSOCIATE')) {
    define('GOOGLE_REDIRECT_URL_ASSOCIATE', $protocol . '://' . $host . $path);
}

// Validate configuration
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    error_log('Google OAuth Error: Missing required credentials for associate authentication');
    // Don't die here, just log the error and continue without OAuth
    // This prevents the error message from being output to the browser
}

// Log OAuth configuration for debugging
error_log('Google OAuth Config (Associate) - Redirect URL: ' . GOOGLE_REDIRECT_URL_ASSOCIATE);