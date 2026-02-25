<?php
// Database Structure Check Script
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking database structure...\n\n";
    
    // Check if tables exist
    $tables = ['leads', 'payouts', 'users'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->rowCount() > 0;
        
        echo "Table '$table': " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
        
        if ($exists) {
            // Show table structure
            $stmt = $pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "  Columns: " . implode(', ', $columns) . "\n";
            
            // Show existing indexes
            $stmt = $pdo->prepare("SHOW INDEX FROM $table");
            $stmt->execute();
            $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "  Existing indexes:\n";
            foreach ($indexes as $index) {
                echo "    - {$index['Key_name']} ({$index['Column_name']})\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
