<?php
// Initialize core application settings and configurations

// Load environment variables
require_once ROOT_PATH . '/.env';

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhome');

// Application settings
define('APP_NAME', 'APS Dream Homes');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/apsdreamhome');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Session configuration
session_start();

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Load core files
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/includes/functions.php';