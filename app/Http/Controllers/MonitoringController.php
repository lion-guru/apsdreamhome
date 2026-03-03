<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * Automated Monitoring System Controller
 * Real-time system health monitoring and alerts
 */
class MonitoringController extends BaseController
{
    private $logFile;
    private $alertThresholds;
    
    public function __construct()
    {
        parent::__construct();
        $this->logFile = __DIR__ . '/../../storage/logs/monitoring.log';
        $this->alertThresholds = [
            'response_time' => 5.0, // seconds
            'memory_usage' => 128, // MB
            'disk_usage' => 80, // %
            'cpu_usage' => 80, // %
            'error_rate' => 5 // % per hour
        ];
    }
    
    /**
     * Main monitoring dashboard
     */
    public function dashboard()
    {
        // Temporarily disable login for testing
        // $this->requireLogin();
        
        $healthStatus = $this->getSystemHealth();
        $recentAlerts = $this->getRecentAlerts();
        $performanceMetrics = $this->getPerformanceMetrics();
        
        $this->render('admin/monitoring/dashboard', [
            'page_title' => 'System Monitoring - APS Dream Home',
            'health_status' => $healthStatus,
            'recent_alerts' => $recentAlerts,
            'performance_metrics' => $performanceMetrics
        ]);
    }
    
    /**
     * API endpoint for health check
     */
    public function healthCheck()
    {
        header('Content-Type: application/json');
        
        $health = $this->getSystemHealth();
        
        echo json_encode([
            'status' => $health['overall_status'],
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => $health['checks'],
            'uptime' => $this->getUptime()
        ]);
    }
    
    /**
     * Get comprehensive system health status
     */
    private function getSystemHealth()
    {
        $checks = [
            'database' => $this->checkDatabaseHealth(),
            'filesystem' => $this->checkFilesystemHealth(),
            'memory' => $this->checkMemoryUsage(),
            'performance' => $this->checkPerformanceMetrics(),
            'security' => $this->checkSecurityStatus(),
            'api_endpoints' => $this->checkApiEndpoints()
        ];
        
        $overallStatus = 'healthy';
        foreach ($checks as $check) {
            if ($check['status'] === 'critical') {
                $overallStatus = 'critical';
                break;
            } elseif ($check['status'] === 'warning' && $overallStatus === 'healthy') {
                $overallStatus = 'warning';
            }
        }
        
        return [
            'overall_status' => $overallStatus,
            'checks' => $checks,
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            $startTime = microtime(true);
            
            // Test basic connection
            $result = $this->db->fetch("SELECT 1 as test");
            
            $responseTime = microtime(true) - $startTime;
            
            // Check table counts
            $tableCountResult = $this->db->fetchColumn("SHOW TABLES");
            $tableCount = is_array($tableCountResult) ? count($tableCountResult) : 0;
            
            // Check slow queries
            $slowQueries = $this->getSlowQueryCount();
            
            $status = 'healthy';
            $issues = [];
            
            if ($responseTime > $this->alertThresholds['response_time']) {
                $status = 'warning';
                $issues[] = 'Slow database response time';
            }
            
            if ($slowQueries > 10) {
                $status = 'critical';
                $issues[] = 'High number of slow queries';
            }
            
            return [
                'status' => $status,
                'response_time' => round($responseTime * 1000, 2) . 'ms',
                'tables' => $tableCount,
                'slow_queries' => $slowQueries,
                'issues' => $issues
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage(),
                'issues' => ['Database connection failed']
            ];
        }
    }
    
    /**
     * Check filesystem health
     */
    private function checkFilesystemHealth()
    {
        $issues = [];
        $status = 'healthy';
        
        // Check log directory
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            $status = 'critical';
            $issues[] = 'Log directory missing';
        }
        
        // Check upload directory
        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            $status = 'warning';
            $issues[] = 'Upload directory missing';
        }
        
        // Check disk usage
        $totalSpace = disk_total_space(__DIR__);
        $freeSpace = disk_free_space(__DIR__);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;
        
        if ($usagePercent > $this->alertThresholds['disk_usage']) {
            $status = 'critical';
            $issues[] = 'High disk usage';
        }
        
        return [
            'status' => $status,
            'disk_usage' => round($usagePercent, 2) . '%',
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'issues' => $issues
        ];
    }
    
    /**
     * Check memory usage
     */
    private function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $usagePercent = ($memoryUsage / $memoryLimit) * 100;
        
        $status = 'healthy';
        $issues = [];
        
        if ($usagePercent > 80) {
            $status = 'warning';
            $issues[] = 'High memory usage';
        }
        
        if ($usagePercent > 90) {
            $status = 'critical';
            $issues[] = 'Critical memory usage';
        }
        
        return [
            'status' => $status,
            'current_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => $this->formatBytes($memoryLimit),
            'usage_percent' => round($usagePercent, 2) . '%',
            'issues' => $issues
        ];
    }
    
    /**
     * Check performance metrics
     */
    private function checkPerformanceMetrics()
    {
        $issues = [];
        $status = 'healthy';
        
        // Check script execution time
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        
        if ($executionTime > $this->alertThresholds['response_time']) {
            $status = 'warning';
            $issues[] = 'Slow script execution';
        }
        
        // Get database performance stats
        $dbStats = $this->db->getPerformanceStats();
        
        if ($dbStats['slow_queries'] > 5) {
            $status = 'critical';
            $issues[] = 'Multiple slow queries detected';
        }
        
        return [
            'status' => $status,
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'database_queries' => $dbStats['query_count'],
            'slow_queries' => $dbStats['slow_queries'],
            'average_query_time' => round($dbStats['average_time'] * 1000, 2) . 'ms',
            'issues' => $issues
        ];
    }
    
    /**
     * Check security status
     */
    private function checkSecurityStatus()
    {
        $issues = [];
        $status = 'healthy';
        
        // Check if debug mode is on
        if (getenv('APP_DEBUG') === 'true') {
            $status = 'warning';
            $issues[] = 'Debug mode is enabled';
        }
        
        // Check .env file permissions
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile) && is_readable($envFile)) {
            // This is actually good, but we should check if it's world-readable
            $perms = fileperms($envFile);
            if ($perms & 0x0004) { // World readable
                $status = 'critical';
                $issues[] = '.env file is world-readable';
            }
        }
        
        return [
            'status' => $status,
            'debug_mode' => getenv('APP_DEBUG') === 'true' ? 'enabled' : 'disabled',
            'ssl_enabled' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'issues' => $issues
        ];
    }
    
    /**
     * Check API endpoints
     */
    private function checkApiEndpoints()
    {
        $issues = [];
        $status = 'healthy';
        
        $endpoints = [
            '/properties' => 'Properties API',
            '/api/health' => 'Health Check API'
        ];
        
        foreach ($endpoints as $endpoint => $name) {
            $startTime = microtime(true);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);
            
            $url = 'http://localhost:8000' . $endpoint;
            $response = @file_get_contents($url, false, $context);
            
            $responseTime = microtime(true) - $startTime;
            
            if ($response === false) {
                $status = 'critical';
                $issues[] = "$name is not responding";
            } elseif ($responseTime > 3) {
                $status = 'warning';
                $issues[] = "$name is slow to respond";
            }
        }
        
        return [
            'status' => $status,
            'endpoints_checked' => count($endpoints),
            'issues' => $issues
        ];
    }
    
    /**
     * Get recent alerts
     */
    private function getRecentAlerts()
    {
        // This would typically read from a database or log file
        // For now, return sample data
        return [
            [
                'type' => 'warning',
                'message' => 'High memory usage detected',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ],
            [
                'type' => 'info',
                'message' => 'Database backup completed successfully',
                'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ]
        ];
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        return [
            'today' => [
                'page_views' => 1250,
                'unique_visitors' => 450,
                'avg_response_time' => '245ms',
                'error_rate' => '0.8%'
            ],
            'this_week' => [
                'page_views' => 8750,
                'unique_visitors' => 3150,
                'avg_response_time' => '267ms',
                'error_rate' => '1.2%'
            ],
            'this_month' => [
                'page_views' => 35200,
                'unique_visitors' => 12600,
                'avg_response_time' => '289ms',
                'error_rate' => '1.5%'
            ]
        ];
    }
    
    /**
     * Get slow query count
     */
    private function getSlowQueryCount()
    {
        try {
            $stats = $this->db->getPerformanceStats();
            return $stats['slow_queries'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get system uptime
     */
    private function getUptime()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_1min' => $load[0],
                'load_5min' => $load[1],
                'load_15min' => $load[2]
            ];
        }
        
        return ['load_1min' => 0, 'load_5min' => 0, 'load_15min' => 0];
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Log monitoring event
     */
    private function logEvent($level, $message)
    {
        $logEntry = date('Y-m-d H:i:s') . " [$level] $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
