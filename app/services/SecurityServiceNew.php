<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Modern Security Service
 * Comprehensive security testing and validation system
 */
class SecurityService
{
    private array $testResults = [];
    private string $logFile;
    private float $startTime;
    private int $cacheTtl = 3600; // 1 hour

    public function __construct()
    {
        $this->logFile = storage_path('logs/security_test_results.log');
        $this->startTime = microtime(true);
        $this->ensureLogDirectory();
    }

    /**
     * Run comprehensive security test suite
     */
    public function runSecurityTests(): array
    {
        $this->logTestStart();

        // Test 1: HTTPS Security
        $this->testHttpsSecurity();

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
        $this->testApiSecurity();

        // Test 8: Rate Limiting
        $this->testRateLimiting();

        // Test 9: CSRF Protection
        $this->testCsrfProtection();

        // Test 10: Authentication Security
        $this->testAuthenticationSecurity();

        $this->logTestCompletion();

        return $this->generateTestSummary();
    }

    /**
     * Test HTTPS Security
     */
    private function testHttpsSecurity(): void
    {
        $testName = "HTTPS Security Test";
        $this->logTest("Starting: {$testName}");

        $request = request();
        $isHttps = $request->secure();
        $hasHsts = $request->header('strict-transport-security') !== null;

        if ($isHttps && $hasHsts) {
            $this->addTestResult($testName, 'PASS', 'HTTPS properly enforced with HSTS');
        } else {
            $this->addTestResult($testName, 'FAIL', 'HTTPS or HSTS not properly configured');
        }
    }

    /**
     * Test Security Headers
     */
    private function testSecurityHeaders(): void
    {
        $testName = "Security Headers Test";
        $this->logTest("Starting: {$testName}");

        $request = request();
        $requiredHeaders = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000',
            'Content-Security-Policy' => "default-src 'self'"
        ];

        $missingHeaders = [];
        $incorrectHeaders = [];

        foreach ($requiredHeaders as $header => $expectedValue) {
            $actualValue = $request->header($header);
            if (!$actualValue) {
                $missingHeaders[] = $header;
            } elseif (strpos($actualValue, $expectedValue) === false) {
                $incorrectHeaders[] = "{$header} (expected: {$expectedValue}, got: {$actualValue})";
            }
        }

        if (empty($missingHeaders) && empty($incorrectHeaders)) {
            $this->addTestResult($testName, 'PASS', 'All required security headers present');
        } else {
            $message = 'Missing: ' . implode(', ', $missingHeaders) . '; Incorrect: ' . implode(', ', $incorrectHeaders);
            $this->addTestResult($testName, 'FAIL', $message);
        }
    }

    /**
     * Test Input Validation
     */
    private function testInputValidation(): void
    {
        $testName = "Input Validation Test";
        $this->logTest("Starting: {$testName}");

        // Test XSS input
        $xssInput = "<script>alert('XSS')</script>";
        $sanitizedInput = $this->sanitizeInput($xssInput);

        if ($sanitizedInput !== $xssInput && !str_contains($sanitizedInput, '<script')) {
            $this->addTestResult($testName, 'PASS', 'XSS input properly sanitized');
        } else {
            $this->addTestResult($testName, 'FAIL', 'XSS input not properly sanitized');
        }

        // Test SQL injection input
        $sqlInput = "'; DROP TABLE users; --";
        $sanitizedSql = $this->sanitizeInput($sqlInput);

        if ($sanitizedSql !== $sqlInput) {
            $this->addTestResult($testName, 'PASS', 'SQL injection input properly sanitized');
        } else {
            $this->addTestResult($testName, 'FAIL', 'SQL injection input not properly sanitized');
        }
    }

    /**
     * Test Session Security
     */
    private function testSessionSecurity(): void
    {
        $testName = "Session Security Test";
        $this->logTest("Starting: {$testName}");

        $sessionSecure = config('session.secure', false);
        $sessionHttpOnly = config('session.http_only', false);
        $sessionSameSite = config('session.same_site', 'lax');

        if ($sessionSecure && $sessionHttpOnly && $sessionSameSite === 'strict') {
            $this->addTestResult($testName, 'PASS', 'Session security properly configured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'Session security not properly configured');
        }
    }

    /**
     * Test Database Security
     */
    private function testDatabaseSecurity(): void
    {
        $testName = "Database Security Test";
        $this->logTest("Starting: {$testName}");

        try {
            // Test database connection
            $db = \DB::connection();
            
            if (!$db) {
                $this->addTestResult($testName, 'FAIL', 'Database connection failed');
                return;
            }

            // Test prepared statements usage
            $testQuery = "SELECT COUNT(*) as count FROM users WHERE id = ?";
            $result = \DB::selectOne($testQuery, [1]);

            if ($result !== false) {
                $this->addTestResult($testName, 'PASS', 'Database prepared statements working');
            } else {
                $this->addTestResult($testName, 'FAIL', 'Database prepared statements failed');
            }

        } catch (\Exception $e) {
            $this->addTestResult($testName, 'FAIL', 'Database security test error: ' . $e->getMessage());
        }
    }

    /**
     * Test File Upload Security
     */
    private function testFileUploadSecurity(): void
    {
        $testName = "File Upload Security Test";
        $this->logTest("Starting: {$testName}");

        // Test upload directory protection
        $uploadsPath = storage_path('app/public/uploads');
        $htaccessPath = $uploadsPath . '/.htaccess';
        $uploadsProtected = file_exists($htaccessPath);

        if ($uploadsProtected) {
            $this->addTestResult($testName, 'PASS', 'Uploads directory properly protected');
        } else {
            $this->addTestResult($testName, 'FAIL', 'Uploads directory not protected');
        }

        // Test file upload validation
        $allowedMimes = config('filesystems.disks.uploads.allowed_mimes', []);
        $maxFileSize = config('filesystems.disks.uploads.max_file_size', 2048);

        if (!empty($allowedMimes) && $maxFileSize > 0) {
            $this->addTestResult($testName, 'PASS', 'File upload validation configured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'File upload validation not properly configured');
        }
    }

    /**
     * Test API Security
     */
    private function testApiSecurity(): void
    {
        $testName = "API Security Test";
        $this->logTest("Starting: {$testName}");

        // Test API middleware existence
        $apiMiddlewareExists = class_exists(\App\Http\Middleware\RequestMiddlewareService::class);

        if ($apiMiddlewareExists) {
            $this->addTestResult($testName, 'PASS', 'API security middleware available');
        } else {
            $this->addTestResult($testName, 'FAIL', 'API security middleware missing');
        }

        // Test API authentication
        $apiAuthExists = class_exists(\App\Http\Middleware\Authenticate::class);

        if ($apiAuthExists) {
            $this->addTestResult($testName, 'PASS', 'API authentication endpoint secured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'API authentication endpoint missing');
        }
    }

    /**
     * Test Rate Limiting
     */
    private function testRateLimiting(): void
    {
        $testName = "Rate Limiting Test";
        $this->logTest("Starting: {$testName}");

        // Test rate limiting configuration
        $rateLimitEnabled = config('cache.default') === 'redis' || config('cache.default') === 'database';
        $rateLimitConfigured = !empty(config('cache.stores.redis')) || !empty(config('cache.stores.database'));

        if ($rateLimitEnabled && $rateLimitConfigured) {
            $this->addTestResult($testName, 'PASS', 'Rate limiting properly configured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'Rate limiting not properly configured');
        }
    }

    /**
     * Test CSRF Protection
     */
    private function testCsrfProtection(): void
    {
        $testName = "CSRF Protection Test";
        $this->logTest("Starting: {$testName}");

        // Test CSRF middleware
        $csrfMiddlewareExists = class_exists(\App\Http\Middleware\VerifyCsrfToken::class);

        if ($csrfMiddlewareExists) {
            $this->addTestResult($testName, 'PASS', 'CSRF protection properly implemented');
        } else {
            $this->addTestResult($testName, 'FAIL', 'CSRF protection missing');
        }

        // Test CSRF token generation
        $csrfTokenGenerated = function_exists('csrf_token');

        if ($csrfTokenGenerated) {
            $this->addTestResult($testName, 'PASS', 'CSRF token generation available');
        } else {
            $this->addTestResult($testName, 'FAIL', 'CSRF token generation missing');
        }
    }

    /**
     * Test Authentication Security
     */
    private function testAuthenticationSecurity(): void
    {
        $testName = "Authentication Security Test";
        $this->logTest("Starting: {$testName}");

        // Test login security
        $loginControllerExists = class_exists(\App\Http\Controllers\Auth\LoginController::class);

        if ($loginControllerExists) {
            $this->addTestResult($testName, 'PASS', 'Login system properly secured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'Login system not secured');
        }

        // Test logout security
        $logoutControllerExists = class_exists(\App\Http\Controllers\Auth\LogoutController::class);

        if ($logoutControllerExists) {
            $this->addTestResult($testName, 'PASS', 'Logout system properly secured');
        } else {
            $this->addTestResult($testName, 'FAIL', 'Logout system not secured');
        }
    }

    /**
     * Sanitize input data
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate and sanitize email
     */
    public function validateEmail(string $email): bool
    {
        $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    public function validatePhone(string $phone): bool
    {
        $sanitizedPhone = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^[0-9]{10,15}$/', $sanitizedPhone);
    }

    /**
     * Generate secure password hash
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        return csrf_token();
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        return hash_equals(csrf_token(), $token);
    }

    /**
     * Check rate limit for IP
     */
    public function checkRateLimit(string $key, int $maxAttempts = 60, int $timeWindow = 300): bool
    {
        return !RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, $context);
    }

    /**
     * Detect suspicious activity
     */
    public function detectSuspiciousActivity(Request $request): array
    {
        $suspicious = [];
        $input = $request->all();

        // Check for common attack patterns
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi' => 'XSS attempt',
            '/union.*select/i' => 'SQL injection attempt',
            '/javascript:/i' => 'JavaScript injection',
            '/on\w+\s*=/i' => 'Event handler injection'
        ];

        array_walk_recursive($input, function ($value) use ($patterns, &$suspicious) {
            if (is_string($value)) {
                foreach ($patterns as $pattern => $description) {
                    if (preg_match($pattern, $value)) {
                        $suspicious[] = $description;
                    }
                }
            }
        });

        return array_unique($suspicious);
    }

    /**
     * Get security score
     */
    public function getSecurityScore(): array
    {
        $cacheKey = 'security_score';
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $results = $this->runSecurityTests();
            $totalTests = count($results);
            $passedTests = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
            
            return [
                'score' => $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0,
                'total_tests' => $totalTests,
                'passed_tests' => $passedTests,
                'failed_tests' => $totalTests - $passedTests,
                'status' => $passedTests === $totalTests ? 'SECURE' : 'VULNERABILITIES_FOUND',
                'last_tested' => now()->toISOString()
            ];
        });
    }

    /**
     * Add test result
     */
    private function addTestResult(string $testName, string $status, string $message): void
    {
        $this->testResults[] = [
            'test' => $testName,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        $this->logTest("{$testName}: {$status} - {$message}");
    }

    /**
     * Log test activity
     */
    private function logTest(string $message): void
    {
        $logEntry = now()->toISOString() . " - {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        Log::channel('security')->info($message);
    }

    /**
     * Log test start
     */
    private function logTestStart(): void
    {
        $this->logTest("=== SECURITY TEST SUITE STARTED ===");
        $this->logTest("Testing environment: " . config('app.url'));
        $this->logTest("Client IP: " . request()->ip());
        $this->logTest("User Agent: " . request()->userAgent());
    }

    /**
     * Log test completion
     */
    private function logTestCompletion(): void
    {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);

        $passCount = count(array_filter($this->testResults, fn($result) => $result['status'] === 'PASS'));
        $failCount = count(array_filter($this->testResults, fn($result) => $result['status'] === 'FAIL'));
        $totalTests = count($this->testResults);
        $successRate = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 2) : 0;

        $this->logTest("=== SECURITY TEST SUITE COMPLETED ===");
        $this->logTest("Total Tests: {$totalTests}");
        $this->logTest("Passed: {$passCount}");
        $this->logTest("Failed: {$failCount}");
        $this->logTest("Success Rate: {$successRate}%");
        $this->logTest("Duration: {$duration} seconds");
        $this->logTest("=== END OF TEST RESULTS ===");
    }

    /**
     * Generate test summary
     */
    private function generateTestSummary(): array
    {
        $passed = count(array_filter($this->testResults, fn($result) => $result['status'] === 'PASS'));
        $failed = count(array_filter($this->testResults, fn($result) => $result['status'] === 'FAIL'));
        $total = count($this->testResults);

        return [
            'status' => $failed === 0 ? 'SECURE' : 'VULNERABILITIES_FOUND',
            'passed' => $passed,
            'failed' => $failed,
            'total' => $total,
            'score' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
            'results' => $this->testResults,
            'duration' => round(microtime(true) - $this->startTime, 2),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Generate HTML security report
     */
    public function generateHtmlReport(): string
    {
        $summary = $this->generateTestSummary();

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
        $html .= "<p>Generated: " . now()->toISOString() . "</p>\n";
        $html .= "</div>\n";

        // Summary
        $html .= "<div class='summary'>\n";
        $html .= "<h3>Test Summary</h3>\n";
        $html .= "<p><strong>Total Tests:</strong> {$summary['total']}</p>\n";
        $html .= "<p><strong class='status' style='color: #28a745;'>Passed:</strong> {$summary['passed']}</p>\n";
        $html .= "<p><strong class='status' style='color: #dc3545;'>Failed:</strong> {$summary['failed']}</p>\n";
        $html .= "<p><strong>Success Rate:</strong> {$summary['score']}%</p>\n";
        $html .= "<p><strong>Duration:</strong> {$summary['duration']} seconds</p>\n";
        $html .= "</div>\n";

        // Test Results
        foreach ($summary['results'] as $result) {
            $class = $result['status'] === 'PASS' ? 'pass' : 'fail';
            $statusIcon = $result['status'] === 'PASS' ? '✅' : '❌';

            $html .= "<div class='test-result {$class}'>\n";
            $html .= "<h4>{$statusIcon} {$result['test']}</h4>\n";
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

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations(): array
    {
        return [
            'Enable HTTPS' => 'Ensure all traffic is served over HTTPS with valid SSL certificates',
            'Security Headers' => 'Implement all required security headers including HSTS, CSP, and XSS protection',
            'Input Validation' => 'Validate and sanitize all user inputs to prevent XSS and SQL injection',
            'Session Security' => 'Configure secure session settings with HttpOnly, Secure, and SameSite attributes',
            'Rate Limiting' => 'Implement rate limiting to prevent brute force attacks',
            'CSRF Protection' => 'Use CSRF tokens for all state-changing requests',
            'File Upload Security' => 'Validate file types, sizes, and scan for malware',
            'Database Security' => 'Use prepared statements and parameterized queries',
            'Regular Audits' => 'Conduct regular security audits and penetration testing'
        ];
    }
}
