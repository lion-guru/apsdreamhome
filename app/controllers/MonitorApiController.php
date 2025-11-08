<?php
/**
 * API Controller for Monitoring and Management
 * Provides endpoints for system monitoring, backups, and performance tracking
 */

namespace App\Controllers;

use App\Core\SystemMonitor;
use App\Core\BackupManager;
use App\Core\PerformanceMonitor;

class MonitorController extends BaseController
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
        $monitor = SystemMonitor::getInstance();
        $status = $monitor->getSystemStatus();

        $this->jsonResponse($status);
    }

    /**
     * Get health checks
     */
    public function health()
    {
        $monitor = SystemMonitor::getInstance();
        $healthChecks = $monitor->runHealthChecks();

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
        $performance = PerformanceMonitor::getInstance();
        $metrics = $performance->getMetrics();

        $this->jsonResponse([
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'execution_time' => number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
            'memory_usage' => $this->formatBytes($metrics['memory_usage']),
            'memory_peak' => $this->formatBytes($metrics['memory_peak']),
            'included_files' => $metrics['included_files'],
            'loaded_extensions' => $metrics['loaded_extensions']
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

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

/**
 * Backup API Controller
 */
class BackupApiController extends BaseController
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

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
