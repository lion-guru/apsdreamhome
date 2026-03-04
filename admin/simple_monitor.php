<?php
/**
 * Simple System Monitor
 * 
 * Lightweight monitoring script without complex dependencies
 */

// Set execution time limit
set_time_limit(0);

class SimpleSystemMonitor {
    private $logFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/simple_monitor.log';
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
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($url, 1, $context);
            
            if ($headers && isset($headers[0]) && strpos($headers[0], '200') !== false) {
                $this->log("$name: OK");
                $results[$name] = 'OK';
            } else {
                $this->log("$name: FAILED", 'ERROR');
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
        
        // Check homepage content
        $homepage = @file_get_contents('http://localhost/apsdreamhome/');
        if ($homepage) {
            // Check Bootstrap CSS
            if (strpos($homepage, 'bootstrap') !== false) {
                $this->log("Bootstrap: OK");
                $features['bootstrap'] = 'OK';
            } else {
                $this->log("Bootstrap: NOT FOUND", 'ERROR');
                $features['bootstrap'] = 'NOT FOUND';
            }
            
            // Check animations
            if (strpos($homepage, 'animation') !== false || strpos($homepage, 'transition') !== false) {
                $this->log("Animations: OK");
                $features['animations'] = 'OK';
            } else {
                $this->log("Animations: NOT FOUND", 'WARNING');
                $features['animations'] = 'NOT FOUND';
            }
            
            // Check Font Awesome
            if (strpos($homepage, 'font-awesome') !== false || strpos($homepage, 'fa-') !== false) {
                $this->log("Font Awesome: OK");
                $features['fontawesome'] = 'OK';
            } else {
                $this->log("Font Awesome: NOT FOUND", 'WARNING');
                $features['fontawesome'] = 'NOT FOUND';
            }
        } else {
            $this->log("Homepage: NOT ACCESSIBLE", 'ERROR');
            $features['homepage'] = 'NOT ACCESSIBLE';
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
            $adminDashboard = @file_get_contents('http://localhost/apsdreamhome/admin/dashboard.php');
            if ($adminDashboard && strlen($adminDashboard) > 1000) {
                $this->log("Admin Dashboard: OK");
                $adminChecks['dashboard'] = 'OK';
            } else {
                $this->log("Admin Dashboard: FAILED", 'ERROR');
                $adminChecks['dashboard'] = 'FAILED';
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
        $homepage = @file_get_contents('http://localhost/apsdreamhome/');
        $end = microtime(true);
        $loadTime = ($end - $start) * 1000; // Convert to milliseconds
        
        if ($loadTime < 5000) { // Less than 5 seconds
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
     * Run single monitoring cycle
     */
    public function runSingleCheck() {
        $this->log("=== RUNNING SINGLE MONITORING CYCLE ===");
        
        $report = $this->generateStatusReport();
        
        return $report;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $monitor = new SimpleSystemMonitor();
    $report = $monitor->runSingleCheck();
    
    echo "\n=== MONITORING SUMMARY ===\n";
    echo "Pages: " . (in_array('FAILED', $report['pages']) ? 'FAILED' : 'OK') . "\n";
    echo "Features: " . (in_array('NOT FOUND', $report['features']) ? 'FAILED' : 'OK') . "\n";
    echo "Admin: " . (in_array('FAILED', $report['admin']) ? 'FAILED' : 'OK') . "\n";
    echo "Performance: " . (in_array(['SLOW', 'HIGH'], $report['performance']) ? 'WARNING' : 'OK') . "\n";
} else {
    // Web access - show current status
    $monitor = new SimpleSystemMonitor();
    $report = $monitor->runSingleCheck();
    
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT);
}
?>
