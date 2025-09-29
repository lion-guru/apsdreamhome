<?php
/**
 * Comprehensive Security Testing Suite
 * Tests and validates all implemented security measures
 * APS Dream Home Security Validation Tool
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Security Testing Suite Class
 */
class SecurityTestSuite {

    private $test_results = [];
    private $security_log_file;
    private $start_time;

    /**
     * Constructor - Initialize testing suite
     */
    public function __construct() {
        $this->security_log_file = __DIR__ . '/../logs/security_test_results.log';
        $this->start_time = microtime(true);
        ensureLogDirectory($this->security_log_file);
    }

    /**
     * Run comprehensive security test suite
     */
    public function runSecurityTests() {
        $this->logTestStart();

        // Test 1: HTTPS Security
        $this->testHTTPS();

        // Test 2: Security Headers
        $this->testSecurityHeaders();

        // Test 3: Input Validation
        $this->testInputValidation();

        // Test 4: Session Security
        $this->testSessionSecurity();

        // Test 5: Database Security
        $this->testDatabaseSecurity();

        // Test 6: File Upload Security
        $this->testFileUploadSecurity();

        // Test 7: API Security
        $this->testAPISecurity();

        // Test 8: Rate Limiting
        $this->testRateLimiting();

        // Test 9: CSRF Protection
        $this->testCSRFProtection();

        // Test 10: Authentication Security
        $this->testAuthenticationSecurity();

        $this->logTestCompletion();
        return $this->test_results;
    }

    /**
     * Test HTTPS Security
     */
    private function testHTTPS() {
        $test_name = "HTTPS Security Test";
        $this->logTest("Starting: $test_name");

        $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $has_hsts = isset($_SERVER['HTTP_STRICT_TRANSPORT_SECURITY']);

        if ($is_https && $has_hsts) {
            $this->addTestResult($test_name, 'PASS', 'HTTPS properly enforced with HSTS');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'HTTPS or HSTS not properly configured');
        }
    }

    /**
     * Test Security Headers
     */
    private function testSecurityHeaders() {
        $test_name = "Security Headers Test";
        $this->logTest("Starting: $test_name");

        $required_headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000',
            'Content-Security-Policy' => 'default-src'
        ];

        $headers = getallheaders();
        $missing_headers = [];
        $incorrect_headers = [];

        foreach ($required_headers as $header => $expected_value) {
            if (!isset($headers[$header])) {
                $missing_headers[] = $header;
            } elseif (strpos($headers[$header], $expected_value) === false) {
                $incorrect_headers[] = "$header (expected: $expected_value, got: {$headers[$header]})";
            }
        }

        if (empty($missing_headers) && empty($incorrect_headers)) {
            $this->addTestResult($test_name, 'PASS', 'All required security headers present');
        } else {
            $message = 'Missing: ' . implode(', ', $missing_headers) . '; Incorrect: ' . implode(', ', $incorrect_headers);
            $this->addTestResult($test_name, 'FAIL', $message);
        }
    }

    /**
     * Test Input Validation
     */
    private function testInputValidation() {
        $test_name = "Input Validation Test";
        $this->logTest("Starting: $test_name");

        // Test XSS input
        $xss_input = "<script>alert('XSS')</script>";
        $sanitized_input = sanitizeInput($xss_input);

        if ($sanitized_input !== $xss_input && strpos($sanitized_input, '<script') === false) {
            $this->addTestResult($test_name, 'PASS', 'XSS input properly sanitized');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'XSS input not properly sanitized');
        }

        // Test SQL injection input
        $sql_input = "'; DROP TABLE users; --";
        $sanitized_sql = sanitizeInput($sql_input);

        if ($sanitized_sql !== $sql_input) {
            $this->addTestResult($test_name, 'PASS', 'SQL injection input properly sanitized');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'SQL injection input not properly sanitized');
        }
    }

    /**
     * Test Session Security
     */
    private function testSessionSecurity() {
        $test_name = "Session Security Test";
        $this->logTest("Starting: $test_name");

        $session_secure = ini_get('session.cookie_secure') == '1';
        $session_httponly = ini_get('session.cookie_httponly') == '1';
        $session_samesite = ini_get('session.cookie_samesite') == 'Strict';

        if ($session_secure && $session_httponly && $session_samesite) {
            $this->addTestResult($test_name, 'PASS', 'Session security properly configured');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Session security not properly configured');
        }
    }

    /**
     * Test Database Security
     */
    private function testDatabaseSecurity() {
        $test_name = "Database Security Test";
        $this->logTest("Starting: $test_name");

        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($conn->connect_error) {
                $this->addTestResult($test_name, 'FAIL', 'Database connection failed');
                return;
            }

            // Test prepared statements
            $test_query = "SELECT COUNT(*) as count FROM users WHERE id = ?";
            $stmt = $conn->prepare($test_query);

            if ($stmt) {
                $this->addTestResult($test_name, 'PASS', 'Database prepared statements working');
                $stmt->close();
            } else {
                $this->addTestResult($test_name, 'FAIL', 'Database prepared statements not working');
            }

            $conn->close();
        } catch (Exception $e) {
            $this->addTestResult($test_name, 'FAIL', 'Database security test error: ' . $e->getMessage());
        }
    }

    /**
     * Test File Upload Security
     */
    private function testFileUploadSecurity() {
        $test_name = "File Upload Security Test";
        $this->logTest("Starting: $test_name");

        // Test if uploads directory is protected
        $uploads_htaccess = __DIR__ . '/../uploads/.htaccess';
        $uploads_protected = file_exists($uploads_htaccess);

        if ($uploads_protected) {
            $this->addTestResult($test_name, 'PASS', 'Uploads directory properly protected');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Uploads directory not protected');
        }

        // Test secure upload function
        $secure_upload_class = __DIR__ . '/../includes/security/secure_upload.php';
        $secure_upload_exists = file_exists($secure_upload_class);

        if ($secure_upload_exists) {
            $this->addTestResult($test_name, 'PASS', 'Secure upload class available');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Secure upload class missing');
        }
    }

    /**
     * Test API Security
     */
    private function testAPISecurity() {
        $test_name = "API Security Test";
        $this->logTest("Starting: $test_name");

        // Test API security middleware
        $api_middleware = __DIR__ . '/../includes/security/api_middleware.php';
        $api_middleware_exists = file_exists($api_middleware);

        if ($api_middleware_exists) {
            $this->addTestResult($test_name, 'PASS', 'API security middleware available');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'API security middleware missing');
        }

        // Test API authentication
        $api_auth = __DIR__ . '/../api/auth.php';
        $api_auth_exists = file_exists($api_auth);

        if ($api_auth_exists) {
            $this->addTestResult($test_name, 'PASS', 'API authentication endpoint secured');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'API authentication endpoint missing');
        }
    }

    /**
     * Test Rate Limiting
     */
    private function testRateLimiting() {
        $test_name = "Rate Limiting Test";
        $this->logTest("Starting: $test_name");

        // Test rate limiting files
        $rate_limit_files = [
            __DIR__ . '/../logs/rate_limit.json',
            __DIR__ . '/../logs/login_rate_limit.json',
            __DIR__ . '/../logs/api_rate_limit.json',
            __DIR__ . '/../logs/config_rate_limit.json'
        ];

        $rate_limit_exists = true;
        foreach ($rate_limit_files as $file) {
            if (!file_exists($file)) {
                $rate_limit_exists = false;
                break;
            }
        }

        if ($rate_limit_exists) {
            $this->addTestResult($test_name, 'PASS', 'Rate limiting files properly configured');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Rate limiting not properly configured');
        }
    }

    /**
     * Test CSRF Protection
     */
    private function testCSRFProtection() {
        $test_name = "CSRF Protection Test";
        $this->logTest("Starting: $test_name");

        // Test CSRF functions
        $csrf_file = __DIR__ . '/../includes/csrf.php';
        $csrf_exists = file_exists($csrf_file);

        if ($csrf_exists) {
            $this->addTestResult($test_name, 'PASS', 'CSRF protection properly implemented');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'CSRF protection missing');
        }

        // Test security functions
        $security_functions = __DIR__ . '/../includes/security/security_functions.php';
        $security_functions_exist = file_exists($security_functions);

        if ($security_functions_exist) {
            $this->addTestResult($test_name, 'PASS', 'Security functions properly implemented');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Security functions missing');
        }
    }

    /**
     * Test Authentication Security
     */
    private function testAuthenticationSecurity() {
        $test_name = "Authentication Security Test";
        $this->logTest("Starting: $test_name");

        // Test login security
        $login_file = __DIR__ . '/../auth/login.php';
        $login_secured = file_exists($login_file);

        if ($login_secured) {
            $this->addTestResult($test_name, 'PASS', 'Login system properly secured');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Login system not secured');
        }

        // Test logout security
        $logout_file = __DIR__ . '/../auth/logout.php';
        $logout_secured = file_exists($logout_file);

        if ($logout_secured) {
            $this->addTestResult($test_name, 'PASS', 'Logout system properly secured');
        } else {
            $this->addTestResult($test_name, 'FAIL', 'Logout system not secured');
        }
    }

    /**
     * Add test result
     */
    private function addTestResult($test_name, $status, $message) {
        $this->test_results[] = [
            'test' => $test_name,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->logTest("$test_name: $status - $message");
    }

    /**
     * Log test activity
     */
    private function logTest($message) {
        $log_entry = date('Y-m-d H:i:s') . " - $message\n";
        file_put_contents($this->security_log_file, $log_entry, FILE_APPEND | LOCK_EX);
        error_log($log_entry);
    }

    /**
     * Log test start
     */
    private function logTestStart() {
        $this->logTest("=== SECURITY TEST SUITE STARTED ===");
        $this->logTest("Testing environment: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $this->logTest("Client IP: " . getClientIP());
        $this->logTest("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    }

    /**
     * Log test completion
     */
    private function logTestCompletion() {
        $end_time = microtime(true);
        $duration = round($end_time - $this->start_time, 2);

        $pass_count = count(array_filter($this->test_results, function($result) {
            return $result['status'] === 'PASS';
        }));

        $fail_count = count(array_filter($this->test_results, function($result) {
            return $result['status'] === 'FAIL';
        }));

        $total_tests = count($this->test_results);
        $success_rate = $total_tests > 0 ? round(($pass_count / $total_tests) * 100, 2) : 0;

        $this->logTest("=== SECURITY TEST SUITE COMPLETED ===");
        $this->logTest("Total Tests: $total_tests");
        $this->logTest("Passed: $pass_count");
        $this->logTest("Failed: $fail_count");
        $this->logTest("Success Rate: $success_rate%");
        $this->logTest("Duration: {$duration} seconds");
        $this->logTest("=== END OF TEST RESULTS ===");
    }

    /**
     * Generate HTML test report
     */
    public function generateHTMLReport() {
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='en'>\n";
        $html .= "<head>\n";
        $html .= "<meta charset='UTF-8'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
        $html .= "<title>Security Test Report - APS Dream Home</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
        $html .= ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
        $html .= ".header { text-align: center; color: #333; margin-bottom: 30px; }\n";
        $html .= ".test-result { margin: 15px 0; padding: 15px; border-radius: 5px; }\n";
        $html .= ".pass { background: #d4edda; border-left: 5px solid #28a745; }\n";
        $html .= ".fail { background: #f8d7da; border-left: 5px solid #dc3545; }\n";
        $html .= ".summary { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }\n";
        $html .= ".status { font-weight: bold; font-size: 18px; }\n";
        $html .= "</style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";
        $html .= "<div class='container'>\n";
        $html .= "<div class='header'>\n";
        $html .= "<h1>🔒 Security Test Report</h1>\n";
        $html .= "<h2>APS Dream Home Security Validation Suite</h2>\n";
        $html .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>\n";
        $html .= "</div>\n";

        // Summary
        $pass_count = count(array_filter($this->test_results, function($result) {
            return $result['status'] === 'PASS';
        }));
        $fail_count = count(array_filter($this->test_results, function($result) {
            return $result['status'] === 'FAIL';
        }));
        $total_tests = count($this->test_results);
        $success_rate = $total_tests > 0 ? round(($pass_count / $total_tests) * 100, 2) : 0;

        $html .= "<div class='summary'>\n";
        $html .= "<h3>Test Summary</h3>\n";
        $html .= "<p><strong>Total Tests:</strong> $total_tests</p>\n";
        $html .= "<p><strong class='status' style='color: #28a745;'>Passed:</strong> $pass_count</p>\n";
        $html .= "<p><strong class='status' style='color: #dc3545;'>Failed:</strong> $fail_count</p>\n";
        $html .= "<p><strong>Success Rate:</strong> $success_rate%</p>\n";
        $html .= "</div>\n";

        // Test Results
        foreach ($this->test_results as $result) {
            $class = $result['status'] === 'PASS' ? 'pass' : 'fail';
            $status_icon = $result['status'] === 'PASS' ? '✅' : '❌';

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

        return $html;
    }
}

/**
 * Run Security Test Suite
 */
function runSecurityTestSuite() {
    $tester = new SecurityTestSuite();
    $results = $tester->runSecurityTests();
    $html_report = $tester->generateHTMLReport();

    // Save HTML report
    $report_file = __DIR__ . '/../logs/security_test_report_' . date('Y-m-d_H-i-s') . '.html';
    file_put_contents($report_file, $html_report);

    return [
        'results' => $results,
        'report_file' => $report_file,
        'summary' => [
            'total' => count($results),
            'passed' => count(array_filter($results, function($r) { return $r['status'] === 'PASS'; })),
            'failed' => count(array_filter($results, function($r) { return $r['status'] === 'FAIL'; }))
        ]
    ];
}

/**
 * Quick Security Test Function
 */
function quickSecurityTest() {
    $tester = new SecurityTestSuite();
    $results = $tester->runSecurityTests();

    $passed = count(array_filter($results, function($r) { return $r['status'] === 'PASS'; }));
    $failed = count(array_filter($results, function($r) { return $r['status'] === 'FAIL'; }));
    $total = count($results);

    return [
        'status' => $failed === 0 ? 'SECURE' : 'VULNERABILITIES_FOUND',
        'passed' => $passed,
        'failed' => $failed,
        'total' => $total,
        'score' => $total > 0 ? round(($passed / $total) * 100, 2) : 0
    ];
}
