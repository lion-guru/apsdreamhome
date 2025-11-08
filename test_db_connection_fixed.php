<?php
/**
 * Test Database Connection Script
 * Verifies database connection and configuration
 */

echo "Testing Database Connection...\n\n";

// Include configuration
require_once 'includes/config.php';

echo "Configuration loaded successfully\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? '*** (set)' : '(empty)') . "\n\n";

// Include database connection
require_once 'includes/db_connection.php';

echo "Database connection file loaded\n";

try {
    // Test PDO connection
    $pdo = getDbConnection();
    echo "✓ PDO Connection successful\n";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Current database: " . $result['db_name'] . "\n";
    
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Tables found: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "  First 5 tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    
    // Test specific tables used in legal_services.php
    $required_tables = ['legal_services', 'team_members', 'faqs'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        if (!in_array($table, $tables)) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo "⚠ Missing tables: " . implode(', ', $missing_tables) . "\n";
    } else {
        echo "✓ All required tables exist\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "Error details: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTest completed.\n";
?>