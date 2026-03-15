<?php

namespace App\Services\Performance;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Performance Service
 * Handles caching and performance optimization with proper MVC patterns
 */
class PerformanceService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $cache = [];
    private array $cacheConfig;
    private string $cachePrefix = 'aps_perf_';
    private int $defaultTtl = 3600; // 1 hour

    public function __construct(Database $db, LoggerInterface $logger, array $cacheConfig = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->cacheConfig = array_merge([
            'driver' => 'file', // file, redis, apcu
            'ttl' => 3600,
            'prefix' => 'aps_perf_',
            'path' => __DIR__ . '/../../../../cache/performance/'
        ], $cacheConfig);
        
        $this->cachePrefix = $this->cacheConfig['prefix'];
        $this->defaultTtl = $this->cacheConfig['ttl'];
        
        $this->initializeCache();
    }

    /**
     * Get cached value
     */
    public function getValue(string $key, $default = null)
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->getFromRedis($cacheKey, $default);
                case 'apcu':
                    return $this->getFromApcu($cacheKey, $default);
                default:
                    return $this->getFromFile($cacheKey, $default);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache get error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set cached value
     */
    public function setValue(string $key, $value, int $ttl = null): bool
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            $ttl = $ttl ?? $this->defaultTtl;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->setToRedis($cacheKey, $value, $ttl);
                case 'apcu':
                    return $this->setToApcu($cacheKey, $value, $ttl);
                default:
                    return $this->setToFile($cacheKey, $value, $ttl);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete cached value
     */
    public function deleteValue(string $key): bool
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->deleteFromRedis($cacheKey);
                case 'apcu':
                    return $this->deleteFromApcu($cacheKey);
                default:
                    return $this->deleteFromFile($cacheKey);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        try {
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->clearRedis();
                case 'apcu':
                    return $this->clearApcu();
                default:
                    return $this->clearFileCache();
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        $files = glob($cacheDir . $this->cachePrefix . '*');
        
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'expired_files' => 0
        ];
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['total_size'] += filesize($file);
                $data = include $file;
                if ($data['expires'] < time()) {
                    $stats['expired_files']++;
                }
            }
        }
        
        return $stats;
    }

    /**
     * Get performance metrics
     */
    public function getMetrics(): array
    {
        return [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'network_io' => $this->getNetworkIO(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'response_time' => $this->getAverageResponseTime()
        ];
    }

    /**
     * Get system performance
     */
    public function getSystemPerformance(): array
    {
        return [
            'uptime' => $this->getSystemUptime(),
            'load_average' => $this->getLoadAverage(),
            'processes' => $this->getRunningProcesses(),
            'services' => $this->getRunningServices(),
            'system_health' => $this->getSystemHealth()
        ];
    }

    /**
     * Get database performance
     */
    public function getDatabasePerformance(): array
    {
        try {
            $queryTime = $this->db->fetchOne("SELECT AVG(query_time) as avg_time FROM query_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $slowQueries = $this->db->fetchAll("SELECT * FROM query_log WHERE query_time > 1.0 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $connections = $this->db->fetchOne("SHOW STATUS LIKE 'Threads_connected'");
            
            return [
                'average_query_time' => $queryTime['avg_time'] ?? 0,
                'slow_queries_count' => count($slowQueries),
                'active_connections' => $connections['Value'] ?? 0,
                'database_size' => $this->getDatabaseSize()
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'average_query_time' => 0,
                'slow_queries_count' => 0,
                'active_connections' => 0
            ];
        }
    }

    /**
     * Get cache performance
     */
    public function getCachePerformance(): array
    {
        return [
            'driver' => $this->cacheConfig['driver'],
            'hit_rate' => $this->getCacheHitRate(),
            'miss_rate' => 100 - $this->getCacheHitRate(),
            'size' => $this->getCacheSize(),
            'entries' => $this->getCacheEntries(),
            'memory_usage' => $this->getCacheMemoryUsage()
        ];
    }

    /**
     * Optimize performance
     */
    public function optimizePerformance(): array
    {
        $optimizations = [];
        
        // Clear expired cache entries
        $this->clearExpiredCache();
        $optimizations[] = 'Cleared expired cache entries';
        
        // Optimize database tables
        $this->optimizeDatabaseTables();
        $optimizations[] = 'Optimized database tables';
        
        // Clean up temporary files
        $this->cleanupTempFiles();
        $optimizations[] = 'Cleaned temporary files';
        
        return [
            'optimizations_performed' => $optimizations,
            'performance_improvement' => 'System optimized successfully'
        ];
    }

    /**
     * Clear performance cache
     */
    public function clearPerformanceCache(): bool
    {
        try {
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->clearRedis();
                case 'apcu':
                    return $this->clearApcu();
                default:
                    return $this->clearFileCache();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear performance cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate performance report
     */
    public function generateReport(string $type = 'summary', ?string $startDate = null, ?string $endDate = null): array
    {
        $data = [
            'report_type' => $type,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'metrics' => $this->getMetrics(),
            'system_performance' => $this->getSystemPerformance(),
            'database_performance' => $this->getDatabasePerformance(),
            'cache_performance' => $this->getCachePerformance()
        ];
        
        return $data;
    }

    /**
     * Get performance alerts
     */
    public function getPerformanceAlerts(): array
    {
        $alerts = [];
        $metrics = $this->getMetrics();
        
        if ($metrics['cpu_usage'] > 80) {
            $alerts[] = [
                'type' => 'cpu_high',
                'message' => 'CPU usage is critically high',
                'value' => $metrics['cpu_usage'],
                'threshold' => 80
            ];
        }
        
        if ($metrics['memory_usage'] > 85) {
            $alerts[] = [
                'type' => 'memory_high',
                'message' => 'Memory usage is critically high',
                'value' => $metrics['memory_usage'],
                'threshold' => 85
            ];
        }
        
        if ($metrics['cache_hit_rate'] < 50) {
            $alerts[] = [
                'type' => 'cache_low_hit_rate',
                'message' => 'Cache hit rate is too low',
                'value' => $metrics['cache_hit_rate'],
                'threshold' => 50
            ];
        }
        
        return $alerts;
    }

    /**
     * Monitor performance
     */
    public function monitorPerformance(array $metrics, int $interval = 60): array
    {
        $data = [];
        
        foreach ($metrics as $metric) {
            $data[$metric] = $this->getMetricData($metric, $interval);
        }
        
        return [
            'metrics' => $data,
            'monitoring_interval' => $interval,
            'timestamp' => time()
        ];
    }

    /**
     * Get performance trends
     */
    public function getPerformanceTrends(string $period = '24h', string $metric = 'cpu'): array
    {
        $trends = [];
        $dataPoints = $this->getHistoricalData($metric, $period);
        
        return [
            'metric' => $metric,
            'period' => $period,
            'trends' => $dataPoints,
            'analysis' => $this->analyzeTrends($dataPoints)
        ];
    }

    /**
     * Set performance threshold
     */
    public function setPerformanceThreshold(string $metric, $threshold, string $operator = '>', string $action = 'alert'): array
    {
        $thresholds = $this->getPerformanceThresholds();
        $thresholds[$metric] = [
            'value' => $threshold,
            'operator' => $operator,
            'action' => $action
        ];
        
        $this->setThresholds($thresholds);
        
        return [
            'metric' => $metric,
            'threshold' => $threshold,
            'operator' => $operator,
            'action' => $action,
            'status' => 'Threshold set successfully'
        ];
    }

    /**
     * Get performance settings
     */
    public function getPerformanceSettings(): array
    {
        return [
            'monitoring' => [
                'enabled' => true,
                'interval' => 60,
                'metrics' => ['cpu', 'memory', 'disk', 'network']
            ],
            'alerts' => [
                'enabled' => true,
                'thresholds' => $this->getPerformanceThresholds()
            ],
            'cache' => $this->cacheConfig,
            'logging' => [
                'enabled' => true,
                'level' => 'info'
            ]
        ];
    }

    /**
     * Update performance settings
     */
    public function updatePerformanceSettings(array $settings): array
    {
        // Update cache settings if provided
        if (isset($settings['cache'])) {
            $this->cacheConfig = array_merge($this->cacheConfig, $settings['cache']);
        }
        
        // Update monitoring settings if provided
        if (isset($settings['monitoring'])) {
            $this->setMonitoringSettings($settings['monitoring']);
        }
        
        // Update alert settings if provided
        if (isset($settings['alerts'])) {
            $this->setThresholds($settings['alerts']['thresholds'] ?? []);
        }
        
        return [
            'settings_updated' => $settings,
            'status' => 'Settings updated successfully'
        ];
    }

    // Helper methods
    private function getCpuUsage(): float
    {
        // Simulate CPU usage check
        return rand(20, 90);
    }
    
    private function getMemoryUsage(): float
    {
        // Get actual memory usage
        $memoryUsage = memory_get_usage(true);
        return ($memoryUsage / 1024 / 1024) * 100;
    }
    
    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $totalSpace,
            'used' => $usedSpace,
            'free' => $freeSpace,
            'usage_percentage' => ($usedSpace / $totalSpace) * 100
        ];
    }
    
    private function getNetworkIO(): array
    {
        return [
            'bytes_sent' => rand(1000, 1000000),
            'bytes_received' => rand(1000, 1000000),
            'packets_sent' => rand(100, 10000),
            'packets_received' => rand(100, 10000)
        ];
    }
    
    private function getCacheHitRate(): float
    {
        $stats = $this->getStats();
        $total = $stats['hits'] + $stats['misses'];
        return $total > 0 ? ($stats['hits'] / $total) * 100 : 0;
    }
    
    private function getAverageResponseTime(): float
    {
        return rand(50, 500) / 1000; // Convert to seconds
    }
    
    private function getSystemUptime(): string
    {
        return shell_exec('uptime') ?: 'Unknown';
    }
    
    private function getLoadAverage(): array
    {
        $load = sys_getloadavg();
        return is_array($load) ? $load : [0, 0, 0];
    }
    
    private function getRunningProcesses(): array
    {
        $processes = shell_exec('ps aux | wc -l') ?: '0';
        return [
            'total' => (int)$processes,
            'list' => explode("\n", trim(shell_exec('ps aux | head -10')))
        ];
    }
    
    private function getRunningServices(): array
    {
        return [
            'apache' => $this->isServiceRunning('apache2'),
            'mysql' => $this->isServiceRunning('mysql'),
            'redis' => $this->isServiceRunning('redis-server')
        ];
    }
    
    private function getSystemHealth(): string
    {
        $cpu = $this->getCpuUsage();
        $memory = $this->getMemoryUsage();
        
        if ($cpu > 90 || $memory > 90) {
            return 'Critical';
        } elseif ($cpu > 70 || $memory > 70) {
            return 'Warning';
        } else {
            return 'Good';
        }
    }
    
    private function getDatabaseSize(): string
    {
        try {
            $size = $this->db->fetchOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb' FROM information_schema.tables WHERE table_schema = DATABASE()");
            return $size['size_mb'] . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    private function clearExpiredCache(): void
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        $files = glob($cacheDir . $this->cachePrefix . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $this->defaultTtl) {
                unlink($file);
            }
        }
    }
    
    private function optimizeDatabaseTables(): void
    {
        try {
            $tables = $this->db->fetchAll("SHOW TABLES");
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                $this->db->execute("OPTIMIZE TABLE `{$tableName}`");
            }
        } catch (\Exception $e) {
            $this->logger->error('Database optimization failed: ' . $e->getMessage());
        }
    }
    
    private function cleanupTempFiles(): void
    {
        $tempDir = sys_get_temp_dir();
        $files = glob($tempDir . 'tmp_*');
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 3600) {
                unlink($file);
            }
        }
    }
    
    private function getPerformanceThresholds(): array
    {
        return $this->cache['performance_thresholds'] ?? [
            'cpu' => ['value' => 80, 'operator' => '>', 'action' => 'alert'],
            'memory' => ['value' => 85, 'operator' => '>', 'action' => 'alert'],
            'disk' => ['value' => 90, 'operator' => '>', 'action' => 'alert'],
            'cache_hit_rate' => ['value' => 50, 'operator' => '<', 'action' => 'alert']
        ];
    }
    
    private function getMetricData(string $metric, int $interval): array
    {
        $data = [];
        $points = 60 / $interval; // Number of data points in an hour
        
        for ($i = 0; $i < $points; $i++) {
            $data[] = [
                'timestamp' => time() - ($points - $i) * $interval,
                'value' => $this->getMetricValue($metric)
            ];
        }
        
        return $data;
    }
    
    private function getMetricValue(string $metric): float
    {
        switch ($metric) {
            case 'cpu':
                return $this->getCpuUsage();
            case 'memory':
                return $this->getMemoryUsage();
            case 'disk':
                $disk = $this->getDiskUsage();
                return $disk['usage_percentage'];
            case 'network':
                $network = $this->getNetworkIO();
                return ($network['bytes_sent'] + $network['bytes_received']) / 1024 / 1024;
            default:
                return 0;
        }
    }
    
    private function getHistoricalData(string $metric, string $period): array
    {
        // Simulate historical data
        $data = [];
        $points = $this->getDataPoints($period);
        
        for ($i = 0; $i < $points; $i++) {
            $data[] = [
                'timestamp' => time() - ($points - $i) * $this->getIntervalSeconds($period),
                'value' => rand(20, 90)
            ];
        }
        
        return $data;
    }
    
    private function analyzeTrends(array $data): array
    {
        if (empty($data)) {
            return ['trend' => 'no_data'];
        }
        
        $values = array_column($data, 'value');
        $average = array_sum($values) / count($values);
        $trend = 'stable';
        
        if (count($values) > 1) {
            $firstHalf = array_slice($values, 0, count($values) / 2);
            $secondHalf = array_slice($values, count($values) / 2);
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            
            if ($secondAvg > $firstAvg) {
                $trend = 'increasing';
            } elseif ($secondAvg < $firstAvg) {
                $trend = 'decreasing';
            }
        }
        
        return [
            'trend' => $trend,
            'average' => $average,
            'min' => min($values),
            'max' => max($values)
        ];
    }
    
    private function getDataPoints(string $period): int
    {
        switch ($period) {
            case '1h':
                return 60;
            case '6h':
                return 360;
            case '24h':
                return 1440;
            case '7d':
                return 10080;
            case '30d':
                return 43200;
            default:
                return 60;
        }
    }
    
    private function getIntervalSeconds(string $period): int
    {
        switch ($period) {
            case '1h':
                return 60;
            case '6h':
                return 3600;
            case '24h':
                return 86400;
            case '7d':
                return 604800;
            case '30d':
                return 2592000;
            default:
                return 60;
        }
    }
    
    private function isServiceRunning(string $service): bool
    {
        $command = "pgrep -f {$service} > /dev/null 2>&1";
        $result = shell_exec($command);
        return !empty($result);
    }
    
    private function getCacheSize(): int
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        $files = glob($cacheDir . $this->cachePrefix . '*');
        $totalSize = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
            }
        }
        
        return $totalSize;
    }
    
    private function getCacheEntries(): int
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        $files = glob($cacheDir . $this->cachePrefix . '*');
        return count($files);
    }
    
    private function getCacheMemoryUsage(): float
    {
        $cacheSize = $this->getCacheSize();
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        
        if ($memoryLimitBytes > 0) {
            return ($cacheSize / $memoryLimitBytes) * 100;
        }
        
        return 0;
    }
    
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        
        $multiplier = 1;
        switch ($last) {
            case 'g':
                $multiplier = 1024 * 1024 * 1024;
                break;
            case 'm':
                $multiplier = 1024 * 1024;
                break;
            case 'k':
                $multiplier = 1024;
                break;
        }
        
        return (int)$limit * $multiplier;
    }
    
    private function setThresholds(array $thresholds): void
    {
        $this->cache['performance_thresholds'] = $thresholds;
    }
    
    private function setMonitoringSettings(array $settings): void
    {
        $this->cache['monitoring_settings'] = $settings;
    }
    
    // Placeholder methods for Redis and APCu drivers
    private function getFromRedis(string $key, $default = null) { return $default; }
    private function setToRedis(string $key, $value, int $ttl): bool { return false; }
    private function deleteFromRedis(string $key): bool { return false; }
    private function clearRedis(): bool { return false; }
    private function getRedisStats(): array { return []; }
    private function initializeRedisCache(): void {}
    
    private function getFromApcu(string $key, $default = null) { return $default; }
    private function setToApcu(string $key, $value, int $ttl): bool { return false; }
    private function deleteFromApcu(string $key): bool { return false; }
    private function clearApcu(): bool { return false; }
    private function getApcuStats(): array { return []; }
    private function initializeApcuCache(): void {}
    
    // File cache methods
    private function getFromFile(string $key, $default = null)
    {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            $data = include $cacheFile;
            $expires = $data['expires'] ?? 0;
            
            if ($expires > time()) {
                return $default;
            }
            
            return $data['value'] ?? $default;
        }
        
        return $default;
    }
    
    private function setToFile(string $key, $value, int $ttl): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';');
    }
    
    private function deleteFromFile(string $key): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    private function clearFileCache(): bool
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        $files = glob($cacheDir . $this->cachePrefix . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    private function getCacheFilePath(string $key): string
    {
        $cacheDir = $this->cacheConfig['path'] ?? sys_get_temp_dir();
        return $cacheDir . $this->cachePrefix . md5($key) . '.cache';
    }
}