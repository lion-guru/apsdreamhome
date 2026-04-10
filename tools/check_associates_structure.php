<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("DESCRIBE associates");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Associates table structure:\n";
    foreach ($columns as $col) {
        echo "Column: {$col['Field']}, Type: {$col['Type']}\n";
    }
    
    echo "\nTotal columns: " . count($columns) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
