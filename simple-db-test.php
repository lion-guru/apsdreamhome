<?php

/**
 * Simple Database Test
 * Quick verification of our unified database system
 */

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/DatabaseManager.php';

use App\Config\DatabaseConfig;
use App\Core\DatabaseManager;

echo "🗄️ Database System Test\n";
echo "========================\n\n";

try {
    // Test 1: Configuration Loading
    echo "1. Testing Configuration Loading...\n";
    $config = DatabaseConfig::getInstance();
    $envInfo = $config->getEnvironmentInfo();
    echo "   ✅ Configuration loaded from: " . $envInfo['config_source'] . "\n";
    
    if (!empty($envInfo['warnings'])) {
        echo "   ⚠️  Warnings:\n";
        foreach ($envInfo['warnings'] as $warning) {
            echo "      - " . $warning . "\n";
        }
    }
    
    // Test 2: Database Connection
    echo "\n2. Testing Database Connection...\n";
    $db = DatabaseManager::getInstance();
    echo "   ✅ Database connection established successfully\n";
    
    // Test 3: Basic Query
    echo "\n3. Testing Basic Query...\n";
    $result = $db->selectOne("SELECT COUNT(*) as total FROM users");
    if ($result) {
        echo "   ✅ Found " . $result['total'] . " users in database\n";
    } else {
        echo "   ⚠️  No users found in database\n";
    }
    
    // Test 4: Table Existence
    echo "\n4. Testing Core Tables...\n";
    $tables = ['users', 'properties', 'projects', 'bookings'];
    foreach ($tables as $table) {
        try {
            $db->selectOne("SELECT 1 FROM {$table} LIMIT 1");
            echo "   ✅ Table '{$table}' exists\n";
        } catch (Exception $e) {
            echo "   ❌ Table '{$table}' not found: " . $e->getMessage() . "\n";
        }
    }
    
    // Test 5: Performance Stats
    echo "\n5. Performance Statistics...\n";
    $stats = $db->getPerformanceStats();
    echo "   Total Queries: " . $stats['total_queries'] . "\n";
    echo "   Cache Hit Rate: " . $stats['cache_hit_rate'] . "%\n";
    echo "   Slow Queries: " . $stats['slow_queries'] . "\n";
    echo "   Avg Execution Time: " . $stats['avg_execution_time'] . "s\n";
    
    echo "\n✅ All database tests completed successfully!\n";
    echo "\nNext Steps:\n";
    echo "- If all tests pass, proceed with routing system consolidation\n";
    echo "- Review performance statistics for optimization opportunities\n";
    echo "- Address any configuration warnings\n";
    
} catch (Exception $e) {
    echo "\n❌ Database Test Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    
    if (isset($config)) {
        echo "\nConfiguration Debug Info:\n";
        print_r($config->getEnvironmentInfo());
    }
}