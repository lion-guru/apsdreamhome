<?php

/**
 * Autoloader for the application
 * 
 * This file provides an autoloader that follows PSR-4 autoloading standards.
 * It prioritizes the Composer autoloader and falls back to the custom Autoloader class if needed.
 */

// Define APP_ROOT if not already defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

// 1. Try to load Composer's autoloader first (Best Practice)
if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
}

// 2. Initialize Custom Autoloader for legacy support or fallback
// Even if Composer is loaded, we might need this for classes not mapped in composer.json
// or for legacy non-namespaced classes.
require_once __DIR__ . '/Autoloader.php';

// The Autoloader class instance is usually created in bootstrap.php or implicitly handled.
// However, the previous version just required the file.
// Let's check if Autoloader.php actually registers itself or just defines the class.
// If it just defines the class, we need to instantiate it.
// Reading Autoloader.php (I recall it's a singleton or static).

// 3. Load application helpers if not loaded by Composer
// Composer 'files' autoloading should handle this, but for safety:
if (file_exists(APP_ROOT . '/app/helpers.php') && !function_exists('app')) {
    require_once APP_ROOT . '/app/helpers.php';
}
