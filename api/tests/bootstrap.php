<?php
/**
 * Test bootstrap file
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set default timezone
date_default_timezone_set('UTC');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';
