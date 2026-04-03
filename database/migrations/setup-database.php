<?php

/**
 * APS Dream Home - Database Setup Script
 * Recreates essential database tables and structure
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    echo "Database setup started...\n";
    
    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            password varchar(255) NOT NULL,
            role enum('admin','user','employee') DEFAULT 'user',
            status enum('active','inactive') DEFAULT 'active',
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Password reset tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id int(11) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            token varchar(255) NOT NULL,
            expires_at datetime NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Employees table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) DEFAULT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            password varchar(255) DEFAULT NULL,
            role varchar(50) DEFAULT 'employee',
            status enum('active','inactive') DEFAULT 'active',
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Properties table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS properties (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            price decimal(10,2) DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            type varchar(50) DEFAULT NULL,
            status enum('available','sold','rented') DEFAULT 'available',
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Leads table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            property_id int(11) DEFAULT NULL,
            status enum('new','contacted','converted') DEFAULT 'new',
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY property_id (property_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Gallery images table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery_images (
            id int(11) NOT NULL AUTO_INCREMENT,
            category varchar(50) DEFAULT 'general',
            image_path varchar(500) DEFAULT NULL,
            caption text DEFAULT NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Insert default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'admin@apsdreamhome.com', $adminPassword, 'admin', 'active']);
    
    // Insert sample property
    $stmt = $pdo->prepare("INSERT IGNORE INTO properties (title, description, price, location, type, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Sample Property', 'A beautiful property for sale', 500000.00, 'Mumbai', 'Apartment', 'available']);
    
    echo "Database setup completed successfully!\n";
    echo "Default admin login: admin@apsdreamhome.com / admin123\n";
    
} catch(PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>
