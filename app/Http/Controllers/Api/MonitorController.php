<?php

// TODO: Add proper error handling with try-catch blocks


/**
 * API Controller for Monitoring and Management
 * Provides endpoints for system monitoring, backups, and performance tracking
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;

use App\Core\SystemMonitor;
use App\Core\BackupManager;

class MonitorController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get system status
     */
    public function status()
    {
        try {
            $monitor = SystemMonitor::getInstance();
            $status = $monitor->getSystemStatus();
        } catch (\Throwable $e) {
            $status = [
                'status' => 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'error' => 'System monitor unavailable: ' . $e->getMessage(),
            ];
        }

        $this->jsonResponse($status);
    }

    /**
     * Get health checks
     */
    public function health()
    {
        try {
            $monitor = SystemMonitor::getInstance();
            $healthChecks = $monitor->runHealthChecks();
        } catch (\Throwable $e) {
            $healthChecks = [
                'error' => 'Health checks unavailable: ' . $e->getMessage(),
            ];
        }

        $this->jsonResponse([
            'timestamp' => date('Y-m-d H:i:s'),
            'health_checks' => $healthChecks
        ]);
    }

    /**
     * Get performance metrics
     */
    public function performance()
    {
        $metricsClass = 'App\\Core\\Performance\\PerformanceMonitoringService';
        if (class_exists($metricsClass) && method_exists($metricsClass, 'getInstance') && method_exists($metricsClass, 'getMetrics')) {
            try {
                $service = $metricsClass::getInstance();
                $metrics = $service->getMetrics();
                $memoryUsage = $metrics['memory_usage'] ?? memory_get_usage(true);
                $memoryPeak = $metrics['memory_peak'] ?? memory_get_peak_usage(true);
                $includedFiles = $metrics['included_files'] ?? count(get_included_files());
                $loadedExtensions = $metrics['loaded_extensions'] ?? count(get_loaded_extensions());
            } catch (\Throwable $e) {
                // Fall through to inline metrics
            }
        }

        $memoryUsage = $memoryUsage ?? memory_get_usage(true);
        $memoryPeak = $memoryPeak ?? memory_get_peak_usage(true);
        $includedFiles = $includedFiles ?? count(get_included_files());
        $loadedExtensions = $loadedExtensions ?? count(get_loaded_extensions());

        $this->jsonResponse([
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'execution_time' => number_format((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2) . 'ms',
            'memory_usage' => $this->formatBytes($memoryUsage),
            'memory_peak' => $this->formatBytes($memoryPeak),
            'included_files' => $includedFiles,
            'loaded_extensions' => $loadedExtensions
        ]);
    }

    /**
     * Get recent errors
     */
    public function errors()
    {
        $logFile = __DIR__ . '/../logs/error.log';

        if (!file_exists($logFile)) {
            $this->jsonResponse([]);
            return;
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // Most recent first
        $recentErrors = array_slice($lines, 0, 20); // Last 20 errors

        $this->jsonResponse($recentErrors);
    }

    /**
     * Format bytes helper
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * Backup API Controller
 */
class BackupApiController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List available backups
     */
    public function list()
    {
        $backupManager = BackupManager::getInstance();
        $backups = $backupManager->listBackups();

        $this->jsonResponse($backups);
    }

    /**
     * Create backup
     */
    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? 'full';

        $backupManager = BackupManager::getInstance();

        if ($type === 'database') {
            $result = $backupManager->createDatabaseBackup();
        } else {
            $result = $backupManager->createFullBackup();
        }

        $this->jsonResponse($result);
    }

    /**
     * Delete backup
     */
    public function delete()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'] ?? '';

        if (empty($filename)) {
            $this->jsonResponse(['success' => false, 'message' => 'Filename required']);
            return;
        }

        $backupFile = __DIR__ . '/../backups/' . $filename;

        if (file_exists($backupFile) && unlink($backupFile)) {
            $this->jsonResponse(['success' => true, 'message' => 'Backup deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete backup']);
        }
    }

    /**
     * Get backup statistics
     */
    public function stats()
    {
        $backupManager = BackupManager::getInstance();
        $stats = $backupManager->getBackupStats();

        $this->jsonResponse($stats);
    }
}
