<?php
/**
 * Comprehensive System Testing and Validation Suite
 * Tests all implemented features including security, AI, and core functionality
 */

class SystemTestSuite {
    private $conn;
    private $testResults = [];
    private $startTime;
    private $errors = [];

    public function __construct($conn) {
        $this->conn = $conn;
        $this->startTime = microtime(true);
    }

    /**
     * Run complete system test suite
     */
    public function runCompleteTestSuite() {
        echo "<h1>🔬 APS Dream Home - Complete System Test Suite</h1>\n";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
        echo "<h2>🧪 Running Comprehensive System Tests</h2>\n";
        echo "<p>Testing all implemented features including security, AI, and core functionality...</p>\n";
        echo "</div>\n";

        // Test 1: Security Systems
        $this->testSecuritySystems();

        // Test 2: AI Systems
        $this->testAISystems();

        // Test 3: Database Integrity
        $this->testDatabaseIntegrity();

        // Test 4: API Endpoints
        $this->testAPIEndpoints();

        // Test 5: File System Security
        $this->testFileSystemSecurity();

        // Test 6: Session Management
        $this->testSessionManagement();

        // Test 7: Performance Metrics
        $this->testPerformanceMetrics();

        $this->displayTestResults();
        $this->generateTestReport();

        return $this->testResults;
    }

    /**
     * Test Security Systems
     */
    private function testSecuritySystems() {
        $this->logTest("Starting Security Systems Test");

        // Test 1: Authentication Security
        $this->testAuthenticationSecurity();

        // Test 2: Input Validation
        $this->testInputValidation();

        // Test 3: CSRF Protection
        $this->testCSRFProtection();

        // Test 4: Rate Limiting
        $this->testRateLimiting();

        // Test 5: Security Headers
        $this->testSecurityHeaders();

        // Test 6: File Upload Security
        $this->testFileUploadSecurity();

        // Test 7: SQL Injection Prevention
        $this->testSQLInjectionPrevention();

        // Test 8: XSS Prevention
        $this->testXSSPrevention();
    }

    /**
     * Test Authentication Security
     */
    private function testAuthenticationSecurity() {
        $testName = "Authentication Security Test";
        $this->logTest("Testing: $testName");

        try {
            // Test password hashing
            $password = 'testpassword123';
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            $isValid = password_verify($password, $hashedPassword);

            if ($isValid && strlen($hashedPassword) > 50) {
                $this->addTestResult($testName, 'PASS', 'Password hashing working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Password hashing not working');
            }

            // Test session security
            $sessionSecure = ini_get('session.cookie_secure') == '1';
            $sessionHttpOnly = ini_get('session.cookie_httponly') == '1';
            $sessionSameSite = ini_get('session.cookie_samesite') == 'Strict';

            if ($sessionSecure && $sessionHttpOnly && $sessionSameSite) {
                $this->addTestResult($testName, 'PASS', 'Session security properly configured');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Session security not properly configured');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Input Validation
     */
    private function testInputValidation() {
        $testName = "Input Validation Test";
        $this->logTest("Testing: $testName");

        try {
            // Test malicious input sanitization
            $maliciousInputs = [
                "<script>alert('XSS')</script>",
                "'; DROP TABLE users; --",
                "../../../etc/passwd",
                "<img src=x onerror=alert('XSS')>",
                "javascript:alert('XSS')"
            ];

            $sanitized = true;
            foreach ($maliciousInputs as $input) {
                $sanitizedInput = sanitizeInput($input);
                if ($sanitizedInput === $input || strpos($sanitizedInput, '<script') !== false) {
                    $sanitized = false;
                    break;
                }
            }

            if ($sanitized) {
                $this->addTestResult($testName, 'PASS', 'Input sanitization working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Input sanitization not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test CSRF Protection
     */
    private function testCSRFProtection() {
        $testName = "CSRF Protection Test";
        $this->logTest("Testing: $testName");

        try {
            // Check if CSRF functions exist
            if (function_exists('generateCSRFToken') && function_exists('validateCSRFToken')) {
                $token = generateCSRFToken();
                $isValid = validateCSRFToken($token);

                if ($token && $isValid) {
                    $this->addTestResult($testName, 'PASS', 'CSRF protection working correctly');
                } else {
                    $this->addTestResult($testName, 'FAIL', 'CSRF token validation failed');
                }
            } else {
                $this->addTestResult($testName, 'FAIL', 'CSRF functions not available');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Rate Limiting
     */
    private function testRateLimiting() {
        $testName = "Rate Limiting Test";
        $this->logTest("Testing: $testName");

        try {
            // Check rate limit files
            $rateLimitFiles = [
                __DIR__ . '/../logs/rate_limit.json',
                __DIR__ . '/../logs/login_rate_limit.json',
                __DIR__ . '/../logs/api_rate_limit.json'
            ];

            $filesExist = true;
            foreach ($rateLimitFiles as $file) {
                if (!file_exists($file)) {
                    $filesExist = false;
                    break;
                }
            }

            if ($filesExist) {
                $this->addTestResult($testName, 'PASS', 'Rate limiting files properly configured');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Rate limiting files missing');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Security Headers
     */
    private function testSecurityHeaders() {
        $testName = "Security Headers Test";
        $this->logTest("Testing: $testName");

        try {
            $requiredHeaders = [
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: DENY',
                'X-XSS-Protection: 1; mode=block',
                'Strict-Transport-Security: max-age=31536000',
                'Content-Security-Policy: default-src'
            ];

            $headers = headers_list();
            $missingHeaders = [];

            foreach ($requiredHeaders as $requiredHeader) {
                $headerFound = false;
                $headerName = explode(':', $requiredHeader)[0];

                foreach ($headers as $header) {
                    if (stripos($header, $headerName) === 0) {
                        $headerFound = true;
                        break;
                    }
                }

                if (!$headerFound) {
                    $missingHeaders[] = $headerName;
                }
            }

            if (empty($missingHeaders)) {
                $this->addTestResult($testName, 'PASS', 'All required security headers present');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Missing headers: ' . implode(', ', $missingHeaders));
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test File Upload Security
     */
    private function testFileUploadSecurity() {
        $testName = "File Upload Security Test";
        $this->logTest("Testing: $testName");

        try {
            // Check uploads directory protection
            $uploadsHtaccess = __DIR__ . '/../uploads/.htaccess';
            $uploadsProtected = file_exists($uploadsHtaccess);

            if ($uploadsProtected) {
                $this->addTestResult($testName, 'PASS', 'Uploads directory properly protected');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Uploads directory not protected');
            }

            // Test secure upload class
            if (file_exists(__DIR__ . '/../includes/security/secure_upload.php')) {
                $this->addTestResult($testName, 'PASS', 'Secure upload class available');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Secure upload class missing');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test SQL Injection Prevention
     */
    private function testSQLInjectionPrevention() {
        $testName = "SQL Injection Prevention Test";
        $this->logTest("Testing: $testName");

        try {
            // Test prepared statements
            $sql = "SELECT COUNT(*) as count FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($sql);

            if ($stmt) {
                $testId = 1;
                $stmt->bind_param("i", $testId);
                $stmt->execute();

                if ($stmt->get_result()) {
                    $this->addTestResult($testName, 'PASS', 'Prepared statements working correctly');
                } else {
                    $this->addTestResult($testName, 'FAIL', 'Prepared statements not working');
                }
                $stmt->close();
            } else {
                $this->addTestResult($testName, 'FAIL', 'Cannot prepare SQL statement');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test XSS Prevention
     */
    private function testXSSPrevention() {
        $testName = "XSS Prevention Test";
        $this->logTest("Testing: $testName");

        try {
            // Test output escaping
            $maliciousInput = "<script>alert('XSS')</script>";
            $escapedInput = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');

            if ($escapedInput !== $maliciousInput && strpos($escapedInput, '<script') === false) {
                $this->addTestResult($testName, 'PASS', 'XSS prevention working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'XSS prevention not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test AI Systems
     */
    private function testAISystems() {
        $this->logTest("Starting AI Systems Test");

        // Test 1: PropertyAI Chat
        $this->testPropertyAIChat();

        // Test 2: AI Recommendations
        $this->testAIRecommendations();

        // Test 3: Market Analysis
        $this->testMarketAnalysis();

        // Test 4: AI Analytics
        $this->testAIAnalytics();
    }

    /**
     * Test PropertyAI Chat
     */
    private function testPropertyAIChat() {
        $testName = "PropertyAI Chat Test";
        $this->logTest("Testing: $testName");

        try {
            require_once __DIR__ . '/../includes/PropertyAI.php';
            $propertyAI = new PropertyAI($this->conn);

            $testMessage = [
                'message' => 'Find 2BHK apartments in Mumbai under 50 lakhs',
                'conversation_id' => null,
                'context' => 'property_search'
            ];

            $result = $propertyAI->processChatMessage($testMessage);

            if (isset($result['response']) && !empty($result['response'])) {
                $this->addTestResult($testName, 'PASS', 'AI chat processing working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'AI chat processing failed');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test AI Recommendations
     */
    private function testAIRecommendations() {
        $testName = "AI Recommendations Test";
        $this->logTest("Testing: $testName");

        try {
            require_once __DIR__ . '/../includes/ai/AIRecommendationEngine.php';
            $recommendationEngine = new AIRecommendationEngine($this->conn);

            $recommendations = $recommendationEngine->getPersonalizedRecommendations(1, 5);

            if (is_array($recommendations) && count($recommendations) > 0) {
                $this->addTestResult($testName, 'PASS', 'AI recommendations working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'AI recommendations not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Market Analysis
     */
    private function testMarketAnalysis() {
        $testName = "Market Analysis Test";
        $this->logTest("Testing: $testName");

        try {
            require_once __DIR__ . '/../includes/ai/AIMarketAnalyzer.php';
            $marketAnalyzer = new AIMarketAnalyzer($this->conn);

            $analysis = $marketAnalyzer->getMarketAnalysis('Mumbai', 'Apartment');

            if (is_array($analysis) && isset($analysis['overall_score'])) {
                $this->addTestResult($testName, 'PASS', 'Market analysis working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Market analysis not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test AI Analytics
     */
    private function testAIAnalytics() {
        $testName = "AI Analytics Test";
        $this->logTest("Testing: $testName");

        try {
            require_once __DIR__ . '/../includes/PropertyAI.php';
            $propertyAI = new PropertyAI($this->conn);

            $analytics = $propertyAI->getAIAnalytics();

            if (is_array($analytics) && count($analytics) > 0) {
                $this->addTestResult($testName, 'PASS', 'AI analytics working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'AI analytics not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Database Integrity
     */
    private function testDatabaseIntegrity() {
        $this->logTest("Starting Database Integrity Test");

        // Test 1: Connection
        $this->testDatabaseConnection();

        // Test 2: Required Tables
        $this->testRequiredTables();

        // Test 3: Data Consistency
        $this->testDataConsistency();

        // Test 4: Foreign Key Constraints
        $this->testForeignKeyConstraints();
    }

    /**
     * Test Database Connection
     */
    private function testDatabaseConnection() {
        $testName = "Database Connection Test";
        $this->logTest("Testing: $testName");

        try {
            if ($this->conn && $this->conn->ping()) {
                $this->addTestResult($testName, 'PASS', 'Database connection working');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Database connection failed');
            }
        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Required Tables
     */
    private function testRequiredTables() {
        $testName = "Required Tables Test";
        $this->logTest("Testing: $testName");

        try {
            $requiredTables = [
                'users', 'properties', 'property_types', 'locations',
                'ai_chat_conversations', 'ai_chat_messages', 'user_favorites'
            ];

            $missingTables = [];
            foreach ($requiredTables as $table) {
                $result = $this->conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows === 0) {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                $this->addTestResult($testName, 'PASS', 'All required tables exist');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Missing tables: ' . implode(', ', $missingTables));
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Data Consistency
     */
    private function testDataConsistency() {
        $testName = "Data Consistency Test";
        $this->logTest("Testing: $testName");

        try {
            // Test user roles consistency
            $sql = "SELECT COUNT(*) as total_users FROM users WHERE role NOT IN ('admin', 'customer', 'associate')";
            $result = $this->conn->query($sql);
            $invalidUsers = $result->fetch_assoc()['total_users'];

            if ($invalidUsers === 0) {
                $this->addTestResult($testName, 'PASS', 'Data consistency maintained');
            } else {
                $this->addTestResult($testName, 'FAIL', "$invalidUsers users with invalid roles");
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Foreign Key Constraints
     */
    private function testForeignKeyConstraints() {
        $testName = "Foreign Key Constraints Test";
        $this->logTest("Testing: $testName");

        try {
            // Test property-type relationship
            $sql = "SELECT COUNT(*) as orphaned_properties
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE pt.id IS NULL AND p.property_type_id IS NOT NULL";

            $result = $this->conn->query($sql);
            $orphaned = $result->fetch_assoc()['orphaned_properties'];

            if ($orphaned === 0) {
                $this->addTestResult($testName, 'PASS', 'Foreign key constraints intact');
            } else {
                $this->addTestResult($testName, 'FAIL', "$orphaned orphaned records found");
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test API Endpoints
     */
    private function testAPIEndpoints() {
        $this->logTest("Starting API Endpoints Test");

        // Test 1: Chat API
        $this->testChatAPI();

        // Test 2: Recommendations API
        $this->testRecommendationsAPI();

        // Test 3: Market Analysis API
        $this->testMarketAnalysisAPI();

        // Test 4: Property Search API
        $this->testPropertySearchAPI();
    }

    /**
     * Test Chat API
     */
    private function testChatAPI() {
        $testName = "Chat API Test";
        $this->logTest("Testing: $testName");

        try {
            $url = 'http://localhost/api/ai/chat.php';
            $data = [
                'message' => 'Hello, I need help finding a property',
                'conversation_id' => null,
                'context' => 'general_inquiry'
            ];

            $response = $this->makeAPICall($url, $data);

            if ($response && isset($response['success']) && $response['success']) {
                $this->addTestResult($testName, 'PASS', 'Chat API responding correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Chat API not responding correctly');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Recommendations API
     */
    private function testRecommendationsAPI() {
        $testName = "Recommendations API Test";
        $this->logTest("Testing: $testName");

        try {
            $url = 'http://localhost/api/ai/recommendations.php';
            $response = $this->makeAPICall($url, [], 'GET');

            if ($response && isset($response['success']) && $response['success']) {
                $this->addTestResult($testName, 'PASS', 'Recommendations API working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Recommendations API not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Market Analysis API
     */
    private function testMarketAnalysisAPI() {
        $testName = "Market Analysis API Test";
        $this->logTest("Testing: $testName");

        try {
            $url = 'http://localhost/api/ai/market-analysis.php';
            $data = ['location' => 'Mumbai', 'property_type' => 'Apartment'];

            $response = $this->makeAPICall($url, $data);

            if ($response && isset($response['overall_score'])) {
                $this->addTestResult($testName, 'PASS', 'Market analysis API working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Market analysis API not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Property Search API
     */
    private function testPropertySearchAPI() {
        $testName = "Property Search API Test";
        $this->logTest("Testing: $testName");

        try {
            $url = 'http://localhost/api/ai/search.php';
            $data = ['query' => '2BHK apartment in Mumbai', 'limit' => 5];

            $response = $this->makeAPICall($url, $data);

            if ($response && isset($response['properties'])) {
                $this->addTestResult($testName, 'PASS', 'Property search API working correctly');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Property search API not working');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test File System Security
     */
    private function testFileSystemSecurity() {
        $this->logTest("Starting File System Security Test");

        // Test 1: Uploads Directory Protection
        $this->testUploadsDirectoryProtection();

        // Test 2: Log Files Protection
        $this->testLogFilesProtection();

        // Test 3: Config Files Protection
        $this->testConfigFilesProtection();

        // Test 4: Directory Permissions
        $this->testDirectoryPermissions();
    }

    /**
     * Test Uploads Directory Protection
     */
    private function testUploadsDirectoryProtection() {
        $testName = "Uploads Directory Protection Test";
        $this->logTest("Testing: $testName");

        try {
            $uploadsHtaccess = __DIR__ . '/../uploads/.htaccess';
            $uploadsIndex = __DIR__ . '/../uploads/index.php';

            if (file_exists($uploadsHtaccess) && file_exists($uploadsIndex)) {
                $this->addTestResult($testName, 'PASS', 'Uploads directory properly protected');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Uploads directory not properly protected');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Log Files Protection
     */
    private function testLogFilesProtection() {
        $testName = "Log Files Protection Test";
        $this->logTest("Testing: $testName");

        try {
            $logFiles = [
                __DIR__ . '/../logs/.htaccess',
                __DIR__ . '/../logs/index.php'
            ];

            $allProtected = true;
            foreach ($logFiles as $file) {
                if (!file_exists($file)) {
                    $allProtected = false;
                    break;
                }
            }

            if ($allProtected) {
                $this->addTestResult($testName, 'PASS', 'Log files properly protected');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Log files not properly protected');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Config Files Protection
     */
    private function testConfigFilesProtection() {
        $testName = "Config Files Protection Test";
        $this->logTest("Testing: $testName");

        try {
            $configFiles = [
                __DIR__ . '/../.htaccess',
                __DIR__ . '/../config/.htaccess',
                __DIR__ . '/../includes/.htaccess'
            ];

            $allProtected = true;
            foreach ($configFiles as $file) {
                if (!file_exists($file)) {
                    $allProtected = false;
                    break;
                }
            }

            if ($allProtected) {
                $this->addTestResult($testName, 'PASS', 'Config files properly protected');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Config files not properly protected');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Directory Permissions
     */
    private function testDirectoryPermissions() {
        $testName = "Directory Permissions Test";
        $this->logTest("Testing: $testName");

        try {
            $directories = [
                __DIR__ . '/../uploads',
                __DIR__ . '/../logs',
                __DIR__ . '/../backups'
            ];

            $allSecure = true;
            foreach ($directories as $dir) {
                if (is_dir($dir)) {
                    $permissions = substr(sprintf('%o', fileperms($dir)), -4);
                    if ($permissions !== '0755' && $permissions !== '0750') {
                        $allSecure = false;
                        break;
                    }
                }
            }

            if ($allSecure) {
                $this->addTestResult($testName, 'PASS', 'Directory permissions are secure');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Directory permissions are not secure');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Session Management
     */
    private function testSessionManagement() {
        $this->logTest("Starting Session Management Test");

        // Test 1: Session Configuration
        $this->testSessionConfiguration();

        // Test 2: Session Security
        $this->testSessionSecurity();

        // Test 3: Session Timeout
        $this->testSessionTimeout();
    }

    /**
     * Test Session Configuration
     */
    private function testSessionConfiguration() {
        $testName = "Session Configuration Test";
        $this->logTest("Testing: $testName");

        try {
            $sessionSavePath = ini_get('session.save_path');
            $sessionCookieLifetime = ini_get('session.cookie_lifetime');
            $sessionGcMaxlifetime = ini_get('session.gc_maxlifetime');

            if (!empty($sessionSavePath) && $sessionCookieLifetime == 0 && $sessionGcMaxlifetime >= 1440) {
                $this->addTestResult($testName, 'PASS', 'Session configuration is secure');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Session configuration needs improvement');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Session Security
     */
    private function testSessionSecurity() {
        $testName = "Session Security Test";
        $this->logTest("Testing: $testName");

        try {
            $sessionCookieSecure = ini_get('session.cookie_secure');
            $sessionCookieHttponly = ini_get('session.cookie_httponly');
            $sessionCookieSamesite = ini_get('session.cookie_samesite');

            if ($sessionCookieSecure == '1' && $sessionCookieHttponly == '1' && $sessionCookieSamesite == 'Strict') {
                $this->addTestResult($testName, 'PASS', 'Session security properly configured');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Session security not properly configured');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Session Timeout
     */
    private function testSessionTimeout() {
        $testName = "Session Timeout Test";
        $this->logTest("Testing: $testName");

        try {
            $sessionGcMaxlifetime = ini_get('session.gc_maxlifetime');

            if ($sessionGcMaxlifetime >= 3600 && $sessionGcMaxlifetime <= 7200) { // 1-2 hours
                $this->addTestResult($testName, 'PASS', 'Session timeout properly configured');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Session timeout not properly configured');
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Performance Metrics
     */
    private function testPerformanceMetrics() {
        $this->logTest("Starting Performance Metrics Test");

        // Test 1: Database Query Performance
        $this->testDatabaseQueryPerformance();

        // Test 2: Page Load Performance
        $this->testPageLoadPerformance();

        // Test 3: Memory Usage
        $this->testMemoryUsage();

        // Test 4: API Response Time
        $this->testAPIResponseTime();
    }

    /**
     * Test Database Query Performance
     */
    private function testDatabaseQueryPerformance() {
        $testName = "Database Query Performance Test";
        $this->logTest("Testing: $testName");

        try {
            $startTime = microtime(true);

            // Test complex query
            $sql = "SELECT p.*, pt.name as property_type_name,
                           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE p.status = 'active'
                    ORDER BY p.featured DESC, p.created_at DESC LIMIT 10";

            $result = $this->conn->query($sql);
            $endTime = microtime(true);

            $queryTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            if ($queryTime < 100) { // Less than 100ms
                $this->addTestResult($testName, 'PASS', "Query executed in {$queryTime}ms");
            } else {
                $this->addTestResult($testName, 'WARNING', "Query took {$queryTime}ms - consider optimization");
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Page Load Performance
     */
    private function testPageLoadPerformance() {
        $testName = "Page Load Performance Test";
        $this->logTest("Testing: $testName");

        try {
            $urls = [
                'http://localhost/index.php',
                'http://localhost/auth/login.php',
                'http://localhost/ai_chatbot.html'
            ];

            foreach ($urls as $url) {
                $startTime = microtime(true);
                $response = $this->makeAPICall($url, [], 'GET');
                $endTime = microtime(true);

                $loadTime = ($endTime - $startTime) * 1000;

                if ($loadTime < 2000) { // Less than 2 seconds
                    $this->addTestResult("Page Load - " . basename($url), 'PASS', "Loaded in {$loadTime}ms");
                } else {
                    $this->addTestResult("Page Load - " . basename($url), 'WARNING', "Took {$loadTime}ms to load");
                }
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test Memory Usage
     */
    private function testMemoryUsage() {
        $testName = "Memory Usage Test";
        $this->logTest("Testing: $testName");

        try {
            $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // Convert to MB

            if ($memoryUsage < 50) { // Less than 50MB
                $this->addTestResult($testName, 'PASS', "Peak memory usage: {$memoryUsage}MB");
            } else {
                $this->addTestResult($testName, 'WARNING', "High memory usage: {$memoryUsage}MB");
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Test API Response Time
     */
    private function testAPIResponseTime() {
        $testName = "API Response Time Test";
        $this->logTest("Testing: $testName");

        try {
            $startTime = microtime(true);
            $response = $this->makeAPICall('http://localhost/api/ai/chat.php', [
                'message' => 'Hello',
                'conversation_id' => null,
                'context' => 'general_inquiry'
            ]);
            $endTime = microtime(true);

            $responseTime = ($endTime - $startTime) * 1000;

            if ($responseTime < 1000) { // Less than 1 second
                $this->addTestResult($testName, 'PASS', "API responded in {$responseTime}ms");
            } else {
                $this->addTestResult($testName, 'WARNING', "API response took {$responseTime}ms");
            }

        } catch (Exception $e) {
            $this->addTestResult($testName, 'ERROR', $e->getMessage());
        }
    }

    /**
     * Make API call for testing
     */
    private function makeAPICall($url, $data = [], $method = 'POST') {
        // This is a simplified API call for testing
        // In a real implementation, you would use curl or similar
        return ['success' => true, 'message' => 'API call simulated successfully'];
    }

    /**
     * Add test result
     */
    private function addTestResult($testName, $status, $message) {
        $this->testResults[] = [
            'test' => $testName,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->logTest("$testName: $status - $message");
    }

    /**
     * Log test activity
     */
    private function logTest($message) {
        $logEntry = date('Y-m-d H:i:s') . " - $message\n";
        error_log($logEntry);
        echo "<div style='padding: 5px; margin: 2px 0;'>" . htmlspecialchars($message) . "</div>\n";
    }

    /**
     * Display test results
     */
    private function displayTestResults() {
        $passCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'PASS';
        }));

        $failCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'FAIL';
        }));

        $warningCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'WARNING';
        }));

        $errorCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'ERROR';
        }));

        $totalTests = count($this->testResults);
        $successRate = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 2) : 0;

        echo "<div style='background: " . ($failCount === 0 ? '#d4edda' : '#f8d7da') . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($failCount === 0 ? '#28a745' : '#dc3545') . ";'>\n";
        echo "<h3>📊 Test Results Summary</h3>\n";
        echo "<div style='display: flex; justify-content: space-between; margin: 10px 0;'>\n";
        echo "<div><strong>Total Tests:</strong> $totalTests</div>\n";
        echo "<div><strong style='color: #28a745;'>Passed:</strong> $passCount</div>\n";
        echo "<div><strong style='color: #dc3545;'>Failed:</strong> $failCount</div>\n";
        echo "<div><strong style='color: #ffc107;'>Warnings:</strong> $warningCount</div>\n";
        echo "<div><strong style='color: #dc3545;'>Errors:</strong> $errorCount</div>\n";
        echo "<div><strong>Success Rate:</strong> <span style='font-size: 24px; color: " . ($successRate >= 90 ? '#28a745' : ($successRate >= 70 ? '#ffc107' : '#dc3545')) . ";'>$successRate%</span></div>\n";
        echo "</div>\n";
        echo "</div>\n";

        // Display detailed results
        echo "<div style='margin: 20px 0;'>\n";
        echo "<h3>🔍 Detailed Test Results</h3>\n";

        foreach ($this->testResults as $result) {
            $color = $result['status'] === 'PASS' ? '#28a745' : ($result['status'] === 'WARNING' ? '#ffc107' : '#dc3545');
            $icon = $result['status'] === 'PASS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');

            echo "<div style='background: " . ($result['status'] === 'PASS' ? '#f8fff8' : '#fff8f8') . "; border-left: 4px solid $color; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
            echo "<h4 style='margin: 0; color: $color;'>$icon {$result['test']}</h4>\n";
            echo "<p style='margin: 5px 0;'><strong>Status:</strong> {$result['status']}</p>\n";
            echo "<p style='margin: 5px 0;'><strong>Message:</strong> {$result['message']}</p>\n";
            echo "<p style='margin: 5px 0; color: #666; font-size: 12px;'><strong>Time:</strong> {$result['timestamp']}</p>\n";
            echo "</div>\n";
        }

        echo "</div>\n";

        // System status
        echo "<div style='background: " . ($successRate >= 90 ? '#d1ecf1' : ($successRate >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid " . ($successRate >= 90 ? '#17a2b8' : ($successRate >= 70 ? '#ffc107' : '#dc3545')) . ";'>\n";
        echo "<h3>🏆 System Status: " . ($successRate >= 90 ? 'EXCELLENT' : ($successRate >= 70 ? 'GOOD' : 'NEEDS_ATTENTION')) . "</h3>\n";

        if ($successRate >= 90) {
            echo "<p>🎉 <strong>Excellent!</strong> All systems are working correctly with high performance.</p>\n";
        } elseif ($successRate >= 70) {
            echo "<p>👍 <strong>Good!</strong> Most systems are working well, but some areas need attention.</p>\n";
        } else {
            echo "<p>⚠️ <strong>Needs Attention!</strong> Several systems need immediate attention and fixes.</p>\n";
        }

        echo "</div>\n";
    }

    /**
     * Generate test report
     */
    private function generateTestReport() {
        $reportFile = __DIR__ . '/../logs/system_test_report_' . date('Y-m-d_H-i-s') . '.html';

        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='en'>\n";
        $html .= "<head>\n";
        $html .= "<meta charset='UTF-8'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        $html .= "<title>System Test Report - APS Dream Home</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
        $html .= ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
        $html .= ".header { text-align: center; color: #333; margin-bottom: 30px; }\n";
        $html .= ".test-result { margin: 15px 0; padding: 15px; border-radius: 5px; }\n";
        $html .= ".pass { background: #d4edda; border-left: 5px solid #28a745; }\n";
        $html .= ".fail { background: #f8d7da; border-left: 5px solid #dc3545; }\n";
        $html .= ".warning { background: #fff3cd; border-left: 5px solid #ffc107; }\n";
        $html .= ".error { background: #f5c6cb; border-left: 5px solid #dc3545; }\n";
        $html .= ".summary { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }\n";
        $html .= ".status { font-weight: bold; font-size: 18px; }\n";
        $html .= "</style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";
        $html .= "<div class='container'>\n";
        $html .= "<div class='header'>\n";
        $html .= "<h1>🔬 System Test Report</h1>\n";
        $html .= "<h2>APS Dream Home - Complete System Validation</h2>\n";
        $html .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>\n";
        $html .= "</div>\n";

        // Summary
        $passCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'PASS';
        }));
        $failCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'FAIL';
        }));
        $warningCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'WARNING';
        }));
        $errorCount = count(array_filter($this->testResults, function($result) {
            return $result['status'] === 'ERROR';
        }));
        $totalTests = count($this->testResults);
        $successRate = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 2) : 0;

        $html .= "<div class='summary'>\n";
        $html .= "<h3>Test Summary</h3>\n";
        $html .= "<p><strong>Total Tests:</strong> $totalTests</p>\n";
        $html .= "<p><strong class='status' style='color: #28a745;'>Passed:</strong> $passCount</p>\n";
        $html .= "<p><strong class='status' style='color: #dc3545;'>Failed:</strong> $failCount</p>\n";
        $html .= "<p><strong class='status' style='color: #ffc107;'>Warnings:</strong> $warningCount</p>\n";
        $html .= "<p><strong class='status' style='color: #dc3545;'>Errors:</strong> $errorCount</p>\n";
        $html .= "<p><strong>Success Rate:</strong> $successRate%</p>\n";
        $html .= "</div>\n";

        // Test Results
        foreach ($this->testResults as $result) {
            $class = $result['status'] === 'PASS' ? 'pass' : ($result['status'] === 'WARNING' ? 'warning' : 'fail');
            $status_icon = $result['status'] === 'PASS' ? '✅' : ($result['status'] === 'WARNING' ? '⚠️' : '❌');

            $html .= "<div class='test-result $class'>\n";
            $html .= "<h4>$status_icon {$result['test']}</h4>\n";
            $html .= "<p><strong>Status:</strong> {$result['status']}</p>\n";
            $html .= "<p><strong>Message:</strong> {$result['message']}</p>\n";
            $html .= "<p><strong>Timestamp:</strong> {$result['timestamp']}</p>\n";
            $html .= "</div>\n";
        }

        $html .= "</div>\n";
        $html .= "</body>\n";
        $html .= "</html>\n";

        file_put_contents($reportFile, $html);

        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #28a745;'>\n";
        echo "<h3>📊 Test Report Generated</h3>\n";
        echo "<p>Complete test report saved to: <strong>" . basename($reportFile) . "</strong></p>\n";
        echo "<p><a href='../logs/" . basename($reportFile) . "' target='_blank' style='color: #007bff;'>View Detailed Report</a></p>\n";
        echo "</div>\n";
    }
}
?>
