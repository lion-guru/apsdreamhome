<?php
/**
 * Advanced Performance Profiling and Monitoring System
 * Provides comprehensive performance tracking, bottleneck detection, 
 * and resource utilization insights
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';

class PerformanceProfiler {
    // Profiling modes
    public const MODE_BASIC = 'basic';
    public const MODE_DETAILED = 'detailed';
    public const MODE_EXTREME = 'extreme';

    // Performance metrics storage
    private $metrics = [];
    private $profilingMode;
    private $logger;
    private $config;

    // Resource tracking
    private $startMemory;
    private $startTime;
    private $trackedOperations = [];

    // Thresholds for performance warnings
    private $performanceThresholds = [
        'execution_time' => 0.5,  // 500ms
        'memory_usage' => 50 * 1024 * 1024,  // 50MB
        'cpu_usage' => 80  // 80% CPU
    ];

    public function __construct($mode = self::MODE_BASIC) {
        $this->profilingMode = $mode;
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();

        // Load custom thresholds from configuration
        $this->loadPerformanceThresholds();
    }

    /**
     * Load performance thresholds from configuration
     */
    private function loadPerformanceThresholds() {
        $customThresholds = [
            'execution_time' => $this->config->get('PERF_THRESHOLD_EXECUTION_TIME', 0.5),
            'memory_usage' => $this->config->get('PERF_THRESHOLD_MEMORY', 50 * 1024 * 1024),
            'cpu_usage' => $this->config->get('PERF_THRESHOLD_CPU', 80)
        ];

        $this->performanceThresholds = array_merge(
            $this->performanceThresholds, 
            $customThresholds
        );
    }

    /**
     * Start global performance tracking
     */
    public function start() {
        $this->startMemory = memory_get_usage();
        $this->startTime = microtime(true);
    }

    /**
     * Begin tracking a specific operation
     * 
     * @param string $operationName Name of the operation
     * @param array $context Additional context
     */
    public function beginOperation($operationName, $context = []) {
        $this->trackedOperations[$operationName] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
            'context' => $context
        ];
    }

    /**
     * End tracking a specific operation
     * 
     * @param string $operationName Name of the operation
     * @return array Performance metrics
     */
    public function endOperation($operationName) {
        if (!isset($this->trackedOperations[$operationName])) {
            throw new \RuntimeException("Operation $operationName not started");
        }

        $operation = $this->trackedOperations[$operationName];
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $metrics = [
            'name' => $operationName,
            'execution_time' => $endTime - $operation['start_time'],
            'memory_used' => $endMemory - $operation['start_memory'],
            'context' => $operation['context']
        ];

        // Check for performance warnings
        $this->checkPerformanceWarnings($metrics);

        // Store metrics
        $this->metrics[] = $metrics;

        // Remove tracked operation
        unset($this->trackedOperations[$operationName]);

        return $metrics;
    }

    /**
     * Check performance metrics against thresholds
     * 
     * @param array $metrics Performance metrics
     */
    private function checkPerformanceWarnings($metrics) {
        $warnings = [];

        // Check execution time
        if ($metrics['execution_time'] > $this->performanceThresholds['execution_time']) {
            $warnings[] = sprintf(
                "Slow operation: %s took %.4f seconds (threshold: %.4f)",
                $metrics['name'],
                $metrics['execution_time'],
                $this->performanceThresholds['execution_time']
            );
        }

        // Check memory usage
        if ($metrics['memory_used'] > $this->performanceThresholds['memory_usage']) {
            $warnings[] = sprintf(
                "High memory usage: %s used %d bytes (threshold: %d)",
                $metrics['name'],
                $metrics['memory_used'],
                $this->performanceThresholds['memory_usage']
            );
        }

        // Log warnings
        if (!empty($warnings)) {
            $this->logger->warning('Performance Warnings', [
                'operation' => $metrics['name'],
                'warnings' => $warnings
            ]);
        }
    }

    /**
     * Generate comprehensive performance report
     * 
     * @return array Detailed performance report
     */
    public function generateReport() {
        $totalExecutionTime = microtime(true) - $this->startTime;
        $totalMemoryUsed = memory_get_usage() - $this->startMemory;

        $report = [
            'total_execution_time' => $totalExecutionTime,
            'total_memory_used' => $totalMemoryUsed,
            'operations' => $this->metrics,
            'system_info' => $this->getSystemInfo()
        ];

        // Detailed analysis for advanced modes
        if ($this->profilingMode !== self::MODE_BASIC) {
            $report['operation_summary'] = $this->analyzeOperations();
        }

        return $report;
    }

    /**
     * Analyze tracked operations
     * 
     * @return array Operation performance summary
     */
    private function analyzeOperations() {
        $summary = [
            'total_operations' => count($this->metrics),
            'slowest_operations' => [],
            'highest_memory_usage' => []
        ];

        // Sort operations by execution time and memory usage
        usort($this->metrics, function($a, $b) {
            return $b['execution_time'] <=> $a['execution_time'];
        });
        $summary['slowest_operations'] = array_slice($this->metrics, 0, 5);

        usort($this->metrics, function($a, $b) {
            return $b['memory_used'] <=> $a['memory_used'];
        });
        $summary['highest_memory_usage'] = array_slice($this->metrics, 0, 5);

        return $summary;
    }

    /**
     * Collect system information
     * 
     * @return array System performance details
     */
    private function getSystemInfo() {
        return [
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'cpu_cores' => PHP_OS_FAMILY === 'Windows' 
                ? getenv('NUMBER_OF_PROCESSORS') 
                : shell_exec('nproc')
        ];
    }

    /**
     * Demonstrate performance profiling capabilities
     */
    public function demonstrateProfiler() {
        // Simulate some operations
        $this->beginOperation('database_query', [
            'query' => 'SELECT * FROM users'
        ]);
        usleep(100000);  // Simulate 100ms delay
        $this->endOperation('database_query');

        $this->beginOperation('data_processing', [
            'records' => 1000
        ]);
        usleep(200000);  // Simulate 200ms delay
        $this->endOperation('data_processing');

        // Generate and display report
        $report = $this->generateReport();
        print_r($report);
    }
}

// Global helper function for easy profiling
function profile($mode = PerformanceProfiler::MODE_BASIC) {
    return new PerformanceProfiler($mode);
}
