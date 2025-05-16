<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class LoginSystemDiagnostic {
    private $baseDir;
    private $diagnosticReport = [];
    private $criticalIssues = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
    }

    public function runCompleteDiagnostic() {
        $this->log("Starting Comprehensive Login System Diagnostic");

        try {
            $this->checkPhpConfiguration();
            $this->validateLoginFiles();
            $this->testDatabaseConnection();
            $this->checkSessionConfiguration();
            $this->validateErrorHandling();
            $this->generateDiagnosticReport();
        } catch (Exception $e) {
            $this->log("Critical Diagnostic Error: " . $e->getMessage());
            $this->criticalIssues[] = $e->getMessage();
        }

        $this->outputResults();
    }

    private function checkPhpConfiguration() {
        $this->log("Checking PHP Configuration");

        $requiredExtensions = ['mysqli', 'session', 'json', 'openssl'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            $this->criticalIssues[] = "Missing PHP Extensions: " . implode(', ', $missingExtensions);
        }

        $this->diagnosticReport['php_extensions'] = [
            'loaded' => get_loaded_extensions(),
            'missing' => $missingExtensions
        ];
    }

    private function validateLoginFiles() {
        $this->log("Validating Login System Files");

        $criticalFiles = [
            '/admin/admin_login_handler.php',
            '/admin/login_error_handler.php',
            '/includes/db_connection.php',
            '/includes/config/.env'
        ];

        $missingFiles = [];
        foreach ($criticalFiles as $file) {
            $fullPath = $this->baseDir . $file;
            if (!file_exists($fullPath)) {
                $missingFiles[] = $file;
            } else {
                // Basic syntax check
                $syntaxCheck = shell_exec("php -l $fullPath");
                if (strpos($syntaxCheck, 'No syntax errors') === false) {
                    $this->criticalIssues[] = "Syntax error in $file: " . trim($syntaxCheck);
                }
            }
        }

        if (!empty($missingFiles)) {
            $this->criticalIssues[] = "Missing Critical Files: " . implode(', ', $missingFiles);
        }
    }

    private function testDatabaseConnection() {
        $this->log("Testing Database Connection");

        try {
            require_once $this->baseDir . '/includes/db_connection.php';
            $conn = getDbConnection();

            if (!$conn) {
                throw new Exception("Database connection failed");
            }

            // Test basic query
            $result = $conn->query("SELECT 1 AS test");
            if (!$result) {
                throw new Exception("Database query test failed");
            }

            $conn->close();
            $this->diagnosticReport['database_connection'] = true;
        } catch (Exception $e) {
            $this->criticalIssues[] = "Database Connection Error: " . $e->getMessage();
            $this->diagnosticReport['database_connection'] = false;
        }
    }

    private function checkSessionConfiguration() {
        $this->log("Checking Session Configuration");

        $sessionSettings = [
            'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
            'session.cookie_httponly' => ini_get('session.cookie_httponly'),
            'session.use_strict_mode' => ini_get('session.use_strict_mode'),
            'session.cookie_secure' => ini_get('session.cookie_secure')
        ];

        // Recommended session settings
        $recommendedSettings = [
            'session.gc_maxlifetime' => 1800, // 30 minutes
            'session.cookie_httponly' => 1,
            'session.use_strict_mode' => 1,
            'session.cookie_secure' => 1
        ];

        $sessionIssues = [];
        foreach ($recommendedSettings as $setting => $recommendedValue) {
            if ($sessionSettings[$setting] != $recommendedValue) {
                $sessionIssues[] = "$setting is not optimally configured";
            }
        }

        if (!empty($sessionIssues)) {
            $this->criticalIssues[] = "Session Configuration Issues: " . implode(', ', $sessionIssues);
        }

        $this->diagnosticReport['session_configuration'] = $sessionSettings;
    }

    private function validateErrorHandling() {
        $this->log("Validating Error Handling Mechanisms");

        $errorHandlerPath = $this->baseDir . '/admin/login_error_handler.php';
        
        if (file_exists($errorHandlerPath)) {
            // Basic error handler validation
            $errorHandlerContent = file_get_contents($errorHandlerPath);
            
            $requiredMethods = [
                'handleLoginError',
                'checkLoginLock',
                'resetLoginAttempts'
            ];

            $missingMethods = [];
            foreach ($requiredMethods as $method) {
                if (strpos($errorHandlerContent, $method) === false) {
                    $missingMethods[] = $method;
                }
            }

            if (!empty($missingMethods)) {
                $this->criticalIssues[] = "Missing Error Handling Methods: " . implode(', ', $missingMethods);
            }
        } else {
            $this->criticalIssues[] = "Login Error Handler File Missing";
        }
    }

    private function generateDiagnosticReport() {
        $reportFile = $this->baseDir . '/logs/login_system_diagnostic_report.json';
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'diagnostic_report' => $this->diagnosticReport,
            'critical_issues' => $this->criticalIssues,
            'status' => empty($this->criticalIssues) ? 'passed' : 'failed'
        ];

        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->baseDir . '/logs/login_system_diagnostic.log', $logMessage, FILE_APPEND);
    }

    private function outputResults() {
        echo "Login System Diagnostic Complete\n";
        echo "Total Critical Issues: " . count($this->criticalIssues) . "\n";
        
        if (!empty($this->criticalIssues)) {
            echo "Issues Detected:\n";
            foreach ($this->criticalIssues as $issue) {
                echo "- $issue\n";
            }
        } else {
            echo "Login system tested successfully! No critical issues found.\n";
        }
    }
}

// Run diagnostic
$diagnostic = new LoginSystemDiagnostic();
$diagnostic->runCompleteDiagnostic();
