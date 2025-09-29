<?php
// Quick Database Setup for APS Dream Home
$db = new mysqli('localhost', 'root', '');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Create database
$db->query("DROP DATABASE IF EXISTS apsdreamhomefinal");
$db->query("CREATE DATABASE apsdreamhomefinal CHARACTER SET utf8mb4");
$db->select_db('apsdreamhomefinal');

// Create users table
$db->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create properties table
$db->query("CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    status ENUM('available','sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert admin user
$password = password_hash('admin123', PASSWORD_DEFAULT);
$db->query("INSERT INTO users (username, email, password, role) 
    VALUES ('admin', 'admin@apsdreamhome.com', '$password', 'admin')");

// Insert sample property
$db->query("INSERT INTO properties (title, description, price, location) 
    VALUES ('Luxury Villa', 'Beautiful modern villa', 2500000.00, 'Mumbai')");

// Show results
echo "<h1>✅ Database Setup Complete</h1>";
echo "<p>Database and tables created successfully!</p>";
echo "<p>Admin Login:</p>";
echo "<ul>";
echo "<li>Username: admin</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";
?>
