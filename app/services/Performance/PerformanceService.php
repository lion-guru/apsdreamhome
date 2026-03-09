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
    public function get(string $key, $default = null)
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->getFromRedis($cacheKey, $default);
                case 'apcu':
                    return $this->getFromApcu($cacheKey, $default);
                case 'file':
                default:
                    return $this->getFromFile($cacheKey, $default);
            }
        } catch (\Exception $e) {
            $this->logger->error("Cache get failed", ['key' => $key, 'error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Set cached value
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            $ttl = $ttl ?? $this->defaultTtl;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->setToRedis($cacheKey, $value, $ttl);
                case 'apcu':
                    return $this->setToApcu($cacheKey, $value, $ttl);
                case 'file':
                default:
                    return $this->setToFile($cacheKey, $value, $ttl);
            }
        } catch (\Exception $e) {
            $this->logger->error("Cache set failed", ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete cached value
     */
    public function delete(string $key): bool
    {
        try {
            $cacheKey = $this->cachePrefix . $key;
            
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->deleteFromRedis($cacheKey);
                case 'apcu':
                    return $this->deleteFromApcu($cacheKey);
                case 'file':
                default:
                    return $this->deleteFromFile($cacheKey);
            }
        } catch (\Exception $e) {
            $this->logger->error("Cache delete failed", ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    return $this->clearRedis();
                case 'apcu':
                    return $this->clearApcu();
                case 'file':
                default:
                    return $this->clearFile();
            }
        } catch (\Exception $e) {
            $this->logger->error("Cache clear failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Cache database query results
     */
    public function cacheQuery(string $sql, array $params = [], int $ttl = 3600): array
    {
        $cacheKey = 'query_' . md5($sql . serialize($params));
        
        $result = $this->get($cacheKey);
        if ($result !== null) {
            $this->logger->debug("Cache hit for query", ['sql' => $sql]);
            return $result;
        }

        $this->logger->debug("Cache miss for query, executing", ['sql' => $sql]);
        $result = $this->db->fetchAll($sql, $params);
        $this->set($cacheKey, $result, $ttl);
        
        return $result;
    }

    /**
     * Cache expensive function results
     */
    public function cacheFunction(callable $function, array $args = [], int $ttl = 3600, string $key = null)
    {
        $cacheKey = $key ?? 'func_' . md5(serialize($function) . serialize($args));
        
        $result = $this->get($cacheKey);
        if ($result !== null) {
            return $result;
        }

        $result = call_user_func_array($function, $args);
        $this->set($cacheKey, $result, $ttl);
        
        return $result;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $stats = [
                'driver' => $this->cacheConfig['driver'],
                'prefix' => $this->cachePrefix,
                'default_ttl' => $this->defaultTtl
            ];

            switch ($this->cacheConfig['driver']) {
                case 'redis':
                    $stats['redis'] = $this->getRedisStats();
                    break;
                case 'apcu':
                    $stats['apcu'] = $this->getApcuStats();
                    break;
                case 'file':
                default:
                    $stats['file'] = $this->getFileStats();
                    break;
            }

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get cache stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Optimize cache performance
     */
    public function optimize(): array
    {
        $optimizations = [];

        try {
            // Clean expired cache entries
            $cleaned = $this->cleanExpired();
            $optimizations['cleaned_expired'] = $cleaned;

            // Compress large cache entries if using file driver
            if ($this->cacheConfig['driver'] === 'file') {
                $compressed = $this->compressLargeFiles();
                $optimizations['compressed_files'] = $compressed;
            }

            // Update cache configuration
            $this->updateCacheConfig();
            $optimizations['config_updated'] = true;

            $this->logger->info("Cache optimization completed", $optimizations);

        } catch (\Exception $e) {
            $this->logger->error("Cache optimization failed", ['error' => $e->getMessage()]);
        }

        return $optimizations;
    }

    /**
     * Initialize cache system
     */
    private function initializeCache(): void
    {
        switch ($this->cacheConfig['driver']) {
            case 'file':
                $this->initializeFileCache();
                break;
            case 'redis':
                $this->initializeRedisCache();
                break;
            case 'apcu':
                $this->initializeApcuCache();
                break;
        }

        $this->logger->info("Cache initialized", ['driver' => $this->cacheConfig['driver']]);
    }

    /**
     * Initialize file cache
     */
    private function initializeFileCache(): void
    {
        $cachePath = $this->cacheConfig['path'];
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
    }

    /**
     * Get from file cache
     */
    private function getFromFile(string $key, $default = null)
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }

        $data = include $filePath;
        
        if ($data['expires'] < time()) {
            unlink($filePath);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set to file cache
     */
    private function setToFile(string $key, $value, int $ttl): bool
    {
        $filePath = $this->getCacheFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        $content = '<?php return ' . var_export($data, true) . ';';
        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * Delete from file cache
     */
    private function deleteFromFile(string $key): bool
    {
        $filePath = $this->getCacheFilePath($key);
        return file_exists($filePath) && unlink($filePath);
    }

    /**
     * Clear file cache
     */
    private function clearFile(): bool
    {
        $cachePath = $this->cacheConfig['path'];
        $files = glob($cachePath . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }

    /**
     * Get cache file path
     */
    private function getCacheFilePath(string $key): string
    {
        return $this->cacheConfig['path'] . md5($key) . '.cache';
    }

    /**
     * Clean expired cache entries
     */
    private function cleanExpired(): int
    {
        $cleaned = 0;
        
        if ($this->cacheConfig['driver'] === 'file') {
            $cachePath = $this->cacheConfig['path'];
            $files = glob($cachePath . '*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $data = include $file;
                    if ($data['expires'] < time()) {
                        unlink($file);
                        $cleaned++;
                    }
                }
            }
        }
        
        return $cleaned;
    }

    /**
     * Compress large cache files
     */
    private function compressLargeFiles(): int
    {
        $compressed = 0;
        
        if ($this->cacheConfig['driver'] === 'file') {
            $cachePath = $this->cacheConfig['path'];
            $files = glob($cachePath . '*');
            
            foreach ($files as $file) {
                if (is_file($file) && filesize($file) > 1024 * 1024) { // > 1MB
                    $data = include $file;
                    $compressedData = gzcompress(serialize($data));
                    file_put_contents($file . '.gz', $compressedData);
                    unlink($file);
                    $compressed++;
                }
            }
        }
        
        return $compressed;
    }

    /**
     * Update cache configuration
     */
    private function updateCacheConfig(): void
    {
        // This would update any dynamic cache configuration
        // For now, it's a placeholder for future enhancements
    }

    /**
     * Get file cache statistics
     */
    private function getFileStats(): array
    {
        $cachePath = $this->cacheConfig['path'];
        $files = glob($cachePath . '*');
        
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
}
