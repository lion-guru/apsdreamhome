<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ComprehensiveSystemDiagnostic {
    private $baseDir;
    private $logFile;
    private $diagnosticReport = [];
    private $criticalIssues = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
        $this->logFile = $this->baseDir . '/logs/system_diagnostic.log';
    }

    public function runCompleteDiagnostic() {
        $this->log("Starting Comprehensive System Diagnostic");

        try {
            $this->checkPhpEnvironment();
            $this->validateDatabaseConnection();
            $this->checkFileSystemIntegrity();
            $this->validateSecurityConfigurations();
            $this->checkPerformanceMetrics();
            $this->repairCriticalIssues();
            $this->generateDiagnosticReport();
        } catch (Exception $e) {
            $this->log("Critical Diagnostic Error: " . $e->getMessage());
            $this->criticalIssues[] = $e->getMessage();
        }

        $this->outputResults();
    }

    private function checkPhpEnvironment() {
        $this->log("Checking PHP Environment");

        $requiredExtensions = [
            'mysqli', 'json', 'session', 'openssl', 'curl'
        ];

        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            $this->criticalIssues[] = "Missing PHP Extensions: " . implode(', ', $missingExtensions);
        }

        $this->diagnosticReport['php_version'] = PHP_VERSION;
        $this->diagnosticReport['loaded_extensions'] = get_loaded_extensions();
    }

    private function validateDatabaseConnection() {
        $this->log("Validating Database Connection");

        try {
            // Load database configuration
            $envFile = $this->baseDir . '/includes/config/.env';
            $dbConfig = parse_ini_file($envFile);

            $host = $dbConfig['DB_HOST'] ?? 'localhost';
            $user = $dbConfig['DB_USER'] ?? 'root';
            $pass = $dbConfig['DB_PASS'] ?? '';
            $dbname = $dbConfig['DB_NAME'] ?? 'apsdreamhomefinal';

            $conn = new mysqli($host, $user, $pass, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Database Connection Failed: " . $conn->connect_error);
            }

            // Check critical tables
            $criticalTables = ['users', 'admin', 'properties'];
            foreach ($criticalTables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows == 0) {
                    $this->criticalIssues[] = "Missing Critical Table: $table";
                }
            }

            $conn->close();
        } catch (Exception $e) {
            $this->criticalIssues[] = $e->getMessage();
        }
    }

    private function checkFileSystemIntegrity() {
        $this->log("Checking File System Integrity");

        $criticalDirectories = [
            '/logs',
            '/includes/config',
            '/admin',
            '/database/secure_scripts'
        ];

        foreach ($criticalDirectories as $dir) {
            $fullPath = $this->baseDir . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->diagnosticReport['created_directories'][] = $fullPath;
            }
        }

        // Check critical files
        $criticalFiles = [
            '/includes/config/.env',
            '/includes/db_connection.php',
            '/admin/admin_login_handler.php'
        ];

        foreach ($criticalFiles as $file) {
            $fullPath = $this->baseDir . $file;
            if (!file_exists($fullPath)) {
                $this->criticalIssues[] = "Missing Critical File: $file";
            }
        }
    }

    private function validateSecurityConfigurations() {
        $this->log("Validating Security Configurations");

        $securityChecks = [
            'GEOBLOCKING_ENABLED' => 'true',
            'SECURITY_MODE' => 'balanced',
            'INTRUSION_DETECTION_ENABLED' => 'true'
        ];

        $envFile = $this->baseDir . '/includes/config/.env';
        $config = parse_ini_file($envFile);

        foreach ($securityChecks as $key => $expectedValue) {
            if (!isset($config[$key]) || $config[$key] != $expectedValue) {
                $this->criticalIssues[] = "Security Configuration Issue: $key";
            }
        }
    }

    private function checkPerformanceMetrics() {
        $this->log("Checking Performance Metrics");

        $this->diagnosticReport['memory_usage'] = memory_get_usage(true);
        $this->diagnosticReport['memory_peak_usage'] = memory_get_peak_usage(true);
        // Windows-compatible performance check
        $this->diagnosticReport['cpu_load'] = $this->getWindowsCpuLoad();
    }

    private function repairCriticalIssues() {
        $this->log("Repairing Critical Issues");

        if (!empty($this->criticalIssues)) {
            // Attempt to auto-repair
            $repairScript = $this->baseDir . '/auto_config_repair.php';
            if (file_exists($repairScript)) {
                include $repairScript;
                $repair = new AutoConfigRepair();
                $repair->runRepair();
            }
        }
    }

    private function generateDiagnosticReport() {
        $reportFile = $this->baseDir . '/logs/comprehensive_diagnostic_report.json';
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'diagnostic_report' => $this->diagnosticReport,
            'critical_issues' => $this->criticalIssues,
            'status' => empty($this->criticalIssues) ? 'healthy' : 'needs_attention'
        ];

        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    private function log($message) {
        error_log($message, 3, $this->logFile);
    }

    private function outputResults() {
        echo "Comprehensive System Diagnostic Complete\n";
        echo "Critical Issues: " . count($this->criticalIssues) . "\n";
        
        if (!empty($this->criticalIssues)) {
            echo "Issues Detected:\n";
            foreach ($this->criticalIssues as $issue) {
                echo "- $issue\n";
            }
        } else {
            echo "System is healthy! No critical issues found.\n";
        }
    }
}

// Run diagnostic
$diagnostic = new ComprehensiveSystemDiagnostic();
$diagnostic->runCompleteDiagnostic();
