<?php
/**
 * Auto Monitor Scheduler
 * 
 * Scheduled task runner for automatic system monitoring
 * This script can be run via cron job or Windows Task Scheduler
 */

// Set execution time limit
set_time_limit(0);

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

class AutoMonitorScheduler {
    private $logFile;
    private $monitorScript;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/scheduler.log';
        $this->monitorScript = __DIR__ . '/auto_system_monitor.php';
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
     * Log scheduler activities
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry; // Also output to console
    }
    
    /**
     * Run system monitoring check
     */
    public function runMonitoringCheck() {
        $this->log("=== RUNNING SCHEDULED MONITORING CHECK ===");
        
        try {
            // Execute the monitor script
            $command = "php \"" . $this->monitorScript . "\"";
            $output = shell_exec($command);
            $returnCode = 0;
            
            if ($returnCode === 0) {
                $this->log("Monitoring check completed successfully");
                $this->log("Output: " . trim($output));
            } else {
                $this->log("Monitoring check failed with return code: $returnCode", 'ERROR');
                $this->log("Error output: " . trim($output), 'ERROR');
            }
            
        } catch (Exception $e) {
            $this->log("Exception during monitoring check: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Check if monitoring is already running
     */
    public function isMonitoringRunning() {
        $lockFile = __DIR__ . '/../logs/monitor.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = file_get_contents($lockFile);
            $currentTime = time();
            
            // If lock is older than 10 minutes, remove it
            if ($currentTime - $lockTime > 600) {
                unlink($lockFile);
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Create monitoring lock
     */
    public function createMonitoringLock() {
        $lockFile = __DIR__ . '/../logs/monitor.lock';
        file_put_contents($lockFile, time());
    }
    
    /**
     * Remove monitoring lock
     */
    public function removeMonitoringLock() {
        $lockFile = __DIR__ . '/../logs/monitor.lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }
    
    /**
     * Run scheduled monitoring with lock protection
     */
    public function runScheduledMonitoring() {
        $this->log("=== SCHEDULED MONITORING STARTED ===");
        
        // Check if monitoring is already running
        if ($this->isMonitoringRunning()) {
            $this->log("Monitoring is already running, skipping this cycle");
            return;
        }
        
        // Create lock
        $this->createMonitoringLock();
        
        try {
            // Run the monitoring check
            $this->runMonitoringCheck();
            
            // Additional checks
            $this->checkSystemResources();
            $this->checkDiskSpace();
            $this->checkMemoryUsage();
            
            $this->log("Scheduled monitoring completed successfully");
            
        } catch (Exception $e) {
            $this->log("Exception during scheduled monitoring: " . $e->getMessage(), 'ERROR');
        } finally {
            // Remove lock
            $this->removeMonitoringLock();
        }
    }
    
    /**
     * Check system resources
     */
    private function checkSystemResources() {
        $this->log("Checking system resources...");
        
        // CPU usage (simplified)
        $load = sys_getloadavg();
        if ($load) {
            $this->log("System load: " . $load[0]);
        }
        
        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryUsageMB = $memoryUsage / 1024 / 1024;
        $this->log("Memory usage: " . number_format($memoryUsageMB, 2) . " MB");
        
        // Peak memory
        $peakMemory = memory_get_peak_usage(true);
        $peakMemoryMB = $peakMemory / 1024 / 1024;
        $this->log("Peak memory: " . number_format($peakMemoryMB, 2) . " MB");
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace() {
        $this->log("Checking disk space...");
        
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        
        $totalGB = $totalSpace / 1024 / 1024 / 1024;
        $freeGB = $freeSpace / 1024 / 1024 / 1024;
        $usedGB = $usedSpace / 1024 / 1024 / 1024;
        
        $usagePercent = ($usedGB / $totalGB) * 100;
        
        $this->log("Disk space: " . number_format($usedGB, 2) . " GB used / " . number_format($totalGB, 2) . " GB total (" . number_format($usagePercent, 1) . "%)");
        
        if ($usagePercent > 90) {
            $this->log("WARNING: Disk usage is above 90%", 'WARNING');
        }
    }
    
    /**
     * Check memory usage
     */
    private function checkMemoryUsage() {
        $this->log("Checking memory usage...");
        
        // Get memory info from /proc/meminfo (Linux) or use alternative
        if (file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $matches);
            $totalMemory = $matches[1] / 1024; // KB to MB
            
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $matches);
            $availableMemory = $matches[1] / 1024; // KB to MB
            
            $usedMemory = $totalMemory - $availableMemory;
            $usagePercent = ($usedMemory / $totalMemory) * 100;
            
            $this->log("System memory: " . number_format($usedMemory, 0) . " MB used / " . number_format($totalMemory, 0) . " MB total (" . number_format($usagePercent, 1) . "%)");
            
            if ($usagePercent > 90) {
                $this->log("WARNING: Memory usage is above 90%", 'WARNING');
            }
        }
    }
    
    /**
     * Create cron job setup instructions
     */
    public function generateCronSetup() {
        $scriptPath = __DIR__ . '/auto_monitor_scheduler.php';
        $logPath = __DIR__ . '/../logs/scheduler.log';
        
        $cronCommand = "*/5 * * * * php \"$scriptPath\" >> \"$logPath\" 2>&1";
        
        echo "=== CRON JOB SETUP INSTRUCTIONS ===\n";
        echo "To set up automatic monitoring every 5 minutes, add the following line to your crontab:\n\n";
        echo "$cronCommand\n\n";
        echo "To edit crontab: crontab -e\n";
        echo "To view current crontab: crontab -l\n\n";
        
        echo "=== WINDOWS TASK SCHEDULER SETUP ===\n";
        echo "1. Open Task Scheduler\n";
        echo "2. Create Basic Task\n";
        echo "3. Set trigger: Daily, repeat every 5 minutes\n";
        echo "4. Action: Start a program\n";
        echo "5. Program/script: php \"$scriptPath\"\n";
        echo "6. Start in: " . dirname($scriptPath) . "\n";
        echo "7. Conditions: Run whether user is logged on or not\n\n";
    }
    
    /**
     * Run continuous monitoring (for testing)
     */
    public function runContinuousMonitoring() {
        $this->log("=== STARTING CONTINUOUS MONITORING ===");
        
        $cycle = 0;
        while (true) {
            $cycle++;
            $this->log("\n--- MONITORING CYCLE $cycle ---");
            
            $this->runScheduledMonitoring();
            
            // Wait for next cycle (5 minutes)
            $this->log("Waiting 5 minutes for next monitoring cycle...");
            sleep(300); // 5 minutes
        }
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $scheduler = new AutoMonitorScheduler();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'check':
                $scheduler->runScheduledMonitoring();
                break;
                
            case 'continuous':
                $scheduler->runContinuousMonitoring();
                break;
                
            case 'setup':
                $scheduler->generateCronSetup();
                break;
                
            default:
                echo "Usage:\n";
                echo "  php auto_monitor_scheduler.php check     - Run single monitoring check\n";
                echo "  php auto_monitor_scheduler.php continuous - Run continuous monitoring\n";
                echo "  php auto_monitor_scheduler.php setup     - Show setup instructions\n";
        }
    } else {
        echo "Auto Monitor Scheduler\n";
        echo "Usage: php auto_monitor_scheduler.php [check|continuous|setup]\n";
    }
} else {
    // Web access - show current status
    $scheduler = new AutoMonitorScheduler();
    $scheduler->generateCronSetup();
}
?>
