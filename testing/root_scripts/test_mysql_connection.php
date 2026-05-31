<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3307', 'root', '');
    echo "MySQL connection successful!\n";
    
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available databases:\n";
    foreach ($databases as $db) {
        echo "- $db\n";
    }
    
} catch (Exception $e) {
    echo "MySQL connection failed: " . $e->getMessage() . "\n";
}
?>
