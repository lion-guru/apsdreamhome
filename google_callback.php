<?php
/**
 * Google OAuth Callback Handler
 * 
 * This is the registered redirect URI in Google Cloud Console.
 * Apache serves this file directly (it exists at root, so .htaccess passes through).
 */

// Override SCRIPT_NAME so bootstrap computes correct BASE_URL
$_SERVER['SCRIPT_NAME'] = '/apsdreamhome/index.php';

// Bootstrap the application framework
require_once __DIR__ . '/config/bootstrap.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure environment variables (.env) are loaded before using ConfigService
\App\Core\ConfigService::getInstance();

// Get the controller
$controller = new \App\Http\Controllers\Auth\GoogleAuthController();

// Handle the callback
$controller->callback();?>