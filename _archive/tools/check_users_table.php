<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check users table structure
    $stmt = $conn->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    
    // Check bookings table structure
    echo "\nBookings table structure:\n";
    $stmt = $conn->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    
    // Check if there's a data type mismatch issue
    echo "\nChecking data types for JOIN columns:\n";
    echo "bookings.customer_id type: ";
    $stmt = $conn->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='bookings' AND COLUMN_NAME='customer_id'");
    echo $stmt->fetch()['DATA_TYPE'] ?? "unknown\n";
    
    echo "users.id type: ";
    $stmt = $conn->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='users' AND COLUMN_NAME='id'");
    echo $stmt->fetch()['DATA_TYPE'] ?? "unknown\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
