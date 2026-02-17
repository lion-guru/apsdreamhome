<?php
/**
 * APS Dream Home - Create Basic Database
 * Creates essential tables for the project
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Basic Database - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e6ffe6; padding: 10px; margin: 10px 0; border-left: 4px solid #44ff44; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff4444; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #4488ff; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🏗️ APS Dream Home - Create Basic Database</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhome';

echo "<div class='info'><h3>📋 Database Configuration</h3>";
echo "<p><strong>Host:</strong> $db_host</p>";
echo "<p><strong>User:</strong> $db_user</p>";
echo "<p><strong>Database:</strong> $db_name</p></div>";

try {
    // Connect to MySQL server
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Connected to MySQL server successfully</div>";
    
    // Drop existing database
    $sql = "DROP DATABASE IF EXISTS `$db_name`";
    $conn->query($sql);
    echo "<div class='success'>✅ Old database dropped</div>";
    
    // Create fresh database
    $sql = "CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='success'>✅ Fresh database '$db_name' created successfully</div>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($db_name);
    
    // Create essential tables
    $tables = [
        // Users table
        "CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `full_name` varchar(100) NOT NULL,
            `phone` varchar(15) DEFAULT NULL,
            `role` enum('admin','agent','user','associate') DEFAULT 'user',
            `email_verified` tinyint(1) DEFAULT 0,
            `phone_verified` tinyint(1) DEFAULT 0,
            `profile_image` varchar(255) DEFAULT NULL,
            `status` enum('active','inactive','suspended') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`),
            KEY `idx_role` (`role`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Properties table
        "CREATE TABLE `properties` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `price` decimal(15,2) NOT NULL,
            `property_type` varchar(50) NOT NULL,
            `location` varchar(255) NOT NULL,
            `bedrooms` int(11) DEFAULT NULL,
            `bathrooms` int(11) DEFAULT NULL,
            `area` decimal(10,2) DEFAULT NULL,
            `status` enum('available','sold','rented','maintenance') DEFAULT 'available',
            `featured` tinyint(1) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_property_type` (`property_type`),
            KEY `idx_status` (`status`),
            KEY `idx_featured` (`featured`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Customers table
        "CREATE TABLE `customers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(15) DEFAULT NULL,
            `address` text,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Leads table
        "CREATE TABLE `leads` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(15) DEFAULT NULL,
            `property_id` int(11) DEFAULT NULL,
            `message` text,
            `status` enum('new','contacted','qualified','converted','lost') DEFAULT 'new',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_property_id` (`property_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Projects table
        "CREATE TABLE `projects` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text,
            `location` varchar(255) NOT NULL,
            `status` enum('planning','construction','completed','cancelled') DEFAULT 'planning',
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Bookings table
        "CREATE TABLE `bookings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `property_id` int(11) NOT NULL,
            `booking_date` date NOT NULL,
            `amount` decimal(15,2) NOT NULL,
            `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_property_id` (`property_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Associates table
        "CREATE TABLE `associates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(15) DEFAULT NULL,
            `commission_rate` decimal(5,2) DEFAULT 0.00,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        // Site settings table
        "CREATE TABLE `site_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
            `description` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    $success_count = 0;
    foreach ($tables as $table_sql) {
        if ($conn->query($table_sql) === TRUE) {
            $success_count++;
            echo "<div class='success'>✅ Table created successfully</div>";
        } else {
            echo "<div class='error'>❌ Error creating table: " . $conn->error . "</div>";
        }
    }
    
    echo "<div class='success'>✅ $success_count tables created successfully</div>";
    
    // Insert admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, full_name, role, status) VALUES ('admin', 'admin@apsdreamhomes.com', '$admin_password', 'Administrator', 'admin', 'active')";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Admin user created (username: admin, password: admin123)</div>";
    }
    
    // Insert default site settings
    $default_settings = [
        ['site_title', 'APS Dream Homes Pvt Ltd', 'text', 'Website Title'],
        ['site_description', 'Leading real estate developer in Gorakhpur with 8+ years of excellence', 'text', 'Website Description'],
        ['contact_email', 'info@apsdreamhomes.com', 'text', 'Contact Email'],
        ['contact_phone', '+91-522-400-1234', 'text', 'Contact Phone'],
        ['contact_address', 'Gorakhpur, Uttar Pradesh, India', 'text', 'Contact Address']
    ];
    
    foreach ($default_settings as $setting) {
        $sql = "INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES ('{$setting[0]}', '{$setting[1]}', '{$setting[2]}', '{$setting[3]}')";
        $conn->query($sql);
    }
    
    echo "<div class='success'>✅ Default site settings inserted</div>";
    
    // Insert sample properties using simple INSERT
    $sql = "INSERT INTO properties (title, description, price, property_type, location, bedrooms, bathrooms, area, status, featured) VALUES 
        ('Luxury Villa in Gorakhpur', 'Beautiful 3BHK villa with modern amenities', 2500000, 'Villa', 'Gorakhpur', 3, 3, 2000, 'available', 1),
        ('Modern Apartment', '2BHK apartment in prime location', 1500000, 'Apartment', 'Gorakhpur', 2, 2, 1200, 'available', 1),
        ('Commercial Space', 'Prime commercial space for business', 5000000, 'Commercial', 'Gorakhpur', 0, 2, 3000, 'available', 0)";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Sample properties inserted</div>";
    } else {
        echo "<div class='error'>❌ Error inserting properties: " . $conn->error . "</div>";
    }
    
    // Insert sample customers using simple INSERT
    $sql = "INSERT INTO customers (name, email, phone, address) VALUES 
        ('John Doe', 'john@example.com', '1234567890', 'Gorakhpur, UP'),
        ('Jane Smith', 'jane@example.com', '0987654321', 'Lucknow, UP'),
        ('Mike Johnson', 'mike@example.com', '1122334455', 'Kanpur, UP')";
    
    if ($conn->query($sql)) {
        echo "<div class='success'>✅ Sample customers inserted</div>";
    } else {
        echo "<div class='error'>❌ Error inserting customers: " . $conn->error . "</div>";
    }
    
    echo "<div class='success'><h3>🎉 Basic database setup completed successfully!</h3>";
    echo "<p><strong>Database:</strong> $db_name</p>";
    echo "<p><strong>Tables created:</strong> users, properties, customers, leads, projects, bookings, associates, site_settings</p>";
    echo "<p><strong>Admin login:</strong> username: admin, password: admin123</p>";
    echo "<p><strong>Next step:</strong> <a href='index.php'>Go to homepage</a> | <a href='admin.php'>Go to admin panel</a></p></div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h3>❌ Database Setup Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Please ensure XAMPP MySQL is running and try again.</p></div>";
}

echo "</div></body></html>";
?>
