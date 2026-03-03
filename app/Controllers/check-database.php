<?php

// Database connection and table listing
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== APS Dream Home Database Tables ===\n\n";
    
    // Get all tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total Tables: " . count($tables) . "\n\n";
    
    // Group tables by type
    $coreTables = [];
    $businessTables = [];
    $systemTables = [];
    
    foreach ($tables as $table) {
        if (in_array($table, ['users', 'properties', 'leads', 'employees', 'projects', 'payments'])) {
            $coreTables[] = $table;
        } elseif (in_array($table, ['password_reset_tokens', 'notifications', 'settings', 'audit_logs'])) {
            $systemTables[] = $table;
        } else {
            $businessTables[] = $table;
        }
    }
    
    echo "📊 CORE TABLES (" . count($coreTables) . "):\n";
    foreach ($coreTables as $table) {
        echo "  ✓ $table\n";
    }
    
    echo "\n⚙️ SYSTEM TABLES (" . count($systemTables) . "):\n";
    foreach ($systemTables as $table) {
        echo "  ✓ $table\n";
    }
    
    echo "\n💼 BUSINESS TABLES (" . count($businessTables) . "):\n";
    foreach ($businessTables as $table) {
        echo "  ✓ $table\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    
    // Check against Models directory
    $modelsPath = __DIR__ . '/app/Models';
    $modelFiles = glob($modelsPath . '/*.php');
    $modelCount = count($modelFiles);
    
    echo "📁 MODELS DIRECTORY: $modelCount model files\n";
    echo "🗄️ DATABASE TABLES: " . count($tables) . " tables\n\n";
    
    // Show table details
    echo "📋 TABLE DETAILS:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n🔹 $table (" . count($columns) . " columns)\n";
        
        // Check if corresponding model exists
        $modelFile = $modelsPath . '/' . ucfirst($table) . '.php';
        $modelExists = file_exists($modelFile);
        $status = $modelExists ? "✅" : "❌";
        
        echo "   Model: $status " . ucfirst($table) . ".php\n";
        
        // Show key columns
        foreach ($columns as $column) {
            if ($column['Key'] === 'PRI') {
                echo "   Primary: {$column['Field']} ({$column['Type']})\n";
            }
            if ($column['Key'] === 'UNI') {
                echo "   Unique: {$column['Field']} ({$column['Type']})\n";
            }
        }
        
        // Show row count
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt->fetchColumn();
        echo "   Records: $count rows\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 SUMMARY:\n";
    echo "• Total Models: $modelCount\n";
    echo "• Total Tables: " . count($tables) . "\n";
    echo "• Tables with Models: " . count(array_intersect($tables, array_map('strtolower', array_map('basename', $modelFiles)))) . "\n";
    echo "• Tables without Models: " . (count($tables) - count(array_intersect($tables, array_map('strtolower', array_map('basename', $modelFiles))))) . "\n";
    
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure database 'apsdreamhome' exists and is accessible.\n";
}
?>
