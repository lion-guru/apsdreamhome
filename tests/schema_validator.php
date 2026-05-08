<?php
/**
 * Database Schema Validator
 * Validates schema consistency and suggests fixes
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

echo "🔍 Database Schema Validator\n";
echo "═══════════════════════════════════════\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables\n\n";
    
    $issues = [];
    $suggestions = [];
    
    // Check for common issues
    foreach ($tables as $table) {
        // Get table structure
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(\PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        // Check for id column
        if (!in_array('id', $columnNames)) {
            $issues[] = "Table '$table' missing 'id' column";
        }
        
        // Check for timestamps
        if (!in_array('created_at', $columnNames)) {
            $suggestions[] = "Table '$table' missing 'created_at' timestamp";
        }
        
        if (!in_array('updated_at', $columnNames)) {
            $suggestions[] = "Table '$table' missing 'updated_at' timestamp";
        }
        
        // Check for soft delete
        if (!in_array('deleted_at', $columnNames) && $table !== 'migrations') {
            $suggestions[] = "Table '$table' could use 'deleted_at' for soft deletes";
        }
    }
    
    // Report issues
    if (!empty($issues)) {
        echo "❌ CRITICAL ISSUES:\n";
        foreach ($issues as $issue) {
            echo "   • $issue\n";
        }
        echo "\n";
    }
    
    if (!empty($suggestions)) {
        echo "💡 SUGGESTIONS:\n";
        foreach (array_slice($suggestions, 0, 10) as $suggestion) {
            echo "   • $suggestion\n";
        }
        if (count($suggestions) > 10) {
            echo "   ... and " . (count($suggestions) - 10) . " more\n";
        }
        echo "\n";
    }
    
    if (empty($issues) && empty($suggestions)) {
        echo "✅ Schema looks good!\n";
    }
    
    // Check indexes on critical tables
    echo "🔍 Checking indexes...\n";
    $criticalTables = ['users', 'properties', 'leads', 'bookings'];
    
    foreach ($criticalTables as $table) {
        if (!in_array($table, $tables)) {
            echo "   ⚠️  Table '$table' not found!\n";
            continue;
        }
        
        $indexes = $pdo->query("SHOW INDEX FROM $table")->fetchAll(\PDO::FETCH_ASSOC);
        $indexNames = array_column($indexes, 'Key_name');
        
        echo "   ✓ $table: " . count($indexes) . " indexes\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Schema validation complete\n";
