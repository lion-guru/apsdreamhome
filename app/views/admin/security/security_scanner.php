<?php
/**
 * Security Scanner Framework for APS Dream Homes
 * Comprehensive security testing and vulnerability scanning
 */

require_once __DIR__ . '/../../../../app/core/App.php';
require_once __DIR__ . '/../classes/AdminInputValidator.php';

/**
 * Security Scanner Class
 */
class SecurityScanner {

    private $db;
    private $scanResults = [];
    private $securityLogFile;

    /**
     * Constructor
     */
    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->securityLogFile = __DIR__ . '/../logs/security_scan.log';
        $this->ensureLogDirectory();
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->securityLogFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Run comprehensive security scan
     */
    public function runFullScan() {
        $this->scanResults = [
            'timestamp' => date('Y-m-d H:i:s'),
            'critical_issues' => [],
            'warnings' => [],
            'info' => [],
            'summary' => []
        ];

        // Run all security checks
        $this->checkFilePermissions();
        $this->checkDatabaseSecurity();
        $this->checkInputValidation();
        $this->checkCSRFProtection();
        $this->checkSessionSecurity();
        $this->checkFileUploadSecurity();
        $this->checkSQLInjectionVulnerabilities();
        $this->checkXSSVulnerabilities();
        $this->checkDirectoryTraversal();
        $this->checkSensitiveFiles();

        // Generate summary
        $this->generateSummary();

        // Log results
        $this->logScanResults();

        return $this->scanResults;
    }

    /**
     * Check file permissions
     */
    private function checkFilePermissions() {
        $criticalFiles = [
            '../config.php' => 0644,
            '../includes/db_config.php' => 0644,
            '../logs/' => 0755,
            '../uploads/' => 0755,
            '../uploads/properties/' => 0755,
            '../uploads/gallery/' => 0755,
            '../uploads/documents/' => 0755
        ];

        foreach ($criticalFiles as $file => $expectedPermission) {
            if (file_exists($file)) {
                $actualPermission = substr(sprintf('%o', fileperms($file)), -4);
                if ($actualPermission != $expectedPermission) {
                    $this->scanResults['warnings'][] = [
                        'type' => 'file_permissions',
                        'file' => $file,
                        'expected' => $expectedPermission,
                        'actual' => $actualPermission,
                        'message' => "File permission mismatch for $file"
                    ];
                }
            } else {
                $this->scanResults['info'][] = [
                    'type' => 'missing_file',
                    'file' => $file,
                    'message' => "File or directory not found: $file"
                ];
            }
        }
    }

    /**
     * Check database security
     */
    private function checkDatabaseSecurity() {
        // Check for common database security issues
        $checks = [
            'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "user"' => 'user_table',
            'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "admin"' => 'admin_table',
            'SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = "user" AND column_name = "upass"' => 'password_column'
        ];

        foreach ($checks as $query => $checkName) {
            try {
                $row = $this->db->fetch($query);
                if ($row) {
                    if ($row['count'] == 0) {
                        $this->scanResults['critical_issues'][] = [
                            'type' => 'database_security',
                            'check' => $checkName,
                            'message' => "Database security check failed: $checkName"
                        ];
                    }
                } else {
                    $this->scanResults['critical_issues'][] = [
                        'type' => 'database_security',
                        'check' => $checkName,
                        'message' => "Database security check failed (no result): $checkName"
                    ];
                }
            } catch (Exception $e) {
                $this->scanResults['warnings'][] = [
                    'type' => 'database_error',
                    'check' => $checkName,
                    'error' => $e->getMessage(),
                    'message' => "Database query failed during security scan"
                ];
            }
        }
    }

    /**
     * Check input validation implementation
     */
    private function checkInputValidation() {
        // Check if AdminInputValidator is properly implemented
        if (!class_exists('AdminInputValidator')) {
            $this->scanResults['critical_issues'][] = [
                'type' => 'input_validation',
                'message' => 'AdminInputValidator class not found'
            ];
            return;
        }

        // Test validation methods
        $testCases = [
            ['method' => 'validateEmail', 'input' => 'test@example.com', 'expected' => true],
            ['method' => 'validateText', 'input' => 'Valid text', 'expected' => 'Valid text'],
            ['method' => 'validatePhone', 'input' => '1234567890', 'expected' => '1234567890']
        ];

        foreach ($testCases as $test) {
            try {
                $method = $test['method'];
                $result = AdminInputValidator::$method($test['input']);
                if ($result !== $test['expected']) {
                    $this->scanResults['warnings'][] = [
                        'type' => 'input_validation',
                        'method' => $test['method'],
                        'message' => "Input validation method {$test['method']} may have issues"
                    ];
                }
            } catch (Exception $e) {
                $this->scanResults['critical_issues'][] = [
                    'type' => 'input_validation',
                    'method' => $test['method'],
                    'error' => $e->getMessage(),
                    'message' => "Input validation method {$test['method']} failed"
                ];
            }
        }
    }

    /**
     * Check CSRF protection
     */
    private function checkCSRFProtection() {
        // Check if CSRF protection is implemented
        $csrfFiles = [
            '../includes/csrf_protection.php',
            '../admin/includes/csrf_protection.php'
        ];

        $csrfFound = false;
        foreach ($csrfFiles as $file) {
            if (file_exists($file)) {
                $csrfFound = true;
                break;
            }
        }

        if (!$csrfFound) {
            $this->scanResults['critical_issues'][] = [
                'type' => 'csrf_protection',
                'message' => 'CSRF protection not found'
            ];
        }
    }

    /**
     * Check session security
     */
    private function checkSessionSecurity() {
        // Check session configuration
        $sessionFiles = [
            '../includes/session_helpers.php',
            '../includes/session_manager.php',
            '../admin/includes/session_manager.php'
        ];

        $sessionFound = false;
        foreach ($sessionFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (strpos($content, 'session_regenerate_id') !== false) {
                    $sessionFound = true;
                    break;
                }
            }
        }

        if (!$sessionFound) {
            $this->scanResults['warnings'][] = [
                'type' => 'session_security',
                'message' => 'Session regeneration not found in session manager'
            ];
        }
    }

    /**
     * Check file upload security
     */
    private function checkFileUploadSecurity() {
        // Check if secure file upload is implemented
        $secureUploadFiles = [
            '../includes/security/secure_upload.php',
            '../admin/classes/SecureFileUpload.php'
        ];

        $secureUploadFound = false;
        foreach ($secureUploadFiles as $file) {
            if (file_exists($file)) {
                $secureUploadFound = true;
                break;
            }
        }

        if (!$secureUploadFound) {
            $this->scanResults['warnings'][] = [
                'type' => 'file_upload_security',
                'message' => 'Secure file upload implementation not found'
            ];
        }
    }

    /**
     * Check for SQL injection vulnerabilities
     */
    private function checkSQLInjectionVulnerabilities() {
        // Scan for files that might have SQL injection vulnerabilities
        $adminDir = '../admin/';
        if (is_dir($adminDir)) {
            $files = glob($adminDir . '*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);

                // Check for potential SQL injection patterns
                if (preg_match('/\$_GET\[.*\].*(mysqli_query|->query|->execute)/', $content) ||
                    preg_match('/\$_POST\[.*\].*(mysqli_query|->query|->execute)/', $content)) {

                    // Check if prepared statements or parameter binding is used
                    if (strpos($content, 'prepare') === false && strpos($content, 'bind_param') === false && strpos($content, '[:') === false) {
                        $this->scanResults['critical_issues'][] = [
                            'type' => 'sql_injection',
                            'file' => basename($file),
                            'message' => "Potential SQL injection vulnerability in " . basename($file) . ". Ensure parameter binding is used with the ORM or prepared statements."
                        ];
                    }
                }
            }
        }
    }

    /**
     * Check for XSS vulnerabilities
     */
    private function checkXSSVulnerabilities() {
        // Scan for potential XSS vulnerabilities
        $adminDir = '../admin/';
        if (is_dir($adminDir)) {
            $files = glob($adminDir . '*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);

                // Check for echo statements with user input
                if (preg_match('/echo.*\$_GET\[.*\]/', $content) ||
                    preg_match('/echo.*\$_POST\[.*\]/', $content)) {

                    // Check if h() or htmlspecialchars() is used
                    if (strpos($content, ' h(') === false && strpos($content, 'htmlspecialchars') === false) {
                        $this->scanResults['warnings'][] = [
                            'type' => 'xss_vulnerability',
                            'file' => basename($file),
                            'message' => "Potential XSS vulnerability in " . basename($file)
                        ];
                    }
                }
            }
        }
    }

    /**
     * Check for directory traversal vulnerabilities
     */
    private function checkDirectoryTraversal() {
        // Scan for potential directory traversal patterns
        $adminDir = '../admin/';
        if (is_dir($adminDir)) {
            $files = glob($adminDir . '*.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);

                // Check for directory traversal patterns
                if (preg_match('/\.\.\//', $content) &&
                    strpos($content, 'basename') === false &&
                    strpos($content, 'realpath') === false) {

                    $this->scanResults['warnings'][] = [
                        'type' => 'directory_traversal',
                        'file' => basename($file),
                        'message' => "Potential directory traversal vulnerability in " . basename($file)
                    ];
                }
            }
        }
    }

    /**
     * Check for sensitive files
     */
    private function checkSensitiveFiles() {
        $sensitiveFiles = [
            '../.env',
            '../config.php',
            '../phpinfo.php',
            '../.git/config',
            '../composer.json',
            '../package.json'
        ];

        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                if ($perms != '0644' && $perms != '0600') {
                    $this->scanResults['warnings'][] = [
                        'type' => 'sensitive_file_permissions',
                        'file' => $file,
                        'permissions' => $perms,
                        'message' => "Sensitive file $file has overly permissive permissions"
                    ];
                }
            }
        }
    }

    /**
     * Generate scan summary
     */
    private function generateSummary() {
        $criticalCount = count($this->scanResults['critical_issues']);
        $warningCount = count($this->scanResults['warnings']);
        $infoCount = count($this->scanResults['info']);

        $this->scanResults['summary'] = [
            'total_critical' => $criticalCount,
            'total_warnings' => $warningCount,
            'total_info' => $infoCount,
            'overall_status' => $criticalCount > 0 ? 'CRITICAL' : ($warningCount > 0 ? 'WARNING' : 'SECURE'),
            'recommendations' => $this->generateRecommendations()
        ];
    }

    /**
     * Generate security recommendations
     */
    private function generateRecommendations() {
        $recommendations = [];

        if (count($this->scanResults['critical_issues']) > 0) {
            $recommendations[] = "Address all critical security issues immediately";
        }

        if (count($this->scanResults['warnings']) > 0) {
            $recommendations[] = "Review and fix security warnings";
        }

        // Add specific recommendations based on findings
        foreach ($this->scanResults['critical_issues'] as $issue) {
            switch ($issue['type']) {
                case 'sql_injection':
                    $recommendations[] = "Use prepared statements for all database queries";
                    break;
                case 'csrf_protection':
                    $recommendations[] = "Implement CSRF protection for all forms";
                    break;
                case 'input_validation':
                    $recommendations[] = "Implement comprehensive input validation";
                    break;
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Log scan results
     */
    private function logScanResults() {
        $logEntry = date('Y-m-d H:i:s') . " - Security Scan Results:\n";
        $logEntry .= "Overall Status: " . $this->scanResults['summary']['overall_status'] . "\n";
        $logEntry .= "Critical Issues: " . $this->scanResults['summary']['total_critical'] . "\n";
        $logEntry .= "Warnings: " . $this->scanResults['summary']['total_warnings'] . "\n";
        $logEntry .= "Info: " . $this->scanResults['summary']['total_info'] . "\n";

        if (count($this->scanResults['critical_issues']) > 0) {
            $logEntry .= "Critical Issues:\n";
            foreach ($this->scanResults['critical_issues'] as $issue) {
                $logEntry .= "  - {$issue['message']}\n";
            }
        }

        file_put_contents($this->securityLogFile, $logEntry . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Get scan results as HTML
     */
    public function getResultsAsHTML() {
        $results = $this->runFullScan();

        $html = '<div class="security-scan-results">';
        $html .= '<h2>Security Scan Results</h2>';
        $html .= '<div class="scan-summary">';
        $html .= '<p><strong>Overall Status:</strong> <span class="status-' . strtolower($results['summary']['overall_status']) . '">' . $results['summary']['overall_status'] . '</span></p>';
        $html .= '<p>Critical Issues: ' . $results['summary']['total_critical'] . '</p>';
        $html .= '<p>Warnings: ' . $results['summary']['total_warnings'] . '</p>';
        $html .= '<p>Info: ' . $results['summary']['total_info'] . '</p>';
        $html .= '</div>';

        if (count($results['critical_issues']) > 0) {
            $html .= '<h3>Critical Issues</h3><ul class="critical-issues">';
            foreach ($results['critical_issues'] as $issue) {
                $html .= '<li>' . h($issue['message']) . '</li>';
            }
            $html .= '</ul>';
        }

        if (count($results['warnings']) > 0) {
            $html .= '<h3>Warnings</h3><ul class="warnings">';
            foreach ($results['warnings'] as $warning) {
                $html .= '<li>' . h($warning['message']) . '</li>';
            }
            $html .= '</ul>';
        }

        if (count($results['info']) > 0) {
            $html .= '<h3>Information</h3><ul class="info">';
            foreach ($results['info'] as $info) {
                $html .= '<li>' . h($info['message']) . '</li>';
            }
            $html .= '</ul>';
        }

        if (count($results['summary']['recommendations']) > 0) {
            $html .= '<h3>Recommendations</h3><ul class="recommendations">';
            foreach ($results['summary']['recommendations'] as $rec) {
                $html .= '<li>' . h($rec) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}

// Usage example
if (php_sapi_name() === 'cli') {
    // Command line usage
    $scanner = new SecurityScanner();
    $results = $scanner->runFullScan();

    echo "Security Scan Results\n";
    echo "====================\n";
    echo "Overall Status: " . $results['summary']['overall_status'] . "\n";
    echo "Critical Issues: " . $results['summary']['total_critical'] . "\n";
    echo "Warnings: " . $results['summary']['total_warnings'] . "\n";
    echo "Info: " . $results['summary']['total_info'] . "\n";

    if (count($results['critical_issues']) > 0) {
        echo "\nCritical Issues:\n";
        foreach ($results['critical_issues'] as $issue) {
            echo "- " . $issue['message'] . "\n";
        }
    }
}
