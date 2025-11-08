<?php
/**
 * Setup script for password reset functionality
 * Creates the necessary database table if it doesn't exist
 */

require_once 'db_connection.php';

header('Content-Type: text/plain');
echo "Setting up password reset functionality...\n\n";

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Check if table already exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'password_resets'");
    
    if ($checkTable->num_rows > 0) {
        echo "Password resets table already exists.\n";
    } else {
        // Create the table
        $sql = "
        CREATE TABLE `password_resets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `token` varchar(255) NOT NULL,
            `created_at` datetime NOT NULL,
            `expires_at` datetime NOT NULL,
            `used` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `email` (`email`),
            KEY `token` (`token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        if ($conn->query($sql) === TRUE) {
            echo "Password resets table created successfully.\n";
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }
    
    // Check if users table has the required fields
    $checkUsersTable = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($checkUsersTable->num_rows === 0) {
        echo "WARNING: 'users' table does not have an 'email' column. Password reset requires this column.\n";
    }
    
    echo "\nPassword reset setup completed successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Provide a link back to the admin panel
echo "\n<a href='index.php'>Back to Admin Panel</a>";
