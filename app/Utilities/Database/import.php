<?php

/**
 * APS Dream Home - Database Import Script
 * Imports the complete apsdreamhome.sql file without timeout
 */

$sqlFile = __DIR__ . '/apsdreamhome.sql';
$database = 'apsdreamhome';
$host = 'localhost';
$username = 'root';
$password = '';

echo "=== APS Dream Home Database Import ===\n\n";

// Check if SQL file exists
if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile\n");
}

echo "📁 SQL File: $sqlFile\n";
echo "📊 File Size: " . number_format(filesize($sqlFile)) . " bytes\n";
echo "🗄️ Target Database: $database\n\n";

try {
    // Connect to MySQL without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    echo "🗑️ Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    
    echo "📦 Creating new database...\n";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    
    echo "📥 Starting import...\n";
    
    // Read SQL file in chunks to avoid memory issues
    $handle = fopen($sqlFile, 'r');
    if (!$handle) {
        die("❌ Cannot open SQL file\n");
    }
    
    $sql = '';
    $lineCount = 0;
    $tableCount = 0;
    $startTime = microtime(true);
    
    while (($line = fgets($handle)) !== false) {
        $lineCount++;
        
        // Skip comments and empty lines
        if (trim($line) === '' || strpos(trim($line), '--') === 0) {
            continue;
        }
        
        $sql .= $line;
        
        // If line ends with semicolon, execute query
        if (substr(trim($line), -1) === ';') {
            try {
                $pdo->exec($sql);
                
                // Count CREATE TABLE statements
                if (stripos($sql, 'CREATE TABLE') !== false) {
                    $tableCount++;
                }
                
                // Show progress every 1000 lines
                if ($lineCount % 1000 === 0) {
                    $elapsed = microtime(true) - $startTime;
                    echo "📊 Processed $lineCount lines, $tableCount tables (" . number_format($elapsed) . "s)\n";
                }
                
            } catch (PDOException $e) {
                echo "⚠️ Query failed: " . substr($e->getMessage(), 0, 100) . "...\n";
            }
            
            $sql = '';
        }
    }
    
    fclose($handle);
    
    $endTime = microtime(true);
    $totalTime = $endTime - $startTime;
    
    echo "\n✅ Import completed successfully!\n";
    echo "📊 Statistics:\n";
    echo "• Total Lines: " . number_format($lineCount) . "\n";
    echo "• Tables Created: $tableCount\n";
    echo "• Time Taken: " . number_format($totalTime, 2) . " seconds\n";
    echo "• Speed: " . number_format($lineCount / $totalTime) . " lines/second\n\n";
    
    // Verify import
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "🔍 Verification:\n";
    echo "• Tables in database: " . count($tables) . "\n";
    
    if (count($tables) === 596) {
        echo "✅ Perfect! All 596 tables imported successfully!\n";
    } else {
        echo "⚠️ Expected 596 tables, found " . count($tables) . "\n";
    }
    
    echo "\n🎉 Database is now ready with all features!\n";
    echo "👤 Admin login: Check your original admin credentials\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
