<?php
/**
 * Test script to verify the missing tables were created
 */

// Include database connection
require_once __DIR__ . '/includes/db_connection.php';

echo "Testing if missing tables were created...\n\n";

try {
    // Get database connection
    $pdo = getDbConnection();
    
    if ($pdo === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // Check if tables exist
    $tables = ['legal_services', 'team_members', 'faqs'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch() !== false;
        
        echo "Table '$table': " . ($exists ? "✓ EXISTS" : "❌ MISSING") . "\n";
        
        if ($exists) {
            // Count rows
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetch()['count'];
            echo "  Rows: $count\n";
            
            // Show sample data
            if ($count > 0) {
                $sampleStmt = $pdo->query("SELECT * FROM $table LIMIT 1");
                $sample = $sampleStmt->fetch(PDO::FETCH_ASSOC);
                echo "  Sample: " . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
            }
        }
        echo "\n";
    }
    
    echo "✅ Database check completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>