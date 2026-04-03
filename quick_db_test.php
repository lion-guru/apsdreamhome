<?php
/**
 * Quick Database Connection Test
 */

echo "Database Connection Test:\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
    echo "SUCCESS: Database connected successfully\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties");
    $result = $stmt->fetch();
    echo "Properties count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
?>
