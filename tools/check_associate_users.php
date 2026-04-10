<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT id, name, email, phone, user_type, status FROM users WHERE user_type = 'associate' LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Associate users in database:\n";
    foreach ($results as $row) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Type: {$row['user_type']}, Status: {$row['status']}\n";
    }
    
    echo "\nTotal count: " . count($results) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
