<?php
/**
 * Centralized Database Configuration
 * This file provides a single source of truth for database credentials
 * across the entire APS Dream Homes application.
 */

// Load environment variables if .env file exists
if (file_exists(dirname(__DIR__, 2) . '/.env')) {
    $env_lines = file(dirname(__DIR__, 2) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Database configuration with fallback to development values
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhomefinal');

/**
 * Get database connection with error handling
 * @return mysqli|null Database connection or null on failure
 */
function getDbConnection() {
    static $connection;
    
    // If we already have a connection, return it
    if ($connection !== null) {
        return $connection;
    }
    
    // Create new connection
    try {
        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$connection) {
            error_log("Failed to connect to MySQL: " . mysqli_connect_error());
            return null;
        }
        
        // Set character set
        if (!mysqli_set_charset($connection, "utf8mb4")) {
            error_log("Error setting character set: " . mysqli_error($connection));
            return null;
        }
        
        // Set timezone
        $timezone = date_default_timezone_get();
        mysqli_query($connection, "SET time_zone = '$timezone'");
        
        return $connection;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Create global connection variables for backward compatibility
$conn = getDbConnection();