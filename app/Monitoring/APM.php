<?php
/**
 * APS Dream Home - Application Performance Monitor
 */

namespace App\Monitoring;

class APM
{
    private static $instance = null;
    private $metrics = [];
    private $startTime;
    private $config;

    private function __construct()
    {
        $this->startTime = microtime(true);
        $this->config = require CONFIG_PATH . '/monitoring.php';
        $this->initializeMetrics();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeMetrics()
    {
        $this->metrics = [
            'request' => [
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'max_time' => 0,
                'min_time' => PHP_FLOAT_MAX
            ],
            'database' => [
                'queries' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'slow_queries' => 0
            ],
            'memory' => [
                'peak_usage' => 0,
                'current_usage' => 0,
                'avg_usage' => 0
            ],
            'errors' => [
                'count' => 0,
                'types' => []
            ],
            'performance' => [
                'cpu_usage' => 0,
                'disk_usage' => 0,
                'network_io' => 0
            ]
        ];
    }

    public function startRequest($requestId = null)
    {
        $requestId = $requestId ?: uniqid('req_');
        $this->metrics['current_request'] = [
            'id' => $requestId,
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'queries' => 0,
            'errors' => 0
        ];
        return $requestId;
    }

    public function endRequest($requestId)
    {
        if (!isset($this->metrics['current_request'])) {
            return;
        }

        $request = $this->metrics['current_request'];
        $endTime = microtime(true);
        $duration = ($endTime - $request['start_time']) * 1000;
        $memoryUsed = memory_get_usage(true) - $request['memory_start'];

        // Update request metrics
        $this->metrics['request']['count']++;
        $this->metrics['request']['total_time'] += $duration;
        $this->metrics['request']['avg_time'] = $this->metrics['request']['total_time'] / $this->metrics['request']['count'];
        $this->metrics['request']['max_time'] = max($this->metrics['request']['max_time'], $duration);
        $this->metrics['request']['min_time'] = min($this->metrics['request']['min_time'], $duration);

        // Update memory metrics
        $this->metrics['memory']['current_usage'] = memory_get_usage(true);
        $this->metrics['memory']['peak_usage'] = max($this->metrics['memory']['peak_usage'], $this->metrics['memory']['current_usage']);

        // Log request details
        $this->logRequest($requestId, $duration, $memoryUsed);

        unset($this->metrics['current_request']);
    }

    public function recordQuery($query, $duration, $type = 'select')
    {
        $this->metrics['database']['queries']++;
        $this->metrics['database']['total_time'] += $duration;
        $this->metrics['database']['avg_time'] = $this->metrics['database']['total_time'] / $this->metrics['database']['queries'];

        if ($duration > $this->config['slow_query_threshold']) {
            $this->metrics['database']['slow_queries']++;
            $this->logSlowQuery($query, $duration);
        }

        if (isset($this->metrics['current_request'])) {
            $this->metrics['current_request']['queries']++;
        }
    }

    public function recordError($error, $type = 'exception')
    {
        $this->metrics['errors']['count']++;
        $this->metrics['errors']['types'][$type] = ($this->metrics['errors']['types'][$type] ?? 0) + 1;

        if (isset($this->metrics['current_request'])) {
            $this->metrics['current_request']['errors']++;
        }

        $this->logError($error, $type);
    }

    public function getMetrics()
    {
        $this->updateSystemMetrics();
        return $this->metrics;
    }

    private function updateSystemMetrics()
    {
        // CPU usage (simplified)
        $load = sys_getloadavg();
        $this->metrics['performance']['cpu_usage'] = $load ? $load[0] : 0;

        // Disk usage
        $totalSpace = disk_total_space(BASE_PATH);
        $freeSpace = disk_free_space(BASE_PATH);
        $this->metrics['performance']['disk_usage'] = $totalSpace ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0;
    }

    private function logRequest($requestId, $duration, $memoryUsed)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $requestId,
            'duration' => round($duration, 2),
            'memory_used' => $this->formatBytes($memoryUsed),
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->writeLog('apm_requests.log', $logEntry);
    }

    private function logSlowQuery($query, $duration)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'query' => $query,
            'duration' => round($duration, 2),
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];

        $this->writeLog('apm_slow_queries.log', $logEntry);
    }

    private function logError($error, $type)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error,
            'type' => $type,
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->writeLog('apm_errors.log', $logEntry);
    }

    private function writeLog($filename, $data)
    {
        $logFile = BASE_PATH . '/logs/' . $filename;
        file_put_contents($logFile, json_encode($data) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
