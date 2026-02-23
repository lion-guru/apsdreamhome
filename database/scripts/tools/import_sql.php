<?php
/**
 * Database Import Script
 * This script imports SQL files into the database
 */

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Function to import SQL file
function importSqlFile($pdo, $sqlFile) {
    // Check if file exists
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found: $sqlFile\n");
    }
    
    // Read SQL file
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        die("Error: Unable to read SQL file: $sqlFile\n");
    }
    
    // Split SQL into individual queries
    $queries = explode(';', $sql);
    $imported = 0;
    $errors = [];
    
    // Execute each query
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->// SECURITY FIX: exec() removed for security reasons$query);
                $imported++;
                echo "✅ Executed query successfully\n";
            } catch (PDOException $e) {
                $errors[] = $e->getMessage();
                echo "❌ Error executing query: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Print summary
    echo "\n📊 Import Summary:\n";
    echo "- Total queries executed: $imported\n";
    echo "- Total errors: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\n❌ Errors encountered:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
    }
}

// Main execution
try {
    // Create database connection without selecting a database first
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Create database if not exists
    $pdo->// SECURITY FIX: exec() removed for security reasons"CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->// SECURITY FIX: exec() removed for security reasons"USE `" . DB_NAME . "`");
    
    echo "🔌 Connected to database successfully\n\n";
    
    // Import SQL file
    $sqlFile = __DIR__ . '/create_tables.sql';
    echo "📂 Importing SQL file: " . basename($sqlFile) . "\n\n";
    
    importSqlFile($pdo, $sqlFile);
    
    echo "\n✅ Database setup completed successfully!\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
}

echo "\n🏁 Script execution completed!\n";
