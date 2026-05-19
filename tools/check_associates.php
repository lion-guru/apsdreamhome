<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT id, name, email, phone, status FROM associates LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Associates in database:\n";
    foreach ($results as $row) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Status: {$row['status']}\n";
    }
    
    echo "\nTotal count: " . count($results) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
