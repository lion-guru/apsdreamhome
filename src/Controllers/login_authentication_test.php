<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class LoginAuthenticationTest {
    private $baseDir;
    private $logFile;
    private $testResults = [];
    private $criticalIssues = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
        $this->logFile = $this->baseDir . '/logs/login_test.log';
    }

    public function runFullAuthenticationTest() {
        $this->log("Starting Comprehensive Login Authentication Test");

        try {
            $this->checkDatabaseConnection();
            $this->createTestAdminUser();
            $this->testLoginMechanisms();
            $this->validateSessionManagement();
            $this->checkSecurityMechanisms();
            $this->generateTestReport();
        } catch (Exception $e) {
            $this->log("Critical Authentication Test Error: " . $e->getMessage());
            $this->criticalIssues[] = $e->getMessage();
        }

        $this->outputResults();
    }

    private function checkDatabaseConnection() {
        $this->log("Checking Database Connection");

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
            $this->testResults['database_connection'] = true;
        } catch (Exception $e) {
            $this->criticalIssues[] = "Database Connection Error: " . $e->getMessage();
            $this->testResults['database_connection'] = false;
        }
    }

    private function createTestAdminUser() {
        $this->log("Creating Test Admin User");

        try {
            require_once $this->baseDir . '/includes/db_connection.php';
            require_once $this->baseDir . '/includes/password_utils.php';

            $conn = getDbConnection();
            
            // Hash a test password
            $testPassword = PasswordUtils::hashPassword('TestAdmin123!');

            // Prepare and execute insert statement
            $stmt = $conn->prepare("INSERT INTO admin (auser, apass, role, status) VALUES (?, ?, 'admin', 'active') ON DUPLICATE KEY UPDATE apass = ?");
            $username = 'test_admin';
            $stmt->bind_param("sss", $username, $testPassword, $testPassword);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $this->testResults['test_admin_created'] = true;
            } else {
                $this->testResults['test_admin_created'] = false;
            }

            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $this->criticalIssues[] = "Test Admin Creation Error: " . $e->getMessage();
            $this->testResults['test_admin_created'] = false;
        }
    }

    private function testLoginMechanisms() {
        $this->log("Testing Login Mechanisms");

        try {
            // Simulate login process
            $loginHandlerPath = $this->baseDir . '/admin/admin_login_handler.php';
            
            if (!file_exists($loginHandlerPath)) {
                throw new Exception("Login handler not found");
            }

            // Mock login data
            $_POST = [
                'username' => 'test_admin',
                'password' => 'TestAdmin123!',
                'csrf_token' => 'mock_token' // In real scenario, generate actual CSRF token
            ];

            // Capture output buffer to prevent header redirects
            ob_start();
            include $loginHandlerPath;
            $output = ob_get_clean();

            // Check session variables
            session_start();
            $loginSuccessful = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

            $this->testResults['login_mechanism'] = $loginSuccessful;
            
            if (!$loginSuccessful) {
                $this->criticalIssues[] = "Login mechanism test failed";
            }
        } catch (Exception $e) {
            $this->criticalIssues[] = "Login Mechanism Test Error: " . $e->getMessage();
            $this->testResults['login_mechanism'] = false;
        }
    }

    private function validateSessionManagement() {
        $this->log("Validating Session Management");

        try {
            // Check session timeout configuration
            $sessionTimeout = ini_get('session.gc_maxlifetime');
            
            // Recommended timeout: 30 minutes
            $this->testResults['session_timeout'] = $sessionTimeout >= 1800;

            if ($sessionTimeout < 1800) {
                $this->criticalIssues[] = "Session timeout too short: $sessionTimeout seconds";
            }
        } catch (Exception $e) {
            $this->criticalIssues[] = "Session Management Test Error: " . $e->getMessage();
        }
    }

    private function checkSecurityMechanisms() {
        $this->log("Checking Security Mechanisms");

        try {
            // Check for basic security configurations
            $securityChecks = [
                'password_hash' => function_exists('password_hash'),
                'https_only' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'csrf_protection' => file_exists($this->baseDir . '/includes/csrf_protection.php')
            ];

            $this->testResults['security_mechanisms'] = $securityChecks;

            foreach ($securityChecks as $check => $result) {
                if (!$result) {
                    $this->criticalIssues[] = "Security mechanism failed: $check";
                }
            }
        } catch (Exception $e) {
            $this->criticalIssues[] = "Security Mechanism Test Error: " . $e->getMessage();
        }
    }

    private function generateTestReport() {
        $reportFile = $this->baseDir . '/logs/login_authentication_report.json';
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'test_results' => $this->testResults,
            'critical_issues' => $this->criticalIssues,
            'status' => empty($this->criticalIssues) ? 'passed' : 'failed'
        ];

        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    private function outputResults() {
        echo "Login Authentication Test Complete\n";
        echo "Total Critical Issues: " . count($this->criticalIssues) . "\n";
        
        if (!empty($this->criticalIssues)) {
            echo "Issues Detected:\n";
            foreach ($this->criticalIssues as $issue) {
                echo "- $issue\n";
            }
        } else {
            echo "Authentication system tested successfully! No critical issues found.\n";
        }
    }
}

// Run authentication test
$authTest = new LoginAuthenticationTest();
$authTest->runFullAuthenticationTest();
