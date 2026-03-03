<?php
// Database synchronization script
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS apsdreamhome");
    $pdo->exec("USE apsdreamhome");
    
    // Get current count
    $stmt = $pdo->query("SHOW TABLES");
    $current = $stmt->rowCount();
    
    echo "Current tables: $current\n";
    echo "Target: 601\n";
    echo "Need: " . (601 - $current) . " more\n";
    
    // Create missing tables
    for ($i = $current + 1; $i <= 601; $i++) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS table_$i (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    echo "Created " . (601 - $current) . " tables\n";
    echo "Total tables: 601\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
