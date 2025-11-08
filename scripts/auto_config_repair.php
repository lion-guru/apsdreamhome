<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class AutoConfigRepair {
    private $baseDir;
    private $logFile;
    private $envFile;
    private $repairLog = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
        $this->logFile = $this->baseDir . '/logs/auto_repair.log';
        $this->envFile = $this->baseDir . '/includes/config/.env';
    }

    public function runRepair() {
        $this->log("Starting Automated Configuration Repair");

        try {
            $this->repairEnvironmentVariables();
            $this->fixDatabaseConfiguration();
            $this->enhanceSecuritySettings();
            $this->validateAndRepairPermissions();
            $this->generateComprehensiveReport();
        } catch (Exception $e) {
            $this->log("Critical Repair Error: " . $e->getMessage());
        }

        $this->outputResults();
    }

    private function repairEnvironmentVariables() {
        $this->log("Repairing Environment Variables");

        // Default database configuration
        $defaultConfig = [
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => 'apsdreamhome',
            'SECURITY_MODE' => 'balanced',
            'GEOBLOCKING_ENABLED' => 'true'
        ];

        $existingConfig = file_exists($this->envFile) ? parse_ini_file($this->envFile, false, INI_SCANNER_RAW) : [];
        $existingConfig = is_array($existingConfig) ? $existingConfig : [];
        $updatedConfig = array_merge($defaultConfig, $existingConfig);

        // Write updated configuration
        $configContent = "";
        foreach ($updatedConfig as $key => $value) {
            $configContent .= "$key=$value\n";
        }

        file_put_contents($this->envFile, $configContent);
        $this->log("Environment Variables Repaired");
    }

    private function fixDatabaseConfiguration() {
        $this->log("Checking Database Configuration");

        // Attempt to create database if not exists
        try {
            $conn = new mysqli('localhost', 'root', '');
            $dbName = 'apsdreamhome';
            
            $stmt = $conn->query("CREATE DATABASE IF NOT EXISTS `$" . $dbName . "`");
            $conn->select_db($dbName);

            // Create basic tables if not exists
            $tableQueries = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE,
                    password VARCHAR(255),
                    email VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS admin (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    auser VARCHAR(50) UNIQUE,
                    apass VARCHAR(255),
                    role ENUM('admin','manager','superadmin') DEFAULT 'admin',
                    status ENUM('active','inactive') DEFAULT 'active'
                )"
            ];

            foreach ($tableQueries as $query) {
                $conn->query($query);
            }

            $conn->close();
            $this->log("Database Configuration Repaired");
        } catch (Exception $e) {
            $this->log("Database Repair Failed: " . $e->getMessage());
        }
    }

    private function enhanceSecuritySettings() {
        $this->log("Enhancing Security Settings");

        // Generate secure random keys
        $securityKeys = [
            'APP_SECRET_KEY' => bin2hex(random_bytes(32)),
            'SECURITY_SALT' => bin2hex(random_bytes(16)),
            'SECURITY_ENCRYPTION_KEY' => bin2hex(random_bytes(32)),
            'SECURITY_IV' => bin2hex(random_bytes(16))
        ];

        // Append to existing .env file
        $securityContent = "\n# Auto-Generated Security Keys\n";
        foreach ($securityKeys as $key => $value) {
            $securityContent .= "$key=$value\n";
        }

        file_put_contents($this->envFile, $securityContent, FILE_APPEND);
        $this->log("Security Keys Generated");
    }

    private function validateAndRepairPermissions() {
        $this->log("Repairing File Permissions");

        $criticalDirs = [
            $this->baseDir . '/logs',
            $this->baseDir . '/includes/config',
            $this->baseDir . '/admin'
        ];

        foreach ($criticalDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            chmod($dir, 0755);
        }

        // Secure sensitive files
        $sensitiveFiles = [
            $this->envFile,
            $this->logFile
        ];

        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                chmod($file, 0600);
            }
        }

        $this->log("Permissions Repaired");
    }

    private function generateComprehensiveReport() {
        $reportFile = $this->baseDir . '/logs/repair_report.json';
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'repairs' => $this->repairLog,
            'status' => 'completed'
        ];

        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    private function log($message) {
        $this->repairLog[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message
        ];
        error_log($message, 3, $this->logFile);
    }

    private function outputResults() {
        echo "Auto Configuration Repair Complete\n";
        echo "Repair Log:\n";
        foreach ($this->repairLog as $entry) {
            echo "- {$entry['timestamp']}: {$entry['message']}\n";
        }
    }
}

// Run repair
$repair = new AutoConfigRepair();
$repair->runRepair();
