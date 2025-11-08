<?php
// scripts/security-test-suite.php

class SecurityTestSuite {
    private $basePath;
    private $results = [];
    private $testCount = 0;
    private $passedTests = 0;
    private $startTime;

    public function __construct() {
        $this->basePath = __DIR__ . '/../';
        $this->startTime = microtime(true);
    }

    public function runCompleteSecurityTestSuite() {
        echo "🧪 APS Dream Home - COMPREHENSIVE SECURITY TEST SUITE\n";
        echo "==================================================\n\n";

        $this->testDatabaseSecurity();
        $this->testAuthenticationSecurity();
        $this->testCSRFProtection();
        $this->testFileUploadSecurity();
        $this->testInputValidation();
        $this->testSessionSecurity();
        $this->testWebSecurity();
        $this->testMonitoringSystem();
        $this->testSecurityHeaders();
        $this->testErrorHandling();

        $this->generateTestReport();
        $this->provideActionableRecommendations();
    }

    private function testDatabaseSecurity() {
        echo "🗄️  TESTING DATABASE SECURITY\n";
        echo "============================\n";

        // Test 1: Check PDO Configuration
        $this->runTest('PDO Emulation Disabled', function() {
            $dbConfig = $this->basePath . 'config/database.php';
            if (file_exists($dbConfig)) {
                $content = file_get_contents($dbConfig);
                return strpos($content, 'PDO::ATTR_EMULATE_PREPARES => false') !== false;
            }
            return false;
        });

        // Test 2: Check Prepared Statements Usage
        $this->runTest('Prepared Statements Used', function() {
            $phpFiles = $this->findFilesByExtension('php');
            $preparedCount = 0;
            $totalQueries = 0;

            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                $preparedCount += substr_count($content, 'prepare(');
                $totalQueries += substr_count($content, '$conn->query(') + substr_count($content, 'prepare(');
            }

            return $totalQueries > 0 && ($preparedCount / $totalQueries) >= 0.8;
        });

        // Test 3: Check Database Connection Security
        $this->runTest('Database Connection Security', function() {
            $dbConnection = $this->basePath . 'includes/db_connection.php';
            return file_exists($dbConnection);
        });

        echo "\n";
    }

    private function testAuthenticationSecurity() {
        echo "🔐 TESTING AUTHENTICATION SECURITY\n";
        echo "=================================\n";

        // Test 1: Password Hashing
        $this->runTest('Password Hashing', function() {
            $phpFiles = $this->findFilesByExtension('php');
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'password_hash') !== false ||
                    strpos($content, 'PASSWORD_DEFAULT') !== false) {
                    return true;
                }
            }
            return false;
        });

        // Test 2: Session Management
        $this->runTest('Session Management', function() {
            $sessionManager = $this->basePath . 'admin/includes/session_manager.php';
            return file_exists($sessionManager);
        });

        // Test 3: Authentication Controller
        $this->runTest('Authentication Controller', function() {
            $authController = $this->basePath . 'app/controllers/AuthController.php';
            return file_exists($authController);
        });

        // Test 4: Multi-factor Session Validation
        $this->runTest('Multi-factor Session Validation', function() {
            $phpFiles = $this->findFilesByExtension('php');
            $sessionValidation = 0;

            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'session_status') !== false ||
                    strpos($content, 'session_regenerate') !== false) {
                    $sessionValidation++;
                }
            }

            return $sessionValidation > 0;
        });

        echo "\n";
    }

    private function testCSRFProtection() {
        echo "🛡️  TESTING CSRF PROTECTION\n";
        echo "==========================\n";

        // Test 1: CSRF Helper Functions
        $this->runTest('CSRF Helper Functions', function() {
            $csrfHelper = $this->basePath . 'app/helpers/security.php';
            if (file_exists($csrfHelper)) {
                $content = file_get_contents($csrfHelper);
                return strpos($content, 'csrf_token') !== false &&
                       strpos($content, 'validate_csrf_token') !== false;
            }
            return false;
        });

        // Test 2: CSRF Token Generation
        $this->runTest('CSRF Token Generation', function() {
            $csrfHelper = $this->basePath . 'app/helpers/security.php';
            if (file_exists($csrfHelper)) {
                require_once $csrfHelper;
                if (function_exists('csrf_token')) {
                    $token = csrf_token();
                    return !empty($token) && strlen($token) >= 32;
                }
            }
            return false;
        });

        // Test 3: CSRF Token Validation
        $this->runTest('CSRF Token Validation', function() {
            $csrfHelper = $this->basePath . 'app/helpers/security.php';
            if (file_exists($csrfHelper)) {
                require_once $csrfHelper;
                if (function_exists('validate_csrf_token')) {
                    $token = bin2hex(random_bytes(32));
                    return validate_csrf_token($token) === false; // Should fail with random token
                }
            }
            return false;
        });

        // Test 4: CSRF Protection in Forms
        $this->runTest('CSRF Protection in Forms', function() {
            $phpFiles = $this->findFilesByExtension('php');
            $formsWithCSRF = 0;
            $totalForms = 0;

            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (preg_match_all('/<form/i', $content, $matches)) {
                    $totalForms += count($matches[0]);
                    if (strpos($content, 'csrf_token') !== false) {
                        $formsWithCSRF++;
                    }
                }
            }

            return $totalForms > 0 && ($formsWithCSRF / $totalForms) >= 0.8;
        });

        echo "\n";
    }

    private function testFileUploadSecurity() {
        echo "📁 TESTING FILE UPLOAD SECURITY\n";
        echo "==============================\n";

        // Test 1: FileUploadService Class
        $this->runTest('FileUploadService Class', function() {
            $uploadService = $this->basePath . 'app/Services/FileUploadService.php';
            return file_exists($uploadService);
        });

        // Test 2: Secure Upload Directory
        $this->runTest('Secure Upload Directory', function() {
            $uploadDir = $this->basePath . 'storage/uploads';
            return is_dir($uploadDir) && !is_writable($uploadDir);
        });

        // Test 3: File Validation
        $this->runTest('File Validation', function() {
            $uploadService = $this->basePath . 'app/Services/FileUploadService.php';
            if (file_exists($uploadService)) {
                $content = file_get_contents($uploadService);
                return strpos($content, 'mime') !== false &&
                       strpos($content, 'size') !== false &&
                       strpos($content, 'virus') !== false;
            }
            return false;
        });

        echo "\n";
    }

    private function testInputValidation() {
        echo "🔍 TESTING INPUT VALIDATION\n";
        echo "==========================\n";

        // Test 1: Input Sanitization Functions
        $this->runTest('Input Sanitization Functions', function() {
            $securityHelper = $this->basePath . 'app/helpers/security.php';
            if (file_exists($securityHelper)) {
                $content = file_get_contents($securityHelper);
                return strpos($content, 'sanitize_input') !== false ||
                       strpos($content, 'htmlspecialchars') !== false;
            }
            return false;
        });

        // Test 2: Input Filtering
        $this->runTest('Input Filtering', function() {
            $phpFiles = $this->findFilesByExtension('php');
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'filter_input') !== false ||
                    strpos($content, 'FILTER_SANITIZE') !== false) {
                    return true;
                }
            }
            return false;
        });

        // Test 3: XSS Prevention
        $this->runTest('XSS Prevention', function() {
            $phpFiles = $this->findFilesByExtension('php');
            $xssProtection = 0;

            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'htmlspecialchars') !== false ||
                    strpos($content, 'htmlentities') !== false ||
                    strpos($content, 'strip_tags') !== false) {
                    $xssProtection++;
                }
            }

            return $xssProtection > 0;
        });

        echo "\n";
    }

    private function testSessionSecurity() {
        echo "🔐 TESTING SESSION SECURITY\n";
        echo "==========================\n";

        // Test 1: Session Cookie Security
        $this->runTest('Session Cookie Security', function() {
            $sessionConfig = ini_get('session.cookie_httponly');
            return $sessionConfig == '1';
        });

        // Test 2: Session Regeneration
        $this->runTest('Session Regeneration', function() {
            $phpFiles = $this->findFilesByExtension('php');
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'session_regenerate') !== false) {
                    return true;
                }
            }
            return false;
        });

        // Test 3: Session Timeout
        $this->runTest('Session Timeout', function() {
            $phpFiles = $this->findFilesByExtension('php');
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'session_timeout') !== false ||
                    strpos($content, 'LAST_ACTIVITY') !== false) {
                    return true;
                }
            }
            return false;
        });

        echo "\n";
    }

    private function testWebSecurity() {
        echo "🌐 TESTING WEB SECURITY\n";
        echo "======================\n";

        // Test 1: Security Headers
        $this->runTest('Security Headers', function() {
            $htaccess = $this->basePath . '.htaccess';
            if (file_exists($htaccess)) {
                $content = file_get_contents($htaccess);
                $headers = ['X-Content-Type-Options', 'X-Frame-Options', 'X-XSS-Protection'];
                $found = 0;
                foreach ($headers as $header) {
                    if (strpos($content, $header) !== false) {
                        $found++;
                    }
                }
                return $found >= 2;
            }
            return false;
        });

        // Test 2: HTTPS Configuration
        $this->runTest('HTTPS Configuration', function() {
            $envFile = $this->basePath . '.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);
                return strpos($content, 'https://') !== false ||
                       strpos($content, 'APP_HTTPS=true') !== false;
            }
            return false;
        });

        // Test 3: Environment File Security
        $this->runTest('Environment File Security', function() {
            $envFile = $this->basePath . '.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);
                return strpos($content, 'APP_DEBUG=false') !== false;
            }
            return false;
        });

        echo "\n";
    }

    private function testMonitoringSystem() {
        echo "📊 TESTING MONITORING SYSTEM\n";
        echo "===========================\n";

        // Test 1: Security Monitor Script
        $this->runTest('Security Monitor Script', function() {
            $monitor = $this->basePath . 'scripts/security-monitor.php';
            return file_exists($monitor);
        });

        // Test 2: Log Directory
        $this->runTest('Log Directory', function() {
            $logDir = $this->basePath . 'storage/logs';
            return is_dir($logDir) && is_writable($logDir);
        });

        // Test 3: Security Validation Script
        $this->runTest('Security Validation Script', function() {
            $validation = $this->basePath . 'scripts/security-validation.php';
            return file_exists($validation);
        });

        // Test 4: Error Logging
        $this->runTest('Error Logging', function() {
            $logDir = $this->basePath . 'storage/logs';
            if (is_dir($logDir)) {
                // Test if we can write to log
                $testLog = $logDir . '/test.log';
                $result = file_put_contents($testLog, 'Test log entry ' . date('Y-m-d H:i:s') . "\n");
                if ($result) {
                    unlink($testLog); // Clean up
                    return true;
                }
            }
            return false;
        });

        echo "\n";
    }

    private function testSecurityHeaders() {
        echo "📋 TESTING SECURITY HEADERS\n";
        echo "==========================\n";

        // Test 1: HTAccess Security Headers
        $this->runTest('HTAccess Security Headers', function() {
            $htaccess = $this->basePath . '.htaccess';
            if (file_exists($htaccess)) {
                $content = file_get_contents($htaccess);
                $requiredHeaders = [
                    'X-Content-Type-Options',
                    'X-Frame-Options',
                    'X-XSS-Protection',
                    'Content-Security-Policy'
                ];

                $found = 0;
                foreach ($requiredHeaders as $header) {
                    if (strpos($content, $header) !== false) {
                        $found++;
                    }
                }

                return $found >= 3;
            }
            return false;
        });

        // Test 2: Robots.txt
        $this->runTest('Robots.txt', function() {
            $robots = $this->basePath . 'robots.txt';
            return file_exists($robots);
        });

        echo "\n";
    }

    private function testErrorHandling() {
        echo "⚠️  TESTING ERROR HANDLING\n";
        echo "=========================\n";

        // Test 1: Error Reporting Configuration
        $this->runTest('Error Reporting Configuration', function() {
            $envFile = $this->basePath . '.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);
                return strpos($content, 'APP_DEBUG=false') !== false;
            }
            return false;
        });

        // Test 2: Secure Error Display
        $this->runTest('Secure Error Display', function() {
            $phpFiles = $this->findFilesByExtension('php');
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'display_errors') !== false ||
                    strpos($content, 'error_reporting') !== false) {
                    return true;
                }
            }
            return false;
        });

        echo "\n";
    }

    private function runTest($testName, $testFunction) {
        $this->testCount++;
        $result = false;

        try {
            $result = $testFunction();
        } catch (Exception $e) {
            $this->results['errors'][] = "$testName: " . $e->getMessage();
        }

        if ($result) {
            $this->passedTests++;
            echo "  ✅ $testName: PASSED\n";
            $this->results['passed'][] = $testName;
        } else {
            echo "  ❌ $testName: FAILED\n";
            $this->results['failed'][] = $testName;
        }

        return $result;
    }

    private function findFilesByExtension($extension) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function generateTestReport() {
        echo "\n📊 SECURITY TEST REPORT\n";
        echo "=======================\n";

        $overallScore = ($this->testCount > 0) ? round(($this->passedTests / $this->testCount) * 100, 1) : 0;

        echo "\n🎯 OVERALL SECURITY SCORE: $overallScore%\n";

        // Security grade
        $grade = 'F';
        if ($overallScore >= 95) $grade = 'A+';
        elseif ($overallScore >= 90) $grade = 'A';
        elseif ($overallScore >= 80) $grade = 'B';
        elseif ($overallScore >= 70) $grade = 'C';
        elseif ($overallScore >= 60) $grade = 'D';

        echo "📈 SECURITY GRADE: $grade\n";

        echo "\n📋 TEST RESULTS BREAKDOWN:\n";
        echo "  • Total Tests: " . $this->testCount . "\n";
        echo "  • Passed Tests: " . $this->passedTests . "\n";
        echo "  • Failed Tests: " . ($this->testCount - $this->passedTests) . "\n";

        $this->results['final_report'] = [
            'total_tests' => $this->testCount,
            'passed_tests' => $this->passedTests,
            'failed_tests' => $this->testCount - $this->passedTests,
            'overall_score' => $overallScore,
            'security_grade' => $grade,
            'test_date' => date('Y-m-d H:i:s')
        ];
    }

    private function provideActionableRecommendations() {
        echo "\n💡 ACTIONABLE RECOMMENDATIONS\n";
        echo "==============================\n";

        if ($this->testCount - $this->passedTests === 0) {
            echo "🎉 ALL TESTS PASSED! Excellent security implementation!\n\n";

            echo "📋 MAINTENANCE RECOMMENDATIONS:\n";
            echo "  • Run this test suite regularly (weekly)\n";
            echo "  • Keep PHP and dependencies updated\n";
            echo "  • Monitor security logs daily\n";
            echo "  • Regular backup procedures\n";
            echo "  • Employee security training\n\n";
        } else {
            echo "⚠️  SECURITY IMPROVEMENTS NEEDED:\n";
            foreach ($this->results['failed'] as $failedTest) {
                echo "  • Fix: $failedTest\n";
            }
            echo "\n";
        }

        echo "🔧 PRODUCTION DEPLOYMENT CHECKLIST:\n";
        echo "  1. Enable HTTPS on web server\n";
        echo "  2. Configure SSL certificates\n";
        echo "  3. Set up security monitoring\n";
        echo "  4. Test all security features\n";
        echo "  5. Configure backup procedures\n";
        echo "  6. Set up error handling\n";
        echo "  7. Review file permissions\n";
        echo "  8. Conduct final security audit\n";

        echo "\n🚀 QUICK SECURITY COMMANDS:\n";
        echo "  php scripts/security-monitor.php    # Daily monitoring\n";
        echo "  php scripts/security-audit.php     # Weekly audit\n";
        echo "  php scripts/security-validation.php # Monthly validation\n";
        echo "  php scripts/deploy-security.php    # Deployment tools\n";

        echo "\n📊 SECURITY STATUS: $this->passedTests/$this->testCount tests passed\n";

        $overallScore = ($this->testCount > 0) ? round(($this->passedTests / $this->testCount) * 100, 1) : 0;

        if ($overallScore >= 90) {
            echo "\n🎉 Your APS Dream Home application has excellent security!\n";
        } elseif ($overallScore >= 75) {
            echo "\n✅ Your APS Dream Home application has good security!\n";
        } elseif ($overallScore >= 60) {
            echo "\n⚠️  Your APS Dream Home application needs security improvements.\n";
        } else {
            echo "\n❌ Your APS Dream Home application has critical security issues.\n";
        }

        $this->saveTestResults();
    }

    private function saveTestResults() {
        $reportPath = $this->basePath . 'storage/logs/security-test-report.json';
        if (!is_dir(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0755, true);
        }

        $this->results['test_summary'] = [
            'test_date' => date('Y-m-d H:i:s'),
            'test_duration' => microtime(true) - $this->startTime,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];

        file_put_contents($reportPath, json_encode($this->results, JSON_PRETTY_PRINT));
    }
}

// Run the complete security test suite
try {
    $testSuite = new SecurityTestSuite();
    $testSuite->runCompleteSecurityTestSuite();

} catch (Exception $e) {
    echo "❌ Security test suite failed: " . $e->getMessage() . "\n";
    echo "Please check your environment and try again.\n";
}
?>
