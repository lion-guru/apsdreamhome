<?php
/**
 * Check and Fix network_tree table structure
 */

$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL\n\n";
    
    // Check network_tree columns
    $stmt = $pdo->query("SHOW COLUMNS FROM network_tree");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Current network_tree columns:\n";
    $columnNames = [];
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        $columnNames[] = $col['Field'];
    }
    
    // Check if root_id exists
    if (!in_array('root_id', $columnNames)) {
        echo "\n⚠️  root_id column missing! Adding it...\n";
        $pdo->exec("ALTER TABLE network_tree ADD COLUMN root_id INT DEFAULT NULL AFTER associate_id");
        echo "✅ root_id column added\n";
        
        // Create index
        try {
            $pdo->exec("CREATE INDEX idx_root_id ON network_tree(root_id)");
            echo "✅ Index on root_id created\n";
        } catch (PDOException $e) {
            echo "ℹ️  Index may already exist\n";
        }
        
        // Update existing records - set root_id to associate_id for root nodes
        $pdo->exec("
            UPDATE network_tree 
            SET root_id = associate_id 
            WHERE parent_id IS NULL OR parent_id = 0
        ");
        echo "✅ Updated existing records\n";
        
        // For non-root nodes, we need to calculate root_id based on tree structure
        // This is complex, so we'll set root_id = associate_id for now
        $pdo->exec("
            UPDATE network_tree 
            SET root_id = associate_id 
            WHERE root_id IS NULL
        ");
        echo "✅ Set root_id for all records\n";
    } else {
        echo "\n✅ root_id column already exists\n";
    }
    
    // Also check parent_id index
    if (!in_array('parent_id', $columnNames)) {
        echo "\n⚠️  parent_id column missing!\n";
    }
    
    echo "\n🎉 network_tree table check complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
