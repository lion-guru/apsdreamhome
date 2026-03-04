<?php
/**
 * Auto System Monitor
 * 
 * Automated system monitoring and maintenance script
 * Runs continuously to ensure all systems are working properly
 */

// Set execution time limit
set_time_limit(0);

// Include required files
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../app/Core/Security.php';

class AutoSystemMonitor {
    private $db;
    private $security;
    private $logFile;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->security = new Security();
        $this->logFile = __DIR__ . '/../logs/auto_monitor.log';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log monitoring activities
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry; // Also output to console
    }
    
    /**
     * Check database connection
     */
    public function checkDatabase() {
        try {
            $conn = $this->db->getConnection();
            if ($conn) {
                // Test a simple query
                $stmt = $conn->query("SELECT 1");
                if ($stmt) {
                    $this->log("Database connection: OK");
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->log("Database connection failed: " . $e->getMessage(), 'ERROR');
            return false;
        }
        return false;
    }
    
    /**
     * Check if main pages are accessible
     */
    public function checkMainPages() {
        $pages = [
            'http://localhost/apsdreamhome/' => 'Homepage',
            'http://localhost/apsdreamhome/contact' => 'Contact Page',
            'http://localhost/apsdreamhome/about' => 'About Page',
            'http://localhost/apsdreamhome/properties' => 'Properties Page',
            'http://localhost/apsdreamhome/admin/dashboard.php' => 'Admin Dashboard'
        ];
        
        $results = [];
        foreach ($pages as $url => $name) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $this->log("$name: OK (HTTP $httpCode)");
                $results[$name] = 'OK';
            } else {
                $this->log("$name: FAILED (HTTP $httpCode)", 'ERROR');
                $results[$name] = 'FAILED';
            }
        }
        
        return $results;
    }
    
    /**
     * Check if enhanced features are working
     */
    public function checkEnhancedFeatures() {
        $features = [];
        
        // Check Bootstrap CSS
        $homepage = file_get_contents('http://localhost/apsdreamhome/');
        if (strpos($homepage, 'bootstrap@5.3.0') !== false) {
            $this->log("Bootstrap 5.3.0: OK");
            $features['bootstrap'] = 'OK';
        } else {
            $this->log("Bootstrap 5.3.0: NOT FOUND", 'ERROR');
            $features['bootstrap'] = 'NOT FOUND';
        }
        
        // Check AOS animations
        if (strpos($homepage, 'aos@2.3.1') !== false) {
            $this->log("AOS Animations: OK");
            $features['aos'] = 'OK';
        } else {
            $this->log("AOS Animations: NOT FOUND", 'ERROR');
            $features['aos'] = 'NOT FOUND';
        }
        
        // Check Font Awesome
        if (strpos($homepage, 'font-awesome@6.4.0') !== false) {
            $this->log("Font Awesome 6.4.0: OK");
            $features['fontawesome'] = 'OK';
        } else {
            $this->log("Font Awesome 6.4.0: NOT FOUND", 'ERROR');
            $features['fontawesome'] = 'NOT FOUND';
        }
        
        return $features;
    }
    
    /**
     * Check admin system functionality
     */
    public function checkAdminSystem() {
        $adminChecks = [];
        
        try {
            // Check admin dashboard
            $adminDashboard = file_get_contents('http://localhost/apsdreamhome/admin/dashboard.php');
            if ($adminDashboard && strlen($adminDashboard) > 1000) {
                $this->log("Admin Dashboard: OK");
                $adminChecks['dashboard'] = 'OK';
            } else {
                $this->log("Admin Dashboard: FAILED", 'ERROR');
                $adminChecks['dashboard'] = 'FAILED';
            }
            
            // Check database tables
            $conn = $this->db->getConnection();
            $tables = $conn->query("SHOW TABLES");
            $tableCount = $tables->rowCount();
            
            if ($tableCount > 0) {
                $this->log("Database Tables: OK ($tableCount tables found)");
                $adminChecks['tables'] = 'OK';
            } else {
                $this->log("Database Tables: FAILED (No tables found)", 'ERROR');
                $adminChecks['tables'] = 'FAILED';
            }
            
        } catch (Exception $e) {
            $this->log("Admin System Check Failed: " . $e->getMessage(), 'ERROR');
            $adminChecks['error'] = $e->getMessage();
        }
        
        return $adminChecks;
    }
    
    /**
     * Check system performance
     */
    public function checkPerformance() {
        $performance = [];
        
        // Check page load time
        $start = microtime(true);
        $homepage = file_get_contents('http://localhost/apsdreamhome/');
        $end = microtime(true);
        $loadTime = ($end - $start) * 1000; // Convert to milliseconds
        
        if ($loadTime < 3000) { // Less than 3 seconds
            $this->log("Page Load Time: OK (" . number_format($loadTime, 2) . "ms)");
            $performance['load_time'] = 'OK';
        } else {
            $this->log("Page Load Time: SLOW (" . number_format($loadTime, 2) . "ms)", 'WARNING');
            $performance['load_time'] = 'SLOW';
        }
        
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryUsageMB = $memoryUsage / 1024 / 1024;
        
        if ($memoryUsageMB < 128) { // Less than 128MB
            $this->log("Memory Usage: OK (" . number_format($memoryUsageMB, 2) . "MB)");
            $performance['memory'] = 'OK';
        } else {
            $this->log("Memory Usage: HIGH (" . number_format($memoryUsageMB, 2) . "MB)", 'WARNING');
            $performance['memory'] = 'HIGH';
        }
        
        return $performance;
    }
    
    /**
     * Generate system status report
     */
    public function generateStatusReport() {
        $this->log("=== SYSTEM STATUS REPORT ===");
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $this->checkDatabase(),
            'pages' => $this->checkMainPages(),
            'features' => $this->checkEnhancedFeatures(),
            'admin' => $this->checkAdminSystem(),
            'performance' => $this->checkPerformance()
        ];
        
        // Save report to file
        $reportFile = __DIR__ . '/../logs/system_status_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->log("Status report saved to: $reportFile");
        
        return $report;
    }
    
    /**
     * Auto-fix common issues
     */
    public function autoFixIssues() {
        $this->log("=== AUTO-FIXING COMMON ISSUES ===");
        
        // Check if .htaccess exists
        if (!file_exists(__DIR__ . '/../.htaccess')) {
            $htaccess = "
# APS Dream Home - Enhanced .htaccess
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# URL rewriting
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
    ExpiresByType image/gif \"access plus 1 month\"
    ExpiresByType image/ico \"access plus 1 month\"
</IfModule>

# PHP settings
<IfModule mod_php7.c>
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 64M
    php_value post_max_size 64M
</IfModule>
";
            file_put_contents(__DIR__ . '/../.htaccess', $htaccess);
            $this->log("Created .htaccess file for better performance");
        }
        
        // Check if logs directory exists
        $logsDir = __DIR__ . '/../logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
            $this->log("Created logs directory");
        }
        
        // Check error reporting
        if (ini_get('display_errors')) {
            $this->log("WARNING: display_errors is ON - should be OFF in production", 'WARNING');
        }
        
        $this->log("Auto-fix completed");
    }
    
    /**
     * Run continuous monitoring
     */
    public function runContinuousMonitoring() {
        $this->log("=== STARTING CONTINUOUS MONITORING ===");
        
        $cycle = 0;
        while (true) {
            $cycle++;
            $this->log("\n--- MONITORING CYCLE $cycle ---");
            
            // Generate status report
            $report = $this->generateStatusReport();
            
            // Auto-fix issues
            $this->autoFixIssues();
            
            // Check if any critical issues
            $criticalIssues = false;
            if (!$report['database']) $criticalIssues = true;
            if (in_array('FAILED', $report['pages'])) $criticalIssues = true;
            if (in_array('FAILED', $report['admin'])) $criticalIssues = true;
            
            if ($criticalIssues) {
                $this->log("CRITICAL ISSUES DETECTED - Immediate attention required!", 'ERROR');
                // Could send email/notification here
            }
            
            // Wait for next cycle (5 minutes)
            $this->log("Waiting 5 minutes for next monitoring cycle...");
            sleep(300); // 5 minutes
        }
    }
    
    /**
     * Run single monitoring cycle
     */
    public function runSingleCheck() {
        $this->log("=== RUNNING SINGLE MONITORING CYCLE ===");
        
        $report = $this->generateStatusReport();
        $this->autoFixIssues();
        
        return $report;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $monitor = new AutoSystemMonitor();
    
    if (isset($argv[1]) && $argv[1] === 'continuous') {
        $monitor->runContinuousMonitoring();
    } else {
        $report = $monitor->runSingleCheck();
        echo "\n=== MONITORING SUMMARY ===\n";
        echo "Database: " . ($report['database'] ? 'OK' : 'FAILED') . "\n";
        echo "Pages: " . (in_array('FAILED', $report['pages']) ? 'FAILED' : 'OK') . "\n";
        echo "Features: " . (in_array('NOT FOUND', $report['features']) ? 'FAILED' : 'OK') . "\n";
        echo "Admin: " . (in_array('FAILED', $report['admin']) ? 'FAILED' : 'OK') . "\n";
        echo "Performance: " . (in_array(['SLOW', 'HIGH'], $report['performance']) ? 'WARNING' : 'OK') . "\n";
    }
} else {
    // Web access - show current status
    $monitor = new AutoSystemMonitor();
    $report = $monitor->runSingleCheck();
    
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT);
}
?>
