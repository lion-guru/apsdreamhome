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

// Get credentials from environment variables
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));

// Set appropriate redirect URL based on environment
$protocol = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) ? 'http' : 'https';
$host = $_SERVER['HTTP_HOST'];
$path = '/march2025apssite/google_callback_associate.php';

// Set the redirect URL for associates
define('GOOGLE_REDIRECT_URL_ASSOCIATE', $protocol . '://' . $host . $path);

// Validate configuration
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    error_log('Google OAuth Error: Missing required credentials for associate authentication');
    die('Google OAuth configuration error. Please check the server logs.');
}

// Log OAuth configuration for debugging
error_log('Google OAuth Config (Associate) - Redirect URL: ' . GOOGLE_REDIRECT_URL_ASSOCIATE);