<?php
/**
 * Script to add missing tables to the APS Dream Home database
 */

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

// Function to execute SQL queries
function executeQuery($conn, $sql) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "Query executed successfully\n";
            return true;
        } else {
            echo "Error executing query: " . $conn->error . "\n";
            return false;
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main function to add missing tables
function addMissingTables($conn) {
    $success = true;
    
    // 1. Check and create admin_activity_log table
    $sql = "CREATE TABLE IF NOT EXISTS `admin_activity_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `admin_id` INT,
        `username` VARCHAR(50),
        `role` VARCHAR(20),
        `action` VARCHAR(100) NOT NULL,
        `details` TEXT,
        `ip_address` VARCHAR(45),
        `user_agent` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (admin_id),
        INDEX (action)
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 2. Check and create associates table
    $sql = "CREATE TABLE IF NOT EXISTS `associates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100),
        `email` VARCHAR(100),
        `phone` VARCHAR(20),
        `parent_id` INT,
        `commission_percent` DECIMAL(5,2),
        `level` INT DEFAULT 1,
        `status` ENUM('active','inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`parent_id`) REFERENCES `associates`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 3. Check and create associate_levels table
    $sql = "CREATE TABLE IF NOT EXISTS `associate_levels` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `level` INT NOT NULL,
        `commission_percent` DECIMAL(5,2) NOT NULL,
        `description` VARCHAR(255)
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 4. Check and create projects table
    $sql = "CREATE TABLE IF NOT EXISTS `projects` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `city` VARCHAR(50),
        `status` ENUM('active','inactive','completed') DEFAULT 'active',
        `land_purchase_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`land_purchase_id`) REFERENCES `land_purchases`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 5. Check and create plots table
    $sql = "CREATE TABLE IF NOT EXISTS `plots` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT NOT NULL,
        `plot_no` VARCHAR(50) NOT NULL,
        `size_sqft` DECIMAL(10,2),
        `status` ENUM('available','booked','sold','rented','resale') DEFAULT 'available',
        `customer_id` INT,
        `associate_id` INT,
        `sale_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 6. Check and create bookings table (main issue)
    $sql = "CREATE TABLE IF NOT EXISTS `bookings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `plot_id` INT NOT NULL,
        `customer_id` INT NOT NULL,
        `associate_id` INT,
        `booking_date` DATE,
        `status` ENUM('booked','cancelled','completed') DEFAULT 'booked',
        `amount` DECIMAL(15,2),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`plot_id`) REFERENCES `plots`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 7. Check and create leads table
    $sql = "CREATE TABLE IF NOT EXISTS `leads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100),
        `phone` VARCHAR(20),
        `source` VARCHAR(50),
        `status` VARCHAR(50) DEFAULT 'new',
        `assigned_to` INT,
        `notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    // 8. Check and create property_visits table
    // First, check if properties table exists
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result && $result->num_rows > 0) {
        $sql = "CREATE TABLE IF NOT EXISTS `property_visits` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `property_id` INT NOT NULL,
            `customer_id` INT NOT NULL,
            `visit_date` DATE,
            `visit_time` TIME,
            `status` ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
            `notes` TEXT,
            `created_by` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB;";
        $success = $success && executeQuery($conn, $sql);
    } else {
        echo "Warning: 'properties' table not found. Skipping property_visits table creation.\n";
        // Don't execute any query
    }

    // 9. Check and create mlm_commissions table
    $sql = "CREATE TABLE IF NOT EXISTS `mlm_commissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `associate_id` INT NOT NULL,
        `booking_id` INT NOT NULL,
        `level` INT NOT NULL,
        `commission_amount` DECIMAL(15,2) NOT NULL,
        `status` ENUM('pending','paid','cancelled') DEFAULT 'pending',
        `payment_date` DATE,
        `notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);

    return $success;
}

// Main execution
echo "Starting database table creation...\n";

try {
    // Get database connection
    $conn = getDbConnection();
    
    if ($conn === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Begin transaction
    $conn->begin_transaction();
    
    echo "Adding missing tables...\n";
    $success = addMissingTables($conn);
    
    if ($success) {
        $conn->commit();
        echo "\nAll tables created successfully!\n";
    } else {
        $conn->rollback();
        echo "\nError creating tables. Changes have been rolled back.\n";
    }
    
    // Close the connection
    $conn->close();
    
} catch (Exception $e) {
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Script execution completed.\n";
?>
