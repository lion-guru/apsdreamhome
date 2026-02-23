<?php
/**
 * System Monitor Dashboard
 * Comprehensive monitoring and health checking system
 */

namespace App\Core;

class SystemMonitor
{
    private static $instance = null;
    private $healthChecks = [];
    private $errorLogs = [];
    private $performanceMetrics = [];

    private function __construct()
    {
        $this->initializeHealthChecks();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize default health checks
     */
    private function initializeHealthChecks()
    {
        $this->healthChecks = [
            'database' => [$this, 'checkDatabase'],
            'filesystem' => [$this, 'checkFilesystem'],
            'cache' => [$this, 'checkCache'],
            'memory' => [$this, 'checkMemory'],
            'disk_space' => [$this, 'checkDiskSpace'],
            'php_extensions' => [$this, 'checkPhpExtensions'],
            'permissions' => [$this, 'checkPermissions']
        ];
    }

    /**
     * Run all health checks
     */
    public function runHealthChecks()
    {
        $results = [];

        foreach ($this->healthChecks as $checkName => $checkFunction) {
            try {
                $results[$checkName] = call_user_func($checkFunction);
            } catch (Exception $e) {
                $results[$checkName] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }

        return $results;
    }

    /**
     * Check database connectivity and performance
     */
    private function checkDatabase()
    {
        try {
            global $pdo;

            if (!$pdo) {
                return [
                    'status' => 'error',
                    'message' => 'Database connection not available',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            $start = microtime(true);
            $pdo->query('SELECT 1');
            $executionTime = microtime(true) - $start;

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'execution_time' => round($executionTime * 1000, 2) . 'ms',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Check filesystem permissions and space
     */
    private function checkFilesystem()
    {
        $criticalPaths = [
            __DIR__ . '/../cache/',
            __DIR__ . '/../logs/',
            __DIR__ . '/../uploads/',
            __DIR__ . '/../backups/'
        ];

        $results = [];
        foreach ($criticalPaths as $path) {
            $writable = is_writable($path);
            $exists = is_dir($path);

            $results[basename($path)] = [
                'exists' => $exists,
                'writable' => $writable,
                'status' => ($exists && $writable) ? 'healthy' : 'warning'
            ];
        }

        return [
            'status' => 'healthy',
            'details' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check cache system functionality
     */
    private function checkCache()
    {
        try {
            $cache = Cache::getInstance();
            $testKey = 'health_check_test';
            $testValue = 'test_value_' . time();

            // Test cache write
            $cache->set($testKey, $testValue, 60);

            // Test cache read
            $retrievedValue = $cache->get($testKey);

            // Clean up
            $cache->delete($testKey);

            $cacheWorking = ($retrievedValue === $testValue);

            return [
                'status' => $cacheWorking ? 'healthy' : 'warning',
                'message' => $cacheWorking ? 'Cache system operational' : 'Cache system issues detected',
                'driver' => config('cache.driver', 'file'),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Check memory usage
     */
    private function checkMemory()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $usagePercentage = ($memoryUsage / $memoryLimit) * 100;

        $status = 'healthy';
        if ($usagePercentage > 80) {
            $status = 'warning';
        }
        if ($usagePercentage > 95) {
            $status = 'critical';
        }

        return [
            'status' => $status,
            'usage' => $this->formatBytes($memoryUsage),
            'limit' => ini_get('memory_limit'),
            'usage_percentage' => round($usagePercentage, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercentage = ($usedSpace / $totalSpace) * 100;

        $status = 'healthy';
        if ($usagePercentage > 80) {
            $status = 'warning';
        }
        if ($usagePercentage > 95) {
            $status = 'critical';
        }

        return [
            'status' => $status,
            'total' => $this->formatBytes($totalSpace),
            'free' => $this->formatBytes($freeSpace),
            'used' => $this->formatBytes($usedSpace),
            'usage_percentage' => round($usagePercentage, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check required PHP extensions
     */
    private function checkPhpExtensions()
    {
        $requiredExtensions = [
            'pdo', 'pdo_mysql', 'mbstring', 'gd', 'curl', 'json', 'zip'
        ];

        $missingExtensions = [];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        return [
            'status' => empty($missingExtensions) ? 'healthy' : 'warning',
            'message' => empty($missingExtensions)
                ? 'All required extensions loaded'
                : 'Missing extensions: ' . implode(', ', $missingExtensions),
            'loaded_extensions' => get_loaded_extensions(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check file permissions
     */
    private function checkPermissions()
    {
        $criticalFiles = [
            __DIR__ . '/../config/bootstrap.php',
            __DIR__ . '/../.env',
            __DIR__ . '/../index.php'
        ];

        $results = [];
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $results[basename($file)] = [
                    'exists' => true,
                    'readable' => is_readable($file),
                    'writable' => is_writable($file)
                ];
            } else {
                $results[basename($file)] = [
                    'exists' => false,
                    'readable' => false,
                    'writable' => false
                ];
            }
        }

        $allGood = true;
        foreach ($results as $file => $perms) {
            if (!$perms['exists'] || !$perms['readable']) {
                $allGood = false;
                break;
            }
        }

        return [
            'status' => $allGood ? 'healthy' : 'warning',
            'details' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get comprehensive system status
     */
    public function getSystemStatus()
    {
        $healthChecks = $this->runHealthChecks();
        $overallStatus = 'healthy';

        foreach ($healthChecks as $check) {
            if ($check['status'] === 'error') {
                $overallStatus = 'error';
                break;
            } elseif ($check['status'] === 'warning' && $overallStatus === 'healthy') {
                $overallStatus = 'warning';
            }
        }

        return [
            'status' => $overallStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => APP_VERSION ?? '2.1',
            'environment' => APP_ENV ?? 'development',
            'uptime' => $this->getUptime(),
            'health_checks' => $healthChecks,
            'performance' => $this->getPerformanceMetrics(),
            'last_errors' => $this->getRecentErrors(10)
        ];
    }

    /**
     * Get system uptime
     */
    private function getUptime()
    {
        if (function_exists('shell_exec') && stripos(PHP_OS, 'WIN') === false) {
            $uptime = shell_exec('uptime -p');
            return $uptime ? trim($uptime) : 'Unknown';
        }
        return 'Feature not available on this system';
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'execution_time' => number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
            'included_files' => count(get_included_files()),
            'loaded_extensions' => count(get_loaded_extensions())
        ];
    }

    /**
     * Get recent errors from log files
     */
    private function getRecentErrors($limit = 10)
    {
        $logFile = __DIR__ . '/../logs/error.log';

        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // Most recent first

        return array_slice($lines, 0, $limit);
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit($memoryLimit)
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $memoryLimit;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Log system event
     */
    public function logEvent($event, $details = [], $level = 'info')
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'details' => $details,
            'level' => $level,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $logFile = __DIR__ . '/../logs/system_events.log';

        // Create logs directory if not exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
    }
}

/**
 * Global monitoring functions
 */
function system_monitor()
{
    return SystemMonitor::getInstance();
}

function health_check()
{
    return system_monitor()->getSystemStatus();
}

?>
