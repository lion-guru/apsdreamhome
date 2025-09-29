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

// Process each database to drop using prepared statements
foreach ($config['databases_to_drop'] as $db) {
    // Check if database exists using prepared statement
    $stmt = $conn->prepare("SHOW DATABASES LIKE ?");
    $stmt->bind_param("s", $db);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $stmt->close();
        log_message("Dropping database: $db");

        // Drop the database using prepared statement with proper escaping
        $db_escaped = $conn->real_escape_string($db);
        if ($conn->query("DROP DATABASE `$db_escaped`")) {
            log_message("  - Successfully dropped database: $db");
        } else {
            log_message("  - Failed to drop database: $db - " . $conn->error);
        }
    } else {
        log_message("Database does not exist: $db");
    }
}

log_message("\nCleanup process completed!");

// Close connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
