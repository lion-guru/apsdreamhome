<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// System Health Check Script
class SystemHealthCheck {
    private $checks = [];
    private $errors = [];

    public function __construct() {
        $this->addChecks();
    }

    private function addChecks() {
        $this->checks = [
            'Database Connection' => [$this, 'checkDatabaseConnection'],
            'Environment Variables' => [$this, 'checkEnvironmentVariables'],
            'Security Configuration' => [$this, 'checkSecurityConfiguration'],
            'Performance Monitoring' => [$this, 'checkPerformanceSettings']
        ];
    }

    public function runHealthCheck() {
        foreach ($this->checks as $name => $check) {
            try {
                call_user_func($check);
                echo "✅ $name: Passed\n";
            } catch (Exception $e) {
                $this->errors[$name] = $e->getMessage();
                echo "❌ $name: Failed - " . $e->getMessage() . "\n";
            }
        }

        $this->generateReport();
    }

    private function checkDatabaseConnection() {
        require_once __DIR__ . '/includes/db_connection.php';
        $conn = getDbConnection();
        if (!$conn) {
            throw new Exception('Unable to establish database connection');
        }
        $conn->close();
    }

    private function checkEnvironmentVariables() {
        $requiredVars = [
            'DB_HOST', 'DB_USER', 'DB_NAME', 
            'SECURITY_MODE', 'GEOBLOCKING_ENABLED'
        ];

        foreach ($requiredVars as $var) {
            if (empty(getenv($var))) {
                throw new Exception("Missing environment variable: $var");
            }
        }
    }

    private function checkSecurityConfiguration() {
        $securityMode = getenv('SECURITY_MODE');
        $geoblockingEnabled = getenv('GEOBLOCKING_ENABLED');

        if ($securityMode === 'strict' && $geoblockingEnabled !== 'true') {
            throw new Exception('Strict security mode requires geoblocking');
        }
    }

    private function checkPerformanceSettings() {
        $profilingEnabled = getenv('PERF_PROFILING_ENABLED');
        $profilingMode = getenv('PERF_PROFILING_MODE');
        $thresholdTime = floatval(getenv('PERF_THRESHOLD_EXECUTION_TIME'));

        if ($profilingEnabled === 'true' && $thresholdTime < 0.1) {
            throw new Exception('Performance threshold is too aggressive');
        }
    }

    private function generateReport() {
        $reportFile = __DIR__ . '/logs/system_health_report.log';
        $reportContent = "System Health Check Report - " . date('Y-m-d H:i:s') . "\n";
        $reportContent .= str_repeat('-', 50) . "\n";

        if (empty($this->errors)) {
            $reportContent .= "Overall Status: HEALTHY ✅\n";
        } else {
            $reportContent .= "Overall Status: ISSUES DETECTED ❌\n";
            $reportContent .= "Errors:\n";
            foreach ($this->errors as $check => $error) {
                $reportContent .= "- $check: $error\n";
            }
        }

        file_put_contents($reportFile, $reportContent, FILE_APPEND);
        echo "\nFull report generated at $reportFile\n";
    }
}

// Run health check
$healthCheck = new SystemHealthCheck();
$healthCheck->runHealthCheck();
