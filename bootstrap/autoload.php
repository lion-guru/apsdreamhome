<?php

// Require Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}