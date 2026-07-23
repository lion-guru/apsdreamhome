<?php
/**
 * Application Monitor for APS Dream Home
 * Provides comprehensive monitoring, logging, and alerting
 */

class Monitor {
    private static $logFile;
    private static $metrics = [];
    private static $startTime;
    
    /**
     * Initialize monitor
     */
    public static function init() {
        self::$startTime = microtime(true);
        self::$logFile = __DIR__ . '/../../storage/logs/app_monitor.log';
        
        // Ensure log directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
    }
    
    /**
     * Log application event
     */
    public static function log($level, $message, $context = []) {
        if (!self::$logFile) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr\n";
        
        // Write to log file
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
        
        // Also write to PHP error log for system-level visibility
        error_log("APS Dream Home: $message");
        
        // Critical alerts get special handling
        if ($level === 'CRITICAL' || $level === 'ERROR') {
            self::sendAlert($level, $message, $context);
        }
    }
    
    /**
     * Track performance metric
     */
    public static function trackMetric($name, $value, $tags = []) {
        self::$metrics[$name] = [
            'value' => $value,
            'timestamp' => time(),
            'tags' => $tags
        ];
        
        // Log significant metrics
        if ($name === 'response_time' && $value > 3000) { // > 3 seconds
            self::log('WARNING', "Slow response time: {$value}ms", ['metric' => $name]);
        }
        
        if ($name === 'memory_usage' && $value > 80) { // > 80%
            self::log('WARNING', "High memory usage: {$value}%", ['metric' => $name]);
        }
    }
    
    /**
     * Get all metrics
     */
    public static function getMetrics() {
        return self::$metrics;
    }
    
    /**
     * Check system health
     */
    public static function healthCheck() {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Check database connection
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1")->fetch();
            $health['checks']['database'] = 'pass';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'fail';
            $health['status'] = 'unhealthy';
            self::log('CRITICAL', 'Database health check failed', ['error' => $e->getMessage()]);
        }
        
        // Check cache directory
        $cacheDir = __DIR__ . '/../../storage/cache';
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $health['checks']['cache'] = 'pass';
        } else {
            $health['checks']['cache'] = 'fail';
            $health['status'] = 'degraded';
            self::log('ERROR', 'Cache directory not writable', ['directory' => $cacheDir]);
        }
        
        // Check upload directory
        $uploadDir = __DIR__ . '/../../public/uploads';
        if (is_dir($uploadDir) && is_writable($uploadDir)) {
            $health['checks']['uploads'] = 'pass';
        } else {
            $health['checks']['uploads'] = 'fail';
            $health['status'] = 'degraded';
            self::log('ERROR', 'Upload directory not writable', ['directory' => $uploadDir]);
        }
        
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryPercent = ($memoryUsage / self::convertToBytes($memoryLimit)) * 100;
        $health['checks']['memory'] = [
            'usage' => self::formatBytes($memoryUsage),
            'limit' => $memoryLimit,
            'percent' => round($memoryPercent, 2),
            'status' => $memoryPercent < 80 ? 'pass' : 'warning'
        ];
        
        if ($memoryPercent > 80) {
            $health['status'] = 'degraded';
        }
        
        // Check disk space
        $diskFree = disk_free_space(__DIR__ . '/../../');
        $diskTotal = disk_total_space(__DIR__ . '/../../');
        $diskPercent = (($diskTotal - $diskFree) / $diskTotal) * 100;
        $health['checks']['disk'] = [
            'free' => self::formatBytes($diskFree),
            'total' => self::formatBytes($diskTotal),
            'used_percent' => round($diskPercent, 2),
            'status' => $diskPercent < 90 ? 'pass' : 'warning'
        ];
        
        if ($diskPercent > 90) {
            $health['status'] = 'critical';
            self::log('CRITICAL', 'Disk space critically low', ['free' => self::formatBytes($diskFree)]);
        }
        
        // Log health check results
        self::log('INFO', 'Health check completed', ['health' => $health]);
        
        return $health;
    }
    
    /**
     * Monitor request performance
     */
    public static function monitorRequest() {
        $executionTime = (microtime(true) - self::$startTime) * 1000;
        $memoryUsage = (memory_get_usage(true) / 1024 / 1024); // MB
        $peakMemory = (memory_get_peak_usage(true) / 1024 / 1024); // MB
        
        self::trackMetric('response_time', $executionTime);
        self::trackMetric('memory_usage', $memoryUsage);
        self::trackMetric('peak_memory', $peakMemory);
        
        // Log slow requests
        if ($executionTime > 3000) {
            self::log('WARNING', 'Slow request detected', [
                'response_time' => $executionTime . 'ms',
                'memory_usage' => $memoryUsage . 'MB',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ]);
        }
        
        return [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'peak_memory' => $peakMemory
        ];
    }
    
    /**
     * Monitor database queries
     */
    public static function monitorDatabaseQuery($query, $executionTime, $rowsAffected) {
        if ($executionTime > 1.0) { // > 1 second
            self::log('WARNING', 'Slow database query', [
                'query' => substr($query, 0, 200) . '...',
                'execution_time' => $executionTime . 's',
                'rows_affected' => $rowsAffected
            ]);
        }
        
        self::trackMetric('db_query_time', $executionTime);
        self::trackMetric('db_rows_affected', $rowsAffected);
    }
    
    /**
     * Send alert for critical issues
     */
    private static function sendAlert($level, $message, $context = []) {
        $alertMessage = sprintf(
            "[%s] APS Dream Home Alert - %s: %s",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        
        if (!empty($context)) {
            $alertMessage .= " | Context: " . json_encode($context);
        }
        
        // Send email alert (configure SMTP in settings)
        if (getenv('ALERT_EMAIL_ENABLED') === 'true') {
            $to = getenv('ALERT_EMAIL');
            $subject = "APS Dream Home $level Alert";
            $headers = "From: noreply@apsdreamhome.com";
            
            mail($to, $subject, $alertMessage, $headers);
        }
        
        // Log to separate alert file
        $alertLog = __DIR__ . '/../../storage/logs/alerts.log';
        file_put_contents($alertLog, $alertMessage . "\n", FILE_APPEND);
    }
    
    /**
     * Get performance summary
     */
    public static function getPerformanceSummary() {
        $logFile = self::$logFile;
        if (!file_exists($logFile)) {
            return ['error' => 'No log file found'];
        }
        
        $logs = file_get_contents($logFile);
        $logLines = explode("\n", $logs);
        
        $summary = [
            'total_logs' => count($logLines),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'critical' => 0,
            'recent_errors' => []
        ];
        
        foreach (array_reverse($logLines) as $line) {
            if (strpos($line, '[ERROR]') !== false) {
                $summary['errors']++;
                if (count($summary['recent_errors']) < 10) {
                    $summary['recent_errors'][] = $line;
                }
            } elseif (strpos($line, '[WARNING]') !== false) {
                $summary['warnings']++;
            } elseif (strpos($line, '[INFO]') !== false) {
                $summary['info']++;
            } elseif (strpos($line, '[CRITICAL]') !== false) {
                $summary['critical']++;
            }
        }
        
        return $summary;
    }
    
    /**
     * Helper function to convert bytes to human-readable format
     */
    private static function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Helper function to convert memory limit to bytes
     */
    private static function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}

// Auto-initialize monitor
Monitor::init();
