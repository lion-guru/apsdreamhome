<?php
/**
 * Application Bootstrap
 * This file is the entry point for the application bootstrapping process.
 */

// Define the application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

// Load the autoloader
require_once __DIR__ . '/app/core/autoload.php';

// Load the configuration bootstrap
if (file_exists(__DIR__ . '/config/bootstrap.php')) {
    require_once __DIR__ . '/config/bootstrap.php';
}
