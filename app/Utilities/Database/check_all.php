<?php

// Check all databases and tables
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== MySQL Server Analysis ===\n\n";
    
    // Get all databases
    $stmt = $pdo->query('SHOW DATABASES');
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Total Databases: " . count($databases) . "\n\n";
    
    $totalTables = 0;
    $databaseInfo = [];
    
    foreach ($databases as $database) {
        // Skip system databases
        if (in_array($database, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
            continue;
        }
        
        try {
            // Connect to specific database
            $dbPdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $dbPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get table count
            $stmt = $dbPdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tableCount = count($tables);
            
            $totalTables += $tableCount;
            $databaseInfo[] = [
                'name' => $database,
                'tables' => $tableCount,
                'table_list' => $tables
            ];
            
            echo "🗄️ $database: $tableCount tables\n";
            
            // Show first few tables if there are many
            if ($tableCount > 10) {
                echo "   Tables: " . implode(', ', array_slice($tables, 0, 5)) . " ... (" . ($tableCount - 5) . " more)\n";
            } else {
                echo "   Tables: " . implode(', ', $tables) . "\n";
            }
            echo "\n";
            
        } catch(PDOException $e) {
            echo "❌ $database: Access denied\n\n";
        }
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "📊 SUMMARY:\n";
    echo "• Total User Databases: " . count($databaseInfo) . "\n";
    echo "• Total Tables: $totalTables\n\n";
    
    // Check specifically for apsdreamhome database
    $apsDatabase = null;
    foreach ($databaseInfo as $db) {
        if ($db['name'] === 'apsdreamhome') {
            $apsDatabase = $db;
            break;
        }
    }
    
    if ($apsDatabase) {
        echo "🎯 APS DREAM HOME DATABASE:\n";
        echo "• Database: apsdreamhome\n";
        echo "• Tables: " . $apsDatabase['tables'] . "\n";
        echo "• Table List:\n";
        
        foreach ($apsDatabase['table_list'] as $i => $table) {
            echo "  " . ($i + 1) . ". $table\n";
        }
        
        // Get detailed table info
        try {
            $apsPdo = new PDO("mysql:host=$host;dbname=apsdreamhome", $username, $password);
            $apsPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "\n📋 DETAILED TABLE INFO:\n";
            echo str_repeat("-", 50) . "\n";
            
            foreach ($apsDatabase['table_list'] as $table) {
                $stmt = $apsPdo->query("SELECT COUNT(*) FROM `$table`");
                $count = $stmt->fetchColumn();
                
                $stmt = $apsPdo->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "🔹 $table\n";
                echo "   Columns: " . count($columns) . "\n";
                echo "   Records: $count\n";
                
                // Show primary key
                foreach ($columns as $column) {
                    if ($column['Key'] === 'PRI') {
                        echo "   Primary: {$column['Field']} ({$column['Type']})\n";
                        break;
                    }
                }
                echo "\n";
            }
            
        } catch(PDOException $e) {
            echo "❌ Could not get detailed info for apsdreamhome\n";
        }
        
    } else {
        echo "❌ APS Dream Home database not found!\n";
        echo "💡 Run auto-setup.php to create it.\n";
    }
    
    // Check if there are any databases with many tables
    echo "\n🔍 LARGE DATABASES (>50 tables):\n";
    foreach ($databaseInfo as $db) {
        if ($db['tables'] > 50) {
            echo "• {$db['name']}: {$db['tables']} tables\n";
        }
    }
    
    if ($totalTables > 500) {
        echo "\n⚠️ WARNING: Found $totalTables total tables!\n";
        echo "This might include system tables or multiple projects.\n";
    }
    
} catch(PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure MySQL server is running and credentials are correct.\n";
}
?>
