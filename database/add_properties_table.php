<?php
/**
 * Script to add the properties table and related tables
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

// Main function to add properties table and related tables
function addPropertiesTable($conn) {
    $success = true;
    
    // 1. Create property_types table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `property_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL,
        `description` TEXT,
        `icon` VARCHAR(100),
        `status` ENUM('active','inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);
    
    // 2. Create property_features table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `property_features` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `icon` VARCHAR(100),
        `status` ENUM('active','inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);
    
    // 3. Create properties table
    $sql = "CREATE TABLE IF NOT EXISTS `properties` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `property_type_id` INT,
        `price` DECIMAL(15,2) NOT NULL,
        `area_sqft` DECIMAL(10,2),
        `bedrooms` INT,
        `bathrooms` INT,
        `address` TEXT,
        `city` VARCHAR(100),
        `state` VARCHAR(100),
        `country` VARCHAR(100),
        `postal_code` VARCHAR(20),
        `latitude` DECIMAL(10,8),
        `longitude` DECIMAL(11,8),
        `status` ENUM('available','sold','reserved','under_construction') DEFAULT 'available',
        `featured` TINYINT(1) DEFAULT 0,
        `hot_offer` TINYINT(1) DEFAULT 0,
        `created_by` INT,
        `updated_by` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`property_type_id`) REFERENCES `property_types`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
        INDEX (`status`),
        INDEX (`featured`),
        INDEX (`hot_offer`),
        FULLTEXT (`title`, `description`, `address`)
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);
    
    // 4. Create property_images table
    $sql = "CREATE TABLE IF NOT EXISTS `property_images` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `image_path` VARCHAR(255) NOT NULL,
        `is_primary` TINYINT(1) DEFAULT 0,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        INDEX (`property_id`, `is_primary`, `sort_order`)
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);
    
    // 5. Create property_feature_mappings table
    $sql = "CREATE TABLE IF NOT EXISTS `property_feature_mappings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `feature_id` INT NOT NULL,
        `value` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`feature_id`) REFERENCES `property_features`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `property_feature` (`property_id`, `feature_id`)
    ) ENGINE=InnoDB;";
    $success = $success && executeQuery($conn, $sql);
    
    return $success;
}

// Function to seed sample data
function seedSampleData($conn) {
    // Skip if properties table already has data
    $result = $conn->query("SELECT COUNT(*) as count FROM properties");
    if ($result && $result->fetch_assoc()['count'] > 0) {
        echo "Properties table already has data. Skipping sample data.\n";
        return true;
    }
    
    // Sample property types
    $propertyTypes = [
        ['name' => 'Apartment', 'icon' => 'fa-building'],
        ['name' => 'Villa', 'icon' => 'fa-home'],
        ['name' => 'Office', 'icon' => 'fa-briefcase'],
        ['name' => 'Shop', 'icon' => 'fa-store'],
        ['name' => 'Land', 'icon' => 'fa-map-marked-alt']
    ];
    
    // Sample property features
    $propertyFeatures = [
        ['name' => 'Swimming Pool', 'icon' => 'fa-swimming-pool'],
        ['name' => 'Gym', 'icon' => 'fa-dumbbell'],
        ['name' => 'Parking', 'icon' => 'fa-parking'],
        ['name' => 'Garden', 'icon' => 'fa-tree'],
        ['name' => 'Security', 'icon' => 'fa-shield-alt']
    ];
    
    // Sample properties
    $properties = [
        [
            'title' => 'Luxury Apartment in City Center',
            'price' => 2500000,
            'area_sqft' => 1500,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'city' => 'Mumbai',
            'status' => 'available',
            'features' => [1, 3, 5] // Feature IDs
        ],
        [
            'title' => 'Modern Villa with Pool',
            'price' => 5000000,
            'area_sqft' => 3200,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'city' => 'Bangalore',
            'status' => 'available',
            'features' => [1, 2, 3, 4, 5]
        ],
        [
            'title' => 'Commercial Office Space',
            'price' => 3500000,
            'area_sqft' => 2200,
            'bedrooms' => 0,
            'bathrooms' => 2,
            'city' => 'Delhi',
            'status' => 'sold',
            'features' => [3, 5]
        ]
    ];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert property types
        foreach ($propertyTypes as $type) {
            $name = $type['name'];
            $icon = $type['icon'];
            $sql = "INSERT INTO property_types (name, icon) VALUES ('$name', '$icon')";
            $conn->query($sql);
        }
        
        // Insert property features
        foreach ($propertyFeatures as $feature) {
            $name = $feature['name'];
            $icon = $feature['icon'];
            $sql = "INSERT INTO property_features (name, icon) VALUES ('$name', '$icon')";
            $conn->query($sql);
        }
        
        // Insert properties
        foreach ($properties as $property) {
            $title = $property['title'];
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $property['title']));
            $description = $conn->real_escape_string("Beautiful property located in " . $property['city']);
            $city = $property['city'];
            $status = $property['status'];
            
            $sql = "INSERT INTO properties 
                    (title, slug, description, property_type_id, price, area_sqft, bedrooms, bathrooms, city, status, created_by) 
                    VALUES 
                    ('$title', '$slug', '$description', 1, {$property['price']}, {$property['area_sqft']}, 
                    {$property['bedrooms']}, {$property['bathrooms']}, '$city', '$status', 1)";
            
            $conn->query($sql);
            $propertyId = $conn->insert_id;
            
            // Insert property features
            foreach ($property['features'] as $featureId) {
                $sql = "INSERT INTO property_feature_mappings (property_id, feature_id) 
                        VALUES ($propertyId, $featureId)";
                $conn->query($sql);
            }
            
            // Insert sample image
            $imagePath = 'assets/images/properties/property-' . $propertyId . '.jpg';
            $sql = "INSERT INTO property_images (property_id, image_path, is_primary) 
                    VALUES ($propertyId, '$imagePath', 1)";
            $conn->query($sql);
        }
        
        // Commit transaction
        $conn->commit();
        echo "Sample data seeded successfully!\n";
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "Error seeding sample data: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
echo "=== Properties Table Setup ===\n\n";

try {
    // Get database connection
    $conn = getDbConnection();
    
    if ($conn === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Add properties table and related tables
    if (addPropertiesTable($conn)) {
        echo "\nAll tables created successfully!\n";
        
        // Ask if user wants to seed sample data
        echo "\nWould you like to seed sample data? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtolower($line)) == 'y') {
            seedSampleData($conn);
        } else {
            echo "Skipping sample data.\n";
        }
        
        echo "\nYou can now access the admin dashboard. The 'Table not found' error should be resolved.\n";
    } else {
        throw new Exception("Failed to create all tables");
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn) && $conn->error) {
        echo "Database Error: " . $conn->error . "\n";
    }
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>
