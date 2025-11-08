<?php
/**
 * Script to update the bookings table to include property_id
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

// Main execution
echo "=== Updating Bookings Table ===\n\n";

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
    
    // 1. Add property_id column if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM `bookings` LIKE 'property_id'");
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        $sql = "ALTER TABLE `bookings` 
                ADD COLUMN `property_id` INT NULL AFTER `id`,
                ADD FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL";
        
        if (!executeQuery($conn, $sql)) {
            throw new Exception("Failed to add property_id column");
        }
        
        echo "Added property_id column to bookings table\n";
    } else {
        echo "property_id column already exists in bookings table\n";
    }
    
    // 2. If there are no properties, create a sample property
    $result = $conn->query("SELECT COUNT(*) as count FROM `properties`");
    $propertyCount = $result->fetch_assoc()['count'];
    
    if ($propertyCount == 0) {
        echo "No properties found. Creating a sample property...\n";
        
        // Create a sample property type if it doesn't exist
        $result = $conn->query("SELECT id FROM `property_types` LIMIT 1");
        if ($result->num_rows == 0) {
            $sql = "INSERT INTO `property_types` (`name`, `icon`) VALUES ('Apartment', 'fa-building')";
            if (!executeQuery($conn, $sql)) {
                throw new Exception("Failed to create sample property type");
            }
            $typeId = $conn->insert_id;
        } else {
            $typeId = $result->fetch_assoc()['id'];
        }
        
        // Create a sample property
        $sql = "INSERT INTO `properties` 
                (`title`, `slug`, `description`, `property_type_id`, `price`, `area_sqft`, `bedrooms`, `bathrooms`, `city`, `status`) 
                VALUES 
                ('Sample Property', 'sample-property', 'This is a sample property', $typeId, 1000000, 1500, 2, 2, 'Sample City', 'available')";
        
        if (!executeQuery($conn, $sql)) {
            throw new Exception("Failed to create sample property");
        }
        
        $propertyId = $conn->insert_id;
        echo "Created sample property with ID: $propertyId\n";
    } else {
        // Get the first property ID
        $result = $conn->query("SELECT id FROM `properties` LIMIT 1");
        $propertyId = $result->fetch_assoc()['id'];
    }
    
    // 3. Update existing bookings to reference the sample property
    $sql = "UPDATE `bookings` SET `property_id` = $propertyId WHERE `property_id` IS NULL";
    if (!executeQuery($conn, $sql)) {
        throw new Exception("Failed to update existing bookings");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\nBookings table has been updated successfully!\n";
    echo "You can now access the admin dashboard without the 'Table not found' error.\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "\nError: " . $e->getMessage() . "\n";
    if (isset($conn) && $conn->error) {
        echo "Database Error: " . $conn->error . "\n";
    }
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>
