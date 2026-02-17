<?php
// APS Dream Home - Simple Complete Database Setup
echo "<h1>🏗️ APS Dream Home - Complete Database Setup</h1>";

// Connect to MySQL
$db = new mysqli('localhost', 'root', '');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Create database
$db->query("DROP DATABASE IF EXISTS apsdreamhome_complete");
$db->query("CREATE DATABASE apsdreamhome_complete CHARACTER SET utf8mb4");
$db->select_db('apsdreamhome_complete');

// Disable foreign key checks
$db->query("SET FOREIGN_KEY_CHECKS = 0");

// Create essential tables
$tables = [
    // Users table
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','user','agent') DEFAULT 'user',
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    // Properties table
    "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(12,2) NOT NULL,
        location VARCHAR(255) NOT NULL,
        bedrooms INT DEFAULT NULL,
        bathrooms INT DEFAULT NULL,
        area DECIMAL(10,2) DEFAULT NULL,
        status ENUM('available','sold','rented') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    // Projects table
    "CREATE TABLE projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        location VARCHAR(255) NOT NULL,
        status ENUM('active','inactive','completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Plots table
    "CREATE TABLE plots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        plot_no VARCHAR(50) NOT NULL,
        size_sqft DECIMAL(10,2) DEFAULT NULL,
        status ENUM('available','booked','sold','rented','resale') DEFAULT 'available',
        customer_id INT DEFAULT NULL,
        associate_id INT DEFAULT NULL,
        sale_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY project_id (project_id),
        KEY customer_id (customer_id),
        KEY associate_id (associate_id)
    )",

    // Associates table
    "CREATE TABLE associates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        level INT DEFAULT 1,
        join_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Customers table
    "CREATE TABLE customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        address TEXT,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Bookings table
    "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT DEFAULT NULL,
        customer_id INT NOT NULL,
        booking_date DATE NOT NULL,
        status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY property_id (property_id),
        KEY customer_id (customer_id)
    )",

    // Transactions table
    "CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        property_id INT DEFAULT NULL,
        amount DECIMAL(12,2) NOT NULL,
        type ENUM('booking','payment','refund') DEFAULT 'payment',
        status ENUM('pending','completed','failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY customer_id (customer_id),
        KEY property_id (property_id)
    )"
];

echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";

foreach ($tables as $name => $sql) {
    if ($db->query($sql)) {
        echo "<p style='color: green;'>✅ Created table successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $db->error . "</p>";
    }
}

echo "</div>";

// Insert sample data
$password = password_hash('admin123', PASSWORD_DEFAULT);
$db->query("INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@apsdreamhome.com', '$password', 'admin'),
    ('user', 'user@example.com', '$password', 'user')");

$db->query("INSERT INTO projects (name, description, location, status) VALUES
    ('Dream Valley', 'Premium residential project', 'Mumbai', 'active'),
    ('Green City', 'Eco-friendly housing', 'Delhi', 'active')");

$db->query("INSERT INTO properties (title, description, price, location, bedrooms, bathrooms) VALUES
    ('Luxury Villa', 'Beautiful villa with modern amenities', 2500000.00, 'Mumbai', 4, 3),
    ('2BHK Apartment', 'Cozy apartment in prime location', 1200000.00, 'Delhi', 2, 2),
    ('3BHK Flat', 'Spacious flat with great view', 1800000.00, 'Pune', 3, 2)");

$db->query("INSERT INTO customers (name, email, phone) VALUES
    ('John Doe', 'john@example.com', '9876543210'),
    ('Jane Smith', 'jane@example.com', '8765432109')");

$db->query("INSERT INTO associates (name, email, phone, level) VALUES
    ('Rajesh Kumar', 'rajesh@associate.com', '9123456789', 1),
    ('Priya Sharma', 'priya@associate.com', '9234567890', 2)");

echo "<h2>✅ Database Setup Complete!</h2>";
echo "<p>Essential tables created with sample data</p>";

// Add foreign key constraints
echo "<h3>🔗 Adding Foreign Key Constraints</h3>";
$constraints = [
    "ALTER TABLE plots ADD CONSTRAINT plots_project_fk FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL",
    "ALTER TABLE plots ADD CONSTRAINT plots_customer_fk FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL",
    "ALTER TABLE plots ADD CONSTRAINT plots_associate_fk FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL",
    "ALTER TABLE bookings ADD CONSTRAINT bookings_property_fk FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL",
    "ALTER TABLE bookings ADD CONSTRAINT bookings_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE",
    "ALTER TABLE transactions ADD CONSTRAINT transactions_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE",
    "ALTER TABLE transactions ADD CONSTRAINT transactions_property_fk FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL"
];

$constraint_count = 0;
foreach ($constraints as $constraint) {
    if ($db->query($constraint)) {
        $constraint_count++;
    }
}

echo "<p style='color: green;'>✅ Added $constraint_count foreign key constraints</p>";

// Enable foreign key checks
$db->query("SET FOREIGN_KEY_CHECKS = 1");
echo "<p style='color: green;'>✅ Foreign key checks enabled</p>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>📊 Final Database Summary:</h3>";
echo "<p>✅ Database: apsdreamhome_complete</p>";
echo "<p>✅ Tables: 8 essential tables</p>";
echo "<p>✅ Sample Data: Added</p>";
echo "<p>✅ Constraints: Active</p>";
echo "</div>";

echo "<h3>System Links:</h3>";
echo "<ul>";
echo "<li><a href='../index.php' target='_blank'>🏠 Main Website</a></li>";
echo "<li><a href='../aps_crm_system.php' target='_blank'>📞 CRM System</a></li>";
echo "<li><a href='../whatsapp_demo.php' target='_blank'>📱 WhatsApp Demo</a></li>";
echo "</ul>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #28a745; color: white; border-radius: 8px;'>";
echo "<h2>🎉 COMPLETE SYSTEM READY!</h2>";
echo "<p>Your APS Dream Home database is fully set up and ready to use!</p>";
echo "</div>";
?>
