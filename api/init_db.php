<?php
/**
 * Initialize APS Dream Home Database
 * 
 * This script sets up the database with sample data for testing.
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'name' => 'apsdreamhomefinal',
    'user' => 'root',
    'pass' => ''
];

// Test user credentials
$testUser = [
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'status' => 'active'
];

// Sample properties
$sampleProperties = [
    [
        'title' => 'Luxury Villa with Ocean View',
        'description' => 'Beautiful 4-bedroom villa with stunning ocean views',
        'price' => 1250000,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => 3200,
        'address' => '123 Ocean Drive, Malibu, CA',
        'status' => 'available',
        'features' => json_encode(['Pool', 'Garden', 'Garage', 'Ocean View', 'Fully Furnished']),
        'images' => json_encode(['villa1.jpg', 'villa2.jpg'])
    ],
    [
        'title' => 'Modern Downtown Apartment',
        'description' => 'Stylish 2-bedroom apartment in the heart of the city',
        'price' => 750000,
        'bedrooms' => 2,
        'bathrooms' => 2,
        'area' => 1200,
        'address' => '456 Downtown Ave, New York, NY',
        'status' => 'available',
        'features' => json_encode(['Gym', 'Concierge', 'Parking', 'Balcony']),
        'images' => json_encode(['apt1.jpg', 'apt2.jpg'])
    ]
];

// Sample customers
$sampleCustomers = [
    [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'address' => '789 Customer St, Los Angeles, CA',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
        'phone' => '+1987654321',
        'address' => '321 Client Ave, New York, NY',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Function to execute SQL queries
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        die("Error executing query: " . $e->getMessage() . "\nSQL: $sql\n");
    }
}

// Function to create tables
function createTables($pdo) {
    // Drop existing tables if they exist
    $tables = [
        'visit_reminders',
        'property_visits',
        'visit_availability',
        'leads',
        'notifications',
        'notification_templates',
        'properties',
        'customers',
        'users'
    ];
    
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
        } catch (PDOException $e) {
            echo "Warning: Could not drop table $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Create users table
    executeQuery($pdo, "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `first_name` varchar(100) NOT NULL,
            `last_name` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `role` enum('admin','agent','user') NOT NULL DEFAULT 'user',
            `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Create properties table
    executeQuery($pdo, "
        CREATE TABLE IF NOT EXISTS `properties` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `price` decimal(12,2) NOT NULL,
            `bedrooms` int(11) DEFAULT NULL,
            `bathrooms` int(11) DEFAULT NULL,
            `area` int(11) DEFAULT NULL COMMENT 'in square feet',
            `address` text,
            `features` text COMMENT 'JSON array of features',
            `images` text COMMENT 'JSON array of image paths',
            `status` enum('available','pending','sold','off_market') NOT NULL DEFAULT 'available',
            `owner_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `owner_id` (`owner_id`),
            KEY `status` (`status`),
            CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Create customers table
    executeQuery($pdo, "
        CREATE TABLE IF NOT EXISTS `customers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `address` text,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    echo "✅ Tables created successfully\n";
}

// Function to insert sample data
function insertSampleData($pdo, $testUser, $sampleProperties, $sampleCustomers) {
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert test user
        $stmt = $pdo->prepare("
            INSERT INTO `users` 
            (`email`, `password`, `first_name`, `last_name`, `role`, `status`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $testUser['email'],
            $testUser['password'],
            $testUser['first_name'],
            $testUser['last_name'],
            $testUser['role'],
            $testUser['status']
        ]);
        $userId = $pdo->lastInsertId();
        
        // Update test user ID for properties
        foreach ($sampleProperties as &$property) {
            $property['owner_id'] = $userId;
        }
        
        // Insert sample properties
        foreach ($sampleProperties as $property) {
            $stmt = $pdo->prepare("
                INSERT INTO `properties` 
                (`title`, `description`, `price`, `bedrooms`, `bathrooms`, `area`, `address`, `features`, `images`, `status`, `owner_id`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $property['title'],
                $property['description'],
                $property['price'],
                $property['bedrooms'],
                $property['bathrooms'],
                $property['area'],
                $property['address'],
                $property['features'],
                $property['images'],
                $property['status'],
                $property['owner_id']
            ]);
        }
        
        // Insert sample customers
        foreach ($sampleCustomers as $customer) {
            $stmt = $pdo->prepare("
                INSERT INTO `customers` 
                (`name`, `email`, `phone`, `address`, `created_at`) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $customer['created_at']
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        echo "✅ Sample data inserted successfully\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        die("❌ Error inserting sample data: " . $e->getMessage() . "\n");
    }
}

// Main execution
try {
    // Create database connection
    $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbConfig['name']}`");
    
    echo "✅ Connected to database successfully\n";
    
    // Create tables
    createTables($pdo);
    
    // Insert sample data
    insertSampleData($pdo, $testUser, $sampleProperties, $sampleCustomers);
    
    echo "\n✅ Database initialization completed successfully!\n";
    echo "Test user: admin@example.com / admin123\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
