<?php
/**
 * Migration script to add profile_picture column to users table
 */

require_once __DIR__ . '/../core/init.php';

header('Content-Type: text/plain');
echo "Starting migration: Add profile_picture column to users table\n\n";

try {
    // Get database connection
    $db = \App\Core\App::database();
    
    // Check if column already exists
    $checkColumn = $db->fetchOne("SHOW COLUMNS FROM `user` LIKE 'uimage'");
    
    if ($checkColumn) {
        echo "Column 'uimage' already exists in user table.\n";
    } else {
        // Add the column
        $sql = "ALTER TABLE `user` 
                ADD COLUMN `uimage` VARCHAR(300) NULL DEFAULT NULL AFTER `uemail`";
        
        if ($db->execute($sql)) {
            echo "Successfully added 'uimage' column to user table.\n";
        } else {
            throw new Exception("Error adding column");
        }
    }
    
    echo "\nMigration completed successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Provide a link back to the admin panel
echo "\n<a href='../index.php'>Back to Admin Panel</a>";
