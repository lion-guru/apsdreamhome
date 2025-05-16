<?php
// Database Configuration File

// Database Connection Parameters
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhomefinal';

// Establish Database Connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database Connection Failed: " . $conn->connect_error);
    }
    
    // Set character set to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Log the error
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display a user-friendly error message
    die("Sorry, we are experiencing technical difficulties. Please try again later.");
}

// Optional: Database security settings
ini_set('display_errors', 0);  // Disable error display in production
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
