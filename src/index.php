<?php
// This file serves as the main entry point for the src directory
// It will handle autoloading and bootstrapping of the application

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path constants
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', __DIR__);

// Load configuration
require_once ROOT_PATH . '/vendor/autoload.php';

// Initialize application
require_once SRC_PATH . '/config/init.php';