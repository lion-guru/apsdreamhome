<?php
/**
 * Application Constants
 * 
 * This file contains all the global constants used throughout the application
 */

// Site Information
if (!defined('SITE_NAME')) define('SITE_NAME', 'APS Dream Home');
if (!defined('SITE_URL')) define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'info@apsdreamhome.com');
if (!defined('SITE_PHONE')) define('SITE_PHONE', '+91 1234567890');
if (!defined('SITE_ADDRESS')) define('SITE_ADDRESS', '123 Dream Avenue, City, State, 123456');

// Paths
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(dirname(__DIR__)));
if (!defined('INCLUDES_PATH')) define('INCLUDES_PATH', BASE_PATH . '/includes');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', BASE_PATH . '/uploads');

// URLs
if (!defined('ASSETS_URL')) define('ASSETS_URL', SITE_URL . '/assets');
if (!defined('UPLOADS_URL')) define('UPLOADS_URL', SITE_URL . '/uploads');

// Defaults
if (!defined('DEFAULT_TIMEZONE')) define('DEFAULT_TIMEZONE', 'Asia/Kolkata');
if (!defined('DEFAULT_LOCALE')) define('DEFAULT_LOCALE', 'en_IN');

// Session
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'aps_dream_home');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 86400); // 24 hours

// Security
if (!defined('CSRF_TOKEN_LENGTH')) define('CSRF_TOKEN_LENGTH', 32);
if (!defined('PASSWORD_HASH_COST')) define('PASSWORD_HASH_COST', 12);

// End of file constants.php
