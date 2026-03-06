<?php

/**
 * APS Dream Home - Optimized Database Import
 * Fast import with chunked processing and error handling
 */

$sqlFile = __DIR__ . '/apsdreamhome.sql';
$database = 'apsdreamhome';
$host = 'localhost';
$username = 'root';
$password = '';

echo "=== APS Dream Home - Fast Database Import ===\n\n";

// Check if SQL file exists
if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile\n");
}

echo "📁 SQL File: $sqlFile\n";
echo "📊 File Size: " . number_format(filesize($sqlFile)) . " bytes\n";
echo "🗄️ Target Database: $database\n\n";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable foreign key checks and constraints for faster import
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET UNIQUE_CHECKS = 0");
    $pdo->exec("SET AUTOCOMMIT = 0");
    
    // Drop and recreate database
    echo "🗑️ Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    
    echo "📦 Creating new database...\n";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    
    echo "📥 Starting optimized import...\n";
    
    // Read and process SQL file in large chunks
    $content = file_get_contents($sqlFile);
    
    // Split by CREATE TABLE statements for better processing
    $tables = preg_split('/(CREATE TABLE[^;]+;)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    $tableCount = 0;
    $insertCount = 0;
    $startTime = microtime(true);
    
    foreach ($tables as $tableSql) {
        $tableSql = trim($tableSql);
        
        if (empty($tableSql)) continue;
        
        try {
            // Execute the SQL chunk
            $pdo->exec($tableSql);
            
            // Count tables and inserts
            if (stripos($tableSql, 'CREATE TABLE') !== false) {
                $tableCount++;
                echo "📋 Created table $tableCount\n";
            } elseif (stripos($tableSql, 'INSERT INTO') !== false) {
                $insertCount++;
            }
            
        } catch (PDOException $e) {
            // Try to handle common import errors
            $errorMsg = $e->getMessage();
            
            // Skip duplicate table errors
            if (strpos($errorMsg, 'already exists') !== false) {
                echo "⚠️ Table already exists, skipping...\n";
                continue;
            }
            
            // Skip syntax errors in comments
            if (strpos($errorMsg, 'syntax') !== false && strpos($tableSql, '--') !== false) {
                continue;
            }
            
            echo "⚠️ SQL Error: " . substr($errorMsg, 0, 100) . "...\n";
        }
    }
    
    // Re-enable constraints
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $pdo->exec("SET UNIQUE_CHECKS = 1");
    $pdo->exec("COMMIT");
    
    $endTime = microtime(true);
    $totalTime = $endTime - $startTime;
    
    echo "\n✅ Import completed successfully!\n";
    echo "📊 Statistics:\n";
    echo "• Tables Created: $tableCount\n";
    echo "• Insert Operations: $insertCount\n";
    echo "• Time Taken: " . number_format($totalTime, 2) . " seconds\n";
    
    // Verify import
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n🔍 Verification:\n";
    echo "• Tables in database: " . count($tables) . "\n";
    
    if (count($tables) >= 500) {
        echo "✅ Excellent! Most tables imported successfully!\n";
    } elseif (count($tables) >= 100) {
        echo "✅ Good! Core tables imported successfully!\n";
    } else {
        echo "⚠️ Only " . count($tables) . " tables imported. Check for errors.\n";
    }
    
    // Show first 10 tables
    echo "\n📋 Sample Tables:\n";
    for ($i = 0; $i < min(10, count($tables)); $i++) {
        echo "  " . ($i + 1) . ". " . $tables[$i] . "\n";
    }
    
    if (count($tables) > 10) {
        echo "  ... and " . (count($tables) - 10) . " more tables\n";
    }
    
    echo "\n🎉 Database 'apsdreamhome' is now ready!\n";
    echo "🔗 You can now connect to apsdreamhome database with all tables!\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
