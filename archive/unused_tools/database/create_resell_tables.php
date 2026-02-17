<?php
/**
 * Create Resell Property System Tables
 * This script creates the necessary database tables for the resell property system
 */

require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create resell_users table
$sql_resell_users = "CREATE TABLE IF NOT EXISTS resell_users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    registration_date DATETIME NOT NULL,
    last_login DATETIME NULL,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mobile (mobile),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Create resell_properties table
$sql_resell_properties = "CREATE TABLE IF NOT EXISTS resell_properties (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    property_type ENUM('apartment', 'house', 'villa', 'plot', 'commercial') NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    bedrooms INT(3) DEFAULT 0,
    bathrooms INT(3) DEFAULT 0,
    area DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NULL,
    description TEXT NULL,
    features JSON NULL,
    status ENUM('pending', 'approved', 'rejected', 'sold') DEFAULT 'pending',
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT(11) DEFAULT 0,
    whatsapp_clicks INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    approved_by INT(11) NULL,
    rejection_reason TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES resell_users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_property_type (property_type),
    INDEX idx_city (city),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_is_featured (is_featured),
    FULLTEXT INDEX idx_search (title, description, address, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Create resell_property_images table
$sql_resell_images = "CREATE TABLE IF NOT EXISTS resell_property_images (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    property_id INT(11) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES resell_properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Create resell_enquiries table
$sql_resell_enquiries = "CREATE TABLE IF NOT EXISTS resell_enquiries (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    property_id INT(11) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    message TEXT NULL,
    enquiry_type ENUM('general', 'viewing', 'price_negotiation') DEFAULT 'general',
    status ENUM('new', 'contacted', 'follow_up', 'converted', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES resell_properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Execute table creation
$tables = [
    'resell_users' => $sql_resell_users,
    'resell_properties' => $sql_resell_properties,
    'resell_property_images' => $sql_resell_images,
    'resell_enquiries' => $sql_resell_enquiries
];

echo "<h2>Creating Resell Property System Tables</h2>";
echo "<div style='font-family: monospace; background: #f4f4f4; padding: 20px; border-radius: 5px;'>";

foreach ($tables as $table_name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table <strong>$table_name</strong> created successfully<br>";
    } else {
        echo "❌ Error creating table $table_name: " . $conn->error . "<br>";
    }
}

// Add sample data for testing
$sample_users = "INSERT IGNORE INTO resell_users (full_name, mobile, email, registration_date) VALUES 
    ('Rajesh Kumar', '9876543210', 'rajesh@example.com', NOW()),
    ('Priya Sharma', '8765432109', 'priya@example.com', NOW()),
    ('Amit Singh', '7654321098', 'amit@example.com', NOW())";

if ($conn->query($sample_users) === TRUE) {
    echo "✅ Sample users added successfully<br>";
} else {
    echo "⚠️ Sample users not added (may already exist): " . $conn->error . "<br>";
}

echo "</div>";
echo "<h3 style='color: green; margin-top: 20px;'>Resell Property System Tables Setup Complete!</h3>";
echo "<p>You can now use the resell property system with:</p>";
echo "<ul>";
echo "<li><strong>resell_users</strong> - User management for property sellers</li>";
echo "<li><strong>resell_properties</strong> - Property listings with approval system</li>";
echo "<li><strong>resell_property_images</strong> - Property image management</li>";
echo "<li><strong>resell_enquiries</strong> - Lead management for resell properties</li>";
echo "</ul>";

$conn->close();
?>