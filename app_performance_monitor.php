<?php
/**
 * Comprehensive Application Performance and Security Monitoring Tool
 * Provides real-time insights into application health, performance, and potential security risks
 */
class AppPerformanceMonitor {
    private $startTime;
    private $monitorConfig;
    private $performanceLog;
    private $securityLog;

    /**
     * Constructor initializes monitoring configurations
     */
    public function __construct() {
        $this->startTime = microtime(true);
        $this->performanceLog = __DIR__ . '/logs/performance_' . date('Y-m-d') . '.log';
        $this->securityLog = __DIR__ . '/logs/security_' . date('Y-m-d') . '.log';
        
        $this->monitorConfig = [
            'memory_threshold' => 128 * 1024 * 1024,  // 128 MB
            'execution_time_threshold' => 5,  // 5 seconds
            'database_query_threshold' => 0.5,  // 0.5 seconds
            'security_checks' => [
                'check_session_hijacking' => true,
                'check_csrf' => true,
                'check_xss' => true,
                'check_sql_injection' => true
            ]
        ];

        $this->ensureLogDirectories();
    }

    /**
     * Ensure log directories exist
     */
    private function ensureLogDirectories() {
        $logDirs = [
            dirname($this->performanceLog),
            dirname($this->securityLog)
        ];

        foreach ($logDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Monitor script performance
     * @return array Performance metrics
     */
    public function monitorPerformance() {
        $metrics = [
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'execution_time' => microtime(true) - $this->startTime
        ];

        // Check performance thresholds
        $performanceIssues = [];
        if ($metrics['memory_usage'] > $this->monitorConfig['memory_threshold']) {
            $performanceIssues[] = 'High memory consumption';
        }

        if ($metrics['execution_time'] > $this->monitorConfig['execution_time_threshold']) {
            $performanceIssues[] = 'Slow script execution';
        }

        $this->logPerformance($metrics, $performanceIssues);

        return $metrics;
    }

    /**
     * Log performance metrics
     * @param array $metrics Performance metrics
     * @param array $issues Performance issues
     */
    private function logPerformance($metrics, $issues) {
        $logEntry = sprintf(
            "[%s] Performance Metrics:\n" .
            "Memory Usage: %s\n" .
            "Peak Memory: %s\n" .
            "Execution Time: %.4f seconds\n" .
            "Issues: %s\n\n",
            date('Y-m-d H:i:s'),
            $this->formatBytes($metrics['memory_usage']),
            $this->formatBytes($metrics['memory_peak']),
            $metrics['execution_time'],
            empty($issues) ? 'None' : implode(', ', $issues)
        );

        file_put_contents($this->performanceLog, $logEntry, FILE_APPEND);
    }

    /**
     * Perform security checks
     * @return array Security check results
     */
    public function performSecurityChecks() {
        $securityResults = [
            'session_hijacking' => $this->checkSessionHijacking(),
            'csrf_protection' => $this->checkCSRFProtection(),
            'xss_protection' => $this->checkXSSProtection(),
            'sql_injection' => $this->checkSQLInjection()
        ];

        $this->logSecurityChecks($securityResults);

        return $securityResults;
    }

    /**
     * Check for potential session hijacking
     * @return bool Security status
     */
    private function checkSessionHijacking() {
        if (!$this->monitorConfig['security_checks']['check_session_hijacking']) {
            return true;
        }

        $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
        $storedIP = $_SESSION['user_ip'] ?? '';

        return $currentIP === $storedIP;
    }

    /**
     * Check CSRF protection
     * @return bool Security status
     */
    private function checkCSRFProtection() {
        if (!$this->monitorConfig['security_checks']['check_csrf']) {
            return true;
        }

        // Implement CSRF token validation logic
        $csrfTokenValid = isset($_POST['csrf_token']) && 
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);

        return $csrfTokenValid;
    }

    /**
     * Check XSS protection
     * @return bool Security status
     */
    private function checkXSSProtection() {
        if (!$this->monitorConfig['security_checks']['check_xss']) {
            return true;
        }

        $xssDetected = false;
        $sensitiveInputs = ['username', 'email', 'comment'];

        foreach ($sensitiveInputs as $input) {
            if (isset($_POST[$input]) && $this->containsXSS($_POST[$input])) {
                $xssDetected = true;
                break;
            }
        }

        return !$xssDetected;
    }

    /**
     * Detect potential XSS in input
     * @param string $input Input to check
     * @return bool XSS detection result
     */
    private function containsXSS($input) {
        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/on\w+\s*=\s*[\'"].*?[\'"]/i',
            '/javascript:/i',
            '/\bdata:text\/html/i'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for SQL injection attempts
     * @return bool Security status
     */
    private function checkSQLInjection() {
        if (!$this->monitorConfig['security_checks']['check_sql_injection']) {
            return true;
        }

        $sqlInjectionPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER)\b/i',
            '/\/\*.*?\*\//s',
            '/--\s*.*$/m',
            '/\b(AND|OR)\s+1\s*=\s*1/i'
        ];

        foreach ($_POST as $key => $value) {
            foreach ($sqlInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Log security check results
     * @param array $results Security check results
     */
    private function logSecurityChecks($results) {
        $logEntry = sprintf(
            "[%s] Security Checks:\n" .
            "Session Hijacking: %s\n" .
            "CSRF Protection: %s\n" .
            "XSS Protection: %s\n" .
            "SQL Injection: %s\n\n",
            date('Y-m-d H:i:s'),
            $results['session_hijacking'] ? 'PASS' : 'FAIL',
            $results['csrf_protection'] ? 'PASS' : 'FAIL',
            $results['xss_protection'] ? 'PASS' : 'FAIL',
            $results['sql_injection'] ? 'PASS' : 'FAIL'
        );

        file_put_contents($this->securityLog, $logEntry, FILE_APPEND);
    }

    /**
     * Format bytes to human-readable format
     * @param int $bytes Number of bytes
     * @return string Formatted byte size
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 4) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Generate comprehensive monitoring report
     * @return array Monitoring report
     */
    public function generateReport() {
        $performanceMetrics = $this->monitorPerformance();
        $securityResults = $this->performSecurityChecks();

        return [
            'performance' => $performanceMetrics,
            'security' => $securityResults,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate HTML report
     * @param array $report Monitoring report
     * @return string HTML report
     */
    public function generateHTMLReport($report) {
        $html = "<html><body>";
        $html .= "<h1>Application Performance and Security Report</h1>";
        $html .= "<p>Timestamp: {$report['timestamp']}</p>";

        // Performance Section
        $html .= "<h2>Performance Metrics</h2>";
        $html .= "<ul>";
        $html .= "<li>Memory Usage: " . $this->formatBytes($report['performance']['memory_usage']) . "</li>";
        $html .= "<li>Peak Memory: " . $this->formatBytes($report['performance']['memory_peak']) . "</li>";
        $html .= "<li>Execution Time: {$report['performance']['execution_time']} seconds</li>";
        $html .= "</ul>";

        // Security Section
        $html .= "<h2>Security Checks</h2>";
        $html .= "<table border='1'>";
        $html .= "<tr><th>Check</th><th>Status</th></tr>";
        foreach ($report['security'] as $check => $status) {
            $statusText = $status ? 'PASS' : 'FAIL';
            $color = $status ? 'green' : 'red';
            $html .= "<tr><td>" . ucwords(str_replace('_', ' ', $check)) . "</td><td style='color:$color'>$statusText</td></tr>";
        }
        $html .= "</table>";

        $html .= "</body></html>";

        return $html;
    }
}

// Execute monitoring if run directly
if (php_sapi_name() === 'cli') {
    session_start();
    $monitor = new AppPerformanceMonitor();
    
    try {
        $report = $monitor->generateReport();
        
        // Generate and save HTML report
        $htmlReport = $monitor->generateHTMLReport($report);
        file_put_contents(__DIR__ . '/logs/app_monitor_report.html', $htmlReport);
        
        echo "Application Performance Monitoring Completed.\n";
        echo "Report saved to: " . __DIR__ . "/logs/app_monitor_report.html\n";
    } catch (Exception $e) {
        echo "Monitoring failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for report
    session_start();
    try {
        $monitor = new AppPerformanceMonitor();
        $report = $monitor->generateReport();
        echo $monitor->generateHTMLReport($report);
    } catch (Exception $e) {
        echo "Monitoring failed: " . $e->getMessage();
    }
}
