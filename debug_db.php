<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Table 'users' does not exist.\n";
        exit(1);
    }
    
    // Get schema
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] == 'id') {
            echo "✅ 'users.id' type: " . $col['Type'] . "\n";
            echo "   Null: " . $col['Null'] . "\n";
            echo "   Key: " . $col['Key'] . "\n";
            echo "   Extra: " . $col['Extra'] . "\n";
        }
    }
    
    // Check engine and charset
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'users'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Engine: " . $status['Engine'] . "\n";
    echo "   Collation: " . $status['Collation'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
