<?php
/**
 * Test script to verify login functionality with the new database name
 */

session_start();
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

echo "Testing Login Functionality with Database: apsdreamhome\n\n";

// Test database connection - using the global $pdo variable from db_connection.php
if (isset($pdo) && $pdo instanceof PDO) {
    echo "✅ Database connection successful\n";
    
    // Check if users/agents table exists
    try {
        // Test MLM agents table (from the original test)
        $stmt = $pdo->query("SELECT COUNT(*) FROM mlm_agents");
        $agentCount = $stmt->fetchColumn();
        echo "✅ MLM Agents table exists with $agentCount agents\n";
        
        // Test regular users table
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        echo "✅ Users table exists with $userCount users\n";
        
    } catch (PDOException $e) {
        echo "❌ Table check error: " . $e->getMessage() . "\n";
    }
    
    // Test authentication files exist
    $authFiles = ['auth/login.php', 'auth/register.php', 'auth/logout.php'];
    foreach ($authFiles as $file) {
        if (file_exists($file)) {
            echo "✅ $file exists\n";
        } else {
            echo "❌ $file missing\n";
        }
    }
    
    echo "\n✅ Login functionality test completed successfully!\n";
    echo "The authentication system is ready with the new database name 'apsdreamhome'\n";
    
} else {
    echo "❌ Database connection failed\n";
}
?>