<?php
// Simple Database Setup for APS Dream Home
echo "<h1>🚀 APS Dream Home - Simple Setup</h1>";

// Connect to MySQL
$db = new mysqli('localhost', 'root', '');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Create database
$db->query("DROP DATABASE IF EXISTS apsdreamhomefinal");
$db->query("CREATE DATABASE apsdreamhomefinal CHARACTER SET utf8mb4");
$db->select_db('apsdreamhomefinal');

// Create basic tables
$tables = [
    "users" => "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','user','agent') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "properties" => "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(12,2) NOT NULL,
        location VARCHAR(255) NOT NULL,
        status ENUM('available','sold','pending') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "customers" => "CREATE TABLE customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "bookings" => "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT,
        customer_id INT,
        booking_date DATE,
        status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id),
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    )"
];

echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";

foreach ($tables as $name => $sql) {
    if ($db->query($sql)) {
        echo "<p style='color: green;'>✅ Table '$name' created</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating '$name': " . $db->error . "</p>";
    }
}

echo "</div>";

// Insert sample data
$password = password_hash('admin123', PASSWORD_DEFAULT);
$db->query("INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@apsdreamhome.com', '$password', 'admin'),
    ('user', 'user@example.com', '$password', 'user')");

$db->query("INSERT INTO properties (title, description, price, location) VALUES
    ('Luxury Villa', 'Beautiful modern villa with all amenities', 2500000.00, 'Mumbai'),
    ('2BHK Apartment', 'Cozy apartment in prime location', 1200000.00, 'Delhi'),
    ('Plot for Sale', 'Prime residential plot', 800000.00, 'Pune')");

$db->query("INSERT INTO customers (name, email, phone) VALUES
    ('John Doe', 'john@example.com', '9876543210'),
    ('Jane Smith', 'jane@example.com', '8765432109')");

// Show results
echo "<h2>✅ Setup Complete!</h2>";
echo "<p>Admin Login:</p>";
echo "<ul>";
echo "<li>Username: admin</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";

echo "<h3>System Links:</h3>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Main Website</a></li>";
echo "<li><a href='aps_crm_system.php' target='_blank'>📞 CRM System</a></li>";
echo "<li><a href='whatsapp_demo.php' target='_blank'>📱 WhatsApp Demo</a></li>";
echo "</ul>";
?>
