<?php
/**
 * Performance Tests for APS Dream Home
 * Tests system performance under various loads
 */

require_once 'includes/config/constants.php';

class PerformanceTest
{
    private $pdo;
    private $results = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
    
    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function assertTrue($condition, $message = 'Assertion failed')
    {
        if ($condition) {
            $this->results['passed']++;
            echo "<span style='color: green;'>✅ {$message}</span><br>\n";
            return true;
        } else {
            $this->results['failed']++;
            echo "<span style='color: red;'>❌ {$message}</span><br>\n";
            return false;
        }
    }
    
    public function assertEquals($expected, $actual, $message = 'Values not equal')
    {
        return $this->assertTrue($expected == $actual, $message . " (Expected: {$expected}, Actual: {$actual})");
    }
    
    public function testDatabaseQueryPerformance()
    {
        echo "<h2>⚡ Database Query Performance Tests</h2>\n";
        
        // Test simple query performance
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $stmt->fetch();
        }
        
        $endTime = microtime(true);
        $avgTime = (($endTime - $startTime) / 100) * 1000; // Convert to milliseconds
        
        $this->assertTrue($avgTime < 50, "Average query time should be < 50ms (Actual: " . round($avgTime, 2) . "ms)");
        
        // Test complex query performance
        $startTime = microtime(true);
        
        $stmt = $this->pdo->prepare("
            SELECT p.title, p.price, p.location, u.name as agent_name
            FROM properties p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.status = 'available'
            ORDER BY p.price DESC
            LIMIT 50
        ");
        $stmt->execute();
        $stmt->fetchAll();
        
        $endTime = microtime(true);
        $complexTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($complexTime < 200, "Complex query time should be < 200ms (Actual: " . round($complexTime, 2) . "ms)");
        
        // Test insert performance
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Performance Test Property {$i}",
                "Performance test description",
                10000000.00 + ($i * 100000),
                'apartment',
                'available',
                'Test Location',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
        }
        
        $endTime = microtime(true);
        $insertTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($insertTime < 500, "10 inserts should complete in < 500ms (Actual: " . round($insertTime, 2) . "ms)");
        
        // Clean up test data
        $stmt = $this->pdo->prepare("DELETE FROM properties WHERE title LIKE 'Performance Test Property%'");
        $stmt->execute();
    }
    
    public function testSearchPerformance()
    {
        echo "<h2>🔍 Search Performance Tests</h2>\n";
        
        // Test property search performance
        $startTime = microtime(true);
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM properties 
            WHERE title LIKE ? OR description LIKE ? OR location LIKE ?
            LIMIT 100
        ");
        $stmt->execute(['%test%', '%test%', '%test%']);
        $stmt->fetchAll();
        
        $endTime = microtime(true);
        $searchTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($searchTime < 100, "Property search should complete in < 100ms (Actual: " . round($searchTime, 2) . "ms)");
        
        // Test multi-entity search performance
        $startTime = microtime(true);
        
        // Search properties
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE title LIKE ?");
        $stmt->execute(['%search%']);
        $propertyCount = $stmt->fetch()['count'];
        
        // Search projects
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE name LIKE ?");
        $stmt->execute(['%search%']);
        $projectCount = $stmt->fetch()['count'];
        
        // Search users
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE name LIKE ?");
        $stmt->execute(['%search%']);
        $userCount = $stmt->fetch()['count'];
        
        $endTime = microtime(true);
        $multiSearchTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($multiSearchTime < 150, "Multi-entity search should complete in < 150ms (Actual: " . round($multiSearchTime, 2) . "ms)");
    }
    
    public function testMemoryUsage()
    {
        echo "<h2>💾 Memory Usage Tests</h2>\n";
        
        $initialMemory = memory_get_usage();
        
        // Test memory usage with large result sets
        $stmt = $this->pdo->prepare("SELECT * FROM properties LIMIT 1000");
        $stmt->execute();
        $properties = $stmt->fetchAll();
        
        $memoryAfterQuery = memory_get_usage();
        $memoryUsed = $memoryAfterQuery - $initialMemory;
        
        $this->assertTrue($memoryUsed < 10 * 1024 * 1024, "Memory usage should be < 10MB for 1000 properties (Actual: " . round($memoryUsed / 1024 / 1024, 2) . "MB)");
        
        // Test memory usage with joins
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.name as agent_name, u.email as agent_email
            FROM properties p
            LEFT JOIN users u ON p.created_by = u.id
            LIMIT 500
        ");
        $stmt->execute();
        $joinResults = $stmt->fetchAll();
        
        $memoryAfterJoin = memory_get_usage();
        $memoryUsedForJoin = $memoryAfterJoin - $initialMemory;
        
        $this->assertTrue($memoryUsedForJoin < 15 * 1024 * 1024, "Memory usage should be < 15MB for joins (Actual: " . round($memoryUsedForJoin / 1024 / 1024, 2) . "MB)");
    }
    
    public function testConcurrentOperations()
    {
        echo "<h2>🔄 Concurrent Operations Tests</h2>\n";
        
        // Simulate concurrent reads
        $startTime = microtime(true);
        
        $processes = [];
        for ($i = 0; $i < 5; $i++) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
            $stmt->execute();
            $processes[] = $stmt->fetch()['count'];
        }
        
        $endTime = microtime(true);
        $concurrentTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($concurrentTime < 100, "5 concurrent reads should complete in < 100ms (Actual: " . round($concurrentTime, 2) . "ms)");
        
        // Test concurrent writes (simulated)
        $startTime = microtime(true);
        
        for ($i = 0; $i < 5; $i++) {
            $stmt = $this->pdo->prepare("
                INSERT INTO inquiries (name, email, phone, message, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Concurrent Test User {$i}",
                "concurrent{$i}@test.com",
                "987654321{$i}",
                "Concurrent test message {$i}",
                'general',
                'pending',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
        }
        
        $endTime = microtime(true);
        $concurrentWriteTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($concurrentWriteTime < 200, "5 concurrent writes should complete in < 200ms (Actual: " . round($concurrentWriteTime, 2) . "ms)");
        
        // Clean up test data
        $stmt = $this->pdo->prepare("DELETE FROM inquiries WHERE email LIKE 'concurrent%@test.com'");
        $stmt->execute();
    }
    
    public function testFileIOPerformance()
    {
        echo "<h2>📁 File I/O Performance Tests</h2>\n";
        
        // Test config file reading
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $content = file_get_contents('includes/config/constants.php');
        }
        
        $endTime = microtime(true);
        $fileReadTime = ($endTime - $startTime) * 1000;
        
        $this->assertTrue($fileReadTime < 50, "10 file reads should complete in < 50ms (Actual: " . round($fileReadTime, 2) . "ms)");
        
        // Test template file reading
        if (file_exists('includes/templates/footer.php')) {
            $startTime = microtime(true);
            
            for ($i = 0; $i < 5; $i++) {
                $content = file_get_contents('includes/templates/footer.php');
            }
            
            $endTime = microtime(true);
            $templateReadTime = ($endTime - $startTime) * 1000;
            
            $this->assertTrue($templateReadTime < 30, "5 template reads should complete in < 30ms (Actual: " . round($templateReadTime, 2) . "ms)");
        }
    }
    
    public function testCachePerformance()
    {
        echo "<h2>🗄️ Cache Performance Tests</h2>\n";
        
        // Test query result caching (simulated)
        $startTime = microtime(true);
        
        // First query (cold cache)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties");
        $stmt->execute();
        $result1 = $stmt->fetch();
        
        $firstQueryTime = microtime(true);
        
        // Second query (warm cache)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties");
        $stmt->execute();
        $result2 = $stmt->fetch();
        
        $secondQueryTime = microtime(true);
        
        $coldTime = ($firstQueryTime - $startTime) * 1000;
        $warmTime = ($secondQueryTime - $firstQueryTime) * 1000;
        
        $this->assertEquals($result1['count'], $result2['count'], 'Cached result should match original');
        $this->assertTrue($warmTime <= $coldTime * 1.5, "Cached query should be faster or similar (Cold: " . round($coldTime, 2) . "ms, Warm: " . round($warmTime, 2) . "ms)");
    }
    
    public function runAllTests()
    {
        echo "<h1>⚡ APS Dream Home - Performance Tests</h1>\n";
        echo "<p>Testing system performance under various loads...</p>\n";
        
        $this->testDatabaseQueryPerformance();
        $this->testSearchPerformance();
        $this->testMemoryUsage();
        $this->testConcurrentOperations();
        $this->testFileIOPerformance();
        $this->testCachePerformance();
        
        $this->printSummary();
    }
    
    private function printSummary()
    {
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $passRate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "<h2>📊 Performance Test Summary</h2>\n";
        echo "<div style='background-color: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0;'>\n";
        echo "<h3>Overall Results</h3>\n";
        echo "<p><strong>Total Tests:</strong> {$total}</p>\n";
        echo "<p><strong>Passed:</strong> <span style='color: green;'>{$this->results['passed']}</span></p>\n";
        echo "<p><strong>Failed:</strong> <span style='color: red;'>{$this->results['failed']}</span></p>\n";
        echo "<p><strong>Skipped:</strong> <span style='color: orange;'>{$this->results['skipped']}</span></p>\n";
        echo "<p><strong>Pass Rate:</strong> <strong>{$passRate}%</strong></p>\n";
        echo "</div>\n";
        
        if ($this->results['failed'] > 0) {
            echo "<div style='background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>\n";
            echo "<strong>⚠️ Performance Issues:</strong> Some performance tests failed. Consider optimization.<br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<strong>✅ Excellent Performance!</strong> All performance tests are passing. System is optimized.<br>\n";
            echo "</div>\n";
        }
        
        echo "<h3>🔧 Performance Environment</h3>\n";
        echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
        echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>\n";
        echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>\n";
        echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>\n";
        echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</p>\n";
        echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "</div>\n";
        
        echo "<h3>💡 Performance Recommendations</h3>\n";
        echo "<ul style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d;'>\n";
        echo "<li><strong>Database:</strong> Consider adding indexes for frequently queried columns</li>\n";
        echo "<li><strong>Caching:</strong> Implement query result caching for repeated queries</li>\n";
        echo "<li><strong>File I/O:</strong> Use file system caching for templates and configs</li>\n";
        echo "<li><strong>Memory:</strong> Monitor memory usage during peak loads</li>\n";
        echo "<li><strong>Concurrency:</strong> Consider connection pooling for high traffic</li>\n";
        echo "</ul>\n";
        
        echo "<hr>\n";
        echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
                <a href='tests/ComprehensiveTestSuite.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🧪 Full Test Suite</a></p>\n";
    }
}

// Run the performance test suite
$performanceTest = new PerformanceTest();
$performanceTest->runAllTests();
?>
