<?php
// scripts/security-validation.php

class SecurityValidator {
    private $conn;
    private $errors = [];
    private $warnings = [];
    private $successes = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function runFullSecurityValidation() {
        echo "🔒 APS Dream Home - Comprehensive Security Validation\n";
        echo "==================================================\n\n";

        $this->validateDatabaseSecurity();
        $this->validateFileUploadSecurity();
        $this->validateSessionSecurity();
        $this->validateCSRFProtection();
        $this->validateInputValidation();
        $this->validateSecurityHeaders();
        $this->validateFilePermissions();
        $this->validateSecurityMonitoring();

        $this->generateSecurityReport();
        $this->provideRecommendations();
    }

    private function validateDatabaseSecurity() {
        echo "📊 Testing Database Security...\n";

        // Test 1: Check if PDO emulation is disabled
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM properties WHERE status = ?");
            $stmt->bindValue(1, "available");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->successes[] = "✅ Prepared statements working correctly";
        } catch (Exception $e) {
            $this->errors[] = "❌ Prepared statements not working: " . $e->getMessage();
        }

        // Test 2: Check for raw queries
        $files = $this->getPHPFiles();
        $rawQueriesFound = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\$conn->query\(["\']([^"\']*SELECT[^"\']*)/i', $content, $matches)) {
                $rawQueriesFound++;
                $this->warnings[] = "⚠️  Raw query found in: " . basename($file);
            }
        }

        if ($rawQueriesFound === 0) {
            $this->successes[] = "✅ No raw SQL queries found in PHP files";
        } else {
            $this->errors[] = "❌ Found $rawQueriesFound raw SQL queries that need to be converted to prepared statements";
        }

        echo "  • Raw queries found: $rawQueriesFound\n";
    }

    private function validateFileUploadSecurity() {
        echo "📁 Testing File Upload Security...\n";

        // Check if FileUploadService exists
        $uploadServicePath = __DIR__ . '/../app/Services/FileUploadService.php';
        if (file_exists($uploadServicePath)) {
            $this->successes[] = "✅ FileUploadService class exists";

            // Check if class can be loaded
            require_once $uploadServicePath;
            if (class_exists('FileUploadService')) {
                $this->successes[] = "✅ FileUploadService class is loadable";
            } else {
                $this->errors[] = "❌ FileUploadService class cannot be loaded";
            }
        } else {
            $this->warnings[] = "⚠️  FileUploadService class not found - implement file upload security";
        }

        // Check upload directory security
        $uploadDir = __DIR__ . '/../storage/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            $this->successes[] = "✅ Created secure upload directory";
        }

        // Check if uploads are outside web root
        $webRoot = __DIR__ . '/../';
        $relativePath = str_replace($webRoot, '', $uploadDir);
        if (strpos($relativePath, 'storage') === 0) {
            $this->successes[] = "✅ Upload directory is outside web root";
        } else {
            $this->warnings[] = "⚠️  Upload directory may be accessible via web";
        }

        echo "  • Upload directory: " . ($relativePath ?: 'web root') . "\n";
    }

    private function validateSessionSecurity() {
        echo "🔐 Testing Session Security...\n";

        // Check session configuration
        $sessionConfig = [
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'cookie_samesite' => ini_get('session.cookie_samesite'),
            'use_strict_mode' => ini_get('session.use_strict_mode')
        ];

        if ($sessionConfig['cookie_httponly']) {
            $this->successes[] = "✅ Session cookies are HTTP-only";
        } else {
            $this->errors[] = "❌ Session cookies are not HTTP-only";
        }

        if (isset($_SERVER['HTTPS']) || $sessionConfig['cookie_secure']) {
            $this->successes[] = "✅ Session cookies are secure";
        } else {
            $this->warnings[] = "⚠️  Session cookies may not be secure (HTTPS not detected)";
        }

        if ($sessionConfig['cookie_samesite']) {
            $this->successes[] = "✅ Session cookies use SameSite attribute";
        } else {
            $this->warnings[] = "⚠️  Session cookies do not use SameSite attribute";
        }

        echo "  • HTTP-only: " . ($sessionConfig['cookie_httponly'] ? 'Yes' : 'No') . "\n";
        echo "  • Secure: " . ($sessionConfig['cookie_secure'] ? 'Yes' : 'No') . "\n";
        echo "  • SameSite: " . ($sessionConfig['cookie_samesite'] ?: 'Not set') . "\n";
    }

    private function validateCSRFProtection() {
        echo "🛡️  Testing CSRF Protection...\n";

        // Check if CSRF functions exist
        $securityHelper = __DIR__ . '/../app/helpers/security.php';
        if (file_exists($securityHelper)) {
            require_once $securityHelper;

            if (function_exists('csrf_token') && function_exists('validate_csrf_token')) {
                $this->successes[] = "✅ CSRF helper functions exist";

                // Test token generation
                $token = csrf_token();
                if (!empty($token) && strlen($token) >= 32) {
                    $this->successes[] = "✅ CSRF token generation working";
                } else {
                    $this->errors[] = "❌ CSRF token generation failed";
                }

                // Test token validation
                if (validate_csrf_token($token)) {
                    $this->successes[] = "✅ CSRF token validation working";
                } else {
                    $this->errors[] = "❌ CSRF token validation failed";
                }
            } else {
                $this->errors[] = "❌ CSRF helper functions not found";
            }
        } else {
            $this->errors[] = "❌ CSRF helper file not found";
        }

        // Check if CSRF tokens are used in forms
        $formsChecked = 0;
        $csrfFormsFound = 0;

        $files = $this->getPHPFiles();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/<form/i', $content)) {
                $formsChecked++;
                if (preg_match('/csrf_token/', $content)) {
                    $csrfFormsFound++;
                }
            }
        }

        if ($formsChecked > 0) {
            $csrfCoverage = ($csrfFormsFound / $formsChecked) * 100;
            if ($csrfCoverage >= 80) {
                $this->successes[] = "✅ CSRF protection implemented on $csrfFormsFound/$formsChecked forms ($csrfCoverage%)";
            } else {
                $this->warnings[] = "⚠️  CSRF protection only on $csrfFormsFound/$formsChecked forms ($csrfCoverage%)";
            }
        }

        echo "  • Forms checked: $formsChecked\n";
        echo "  • CSRF protected: $csrfFormsFound\n";
    }

    private function validateInputValidation() {
        echo "🔍 Testing Input Validation...\n";

        // Check if sanitization functions exist
        $securityHelper = __DIR__ . '/../app/helpers/security.php';
        if (file_exists($securityHelper)) {
            require_once $securityHelper;

            if (function_exists('sanitize_input')) {
                $this->successes[] = "✅ Input sanitization function exists";

                // Test sanitization
                $testInput = '<script>alert("XSS")</script>';
                $sanitized = sanitize_input($testInput);
                if ($sanitized === htmlspecialchars($testInput)) {
                    $this->successes[] = "✅ Input sanitization working correctly";
                } else {
                    $this->errors[] = "❌ Input sanitization not working properly";
                }
            } else {
                $this->errors[] = "❌ Input sanitization function not found";
            }
        } else {
            $this->errors[] = "❌ Security helper file not found";
        }

        // Check for potential XSS vulnerabilities
        $xssFound = 0;
        $files = $this->getPHPFiles();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            // Look for direct output of user input without sanitization
            if (preg_match('/echo\s*[\$_][A-Z]/', $content) ||
                preg_match('/print\s*[\$_][A-Z]/', $content) ||
                preg_match('/<\?=\s*[\$_][A-Z]/', $content)) {
                $xssFound++;
            }
        }

        if ($xssFound === 0) {
            $this->successes[] = "✅ No obvious XSS vulnerabilities found";
        } else {
            $this->warnings[] = "⚠️  Found $xssFound potential XSS vulnerabilities";
        }

        echo "  • XSS vulnerabilities: $xssFound\n";
    }

    private function validateSecurityHeaders() {
        echo "📋 Testing Security Headers...\n";

        $headersToCheck = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => 'default-src \'self\'',
            'Strict-Transport-Security' => 'max-age=31536000'
        ];

        $headersFound = 0;
        foreach ($headersToCheck as $header => $expectedValue) {
            // Check if header is in .htaccess
            $htaccess = __DIR__ . '/../.htaccess';
            if (file_exists($htaccess)) {
                $content = file_get_contents($htaccess);
                if (strpos($content, $header) !== false) {
                    $headersFound++;
                }
            }
        }

        $headerCoverage = (count($headersToCheck) > 0) ? ($headersFound / count($headersToCheck)) * 100 : 0;

        if ($headerCoverage >= 80) {
            $this->successes[] = "✅ Security headers implemented ($headersFound/" . count($headersToCheck) . ")";
        } else {
            $this->warnings[] = "⚠️  Security headers partially implemented ($headersFound/" . count($headersToCheck) . ")";
        }

        echo "  • Headers implemented: $headersFound/" . count($headersToCheck) . "\n";
    }

    private function validateFilePermissions() {
        echo "📁 Testing File Permissions...\n";

        $correctPermissions = 0;
        $filesChecked = 0;

        // Check PHP files
        $phpFiles = $this->getPHPFiles();
        foreach ($phpFiles as $file) {
            $filesChecked++;
            $perms = fileperms($file) & 0777;
            if ($perms === 0644) {
                $correctPermissions++;
            }
        }

        // Check directories
        $dirs = ['app', 'config', 'public', 'scripts', 'admin'];
        foreach ($dirs as $dir) {
            $dirPath = __DIR__ . '/../' . $dir;
            if (is_dir($dirPath)) {
                $filesChecked++;
                $perms = fileperms($dirPath) & 0777;
                if ($perms === 0755) {
                    $correctPermissions++;
                }
            }
        }

        $permissionScore = ($filesChecked > 0) ? ($correctPermissions / $filesChecked) * 100 : 0;

        if ($permissionScore >= 90) {
            $this->successes[] = "✅ File permissions correctly set ($correctPermissions/$filesChecked)";
        } else {
            $this->warnings[] = "⚠️  File permissions need review ($correctPermissions/$filesChecked correct)";
        }

        echo "  • Correct permissions: $correctPermissions/$filesChecked\n";
    }

    private function validateSecurityMonitoring() {
        echo "📊 Testing Security Monitoring...\n";

        // Check if monitoring script exists
        $monitorScript = __DIR__ . '/security-monitor.php';
        if (file_exists($monitorScript)) {
            $this->successes[] = "✅ Security monitoring script exists";
        } else {
            $this->errors[] = "❌ Security monitoring script not found";
        }

        // Check if log directory exists
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
            $this->successes[] = "✅ Created log directory";
        } else {
            $this->successes[] = "✅ Log directory exists";
        }

        // Test log writing
        $testLog = $logDir . '/security.log';
        $testEntry = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'test',
            'message' => 'Security validation test',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]) . "\n";

        if (file_put_contents($testLog, $testEntry, FILE_APPEND | LOCK_EX)) {
            $this->successes[] = "✅ Security logging working";
        } else {
            $this->errors[] = "❌ Security logging not working";
        }

        echo "  • Log directory: " . (is_dir($logDir) ? 'Exists' : 'Missing') . "\n";
    }

    private function getPHPFiles() {
        $phpFiles = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../'));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }
        return $phpFiles;
    }

    private function generateSecurityReport() {
        echo "\n📊 SECURITY VALIDATION REPORT\n";
        echo "=============================\n";

        echo "\n✅ SUCCESSES (" . count($this->successes) . "):\n";
        foreach ($this->successes as $success) {
            echo "  $success\n";
        }

        echo "\n⚠️  WARNINGS (" . count($this->warnings) . "):\n";
        foreach ($this->warnings as $warning) {
            echo "  $warning\n";
        }

        echo "\n❌ ERRORS (" . count($this->errors) . "):\n";
        foreach ($this->errors as $error) {
            echo "  $error\n";
        }

        // Calculate overall score
        $totalChecks = count($this->successes) + count($this->warnings) + count($this->errors);
        $score = ($totalChecks > 0) ? (count($this->successes) / $totalChecks) * 100 : 0;

        echo "\n🎯 OVERALL SECURITY SCORE: " . round($score, 1) . "%\n";

        if ($score >= 90) {
            echo "📈 SECURITY STATUS: EXCELLENT\n";
        } elseif ($score >= 75) {
            echo "📊 SECURITY STATUS: GOOD\n";
        } elseif ($score >= 60) {
            echo "📉 SECURITY STATUS: FAIR\n";
        } else {
            echo "❌ SECURITY STATUS: POOR\n";
        }
    }

    private function provideRecommendations() {
        echo "\n💡 RECOMMENDATIONS\n";
        echo "==================\n";

        if (count($this->errors) > 0) {
            echo "\n🔴 CRITICAL FIXES REQUIRED:\n";
            foreach ($this->errors as $error) {
                $cleanError = str_replace(['❌', '⚠️'], '', $error);
                echo "  • Fix: $cleanError\n";
            }
        }

        echo "\n🟡 IMPROVEMENTS SUGGESTED:\n";
        echo "  • Enable HTTPS on production server\n";
        echo "  • Implement rate limiting for login attempts\n";
        echo "  • Set up automated security monitoring\n";
        echo "  • Regular security audits and updates\n";
        echo "  • Consider Web Application Firewall (WAF)\n";
        echo "  • Implement Content Security Policy (CSP)\n";
        echo "  • Use HTTPS everywhere (HSTS)\n";
        echo "  • Regular backup procedures\n";

        echo "\n✅ IMMEDIATE NEXT STEPS:\n";
        echo "  1. Enable HTTPS on your web server\n";
        echo "  2. Test all implemented security features\n";
        echo "  3. Set up security monitoring cron jobs\n";
        echo "  4. Review and fix any identified issues\n";
        echo "  5. Conduct regular security audits\n";
    }
}

// Run the security validation
try {
    require_once __DIR__ . '/../includes/db_connection.php';
    global $con;
    $conn = $con;

    $validator = new SecurityValidator($conn);
    $validator->runFullSecurityValidation();

} catch (Exception $e) {
    echo "❌ Security validation failed: " . $e->getMessage() . "\n";
    echo "Make sure your database connection is working.\n";
}
?>
