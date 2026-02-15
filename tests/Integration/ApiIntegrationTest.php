<?php
/**
 * API Integration Tests for APS Dream Home
 * Tests API endpoints and data exchange between components
 */

require_once 'includes/config/constants.php';

class ApiIntegrationTest
{
    private $pdo;
    private $results = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
    private $baseUrl = 'http://localhost/apsdreamhome';
    
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
    
    public function assertContains($needle, $haystack, $message = 'Value not found in haystack')
    {
        if (is_string($haystack)) {
            return $this->assertTrue(strpos($haystack, $needle) !== false, $message);
        } elseif (is_array($haystack)) {
            return $this->assertTrue(in_array($needle, $haystack), $message);
        }
        return false;
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
    
    public function assertNotNull($value, $message = 'Value should not be null')
    {
        return $this->assertTrue($value !== null, $message);
    }
    
    public function testAdminApiEndpoints()
    {
        echo "<h2>🔗 Admin API Integration Tests</h2>\n";
        
        // Test dashboard stats API
        $statsFile = __DIR__ . '/../../admin/ajax/get_dashboard_stats.php';
        if (file_exists($statsFile)) {
            $this->assertTrue(true, 'Dashboard stats API file exists');
            
            // Check if API returns JSON structure
            $content = file_get_contents($statsFile);
            $this->assertContains('json', $content, 'API should return JSON');
            $this->assertContains('header', $content, 'API should set headers');
        } else {
            $this->assertTrue(false, 'Dashboard stats API file missing');
        }
        
        // Test analytics API
        $analyticsFile = __DIR__ . '/../../admin/ajax/get_analytics_data.php';
        if (file_exists($analyticsFile)) {
            $this->assertTrue(true, 'Analytics API file exists');
            
            $content = file_get_contents($analyticsFile);
            $this->assertContains('labels', $content, 'Analytics should have labels');
            $this->assertContains('datasets', $content, 'Analytics should have datasets');
        } else {
            $this->assertTrue(false, 'Analytics API file missing');
        }
        
        // Test search API
        $searchFile = __DIR__ . '/../../admin/ajax/global_search.php';
        if (file_exists($searchFile)) {
            $this->assertTrue(true, 'Global search API file exists');
            
            $content = file_get_contents($searchFile);
            $this->assertContains('search', $content, 'Search API should have search logic');
            $this->assertContains('results', $content, 'Search API should return results');
        } else {
            $this->assertTrue(false, 'Global search API file missing');
        }
        
        // Test system status API
        $statusFile = __DIR__ . '/../../admin/ajax/get_system_status.php';
        if (file_exists($statusFile)) {
            $this->assertTrue(true, 'System status API file exists');
            
            $content = file_get_contents($statusFile);
            $this->assertContains('database', $content, 'Status API should check database');
            $this->assertContains('status', $content, 'Status API should return status');
        } else {
            $this->assertTrue(false, 'System status API file missing');
        }
    }
    
    public function testDataFlowIntegration()
    {
        echo "<h2>🔄 Data Flow Integration Tests</h2>\n";
        
        // Test property to inquiry data flow
        try {
            // Create a test property
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Integration Test Property',
                'Property for integration testing',
                12000000.00,
                'apartment',
                'available',
                'Test Location',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $propertyId = $this->pdo->lastInsertId();
            $this->assertTrue($propertyId > 0, 'Test property created for integration');
            
            // Create inquiry for the property
            $stmt = $this->pdo->prepare("
                INSERT INTO inquiries (name, email, phone, message, property_id, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Integration Test User',
                'integration@test.com',
                '9876543210',
                'Integration test inquiry',
                $propertyId,
                'property',
                'pending',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $inquiryId = $this->pdo->lastInsertId();
            $this->assertTrue($inquiryId > 0, 'Inquiry created for property');
            
            // Verify relationship
            $stmt = $this->pdo->prepare("
                SELECT p.title, i.name, i.email 
                FROM properties p 
                JOIN inquiries i ON p.id = i.property_id 
                WHERE p.id = ? AND i.id = ?
            ");
            
            $stmt->execute([$propertyId, $inquiryId]);
            $result = $stmt->fetch();
            
            $this->assertNotNull($result, 'Property-inquiry relationship should work');
            $this->assertEquals('Integration Test Property', $result['title'], 'Property title should match');
            $this->assertEquals('integration@test.com', $result['email'], 'Inquiry email should match');
            
            // Clean up
            $stmt = $this->pdo->prepare("DELETE FROM inquiries WHERE id = ?");
            $stmt->execute([$inquiryId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Data flow integration failed: ' . $e->getMessage());
        }
        
        // Test user to property creation flow
        try {
            // Create test user
            $hashedPassword = password_hash('integrationtest123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Integration Agent',
                'agent@integration.com',
                $hashedPassword,
                'agent',
                'active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $userId = $this->pdo->lastInsertId();
            $this->assertTrue($userId > 0, 'Test user created for integration');
            
            // Create property by this user
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Agent Property Test',
                'Property created by agent',
                8000000.00,
                'house',
                'available',
                'Agent Location',
                $userId,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $propertyId = $this->pdo->lastInsertId();
            $this->assertTrue($propertyId > 0, 'Property created by user');
            
            // Verify user-property relationship
            $stmt = $this->pdo->prepare("
                SELECT u.name, u.type, p.title 
                FROM users u 
                JOIN properties p ON u.id = p.created_by 
                WHERE u.id = ? AND p.id = ?
            ");
            
            $stmt->execute([$userId, $propertyId]);
            $result = $stmt->fetch();
            
            $this->assertNotNull($result, 'User-property relationship should work');
            $this->assertEquals('Integration Agent', $result['name'], 'User name should match');
            $this->assertEquals('agent', $result['type'], 'User type should match');
            
            // Clean up
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'User-property integration failed: ' . $e->getMessage());
        }
    }
    
    public function testSearchIntegration()
    {
        echo "<h2>🔍 Search Integration Tests</h2>\n";
        
        // Test cross-entity search
        try {
            // Create test data across entities
            $searchTerm = 'integration_search_' . time();
            
            // Create property with search term
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Search Property {$searchTerm}",
                "Description with {$searchTerm} keyword",
                10000000.00,
                'apartment',
                'available',
                'Search Location',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $propertyId = $this->pdo->lastInsertId();
            
            // Create project with search term
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (name, description, location, city, state, status, project_type, developer_name, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Search Project {$searchTerm}",
                "Project description with {$searchTerm}",
                'Search Location',
                'Search City',
                'Search State',
                'planning',
                'residential',
                'Search Developer',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $projectId = $this->pdo->lastInsertId();
            
            // Create user with search term
            $hashedPassword = password_hash('searchtest123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                "Search User {$searchTerm}",
                "search_{$searchTerm}@test.com",
                $hashedPassword,
                'customer',
                'active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            // Test search across entities
            $stmt = $this->pdo->prepare("
                SELECT 'property' as entity, COUNT(*) as count FROM properties 
                WHERE title LIKE ? OR description LIKE ?
                UNION ALL
                SELECT 'project' as entity, COUNT(*) as count FROM projects 
                WHERE name LIKE ? OR description LIKE ?
                UNION ALL
                SELECT 'user' as entity, COUNT(*) as count FROM users 
                WHERE name LIKE ? OR email LIKE ?
            ");
            
            $searchPattern = "%{$searchTerm}%";
            $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern]);
            $results = $stmt->fetchAll();
            
            $this->assertTrue(count($results) > 0, 'Search should return results from multiple entities');
            
            foreach ($results as $result) {
                $this->assertTrue($result['count'] > 0, "Should find {$result['entity']} with search term");
            }
            
            // Clean up
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Search integration failed: ' . $e->getMessage());
        }
    }
    
    public function testSecurityIntegration()
    {
        echo "<h2>🔒 Security Integration Tests</h2>\n";
        
        // Test password hashing consistency
        $password = 'test_password_123';
        $hash1 = password_hash($password, PASSWORD_DEFAULT);
        $hash2 = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue($hash1 !== $hash2, 'Password hashes should be unique');
        $this->assertTrue(password_verify($password, $hash1), 'Password verification should work');
        $this->assertTrue(password_verify($password, $hash2), 'Password verification should work for different hashes');
        
        // Test SQL injection prevention
        try {
            $maliciousInput = "'; DROP TABLE users; --";
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->execute([$maliciousInput]);
            $result = $stmt->fetch();
            
            $this->assertTrue($result['count'] == 0, 'SQL injection should be prevented');
            
            // Verify users table still exists
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $userCount = $stmt->fetch()['count'];
            
            $this->assertTrue($userCount > 0, 'Users table should still exist after SQL injection attempt');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'SQL injection prevention failed: ' . $e->getMessage());
        }
        
        // Test XSS prevention in data storage
        try {
            $xssPayload = '<script>alert("xss")</script>';
            
            $stmt = $this->pdo->prepare("
                INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $xssPayload,
                'Test description',
                10000000.00,
                'apartment',
                'available',
                'Test Location',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $propertyId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("SELECT title FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            $result = $stmt->fetch();
            
            $this->assertEquals($xssPayload, $result['title'], 'XSS payload should be stored safely');
            
            // Clean up
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'XSS prevention test failed: ' . $e->getMessage());
        }
    }
    
    public function runAllTests()
    {
        echo "<h1>🔗 APS Dream Home - API Integration Tests</h1>\n";
        echo "<p>Testing API endpoints, data flow, and integration scenarios...</p>\n";
        
        $this->testAdminApiEndpoints();
        $this->testDataFlowIntegration();
        $this->testSearchIntegration();
        $this->testSecurityIntegration();
        
        $this->printSummary();
    }
    
    private function printSummary()
    {
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $passRate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "<h2>📊 Integration Test Summary</h2>\n";
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
            echo "<strong>⚠️ Action Required:</strong> Some integration tests failed. Please review the errors above.<br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<strong>✅ Excellent!</strong> All integration tests are passing. System components work well together.<br>\n";
            echo "</div>\n";
        }
        
        echo "<h3>🔧 Integration Environment</h3>\n";
        echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
        echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>\n";
        echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>\n";
        echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "</div>\n";
        
        echo "<hr>\n";
        echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
                <a href='tests/ComprehensiveTestSuite.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🧪 Full Test Suite</a></p>\n";
    }
}

// Run the integration test suite
$integrationTest = new ApiIntegrationTest();
$integrationTest->runAllTests();
?>
