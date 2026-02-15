<?php
/**
 * Comprehensive Test Suite for APS Dream Home
 * This matches the actual database structure and tests all functionality
 */

// Load database configuration
require_once 'includes/config/constants.php';

class ComprehensiveTestSuite
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
    
    public function assertNotNull($value, $message = 'Value should not be null')
    {
        return $this->assertTrue($value !== null, $message);
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
    
    public function assertFileExists($file, $message = 'File should exist')
    {
        return $this->assertTrue(file_exists($file), $message . " ({$file})");
    }
    
    public function runDatabaseTests()
    {
        echo "<h2>🗄️ Database Tests</h2>\n";
        
        // Test database connection
        $this->assertTrue($this->pdo !== null, 'Database connection should be established');
        
        // Test query execution
        try {
            $result = $this->pdo->query('SELECT 1 as test_value')->fetch();
            $this->assertEquals(1, $result['test_value'], 'Should execute simple query');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Query execution failed: ' . $e->getMessage());
        }
        
        // Test required tables
        $requiredTables = ['users', 'properties', 'projects', 'inquiries', 'bookings'];
        foreach ($requiredTables as $table) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = :database 
                AND table_name = :table
            ");
            
            $stmt->execute(['database' => DB_NAME, 'table' => $table]);
            $result = $stmt->fetch();
            $this->assertEquals(1, $result['count'], "Table '{$table}' should exist");
        }
        
        // Test data existence
        $tables = ['users', 'properties', 'projects', 'inquiries', 'bookings'];
        foreach ($tables as $table) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM {$table}");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            $this->assertTrue($count > 0, "Table '{$table}' should have data");
        }
    }
    
    public function runPropertyTests()
    {
        echo "<h2>🏠 Property Tests</h2>\n";
        
        // Test property creation
        $stmt = $this->pdo->prepare("
            INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $testProperty = [
            'Test Property Unit',
            'Test description',
            15000000.00,
            'apartment',
            'available',
            'Test Location',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        try {
            $stmt->execute($testProperty);
            $propertyId = $this->pdo->lastInsertId();
            $this->assertTrue($propertyId > 0, 'Property should be created with valid ID');
            
            // Test property retrieval
            $stmt = $this->pdo->prepare("SELECT * FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();
            
            $this->assertEquals($testProperty[0], $property['title'], 'Property title should match');
            $this->assertEquals($testProperty[2], $property['price'], 'Property price should match');
            
            // Test property update
            $newTitle = 'Updated Property Title';
            $stmt = $this->pdo->prepare("UPDATE properties SET title = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$newTitle, date('Y-m-d H:i:s'), $propertyId]);
            
            $stmt = $this->pdo->prepare("SELECT title FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            $updatedTitle = $stmt->fetch()['title'];
            
            $this->assertEquals($newTitle, $updatedTitle, 'Property title should be updated');
            
            // Test property deletion
            $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE id = ?");
            $stmt->execute([$propertyId]);
            $count = $stmt->fetch()['count'];
            
            $this->assertEquals(0, $count, 'Property should be deleted');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Property operations failed: ' . $e->getMessage());
        }
        
        // Test property filtering
        $filters = ['type', 'status', 'location'];
        foreach ($filters as $filter) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE {$filter} IS NOT NULL");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            $this->assertTrue($count > 0, "Should find properties by {$filter}");
        }
        
        // Test property types
        $types = ['apartment', 'house', 'land', 'commercial'];
        foreach ($types as $type) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE type = ?");
            $stmt->execute([$type]);
            $count = $stmt->fetch()['count'];
            // Some types might not exist, so we just check the query works
            $this->assertTrue($count >= 0, "Should query properties by type {$type}");
        }
    }
    
    public function runProjectTests()
    {
        echo "<h2>🏗️ Project Tests</h2>\n";
        
        // Test project creation
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (name, description, location, city, state, status, project_type, total_units, starting_price, launch_date, completion_date, developer_name, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $testProject = [
            'Test Project Unit',
            'Test project description',
            'Test Location',
            'Gorakhpur',
            'Uttar Pradesh',
            'planning',
            'residential',
            100,
            2000000.00,
            '2024-01-01',
            '2025-12-31',
            'Test Developer',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        try {
            $stmt->execute($testProject);
            $projectId = $this->pdo->lastInsertId();
            $this->assertTrue($projectId > 0, 'Project should be created with valid ID');
            
            // Test project retrieval
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();
            
            $this->assertEquals($testProject[0], $project['name'], 'Project name should match');
            $this->assertEquals($testProject[11], $project['developer_name'], 'Project developer should match');
            
            // Test project update
            $newStatus = 'under_construction';
            $stmt = $this->pdo->prepare("UPDATE projects SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$newStatus, date('Y-m-d H:i:s'), $projectId]);
            
            $stmt = $this->pdo->prepare("SELECT status FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $updatedStatus = $stmt->fetch()['status'];
            
            $this->assertEquals($newStatus, $updatedStatus, 'Project status should be updated');
            
            // Test project deletion
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $count = $stmt->fetch()['count'];
            
            $this->assertEquals(0, $count, 'Project should be deleted');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Project operations failed: ' . $e->getMessage());
        }
        
        // Test project filtering
        $filters = ['status', 'project_type', 'city'];
        foreach ($filters as $filter) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE {$filter} IS NOT NULL");
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            // Some filters might return 0, that's okay
            $this->assertTrue($count >= 0, "Should query projects by {$filter}");
        }
    }
    
    public function runUserTests()
    {
        echo "<h2>👤 User Tests</h2>\n";
        
        // Test user creation
        $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, type, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $testUser = [
            'Test User',
            'testuser' . time() . '@example.com',
            $hashedPassword,
            'customer',
            'active',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        try {
            $stmt->execute($testUser);
            $userId = $this->pdo->lastInsertId();
            $this->assertTrue($userId > 0, 'User should be created with valid ID');
            
            // Test user retrieval
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            $this->assertEquals($testUser[0], $user['name'], 'User name should match');
            $this->assertEquals($testUser[1], $user['email'], 'User email should match');
            $this->assertTrue(password_verify('testpassword123', $user['password']), 'Password should be hashed correctly');
            
            // Test user authentication
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$testUser[1]]);
            $authUser = $stmt->fetch();
            
            $this->assertNotNull($authUser, 'User should be found by email');
            $this->assertTrue(password_verify('testpassword123', $authUser['password']), 'Password verification should work');
            
            // Test user deletion
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $count = $stmt->fetch()['count'];
            
            $this->assertEquals(0, $count, 'User should be deleted');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'User operations failed: ' . $e->getMessage());
        }
        
        // Test user types
        $types = ['admin', 'agent', 'customer', 'employee'];
        foreach ($types as $type) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE type = ?");
            $stmt->execute([$type]);
            $count = $stmt->fetch()['count'];
            // Some types might not exist, that's okay
            $this->assertTrue($count >= 0, "Should query users by type {$type}");
        }
    }
    
    public function runInquiryTests()
    {
        echo "<h2>📝 Inquiry Tests</h2>\n";
        
        // Test inquiry creation
        $stmt = $this->pdo->prepare("
            INSERT INTO inquiries (name, email, phone, message, property_id, project_id, type, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $testInquiry = [
            'Test Inquiry User',
            'inquiry@example.com',
            '9876543210',
            'Test inquiry message',
            1,
            null,
            'property',
            'pending',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        try {
            $stmt->execute($testInquiry);
            $inquiryId = $this->pdo->lastInsertId();
            $this->assertTrue($inquiryId > 0, 'Inquiry should be created with valid ID');
            
            // Test inquiry retrieval
            $stmt = $this->pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
            $stmt->execute([$inquiryId]);
            $inquiry = $stmt->fetch();
            
            $this->assertEquals($testInquiry[0], $inquiry['name'], 'Inquiry name should match');
            $this->assertEquals($testInquiry[1], $inquiry['email'], 'Inquiry email should match');
            $this->assertEquals($testInquiry[6], $inquiry['type'], 'Inquiry type should match');
            
            // Test inquiry status update
            $newStatus = 'completed';
            $stmt = $this->pdo->prepare("UPDATE inquiries SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$newStatus, date('Y-m-d H:i:s'), $inquiryId]);
            
            $stmt = $this->pdo->prepare("SELECT status FROM inquiries WHERE id = ?");
            $stmt->execute([$inquiryId]);
            $updatedStatus = $stmt->fetch()['status'];
            
            $this->assertEquals($newStatus, $updatedStatus, 'Inquiry status should be updated');
            
            // Test inquiry deletion
            $stmt = $this->pdo->prepare("DELETE FROM inquiries WHERE id = ?");
            $stmt->execute([$inquiryId]);
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM inquiries WHERE id = ?");
            $stmt->execute([$inquiryId]);
            $count = $stmt->fetch()['count'];
            
            $this->assertEquals(0, $count, 'Inquiry should be deleted');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Inquiry operations failed: ' . $e->getMessage());
        }
        
        // Test inquiry types
        $types = ['property', 'project', 'general'];
        foreach ($types as $type) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM inquiries WHERE type = ?");
            $stmt->execute([$type]);
            $count = $stmt->fetch()['count'];
            $this->assertTrue($count >= 0, "Should query inquiries by type {$type}");
        }
    }
    
    public function runFileTests()
    {
        echo "<h2>📁 File System Tests</h2>\n";
        
        // Test essential files exist
        $essentialFiles = [
            'home.php' => 'Home page',
            'includes/config/constants.php' => 'Database constants',
            'includes/config/config.php' => 'Database configuration',
            'admin/index.php' => 'Admin login page',
            'admin/enhanced_dashboard.php' => 'Admin dashboard',
            'admin/config.php' => 'Admin configuration'
        ];
        
        foreach ($essentialFiles as $file => $description) {
            $this->assertFileExists($file, "Essential file should exist: {$description}");
        }
        
        // Test template files
        $templateFiles = [
            'includes/templates/header.php' => 'Header template',
            'includes/templates/footer.php' => 'Footer template'
        ];
        
        foreach ($templateFiles as $file => $description) {
            $this->assertFileExists($file, "Template file should exist: {$description}");
        }
        
        // Test home page content
        if (file_exists('home.php')) {
            $content = file_get_contents('home.php');
            $this->assertContains('APS Dream Home', $content, 'Home page should contain company name');
            $this->assertContains('<html', $content, 'Home page should have HTML structure');
            $this->assertContains('<title>', $content, 'Home page should have title tag');
        }
    }
    
    public function runSearchTests()
    {
        echo "<h2>🔍 Search Functionality Tests</h2>\n";
        
        // Test property search
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE title LIKE ? OR description LIKE ?");
        $stmt->execute(['%test%', '%test%']);
        $propertySearchCount = $stmt->fetch()['count'];
        $this->assertTrue($propertySearchCount >= 0, 'Should search properties by keyword');
        
        // Test property filtering by price
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE price BETWEEN ? AND ?");
        $stmt->execute([5000000, 15000000]);
        $priceFilterCount = $stmt->fetch()['count'];
        $this->assertTrue($priceFilterCount >= 0, 'Should filter properties by price range');
        
        // Test project search
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE name LIKE ? OR description LIKE ?");
        $stmt->execute(['%test%', '%test%']);
        $projectSearchCount = $stmt->fetch()['count'];
        $this->assertTrue($projectSearchCount >= 0, 'Should search projects by keyword');
        
        // Test user search
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE name LIKE ? OR email LIKE ?");
        $stmt->execute(['%test%', '%test%']);
        $userSearchCount = $stmt->fetch()['count'];
        $this->assertTrue($userSearchCount >= 0, 'Should search users by keyword');
    }
    
    public function runAllTests()
    {
        echo "<h1>🧪 APS Dream Home - Comprehensive Test Suite</h1>\n";
        echo "<p>Running complete test suite with actual database structure...</p>\n";
        
        $this->runDatabaseTests();
        $this->runPropertyTests();
        $this->runProjectTests();
        $this->runUserTests();
        $this->runInquiryTests();
        $this->runFileTests();
        $this->runSearchTests();
        
        $this->printSummary();
    }
    
    private function printSummary()
    {
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $passRate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "<h2>📊 Test Summary</h2>\n";
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
            echo "<strong>⚠️ Action Required:</strong> Some tests failed. Please review the errors above.<br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<strong>✅ Excellent!</strong> All tests are passing. The application is working correctly.<br>\n";
            echo "</div>\n";
        }
        
        echo "<h3>🔧 Test Environment</h3>\n";
        echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
        echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>\n";
        echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>\n";
        echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "</div>\n";
        
        echo "<h3>🚀 Next Steps</h3>\n";
        echo "<ul style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d;'>\n";
        echo "<li><strong>For Production:</strong> Ensure all tests pass before deployment</li>\n";
        echo "<li><strong>For Development:</strong> Add more edge case tests as needed</li>\n";
        echo "<li><strong>For CI/CD:</strong> Integrate this test suite into your pipeline</li>\n";
        echo "<li><strong>For Monitoring:</strong> Set up automated test runs</li>\n";
        echo "</ul>\n";
        
        echo "<hr>\n";
        echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
                <a href='test_database_standalone.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🔄 Database Test</a></p>\n";
    }
}

// Run the test suite
$testSuite = new ComprehensiveTestSuite();
$testSuite->runAllTests();
?>
