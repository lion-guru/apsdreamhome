<?php

/**
 * Database System Test
 * Tests the unified database configuration and connection
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/DatabaseManager.php';

use App\Config\DatabaseConfig;
use App\Core\DatabaseManager;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { margin: 20px 0; padding: 15px; border-radius: 8px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">🗄️ Database System Test</h1>
        
        <?php
        $tests = [];
        
        try {
            // Test 1: Configuration Loading
            echo "<h3>Configuration Tests</h3>";
            
            $config = DatabaseConfig::getInstance();
            $tests['config_loading'] = ['status' => 'success', 'message' => 'Configuration loaded successfully'];
            
            // Display configuration info
            $envInfo = $config->getEnvironmentInfo();
            echo "<div class='test-result info'>";
            echo "<strong>Configuration Source:</strong> " . htmlspecialchars($envInfo['config_source']) . "<br>";
            echo "<strong>Available Methods:</strong> " . implode(', ', $envInfo['available_methods']) . "<br>";
            if (!empty($envInfo['warnings'])) {
                echo "<strong>Warnings:</strong><ul>";
                foreach ($envInfo['warnings'] as $warning) {
                    echo "<li>" . htmlspecialchars($warning) . "</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
            
            // Test 2: Database Connection
            echo "<h3>Connection Tests</h3>";
            
            $db = DatabaseManager::getInstance();
            $tests['connection'] = ['status' => 'success', 'message' => 'Database connection established'];
            
            echo "<div class='test-result success'>✅ Database connection successful!</div>";
            
            // Test 3: Basic Query Execution
            echo "<h3>Query Tests</h3>";
            
            // Test SELECT query
            $result = $db->selectOne("SELECT COUNT(*) as total FROM users");
            if ($result) {
                $tests['select_query'] = ['status' => 'success', 'message' => 'SELECT query executed successfully'];
                echo "<div class='test-result success'>✅ SELECT query successful - Found " . $result['total'] . " users</div>";
            } else {
                $tests['select_query'] = ['status' => 'warning', 'message' => 'No users found in database'];
                echo "<div class='test-result warning'>⚠️ No users found in database</div>";
            }
            
            // Test table existence
            $tables = ['users', 'properties', 'projects', 'bookings'];
            foreach ($tables as $table) {
                try {
                    $db->selectOne("SELECT 1 FROM {$table} LIMIT 1");
                    echo "<div class='test-result success'>✅ Table '{$table}' exists</div>";
                } catch (Exception $e) {
                    echo "<div class='test-result error'>❌ Table '{$table}' not found: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            
            // Test 4: Performance Monitoring
            echo "<h3>Performance Tests</h3>";
            
            // Run multiple queries to generate performance data
            for ($i = 0; $i < 5; $i++) {
                $db->selectOne("SELECT COUNT(*) as count FROM users WHERE id > :id", ['id' => $i]);
            }
            
            $stats = $db->getPerformanceStats();
            echo "<div class='test-result info'>";
            echo "<strong>Performance Statistics:</strong><br>";
            echo "Total Queries: " . $stats['total_queries'] . "<br>";
            echo "Cache Hits: " . $stats['cache_hits'] . " (" . $stats['cache_hit_rate'] . "%)<br>";
            echo "Slow Queries: " . $stats['slow_queries'] . "<br>";
            echo "Error Queries: " . $stats['error_queries'] . "<br>";
            echo "Average Execution Time: " . $stats['avg_execution_time'] . "s<br>";
            echo "Total Execution Time: " . $stats['total_execution_time'] . "s<br>";
            echo "Cache Size: " . $stats['cache_size'] . " entries<br>";
            echo "In Transaction: " . ($stats['in_transaction'] ? 'Yes' : 'No') . "<br>";
            echo "</div>";
            
            // Test 5: Transaction Support
            echo "<h3>Transaction Tests</h3>";
            
            try {
                $db->beginTransaction();
                $db->selectOne("SELECT 1");
                $db->commit();
                echo "<div class='test-result success'>✅ Transaction support working</div>";
            } catch (Exception $e) {
                echo "<div class='test-result error'>❌ Transaction failed: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
            // Test 6: CRUD Operations
            echo "<h3>CRUD Tests</h3>";
            
            try {
                // Test INSERT (with rollback)
                $db->beginTransaction();
                $testId = $db->insert('users', [
                    'name' => 'Test User',
                    'email' => 'test_' . time() . '@example.com',
                    'password' => password_hash('test123', PASSWORD_DEFAULT),
                    'utype' => 'user',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                echo "<div class='test-result success'>✅ INSERT successful - ID: " . $testId . "</div>";
                
                // Test UPDATE
                $updatedRows = $db->update('users', 
                    ['name' => 'Updated Test User'], 
                    ['id' => $testId]
                );
                
                echo "<div class='test-result success'>✅ UPDATE successful - Rows: " . $updatedRows . "</div>";
                
                // Test DELETE (rollback)
                $deletedRows = $db->delete('users', ['id' => $testId]);
                echo "<div class='test-result success'>✅ DELETE successful - Rows: " . $deletedRows . "</div>";
                
                // Rollback test changes
                $db->rollback();
                echo "<div class='test-result info'>🔄 Test changes rolled back</div>";
                
            } catch (Exception $e) {
                echo "<div class='test-result error'>❌ CRUD test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
                if ($db->inTransaction()) {
                    $db->rollback();
                }
            }
            
            // Summary
            echo "<h3>Test Summary</h3>";
            $passed = count(array_filter($tests, function($test) { return $test['status'] === 'success'; }));
            $total = count($tests);
            
            echo "<div class='test-result " . ($passed === $total ? 'success' : 'warning') . "'>";
            echo "<strong>Tests Passed:</strong> {$passed}/{$total}<br>";
            echo "<strong>Status:</strong> " . ($passed === $total ? 'All tests passed! ✅' : 'Some tests failed ⚠️');
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='test-result error'>";
            echo "<h4>❌ Database System Test Failed</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "</div>";
            
            // Configuration debug info
            if (isset($config)) {
                $envInfo = $config->getEnvironmentInfo();
                echo "<div class='test-result info'>";
                echo "<h5>Configuration Debug Info:</h5>";
                echo "<pre>" . htmlspecialchars(json_encode($envInfo, JSON_PRETTY_PRINT)) . "</pre>";
                echo "</div>";
            }
        }
        ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Back to Homepage</a>
            <a href="test-ui.php" class="btn btn-secondary">View UI Test</a>
            <button onclick="location.reload()" class="btn btn-outline-primary">Refresh Test</button>
        </div>
        
        <hr class="my-4">
        <div class="text-muted">
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>If all tests pass, proceed with routing system consolidation</li>
                <li>Check performance statistics for optimization opportunities</li>
                <li>Review configuration warnings and address security concerns</li>
                <li>Test with your actual database credentials</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>