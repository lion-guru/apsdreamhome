<?php

namespace App\Services\Performance;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Performance Configuration Service
 * Handles performance optimization with proper MVC patterns
 */
class PerformanceConfigService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $cache = [];
    private array $metrics = [];

    // Performance levels
    public const LEVEL_LOW = 1;
    public const LEVEL_NORMAL = 2;
    public const LEVEL_HIGH = 3;
    public const LEVEL_CRITICAL = 4;

    // Cache types
    public const CACHE_FILE = 'file';
    public const CACHE_REDIS = 'redis';
    public const CACHE_APCU = 'apcu';
    public const CACHE_MEMORY = 'memory';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'cache_enabled' => true,
            'cache_type' => self::CACHE_FILE,
            'cache_lifetime' => 3600, // 1 hour
            'cache_dir' => __DIR__ . '/../../../cache/performance',
            'performance_logging' => true,
            'log_dir' => __DIR__ . '/../../../logs/performance',
            'max_cache_size' => 100 * 1024 * 1024, // 100MB
            'optimization_interval' => 3600, // 1 hour
            'metrics_retention_days' => 30
        ], $config);
        
        $this->initializePerformanceTables();
        $this->ensureDirectories();
    }

    /**
     * Get performance configuration
     */
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Set performance configuration
     */
    public function setConfig(string $key, $value): array
    {
        try {
            $this->config[$key] = $value;
            
            // Save to database
            $sql = "INSERT INTO performance_config (config_key, config_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()";
            
            $this->db->execute($sql, [$key, json_encode($value), json_encode($value)]);

            $this->logger->info("Performance config updated", [
                'key' => $key,
                'value' => $value
            ]);

            return [
                'success' => true,
                'message' => 'Configuration updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to update config", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cache data
     */
    public function cache(string $key, $data, int $ttl = null): bool
    {
        if (!$this->config['cache_enabled']) {
            return false;
        }

        try {
            $ttl = $ttl ?? $this->config['cache_lifetime'];
            $cacheKey = $this->generateCacheKey($key);
            $serializedData = serialize($data);
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

            switch ($this->config['cache_type']) {
                case self::CACHE_REDIS:
                    return $this->cacheRedis($cacheKey, $serializedData, $ttl);
                
                case self::CACHE_APCU:
                    return $this->cacheApcu($cacheKey, $serializedData, $ttl);
                
                case self::CACHE_MEMORY:
                    return $this->cacheMemory($cacheKey, $serializedData, $ttl);
                
                default:
                    return $this->cacheFile($cacheKey, $serializedData, $expiresAt);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to cache data", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cached data
     */
    public function getCached(string $key, $default = null)
    {
        if (!$this->config['cache_enabled']) {
            return $default;
        }

        try {
            $cacheKey = $this->generateCacheKey($key);

            switch ($this->config['cache_type']) {
                case self::CACHE_REDIS:
                    $data = $this->getCachedRedis($cacheKey);
                    break;
                
                case self::CACHE_APCU:
                    $data = $this->getCachedApcu($cacheKey);
                    break;
                
                case self::CACHE_MEMORY:
                    $data = $this->getCachedMemory($cacheKey);
                    break;
                
                default:
                    $data = $this->getCachedFile($cacheKey);
                    break;
            }

            return $data !== null ? unserialize($data) : $default;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get cached data", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Delete cached data
     */
    public function deleteCached(string $key): bool
    {
        try {
            $cacheKey = $this->generateCacheKey($key);

            switch ($this->config['cache_type']) {
                case self::CACHE_REDIS:
                    return $this->deleteCachedRedis($cacheKey);
                
                case self::CACHE_APCU:
                    return $this->deleteCachedApcu($cacheKey);
                
                case self::CACHE_MEMORY:
                    return $this->deleteCachedMemory($cacheKey);
                
                default:
                    return $this->deleteCachedFile($cacheKey);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to delete cached data", [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clearCache(): array
    {
        try {
            $cleared = 0;

            switch ($this->config['cache_type']) {
                case self::CACHE_REDIS:
                    $cleared = $this->clearCacheRedis();
                    break;
                
                case self::CACHE_APCU:
                    $cleared = $this->clearCacheApcu();
                    break;
                
                case self::CACHE_MEMORY:
                    $cleared = $this->clearCacheMemory();
                    break;
                
                default:
                    $cleared = $this->clearCacheFile();
                    break;
            }

            $this->logger->info("Cache cleared", ['items_cleared' => $cleared]);

            return [
                'success' => true,
                'message' => "Cache cleared successfully",
                'items_cleared' => $cleared
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to clear cache", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log performance event
     */
    public function logPerformanceEvent(string $event, array $data = [], float $executionTime = null, int $level = self::LEVEL_NORMAL): void
    {
        if (!$this->config['performance_logging']) {
            return;
        }

        try {
            $sql = "INSERT INTO performance_logs 
                    (event, event_data, execution_time_ms, level, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $event,
                json_encode($data),
                $executionTime ? ($executionTime * 1000) : null,
                $level
            ]);

            // Also log to file for debugging
            $this->logToFile($event, $data, $executionTime, $level);

        } catch (\Exception $e) {
            // Fallback to file logging only
            $this->logToFile($event, $data, $executionTime, $level);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        try {
            $metrics = [];

            // Recent performance logs
            $sql = "SELECT * FROM performance_logs";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $metrics['recent_logs'] = $this->db->fetchAll($sql, $params);

            // Performance summary
            $summarySql = "SELECT 
                        COUNT(*) as total_events,
                        AVG(execution_time_ms) as avg_execution_time,
                        MAX(execution_time_ms) as max_execution_time,
                        MIN(execution_time_ms) as min_execution_time
                    FROM performance_logs 
                    WHERE execution_time_ms IS NOT NULL";
            
            $summaryParams = [];
            
            if (!empty($filters['date_from'])) {
                $summarySql .= " AND created_at >= ?";
                $summaryParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $summarySql .= " AND created_at <= ?";
                $summaryParams[] = $filters['date_to'];
            }

            $summary = $this->db->fetchOne($summarySql, $summaryParams);
            $metrics['summary'] = $summary ?? [
                'total_events' => 0,
                'avg_execution_time_ms' => 0,
                'max_execution_time_ms' => 0,
                'min_execution_time_ms' => 0
            ];

            // Events by level
            $levelSql = "SELECT level, COUNT(*) as count FROM performance_logs";
            $levelParams = [];
            
            if (!empty($filters['date_from'])) {
                $levelSql .= " WHERE created_at >= ?";
                $levelParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $levelSql .= " AND created_at <= ?";
                $levelParams[] = $filters['date_to'];
            }
            
            $levelSql .= " GROUP BY level";
            
            $levelStats = $this->db->fetchAll($levelSql, $levelParams);
            $metrics['by_level'] = [];
            foreach ($levelStats as $stat) {
                $metrics['by_level'][$stat['level']] = $stat['count'];
            }

            // Cache statistics
            $metrics['cache_stats'] = $this->getCacheStatistics();

            return $metrics;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get performance metrics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Optimize performance
     */
    public function optimizePerformance(): array
    {
        try {
            $optimizations = [];
            $improvements = 0;

            // Clean old performance logs
            $result = $this->cleanOldLogs();
            if ($result['success']) {
                $optimizations[] = 'Cleaned old performance logs';
                $improvements++;
            }

            // Optimize cache
            $result = $this->optimizeCache();
            if ($result['success']) {
                $optimizations[] = 'Optimized cache storage';
                $improvements++;
            }

            // Update configuration based on metrics
            $result = $this->updateConfigurationFromMetrics();
            if ($result['success']) {
                $optimizations[] = 'Updated configuration from metrics';
                $improvements++;
            }

            $this->logger->info("Performance optimization completed", [
                'optimizations' => $optimizations,
                'improvements' => $improvements
            ]);

            return [
                'success' => true,
                'message' => "Performance optimization completed",
                'optimizations' => $optimizations,
                'improvements' => $improvements
            ];

        } catch (\Exception $e) {
            $this->logger->error("Performance optimization failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Performance optimization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializePerformanceTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event VARCHAR(255) NOT NULL,
                event_data JSON,
                execution_time_ms DECIMAL(10,2),
                level INT NOT NULL DEFAULT 2,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event (event),
                INDEX idx_level (level),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS performance_config (
                config_key VARCHAR(255) PRIMARY KEY,
                config_value JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function ensureDirectories(): void
    {
        $directories = [
            $this->config['cache_dir'],
            $this->config['log_dir']
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private function generateCacheKey(string $key): string
    {
        return 'perf_' . md5($key);
    }

    private function cacheFile(string $key, string $data, string $expiresAt): bool
    {
        $filename = $this->config['cache_dir'] . '/' . $key . '.cache';
        $cacheData = [
            'data' => $data,
            'expires_at' => $expiresAt
        ];
        
        return file_put_contents($filename, serialize($cacheData)) !== false;
    }

    private function getCachedFile(string $key): ?string
    {
        $filename = $this->config['cache_dir'] . '/' . $key . '.cache';
        
        if (!file_exists($filename)) {
            return null;
        }

        $cacheData = unserialize(file_get_contents($filename));
        
        if ($cacheData['expires_at'] < date('Y-m-d H:i:s')) {
            unlink($filename);
            return null;
        }

        return $cacheData['data'];
    }

    private function deleteCachedFile(string $key): bool
    {
        $filename = $this->config['cache_dir'] . '/' . $key . '.cache';
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    private function clearCacheFile(): int
    {
        $cleared = 0;
        $files = glob($this->config['cache_dir'] . '/*.cache');
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }

    private function cacheRedis(string $key, string $data, int $ttl): bool
    {
        // Mock Redis implementation
        return true;
    }

    private function getCachedRedis(string $key): ?string
    {
        // Mock Redis implementation
        return null;
    }

    private function deleteCachedRedis(string $key): bool
    {
        // Mock Redis implementation
        return true;
    }

    private function clearCacheRedis(): int
    {
        // Mock Redis implementation
        return 0;
    }

    private function cacheApcu(string $key, string $data, int $ttl): bool
    {
        return apcu_store($key, $data, $ttl);
    }

    private function getCachedApcu(string $key): ?string
    {
        return apcu_fetch($key) ?: null;
    }

    private function deleteCachedApcu(string $key): bool
    {
        return apcu_delete($key);
    }

    private function clearCacheApcu(): int
    {
        apcu_clear();
        return 1;
    }

    private function cacheMemory(string $key, string $data, int $ttl): bool
    {
        $this->cache[$key] = [
            'data' => $data,
            'expires_at' => time() + $ttl
        ];
        
        return true;
    }

    private function getCachedMemory(string $key): ?string
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        $cacheItem = $this->cache[$key];
        
        if ($cacheItem['expires_at'] < time()) {
            unset($this->cache[$key]);
            return null;
        }

        return $cacheItem['data'];
    }

    private function deleteCachedMemory(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    private function clearCacheMemory(): int
    {
        $cleared = count($this->cache);
        $this->cache = [];
        return $cleared;
    }

    private function getCacheStatistics(): array
    {
        $stats = [
            'type' => $this->config['cache_type'],
            'enabled' => $this->config['cache_enabled'],
            'items' => 0,
            'size' => 0
        ];

        switch ($this->config['cache_type']) {
            case self::CACHE_FILE:
                $files = glob($this->config['cache_dir'] . '/*.cache');
                $stats['items'] = count($files);
                $stats['size'] = array_sum(array_map('filesize', $files));
                break;
            
            case self::CACHE_MEMORY:
                $stats['items'] = count($this->cache);
                $stats['size'] = strlen(serialize($this->cache));
                break;
            
            case self::CACHE_APCU:
                $info = apcu_cache_info();
                $stats['items'] = $info['num_entries'] ?? 0;
                $stats['size'] = $info['mem_size'] ?? 0;
                break;
        }

        return $stats;
    }

    private function optimizeCache(): array
    {
        // Clean expired cache items
        $cleaned = 0;

        switch ($this->config['cache_type']) {
            case self::CACHE_FILE:
                $files = glob($this->config['cache_dir'] . '/*.cache');
                foreach ($files as $file) {
                    $cacheData = unserialize(file_get_contents($file));
                    if ($cacheData['expires_at'] < date('Y-m-d H:i:s')) {
                        if (unlink($file)) {
                            $cleaned++;
                        }
                    }
                }
                break;
            
            case self::CACHE_MEMORY:
                $currentTime = time();
                foreach ($this->cache as $key => $item) {
                    if ($item['expires_at'] < $currentTime) {
                        unset($this->cache[$key]);
                        $cleaned++;
                    }
                }
                break;
        }

        return [
            'success' => true,
            'cleaned_items' => $cleaned
        ];
    }

    private function updateConfigurationFromMetrics(): array
    {
        // Analyze recent performance metrics and update configuration
        $metrics = $this->getPerformanceMetrics(['date_from' => date('Y-m-d H:i:s', time() - 3600)]);

        if (!empty($metrics['summary']['avg_execution_time_ms'])) {
            $avgTime = $metrics['summary']['avg_execution_time_ms'];
            
            // Adjust cache lifetime based on performance
            if ($avgTime > 1000) { // 1 second
                $this->setConfig('cache_lifetime', 7200); // Increase to 2 hours
            } elseif ($avgTime < 100) { // 0.1 second
                $this->setConfig('cache_lifetime', 1800); // Decrease to 30 minutes
            }
        }

        return [
            'success' => true,
            'message' => 'Configuration updated based on metrics'
        ];
    }

    private function cleanOldLogs(): array
    {
        $sql = "DELETE FROM performance_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $deletedRows = $this->db->execute($sql, [$this->config['metrics_retention_days']]);

        return [
            'success' => true,
            'deleted_rows' => $deletedRows
        ];
    }

    private function logToFile(string $event, array $data, ?float $executionTime, int $level): void
    {
        $logFile = $this->config['log_dir'] . '/performance_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logMessage = "[{$timestamp}] [LEVEL: {$level}] {$event}";
        
        if ($executionTime !== null) {
            $logMessage .= " (Execution Time: " . round($executionTime * 1000, 2) . "ms)";
        }
        
        if (!empty($data)) {
            $logMessage .= " Data: " . json_encode($data);
        }
        
        $logMessage .= "\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
