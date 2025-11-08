<?php
/**
 * Simple script to check if tables exist
 */

require_once __DIR__ . '/includes/db_connection.php';

try {
    $pdo = getDbConnection();
    
    $tables = ['legal_services', 'team_members', 'faqs'];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->fetch()) {
            echo "✓ $table exists\n";
        } else {
            echo "❌ $table does NOT exist\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>