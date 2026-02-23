<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class SystemRepair {
    private $baseDir;
    private $logFile;
    private $issues = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
        $this->logFile = $this->baseDir . '/logs/system_repair.log';
    }

    public function runFullRepair() {
        $this->log("Starting Comprehensive System Repair");

        try {
            $this->checkAndRepairEnvironment();
            $this->validateAndFixDatabase();
            $this->secureConfigFiles();
            $this->repairPermissions();
            $this->generateRepairReport();
        } catch (Exception $e) {
            $this->log("Critical Repair Error: " . $e->getMessage());
            $this->issues[] = $e->getMessage();
        }

        $this->outputResults();
    }

    private function checkAndRepairEnvironment() {
        $this->log("Checking and Repairing Environment");

        // Required PHP extensions
        $requiredExtensions = ['mysqli', 'json', 'session', 'openssl'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            $this->issues[] = "Missing PHP Extensions: " . implode(', ', $missingExtensions);
        }

        // Repair .env file
        $envFile = $this->baseDir . '/includes/config/.env';
        $defaultConfig = [
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => 'apsdreamhome',
            'SECURITY_MODE' => 'balanced',
            'GEOBLOCKING_ENABLED' => 'true'
        ];

        if (!file_exists($envFile)) {
            $configContent = "";
            foreach ($defaultConfig as $key => $value) {
                $configContent .= "$key=$value\n";
            }
            file_put_contents($envFile, $configContent);
            $this->log("Created default .env file");
        }
    }

    private function validateAndFixDatabase() {
        $this->log("Validating and Fixing Database");

        try {
            // Load database configuration
            $envFile = $this->baseDir . '/includes/config/.env';
            $dbConfig = parse_ini_file($envFile);

            $host = $dbConfig['DB_HOST'] ?? 'localhost';
            $user = $dbConfig['DB_USER'] ?? 'root';
            $pass = $dbConfig['DB_PASS'] ?? '';
            $dbname = $dbConfig['DB_NAME'] ?? 'apsdreamhome';

            // Attempt to create database if not exists
            $conn = new mysqli($host, $user, $pass);
            $stmt = $conn->query("CREATE DATABASE IF NOT EXISTS `$" . $dbname . "`");
            $conn->select_db($dbname);

            // Critical tables
            $criticalTables = [
                'users' => "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE,
                    password VARCHAR(255),
                    email VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
                'admin' => "CREATE TABLE IF NOT EXISTS admin (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    auser VARCHAR(50) UNIQUE,
                    apass VARCHAR(255),
                    role ENUM('admin','manager','superadmin') DEFAULT 'admin',
                    status ENUM('active','inactive') DEFAULT 'active'
                )"
            ];

            foreach ($criticalTables as $table => $createQuery) {
                $conn->query($createQuery);
            }

            $conn->close();
        } catch (Exception $e) {
            $this->issues[] = "Database Repair Failed: " . $e->getMessage();
        }
    }

    private function secureConfigFiles() {
        $this->log("Securing Configuration Files");

        $sensitiveFiles = [
            '/includes/config/.env',
            '/logs/system_repair.log'
        ];

        foreach ($sensitiveFiles as $file) {
            $fullPath = $this->baseDir . $file;
            if (file_exists($fullPath)) {
                chmod($fullPath, 0600); // Read/write for owner only
            }
        }
    }

    private function repairPermissions() {
        $this->log("Repairing File Permissions");

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
                $this->log("Created directory: $fullPath");
            }
            chmod($fullPath, 0755);
        }
    }

    private function generateRepairReport() {
        $reportFile = $this->baseDir . '/logs/system_repair_report.json';
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'issues' => $this->issues,
            'status' => empty($this->issues) ? 'successful' : 'partial_repair'
        ];

        file_put_contents($reportFile, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    private function outputResults() {
        echo "System Repair Complete\n";
        echo "Total Issues Addressed: " . count($this->issues) . "\n";
        
        if (!empty($this->issues)) {
            echo "Issues Found:\n";
            foreach ($this->issues as $issue) {
                echo "- $issue\n";
            }
        } else {
            echo "System repaired successfully! No critical issues found.\n";
        }
    }
}

// Run repair
$repair = new SystemRepair();
$repair->runFullRepair();
