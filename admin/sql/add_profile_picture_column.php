<?php
/**
 * Migration script to add profile_picture column to users table
 */

require_once __DIR__ . '/../db_connection.php';

header('Content-Type: text/plain');
echo "Starting migration: Add profile_picture column to users table\n\n";

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Check if column already exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM `users` LIKE 'profile_picture'");
    
    if ($checkColumn->num_rows > 0) {
        echo "Column 'profile_picture' already exists in users table.\n";
    } else {
        // Add the column
        $sql = "ALTER TABLE `users` 
                ADD COLUMN `profile_picture` VARCHAR(255) NULL DEFAULT NULL AFTER `email`,
                ADD INDEX `idx_profile_picture` (`profile_picture`)";
        
        if ($conn->query($sql) === TRUE) {
            echo "Successfully added 'profile_picture' column to users table.\n";
        } else {
            throw new Exception("Error adding column: " . $conn->error);
        }
    }
    
    echo "\nMigration completed successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Provide a link back to the admin panel
echo "\n<a href='../index.php'>Back to Admin Panel</a>";
