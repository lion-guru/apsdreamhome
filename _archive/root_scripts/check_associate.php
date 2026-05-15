<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n\n";
    
    // Check if associates table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'associates'");
    if ($stmt->rowCount() > 0) {
        echo "Associates table exists.\n\n";
        
        // Get all associates
        $stmt = $pdo->query("SELECT id, name, email, status, created_at FROM associates LIMIT 10");
        $associates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($associates) > 0) {
            echo "Found " . count($associates) . " associates:\n\n";
            foreach ($associates as $associate) {
                echo "ID: " . $associate['id'] . "\n";
                echo "Name: " . $associate['name'] . "\n";
                echo "Email: " . $associate['email'] . "\n";
                echo "Status: " . $associate['status'] . "\n";
                echo "Created: " . $associate['created_at'] . "\n";
                echo "-------------------------\n";
            }
        } else {
            echo "No associates found in the database.\n";
        }
    } else {
        echo "Associates table does not exist.\n";
        
        // Show all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "\nAvailable tables:\n";
        foreach ($tables as $table) {
            echo "- " . $table . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
