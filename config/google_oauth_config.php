<?php
/**
 * Google OAuth Configuration
 * 
 * Steps to set up Google OAuth:
 * 1. Go to Google Cloud Console (https://console.cloud.google.com/)
 * 2. Create a new project or select an existing one
 * 3. Enable the Google+ API and Google OAuth API
 * 4. Go to 'Credentials' section
 * 5. Click 'Create Credentials' -> 'OAuth Client ID'
 * 6. Choose 'Web Application' as application type
 * 7. Set up OAuth consent screen if not done already
 * 8. Add authorized redirect URI: [Your domain]/march2025apssite/google_callback.php
 *    - For localhost: http://localhost/march2025apssite/google_callback.php
 *    - For production: https://your-domain.com/march2025apssite/google_callback.php
 * 9. Copy the Client ID and Client Secret and paste them below
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
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
}

// Set appropriate redirect URL based on environment
// Set protocol based on environment
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = ($http_host === 'localhost' || strpos($http_host, '127.0.0.1') !== false) ? 'http' : 'https';
$host = $http_host;
$path = '/march2025apssite/google_callback.php';

// Set the redirect URL
if (!defined('GOOGLE_REDIRECT_URL')) {
    define('GOOGLE_REDIRECT_URL', $protocol . '://' . $host . $path);
}

// Validate configuration
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    error_log('Google OAuth Error: Missing required credentials');
    // Don't die here, just log the error and continue without OAuth
    // This prevents the error message from being output to the browser
}

// Log OAuth configuration for debugging
error_log('Google OAuth Config - Redirect URL: ' . GOOGLE_REDIRECT_URL);