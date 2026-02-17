<?php
/**
 * Create Essential Tables for APS Dream Home
 * This script creates the most critical tables needed for basic functionality
 */

// Database connection
require_once 'includes/db_config.php';

// Create database connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$tables_created = [];
$errors = [];

echo "<h2>Creating Essential Tables for APS Dream Home</h2>";
echo "<div style='font-family: monospace;'>";

// 1. Create leads table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `leads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100),
        `phone` VARCHAR(20),
        `source` VARCHAR(50) DEFAULT 'website',
        `status` VARCHAR(50) DEFAULT 'new',
        `assigned_to` INT,
        `property_interest` VARCHAR(200),
        `budget_range` VARCHAR(50),
        `location_preference` VARCHAR(100),
        `notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `is_converted` BOOLEAN DEFAULT FALSE,
        `converted_at` TIMESTAMP NULL,
        INDEX idx_status (status),
        INDEX idx_assigned_to (assigned_to),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'leads';
    echo "✅ <span style='color: green;'>Table 'leads' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "leads: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'leads': " . $e->getMessage() . "</span><br>";
}

// 2. Create projects table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `projects` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(200) NOT NULL,
        `description` TEXT,
        `location` VARCHAR(200),
        `city` VARCHAR(100),
        `state` VARCHAR(100),
        `status` VARCHAR(50) DEFAULT 'planning',
        `project_type` VARCHAR(50),
        `total_units` INT DEFAULT 0,
        `available_units` INT DEFAULT 0,
        `starting_price` DECIMAL(15,2),
        `completion_date` DATE,
        `launch_date` DATE,
        `developer_name` VARCHAR(200),
        `contact_person` VARCHAR(100),
        `contact_phone` VARCHAR(20),
        `contact_email` VARCHAR(100),
        `address` TEXT,
        `amenities` TEXT,
        `images` TEXT,
        `created_by` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_city (city),
        INDEX idx_project_type (project_type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'projects';
    echo "✅ <span style='color: green;'>Table 'projects' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "projects: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'projects': " . $e->getMessage() . "</span><br>";
}

// 3. Create payments table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `payments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `booking_id` INT,
        `customer_id` INT,
        `amount` DECIMAL(15,2) NOT NULL,
        `payment_type` VARCHAR(50) NOT NULL,
        `payment_method` VARCHAR(50),
        `payment_date` DATE,
        `transaction_id` VARCHAR(100),
        `status` VARCHAR(50) DEFAULT 'pending',
        `notes` TEXT,
        `created_by` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_booking_id (booking_id),
        INDEX idx_customer_id (customer_id),
        INDEX idx_payment_date (payment_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'payments';
    echo "✅ <span style='color: green;'>Table 'payments' created successfully</span><span style='color: green;'><br>";
} catch (Exception $e) {
    $errors[] = "payments: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'payments': " . $e->getMessage() . "</span><br>";
}

// 4. Create property_types table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `property_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `type` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `status` VARCHAR(50) DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'property_types';
    echo "✅ <span style='color: green;'>Table 'property_types' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "property_types: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'property_types': " . $e->getMessage() . "</span><br>";
}

// 5. Create property_features table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `property_features` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `feature_name` VARCHAR(100) NOT NULL,
        `feature_value` VARCHAR(200),
        `feature_type` VARCHAR(50),
        `is_active` BOOLEAN DEFAULT TRUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_property_id (property_id),
        INDEX idx_feature_name (feature_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'property_features';
    echo "✅ <span style='color: green;'>Table 'property_features' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "property_features: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'property_features': " . $e->getMessage() . "</span><br>";
}

// 6. Create property_images table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `property_images` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `image_path` VARCHAR(500) NOT NULL,
        `image_type` VARCHAR(50) DEFAULT 'gallery',
        `is_primary` BOOLEAN DEFAULT FALSE,
        `caption` VARCHAR(200),
        `alt_text` VARCHAR(200),
        `sort_order` INT DEFAULT 0,
        `is_active` BOOLEAN DEFAULT TRUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_property_id (property_id),
        INDEX idx_is_primary (is_primary),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'property_images';
    echo "✅ <span style='color: green;'>Table 'property_images' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "property_images: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'property_images': " . $e->getMessage() . "</span><br>";
}

// 7. Create notifications table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `type` VARCHAR(50) NOT NULL,
        `title` VARCHAR(200) NOT NULL,
        `message` TEXT,
        `is_read` BOOLEAN DEFAULT FALSE,
        `priority` VARCHAR(20) DEFAULT 'normal',
        `related_id` INT,
        `related_type` VARCHAR(50),
        `action_url` VARCHAR(500),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `read_at` TIMESTAMP NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_is_read (is_read),
        INDEX idx_type (type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'notifications';
    echo "✅ <span style='color: green;'>Table 'notifications' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "notifications: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'notifications': " . $e->getMessage() . "</span><br>";
}

// 8. Create tasks table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `tasks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(200) NOT NULL,
        `description` TEXT,
        `assigned_to` INT,
        `created_by` INT,
        `priority` VARCHAR(20) DEFAULT 'medium',
        `status` VARCHAR(50) DEFAULT 'pending',
        `due_date` DATE,
        `completed_at` TIMESTAMP NULL,
        `notes` TEXT,
        `related_type` VARCHAR(50),
        `related_id` INT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_assigned_to (assigned_to),
        INDEX idx_created_by (created_by),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_due_date (due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    $tables_created[] = 'tasks';
    echo "✅ <span style='color: green;'>Table 'tasks' created successfully</span><br>";
} catch (Exception $e) {
    $errors[] = "tasks: " . $e->getMessage();
    echo "❌ <span style='color: red;'>Error creating 'tasks': " . $e->getMessage() . "</span><br>";
}

// Insert sample data for property_types
try {
    $sql = "INSERT INTO `property_types` (`type`, `description`) VALUES
    ('Apartment', 'Residential apartment units'),
    ('Villa', 'Independent villa/bungalow'),
    ('Plot', 'Residential or commercial plot'),
    ('Commercial', 'Commercial space/shops'),
    ('Office', 'Office space'),
    ('Studio', 'Studio apartment'),
    ('Penthouse', 'Luxury penthouse unit'),
    ('Duplex', 'Duplex apartment')";
    
    $conn->query($sql);
    echo "✅ <span style='color: blue;'>Sample data inserted into 'property_types'</span><br>";
} catch (Exception $e) {
    echo "⚠️ <span style='color: orange;'>Warning inserting sample data: " . $e->getMessage() . "</span><br>";
}

echo "</div>";

// Summary
echo "<h3>Summary</h3>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
if (count($tables_created) > 0) {
    echo "<p><strong>✅ Successfully created tables:</strong></p>";
    echo "<ul>";
    foreach ($tables_created as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
}

if (count($errors) > 0) {
    echo "<p><strong>❌ Errors encountered:</strong></p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p><strong>Total tables created:</strong> " . count($tables_created) . "</p>";
echo "<p><strong>Total errors:</strong> " . count($errors) . "</p>";
echo "</div>";

// Next steps
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Next Steps:</h4>";
echo "<ol>";
echo "<li>Test the admin dashboard to see if it loads properly</li>";
echo "<li>Create additional tables as needed for specific features</li>";
echo "<li>Insert sample data for testing</li>";
echo "<li>Verify all dashboard widgets display correctly</li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>