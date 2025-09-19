<?php
/**
 * Cleanup Script for Old Databases
 * 
 * This script will drop the old databases that are no longer needed
 * after successful migration to apsdreamhomefinal
 */

// Configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'databases_to_drop' => [
        'aps_dream_home',
        'apsdreamhome',
        'apsdreamhomes'
    ]
];

// Function to log messages
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

// Create connection
$conn = new mysqli($config['host'], $config['user'], $config['pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

log_message("Starting database cleanup process");
log_message("=======================================\n");

// Process each database to drop
foreach ($config['databases_to_drop'] as $db) {
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '$db'");
    
    if ($result && $result->num_rows > 0) {
        log_message("Dropping database: $db");
        
        // Drop the database
        if ($conn->query("DROP DATABASE `$db`")) {
            log_message("  - Successfully dropped database: $db");
        } else {
            log_message("  - Error dropping database $db: " . $conn->error);
        }
    } else {
        log_message("Database $db does not exist, skipping...");
    }
}

log_message("\nCleanup process completed!");

// Close connection
$conn->close();
?>
