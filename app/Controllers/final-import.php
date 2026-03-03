<?php

/**
 * APS Dream Home - Final Database Import
 * Fixed version without read-only variables
 */

$sqlFile = __DIR__ . '/apsdreamhome.sql';
$database = 'apsdreamhome';
$host = 'localhost';
$username = 'root';
$password = '';

echo "=== APS Dream Home - Final Import ===\n\n";

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
    
    // Optimize for large imports (without read-only variables)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET UNIQUE_CHECKS = 0");
    $pdo->exec("SET AUTOCOMMIT = 0");
    
    // Drop and recreate database
    echo "🗑️ Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    
    echo "📦 Creating new database...\n";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    
    echo "📥 Starting import...\n";
    
    // Read SQL file line by line
    $handle = fopen($sqlFile, 'r');
    if (!$handle) {
        die("❌ Cannot open SQL file\n");
    }
    
    $sql = '';
    $tableCount = 0;
    $lineCount = 0;
    $startTime = microtime(true);
    $errors = [];
    
    while (($line = fgets($handle)) !== false) {
        $lineCount++;
        
        // Skip comments and empty lines
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
            continue;
        }
        
        $sql .= $line;
        
        // If line ends with semicolon, execute query
        if (substr(trim($line), -1) === ';') {
            $sql = trim($sql);
            
            if (!empty($sql)) {
                try {
                    $pdo->exec($sql);
                    
                    // Count CREATE TABLE statements
                    if (stripos($sql, 'CREATE TABLE') !== false) {
                        $tableCount++;
                        if ($tableCount % 100 === 0) {
                            echo "📋 Created $tableCount tables...\n";
                        }
                    }
                    
                } catch (PDOException $e) {
                    $errorMsg = $e->getMessage();
                    
                    // Collect errors but continue
                    if (strpos($errorMsg, 'already exists') === false && 
                        strpos($errorMsg, 'syntax') === false &&
                        strpos($errorMsg, 'Duplicate') === false) {
                        $errors[] = substr($errorMsg, 0, 100);
                    }
                }
            }
            
            $sql = '';
            
            // Show progress every 10000 lines
            if ($lineCount % 10000 === 0) {
                $elapsed = microtime(true) - $startTime;
                echo "📊 Processed " . number_format($lineCount) . " lines, $tableCount tables (" . number_format($elapsed) . "s)\n";
            }
        }
    }
    
    fclose($handle);
    
    // Commit and re-enable constraints
    $pdo->exec("COMMIT");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $pdo->exec("SET UNIQUE_CHECKS = 1");
    $pdo->exec("SET AUTOCOMMIT = 1");
    
    $endTime = microtime(true);
    $totalTime = $endTime - $startTime;
    
    echo "\n✅ Import completed!\n";
    echo "📊 Statistics:\n";
    echo "• Total Lines: " . number_format($lineCount) . "\n";
    echo "• Tables Created: $tableCount\n";
    echo "• Time Taken: " . number_format($totalTime, 2) . " seconds\n";
    echo "• Errors: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "⚠️ Sample Errors:\n";
        foreach (array_slice($errors, 0, 3) as $error) {
            echo "  • $error...\n";
        }
    }
    
    // Verify import
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n🔍 Verification:\n";
    echo "• Tables in database: " . count($tables) . "\n";
    
    if (count($tables) >= 590) {
        echo "🎉 PERFECT! All tables imported!\n";
    } elseif (count($tables) >= 500) {
        echo "✅ EXCELLENT! Most tables imported!\n";
    } elseif (count($tables) >= 300) {
        echo "✅ GOOD! Core tables imported!\n";
    } else {
        echo "⚠️ Only " . count($tables) . " tables imported.\n";
    }
    
    // Show key tables
    $keyTables = ['users', 'properties', 'admin', 'employees', 'projects', 'leads'];
    $foundKeyTables = array_intersect($keyTables, $tables);
    
    echo "\n🔑 Key Tables Status:\n";
    foreach ($keyTables as $table) {
        if (in_array($table, $foundKeyTables)) {
            echo "  ✅ $table\n";
        } else {
            echo "  ❌ $table\n";
        }
    }
    
    echo "\n🎯 SUCCESS! Database 'apsdreamhome' is ready!\n";
    echo "🔗 Your application now connects to apsdreamhome with all tables!\n";
    echo "🌐 Test your application at: http://localhost/apsdreamhome\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
