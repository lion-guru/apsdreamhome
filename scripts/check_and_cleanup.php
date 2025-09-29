<?php
/**
 * Database Check and Cleanup Script
 * 
 * This script will check for specified databases and drop them if they exist
 */

// Configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'databases_to_check' => [
        'realestate_new',
        'realestatephp',
        'march2025apssite',
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

log_message("Starting database check and cleanup process");
log_message("==========================================\n");

// First, list all databases to verify
log_message("Current databases on the server:");
log_message("--------------------------------");
$result = $conn->query("SHOW DATABASES");
$all_databases = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_row()) {
        $db_name = $row[0];
        $all_databases[] = $db_name;
        log_message("- $db_name");
    }
}

log_message("\nChecking databases to clean up:");
log_message("----------------------------");

// Process each database to check and drop if exists
foreach ($config['databases_to_check'] as $db) {
    if (in_array($db, $all_databases)) {
        log_message("Dropping database: $db");
        
        // Drop the database
        if ($stmt = $conn->prepare("DROP DATABASE `?);
    
    $stmt->execute();
    $result = $stmt->get_result();")) {
            log_message("  ✅ Successfully dropped database: $db");
        } else {
            log_message("  ❌ Error dropping database $db: " . $conn->error);
        }
    } else {
        log_message("  ℹ️  Database $db does not exist, skipping...");
    }
}

log_message("\nCleanup process completed!");

// Close connection
$conn->close();
?>
