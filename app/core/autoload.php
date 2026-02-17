<?php

/**
 * Autoloader for the application
 * 
 * This file provides an autoloader that follows PSR-4 autoloading standards.
 * It initializes the comprehensive Autoloader class.
 */

// Define APP_ROOT if not already defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

// Load the comprehensive Autoloader class
require_once __DIR__ . '/Autoloader.php';

// Load composer autoloader if it exists
if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
}

// Load application helpers
if (file_exists(APP_ROOT . '/app/helpers.php')) {
    require_once APP_ROOT . '/app/helpers.php';
}
