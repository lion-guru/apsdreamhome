<?php
/**
 * APS Dream Home Performance Monitoring System
 * Advanced performance tracking, profiling, and optimization
 */
class PerformanceMonitor {
    // Monitoring configuration
    private const CONFIG = [
        'log_path' => __DIR__ . '/../logs/performance/',
        'sample_rate' => 0.1,  // 10% sampling
        'slow_query_threshold' => 0.5,  // seconds
        'memory_warning_threshold' => 128 * 1024 * 1024,  // 128 MB
        'cpu_warning_threshold' => 80  // percent
    ];

    // Performance metrics
    private $metrics = [
        'start_time' => 0,
        'end_time' => 0,
        'memory_peak' => 0,
        'cpu_usage' => 0
    ];

    // Database query tracking
    private $queries = [];

    // Singleton instance
    private static $instance = null;

    private function __construct() {
        $this->createLogDirectory();
    }

    /**
     * Get singleton instance
     * @return PerformanceMonitor
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start performance monitoring
     */
    public function start() {
        $this->metrics['start_time'] = microtime(true);
        $this->startResourceMonitoring();
    }

    /**
     * End performance monitoring and log results
     */
    public function end() {
        $this->metrics['end_time'] = microtime(true);
        $this->stopResourceMonitoring();
        $this->logPerformanceMetrics();
    }

    /**
     * Track database query performance
     * @param string $query SQL query
     * @param float $execution_time Query execution time
     */
    public function trackQuery($query, $execution_time) {
        // Anonymize sensitive data in query
        $anonymized_query = $this->anonymizeQuery($query);

        $this->queries[] = [
            'query' => $anonymized_query,
            'execution_time' => $execution_time,
            'is_slow' => $execution_time > self::CONFIG['slow_query_threshold']
        ];

        // Log slow queries
        if ($execution_time > self::CONFIG['slow_query_threshold']) {
            $this->logSlowQuery($anonymized_query, $execution_time);
        }
    }

    /**
     * Start system resource monitoring
     */
    private function startResourceMonitoring() {
        // Track memory peak and initial CPU usage
        $this->metrics['memory_peak'] = memory_get_peak_usage(true);
        $this->metrics['cpu_usage'] = $this->getCpuUsage();
    }

    /**
     * Stop system resource monitoring
     */
    private function stopResourceMonitoring() {
        $this->metrics['memory_peak'] = max(
            $this->metrics['memory_peak'], 
            memory_get_peak_usage(true)
        );
        $this->metrics['cpu_usage'] = $this->getCpuUsage();
    }

    /**
     * Get current CPU usage
     * @return float CPU usage percentage
     */
    private function getCpuUsage() {
        // Cross-platform CPU usage detection
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->getWindowsCpuUsage();
        } else {
            return $this->getUnixCpuUsage();
        }
    }

    /**
     * Get CPU usage on Windows
     * @return float CPU usage percentage
     */
    private function getWindowsCpuUsage() {
        // Windows-specific CPU usage (requires WMI)
        try {
            $wmi = new COM('WbemScripting.SWbemLocator');
            $server = $wmi->ConnectServer('.');
            $cpu_load = $server->ExecQuery('SELECT LoadPercentage FROM Win32_Processor');
            
            $total = 0;
            $count = 0;
            foreach ($cpu_load as $cpu) {
                $total += $cpu->LoadPercentage;
                $count++;
            }
            
            return $count > 0 ? ($total / $count) : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get CPU usage on Unix-like systems
     * @return float CPU usage percentage
     */
    private function getUnixCpuUsage() {
        // Unix-like CPU usage via /proc filesystem
        $load = sys_getloadavg();
        return ($load[0] / $this->getCpuCoreCount()) * 100;
    }

    /**
     * Get number of CPU cores
     * @return int Number of CPU cores
     */
    private function getCpuCoreCount() {
        return PHP_OS_FAMILY === 'Windows' 
            ? getenv('NUMBER_OF_PROCESSORS') 
            : shell_exec('nproc');
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics() {
        // Probabilistic logging to reduce overhead
        if (mt_rand(1, 100) / 100 > self::CONFIG['sample_rate']) {
            return;
        }

        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => $this->metrics['end_time'] - $this->metrics['start_time'],
            'memory_peak' => $this->metrics['memory_peak'],
            'cpu_usage' => $this->metrics['cpu_usage'],
            'query_count' => count($this->queries),
            'slow_queries' => array_filter($this->queries, function($q) { 
                return $q['is_slow']; 
            })
        ];

        // Check for performance warnings
        $warnings = $this->checkPerformanceWarnings($log_data);
        if (!empty($warnings)) {
            $log_data['warnings'] = $warnings;
        }

        // Write to performance log
        $log_file = self::CONFIG['log_path'] . 'performance_' . date('Y-m-d') . '.json';
        file_put_contents(
            $log_file, 
            json_encode($log_data, JSON_PRETTY_PRINT) . PHP_EOL, 
            FILE_APPEND
        );
    }

    /**
     * Check for performance warnings
     * @param array $metrics Performance metrics
     * @return array Performance warnings
     */
    private function checkPerformanceWarnings($metrics) {
        $warnings = [];

        // Memory usage warning
        if ($metrics['memory_peak'] > self::CONFIG['memory_warning_threshold']) {
            $warnings[] = [
                'type' => 'memory_high',
                'message' => 'Memory usage exceeds threshold',
                'value' => $metrics['memory_peak']
            ];
        }

        // CPU usage warning
        if ($metrics['cpu_usage'] > self::CONFIG['cpu_warning_threshold']) {
            $warnings[] = [
                'type' => 'cpu_high',
                'message' => 'CPU usage exceeds threshold',
                'value' => $metrics['cpu_usage']
            ];
        }

        // Slow query warning
        if (!empty($metrics['slow_queries'])) {
            $warnings[] = [
                'type' => 'slow_queries',
                'message' => 'Detected slow database queries',
                'count' => count($metrics['slow_queries'])
            ];
        }

        return $warnings;
    }

    /**
     * Log slow database queries
     * @param string $query Anonymized query
     * @param float $execution_time Query execution time
     */
    private function logSlowQuery($query, $execution_time) {
        $log_file = self::CONFIG['log_path'] . 'slow_queries_' . date('Y-m-d') . '.json';
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'query' => $query,
            'execution_time' => $execution_time
        ];

        file_put_contents(
            $log_file, 
            json_encode($log_data, JSON_PRETTY_PRINT) . PHP_EOL, 
            FILE_APPEND
        );
    }

    /**
     * Anonymize sensitive data in SQL query
     * @param string $query Original SQL query
     * @return string Anonymized query
     */
    private function anonymizeQuery($query) {
        // Remove sensitive values
        $anonymized = preg_replace([
            '/VALUES\s*\(.*\)/i',
            '/SET\s+.*WHERE/i',
            '/WHERE\s+.*$/i'
        ], ['VALUES (***)', 'SET *** WHERE', 'WHERE ***'], $query);

        // Truncate very long queries
        return strlen($anonymized) > 1000 
            ? substr($anonymized, 0, 1000) . '...' 
            : $anonymized;
    }

    /**
     * Create log directory if it doesn't exist
     */
    private function createLogDirectory() {
        if (!is_dir(self::CONFIG['log_path'])) {
            mkdir(self::CONFIG['log_path'], 0755, true);
        }
    }

    /**
     * Generate performance report
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Performance report
     */
    public function generateReport($start_date = null, $end_date = null) {
        $start_date = $start_date ?? date('Y-m-d', strtotime('-7 days'));
        $end_date = $end_date ?? date('Y-m-d');

        $reports = [];
        $log_files = glob(
            self::CONFIG['log_path'] . 'performance_' . 
            '{' . $start_date . ',' . $end_date . '}.json', 
            GLOB_BRACE
        );

        foreach ($log_files as $file) {
            $log_contents = file($file, FILE_IGNORE_NEW_LINES);
            foreach ($log_contents as $line) {
                $reports[] = json_decode($line, true);
            }
        }

        return $this->analyzePerformanceReports($reports);
    }

    /**
     * Analyze performance reports
     * @param array $reports Performance log reports
     * @return array Analyzed performance metrics
     */
    private function analyzePerformanceReports($reports) {
        $analysis = [
            'total_requests' => count($reports),
            'avg_execution_time' => 0,
            'max_execution_time' => 0,
            'avg_memory_usage' => 0,
            'max_memory_usage' => 0,
            'avg_cpu_usage' => 0,
            'total_slow_queries' => 0
        ];

        if (empty($reports)) return $analysis;

        foreach ($reports as $report) {
            $analysis['avg_execution_time'] += $report['execution_time'];
            $analysis['max_execution_time'] = max(
                $analysis['max_execution_time'], 
                $report['execution_time']
            );

            $analysis['avg_memory_usage'] += $report['memory_peak'];
            $analysis['max_memory_usage'] = max(
                $analysis['max_memory_usage'], 
                $report['memory_peak']
            );

            $analysis['avg_cpu_usage'] += $report['cpu_usage'];
            $analysis['total_slow_queries'] += count($report['slow_queries'] ?? []);
        }

        $analysis['avg_execution_time'] /= count($reports);
        $analysis['avg_memory_usage'] /= count($reports);
        $analysis['avg_cpu_usage'] /= count($reports);

        return $analysis;
    }
}

// Global performance tracking helper functions
function start_performance_monitoring() {
    PerformanceMonitor::getInstance()->start();
}

function end_performance_monitoring() {
    PerformanceMonitor::getInstance()->end();
}

function track_database_query($query, $execution_time) {
    PerformanceMonitor::getInstance()->trackQuery($query, $execution_time);
}

// Automatic performance monitoring setup
register_shutdown_function('end_performance_monitoring');
start_performance_monitoring();
