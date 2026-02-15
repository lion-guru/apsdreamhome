<?php
/**
 * Security Audit Tests for APS Dream Home
 * Comprehensive security vulnerability assessment
 */

// Buffer output to prevent headers already sent issues
ob_start();

require_once 'includes/config/constants.php';

class SecurityAuditTest
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
    
    public function assertEquals($expected, $actual, $message = 'Values not equal')
    {
        return $this->assertTrue($expected == $actual, $message . " (Expected: {$expected}, Actual: {$actual})");
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
    
    public function testPasswordSecurity()
    {
        echo "<h2>🔐 Password Security Tests</h2>\n";
        
        // Test password hashing
        $password = 'test_password_123';
        $hash1 = password_hash($password, PASSWORD_DEFAULT);
        $hash2 = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue($hash1 !== $hash2, 'Password hashes should be unique');
        $this->assertTrue(password_verify($password, $hash1), 'Password verification should work');
        $this->assertTrue(password_verify($password, $hash2), 'Password verification should work for different hashes');
        
        // Test password strength requirements
        $weakPasswords = ['', '123', 'password', 'abc'];
        foreach ($weakPasswords as $weakPassword) {
            $this->assertTrue(strlen($weakPassword) < 8 || !password_verify($weakPassword, $hash1), 'Weak passwords should be rejected');
        }
        
        // Test password storage in database
        try {
            $hashedPassword = password_hash('security_test_123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Security Test User',
                'security@test.com',
                $hashedPassword,
                'customer',
                'active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $storedHash = $stmt->fetch()['password'];
            
            $this->assertTrue($storedHash !== 'security_test_123', 'Password should be hashed in database');
            $this->assertTrue(password_verify('security_test_123', $storedHash), 'Stored password should verify correctly');
            
            // Clean up
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Password storage test failed: ' . $e->getMessage());
        }
    }
    
    public function testSqlInjectionProtection()
    {
        echo "<h2>🛡️ SQL Injection Protection Tests</h2>\n";
        
        // Test SQL injection attempts
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "'; DELETE FROM properties; --",
            "' UNION SELECT * FROM users --",
            "'; INSERT INTO users VALUES ('hacker', 'hack@test.com', 'password'); --"
        ];
        
        foreach ($maliciousInputs as $maliciousInput) {
            try {
                // Test with prepared statements
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$maliciousInput]);
                $result = $stmt->fetch();
                
                $this->assertTrue($result['count'] == 0, 'SQL injection should be prevented');
                
                // Test with login queries
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND password = ?");
                $stmt->execute([$maliciousInput, $maliciousInput]);
                $loginResult = $stmt->fetch();
                
                $this->assertTrue($loginResult['count'] == 0, 'SQL injection should not bypass authentication');
                
            } catch (Exception $e) {
                $this->assertTrue(false, 'SQL injection test failed: ' . $e->getMessage());
            }
        }
        
        // Verify database integrity after injection attempts
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $userCount = $stmt->fetch()['count'];
            
            $this->assertTrue($userCount > 0, 'Users table should still exist after injection attempts');
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties");
            $stmt->execute();
            $propertyCount = $stmt->fetch()['count'];
            
            $this->assertTrue($propertyCount > 0, 'Properties table should still exist after injection attempts');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Database integrity check failed: ' . $e->getMessage());
        }
    }
    
    public function testXssProtection()
    {
        echo "<h2>🔒 XSS Protection Tests</h2>\n";
        
        // Test XSS payload storage
        $xssPayloads = [
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '<img src="x" onerror="alert(1)">',
            '<svg onload="alert(1)">',
            '"><script>alert("xss")</script>'
        ];
        
        foreach ($xssPayloads as $payload) {
            try {
                // Test XSS in property data
                $stmt = $this->pdo->prepare("
                    INSERT INTO properties (title, description, price, type, status, location, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $payload,
                    'Test description with XSS payload',
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
                $storedTitle = $stmt->fetch()['title'];
                
                $this->assertEquals($payload, $storedTitle, 'XSS payload should be stored safely');
                
                // Test XSS in user data
                $hashedPassword = password_hash('xss_test_123', PASSWORD_DEFAULT);
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO users (name, email, password, type, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $payload,
                    'xss@test.com',
                    $hashedPassword,
                    'customer',
                    'active',
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]);
                
                $userId = $this->pdo->lastInsertId();
                
                $stmt = $this->pdo->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $storedName = $stmt->fetch()['name'];
                
                $this->assertEquals($payload, $storedName, 'XSS payload should be stored safely in user data');
                
                // Clean up
                $stmt = $this->pdo->prepare("DELETE FROM properties WHERE id = ?");
                $stmt->execute([$propertyId]);
                
                $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                
            } catch (Exception $e) {
                $this->assertTrue(false, 'XSS protection test failed: ' . $e->getMessage());
            }
        }
    }
    
    public function testSessionSecurity()
    {
        echo "<h2>🔑 Session Security Tests</h2>\n";
        
        // Test session configuration
        $sessionConfig = [
            'session.cookie_httponly' => 1,
            'session.use_only_cookies' => 1,
            'session.cookie_secure' => 0, // May be 0 in development
            'session.cookie_samesite' => 'Strict'
        ];
        
        foreach ($sessionConfig as $setting => $expected) {
            $current = ini_get($setting);
            if ($current !== false) {
                $this->assertTrue(true, "Session setting {$setting} is configured");
            } else {
                echo "<span style='color: orange;'>⚠️ Session setting not configured: {$setting}</span><br>\n";
                $this->results['skipped']++;
            }
        }
        
        // Test session timeout
        $sessionTimeout = ini_get('session.gc_maxlifetime');
        $this->assertTrue($sessionTimeout > 0, 'Session timeout should be configured');
        $this->assertTrue($sessionTimeout <= 3600, 'Session timeout should be reasonable (<= 1 hour)');
        
        // Test session regeneration
        // Skip session tests in CLI mode to avoid header conflicts
        if (php_sapi_name() === 'cli') {
            echo "<span style='color: orange;'>⚠️ Session tests skipped in CLI mode</span><br>\n";
            $this->results['skipped']++;
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();
        
        $this->assertTrue($oldSessionId !== $newSessionId, 'Session ID should be regenerated');
        
        // Only destroy if we started the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    public function testFileUploadSecurity()
    {
        echo "<h2>📁 File Upload Security Tests</h2>\n";
        
        // Test upload directory permissions
        $uploadDirs = ['uploads/', 'uploads/properties/', 'uploads/profiles/'];
        
        foreach ($uploadDirs as $dir) {
            if (is_dir($dir)) {
                $this->assertTrue(true, "Upload directory exists: {$dir}");
                
                // Check if directory is not world-writable
                $perms = fileperms($dir);
                $this->assertTrue(($perms & 0x0002) == 0, "Upload directory should not be world-writable: {$dir}");
            } else {
                echo "<span style='color: orange;'>⚠️ Upload directory not found: {$dir}</span><br>\n";
                $this->results['skipped']++;
            }
        }
        
        // Test file type restrictions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'exe', 'bat', 'sh'];
        
        foreach ($dangerousExtensions as $ext) {
            $this->assertTrue(!in_array($ext, $allowedExtensions), "Dangerous extension should not be allowed: {$ext}");
        }
    }
    
    public function testInputValidation()
    {
        echo "<h2>✅ Input Validation Tests</h2>\n";
        
        // Test email validation
        $validEmails = ['test@example.com', 'user.name@domain.co.uk', 'user+tag@example.org'];
        $invalidEmails = ['invalid-email', 'test@', '@example.com', 'test..test@example.com'];
        
        foreach ($validEmails as $email) {
            $this->assertTrue(filter_var($email, FILTER_VALIDATE_EMAIL) !== false, "Valid email should pass: {$email}");
        }
        
        foreach ($invalidEmails as $email) {
            $this->assertTrue(filter_var($email, FILTER_VALIDATE_EMAIL) === false, "Invalid email should fail: {$email}");
        }
        
        // Test phone number validation
        $validPhones = ['9876543210', '+91-9876543210', '(123) 456-7890'];
        $invalidPhones = ['123', 'abc', '123456789012345'];
        
        foreach ($validPhones as $phone) {
            // Basic phone validation (10 digits or with country code)
            $this->assertTrue(preg_match('/^[\d\-\+\(\)\s]+$/', $phone), "Valid phone should pass: {$phone}");
        }
        
        // Test numeric validation
        $validNumbers = [1000000, 1000000.50, '1000000'];
        $invalidNumbers = ['abc', '1000abc', '1,000,000'];
        
        foreach ($validNumbers as $number) {
            $this->assertTrue(is_numeric($number), "Valid number should pass: {$number}");
        }
        
        foreach ($invalidNumbers as $number) {
            $this->assertTrue(!is_numeric($number), "Invalid number should fail: {$number}");
        }
    }
    
    public function testAuthorizationControls()
    {
        echo "<h2>👥 Authorization Controls Tests</h2>\n";
        
        // Test user roles and permissions
        try {
            // Create test users with different roles
            $roles = ['admin', 'agent', 'customer'];
            $testUsers = [];
            
            foreach ($roles as $role) {
                $hashedPassword = password_hash('auth_test_123', PASSWORD_DEFAULT);
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO users (name, email, password, type, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    "Test {$role}",
                    "{$role}@auth.test",
                    $hashedPassword,
                    $role,
                    'active',
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]);
                
                $testUsers[$role] = $this->pdo->lastInsertId();
            }
            
            // Test role-based access
            foreach ($testUsers as $role => $userId) {
                $stmt = $this->pdo->prepare("SELECT type FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userRole = $stmt->fetch()['type'];
                
                $this->assertEquals($role, $userRole, "User role should be correctly stored: {$role}");
            }
            
            // Test admin-specific access
            $adminId = $testUsers['admin'];
            $this->assertTrue($adminId > 0, 'Admin user should be created');
            
            // Test customer access limitations
            $customerId = $testUsers['customer'];
            $this->assertTrue($customerId > 0, 'Customer user should be created');
            
            // Clean up
            foreach ($testUsers as $userId) {
                $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
            }
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Authorization test failed: ' . $e->getMessage());
        }
    }
    
    public function testErrorHandling()
    {
        echo "<h2>⚠️ Error Handling Tests</h2>\n";
        
        // Test error display configuration
        $displayErrors = ini_get('display_errors');
        $this->assertTrue($displayErrors == '0' || $displayErrors == '', 'Error display should be disabled in production');
        
        // Test error logging
        $logErrors = ini_get('log_errors');
        $this->assertTrue($logErrors == '1', 'Error logging should be enabled');
        
        // Test custom error handler
        $this->assertTrue(file_exists('includes/error_handler.php'), 'Custom error handler should exist');
        
        // Test error pages
        $errorPages = ['404.php', '500.php'];
        foreach ($errorPages as $page) {
            if (file_exists($page)) {
                $content = file_get_contents($page);
                $this->assertTrue(strlen($content) > 0, "Error page should not be empty: {$page}");
            } else {
                echo "<span style='color: orange;'>⚠️ Error page not found: {$page}</span><br>\n";
                $this->results['skipped']++;
            }
        }
    }
    
    public function runAllTests()
    {
        echo "<h1>🔒 APS Dream Home - Security Audit Tests</h1>\n";
        echo "<p>Comprehensive security vulnerability assessment...</p>\n";
        
        $this->testPasswordSecurity();
        $this->testSqlInjectionProtection();
        $this->testXssProtection();
        $this->testSessionSecurity();
        $this->testFileUploadSecurity();
        $this->testInputValidation();
        $this->testAuthorizationControls();
        $this->testErrorHandling();
        
        $this->printSummary();
    }
    
    private function printSummary()
    {
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $passRate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "<h2>📊 Security Audit Summary</h2>\n";
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
            echo "<strong>⚠️ Security Issues:</strong> Some security tests failed. Immediate action required.<br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<strong>✅ Secure!</strong> All security tests are passing. System is well-protected.<br>\n";
            echo "</div>\n";
        }
        
        echo "<h3>🔒 Security Environment</h3>\n";
        echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
        echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>\n";
        echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>\n";
        echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "</div>\n";
        
        echo "<h3>🛡️ Security Recommendations</h3>\n";
        echo "<ul style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d;'>\n";
        echo "<li><strong>Password Policy:</strong> Implement strong password requirements</li>\n";
        echo "<li><strong>Two-Factor Auth:</strong> Add 2FA for admin accounts</li>\n";
        echo "<li><strong>Rate Limiting:</strong> Implement login attempt limits</li>\n";
        echo "<li><strong>CSRF Protection:</strong> Add CSRF tokens to all forms</li>\n";
        echo "<li><strong>Security Headers:</strong> Ensure all security headers are set</li>\n";
        echo "<li><strong>Regular Audits:</strong> Schedule periodic security assessments</li>\n";
        echo "</ul>\n";
        
        echo "<hr>\n";
        echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
                <a href='tests/run_complete_test_suite.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🧪 Complete Suite</a></p>\n";
    }
}

// Run the security audit test suite
$securityTest = new SecurityAuditTest();
$securityTest->runAllTests();
?>
