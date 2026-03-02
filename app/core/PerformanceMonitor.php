<?php
/**
 * APS Dream Home - Performance Monitor
 */

namespace App\Core;

class PerformanceMonitor
{
    private static $instance = null;
    private $startTime;
    private $metrics;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->metrics = [];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function startTimer($name)
    {
        $this->metrics[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }

    public function endTimer($name)
    {
        if (isset($this->metrics[$name])) {
            $this->metrics[$name]['end'] = microtime(true);
            $this->metrics[$name]['duration'] = ($this->metrics[$name]['end'] - $this->metrics[$name]['start']) * 1000;
            $this->metrics[$name]['memory_end'] = memory_get_usage(true);
            $this->metrics[$name]['memory_used'] = $this->metrics[$name]['memory_end'] - $this->metrics[$name]['memory_start'];
        }
    }

    public function getMetrics()
    {
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        $peakMemory = memory_get_peak_usage(true);
        $currentMemory = memory_get_usage(true);

        return [
            'total_time' => round($totalTime, 2),
            'peak_memory' => $this->formatBytes($peakMemory),
            'current_memory' => $this->formatBytes($currentMemory),
            'metrics' => $this->metrics
        ];
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

    public function logMetrics()
    {
        $metrics = $this->getMetrics();
        $logFile = BASE_PATH . '/logs/performance.log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($metrics) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
