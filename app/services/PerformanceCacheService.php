<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

/**
 * Modern Performance Cache Service
 * Advanced caching mechanisms with multiple drivers and performance optimization
 */
class PerformanceCacheService
{
    private string $cachePrefix;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
        'clears' => 0
    ];

    public function __construct()
    {
        $this->cachePrefix = config('cache.prefix', 'aps_');
        $this->initializeStats();
    }

    /**
     * Set cache item with advanced options
     */
    public function set(string $key, $value, int $ttl = 3600, array $options = []): bool
    {
        try {
            $fullKey = $this->getFullKey($key);
            $tags = $options['tags'] ?? [];
            $compressed = $options['compressed'] ?? false;
            $priority = $options['priority'] ?? 'normal';

            // Prepare value
            $preparedValue = $this->prepareValue($value, $compressed);
            
            // Set cache with tags if available
            if (!empty($tags) && method_exists(Cache::store(), 'tags')) {
                $result = Cache::tags($tags)->put($fullKey, $preparedValue, $ttl);
            } else {
                $result = Cache::put($fullKey, $preparedValue, $ttl);
            }

            if ($result) {
                $this->stats['sets']++;
                $this->logCacheOperation('set', $key, ['ttl' => $ttl, 'tags' => $tags]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cache item with fallback
     */
    public function get(string $key, $default = null, array $options = [])
    {
        try {
            $fullKey = $this->getFullKey($key);
            $tags = $options['tags'] ?? [];

            // Get cache with tags if available
            if (!empty($tags) && method_exists(Cache::store(), 'tags')) {
                $value = Cache::tags($tags)->get($fullKey);
            } else {
                $value = Cache::get($fullKey);
            }

            if ($value !== null) {
                $this->stats['hits']++;
                $this->logCacheOperation('hit', $key);
                return $this->restoreValue($value);
            }

            $this->stats['misses']++;
            $this->logCacheOperation('miss', $key);
            return $default;
        } catch (\Exception $e) {
            Log::error('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Remember value with callback
     */
    public function remember(string $key, int $ttl, callable $callback, array $options = []): mixed
    {
        $value = $this->get($key, null, $options);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl, $options);
        
        return $value;
    }

    /**
     * Remember value forever
     */
    public function rememberForever(string $key, callable $callback, array $options = []): mixed
    {
        return $this->remember($key, 0, $callback, $options);
    }

    /**
     * Delete cache item
     */
    public function delete(string $key, array $options = []): bool
    {
        try {
            $fullKey = $this->getFullKey($key);
            $tags = $options['tags'] ?? [];

            // Delete with tags if available
            if (!empty($tags) && method_exists(Cache::store(), 'tags')) {
                $result = Cache::tags($tags)->forget($fullKey);
            } else {
                $result = Cache::forget($fullKey);
            }

            if ($result) {
                $this->stats['deletes']++;
                $this->logCacheOperation('delete', $key);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear cache by tags
     */
    public function clearByTags(array $tags): bool
    {
        try {
            if (method_exists(Cache::store(), 'tags')) {
                Cache::tags($tags)->flush();
                $this->logCacheOperation('clear_by_tags', '', ['tags' => $tags]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Cache clear by tags failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            Cache::flush();
            $this->stats['clears']++;
            $this->logCacheOperation('clear', '');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Memoize function results
     */
    public function memoize(callable $callback, array $args = [], int $ttl = 3600): mixed
    {
        $cacheKey = $this->generateMemoizeKey($callback, $args);
        
        return $this->remember($cacheKey, $ttl, function () use ($callback, $args) {
            return call_user_func_array($callback, $args);
        });
    }

    /**
     * Cache database query results
     */
    public function cacheQuery(string $queryKey, callable $queryCallback, int $ttl = 3600): mixed
    {
        $fullKey = "query:{$queryKey}";
        
        return $this->remember($fullKey, $ttl, $queryCallback);
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse(string $endpoint, callable $apiCallback, int $ttl = 300): mixed
    {
        $fullKey = "api:{$endpoint}";
        
        return $this->remember($fullKey, $ttl, $apiCallback);
    }

    /**
     * Cache computed values
     */
    public function cacheComputed(string $key, callable $computeCallback, int $ttl = 3600): mixed
    {
        $fullKey = "computed:{$key}";
        
        return $this->remember($fullKey, $ttl, $computeCallback);
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0;

        return array_merge($this->stats, [
            'total_requests' => $total,
            'hit_rate' => $hitRate,
            'cache_driver' => config('cache.default'),
            'cache_prefix' => $this->cachePrefix
        ]);
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
            'clears' => 0
        ];
    }

    /**
     * Get cache information
     */
    public function getCacheInfo(): array
    {
        $driver = config('cache.default');
        $info = [
            'driver' => $driver,
            'prefix' => $this->cachePrefix,
            'supports_tags' => method_exists(Cache::store(), 'tags'),
            'supports_locking' => method_exists(Cache::store(), 'lock'),
            'supports_many' => method_exists(Cache::store(), 'many')
        ];

        // Driver-specific information
        switch ($driver) {
            case 'redis':
                $info['redis_info'] = $this->getRedisInfo();
                break;
            case 'memcached':
                $info['memcached_info'] = $this->getMemcachedInfo();
                break;
            case 'database':
                $info['database_info'] = $this->getDatabaseInfo();
                break;
        }

        return $info;
    }

    /**
     * Warm up cache with predefined data
     */
    public function warmUp(array $warmupData): void
    {
        foreach ($warmupData as $key => $data) {
            $ttl = $data['ttl'] ?? 3600;
            $value = $data['value'];
            $options = $data['options'] ?? [];
            
            $this->set($key, $value, $ttl, $options);
        }

        Log::info('Cache warmup completed', [
            'items_count' => count($warmupData)
        ]);
    }

    /**
     * Get cache size estimation
     */
    public function getCacheSize(): array
    {
        $driver = config('cache.default');
        
        switch ($driver) {
            case 'redis':
                return $this->getRedisSize();
            case 'memcached':
                return $this->getMemcachedSize();
            case 'file':
                return $this->getFileSize();
            default:
                return ['estimated_size' => 0, 'items_count' => 0];
        }
    }

    /**
     * Optimize cache performance
     */
    public function optimize(): array
    {
        $optimizations = [];

        // Clear expired entries if supported
        if (method_exists(Cache::store(), 'clearExpired')) {
            Cache::store()->clearExpired();
            $optimizations[] = 'Cleared expired cache entries';
        }

        // Optimize Redis memory
        if (config('cache.default') === 'redis') {
            $this->optimizeRedis();
            $optimizations[] = 'Optimized Redis memory usage';
        }

        // Reset statistics for fresh monitoring
        $this->resetStats();
        $optimizations[] = 'Reset cache statistics';

        Log::info('Cache optimization completed', [
            'optimizations' => $optimizations
        ]);

        return $optimizations;
    }

    /**
     * Generate cache report
     */
    public function generateReport(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'statistics' => $this->getStats(),
            'cache_info' => $this->getCacheInfo(),
            'cache_size' => $this->getCacheSize(),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $stats = $this->getStats();
        
        return [
            'hit_rate_percentage' => $stats['hit_rate'],
            'total_operations' => array_sum($stats),
            'operations_per_second' => $this->calculateOpsPerSecond(),
            'memory_efficiency' => $this->calculateMemoryEfficiency(),
            'cache_health' => $this->assessCacheHealth($stats)
        ];
    }

    /**
     * Prepare value for caching
     */
    private function prepareValue($value, bool $compressed): mixed
    {
        if ($compressed && function_exists('gzcompress')) {
            return [
                'compressed' => true,
                'data' => gzcompress(serialize($value), 6),
                'original_size' => strlen(serialize($value))
            ];
        }

        return $value;
    }

    /**
     * Restore cached value
     */
    private function restoreValue($value): mixed
    {
        if (is_array($value) && isset($value['compressed'])) {
            return unserialize(gzuncompress($value['data']));
        }

        return $value;
    }

    /**
     * Get full cache key
     */
    private function getFullKey(string $key): string
    {
        return $this->cachePrefix . $key;
    }

    /**
     * Generate memoize key
     */
    private function generateMemoizeKey(callable $callback, array $args): string
    {
        if (is_array($callback)) {
            $functionName = get_class($callback[0]) . '::' . $callback[1];
        } else {
            $functionName = $callback;
        }

        return 'memoize:' . md5($functionName . serialize($args));
    }

    /**
     * Initialize statistics
     */
    private function initializeStats(): void
    {
        // Load stats from cache if available
        $cachedStats = Cache::get('performance_cache_stats');
        if ($cachedStats) {
            $this->stats = $cachedStats;
        }
    }

    /**
     * Log cache operation
     */
    private function logCacheOperation(string $operation, string $key, array $context = []): void
    {
        Log::debug("Cache operation: {$operation}", array_merge([
            'key' => $key,
            'driver' => config('cache.default')
        ], $context));

        // Persist stats periodically
        if ($this->stats['hits'] + $this->stats['misses'] % 100 === 0) {
            Cache::put('performance_cache_stats', $this->stats, 3600);
        }
    }

    /**
     * Get Redis information
     */
    private function getRedisInfo(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Memcached information
     */
    private function getMemcachedInfo(): array
    {
        try {
            $memcached = Cache::store('memcached')->getMemcached();
            $stats = $memcached->getStats();
            
            return [
                'version' => $memcached->getVersion(),
                'servers' => count($stats),
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get database cache information
     */
    private function getDatabaseInfo(): array
    {
        try {
            $count = \DB::table('cache')->count();
            $size = \DB::table('cache')->sum(\DB::raw('LENGTH(value)'));
            
            return [
                'entries_count' => $count,
                'total_size_bytes' => $size,
                'total_size_human' => $this->formatBytes($size)
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Redis cache size
     */
    private function getRedisSize(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info('memory');
            
            return [
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'used_memory_peak_human' => $info['used_memory_peak_human'] ?? '0B'
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Memcached cache size
     */
    private function getMemcachedSize(): array
    {
        try {
            $memcached = Cache::store('memcached')->getMemcached();
            $stats = $memcached->getStats();
            
            $totalBytes = 0;
            $totalItems = 0;
            
            foreach ($stats as $server => $serverStats) {
                $totalBytes += $serverStats['bytes'] ?? 0;
                $totalItems += $serverStats['curr_items'] ?? 0;
            }
            
            return [
                'total_bytes' => $totalBytes,
                'total_bytes_human' => $this->formatBytes($totalBytes),
                'total_items' => $totalItems
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get file cache size
     */
    private function getFileSize(): array
    {
        try {
            $cachePath = storage_path('framework/cache');
            $totalSize = 0;
            $fileCount = 0;
            
            if (is_dir($cachePath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cachePath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $totalSize += $file->getSize();
                        $fileCount++;
                    }
                }
            }
            
            return [
                'total_bytes' => $totalSize,
                'total_bytes_human' => $this->formatBytes($totalSize),
                'file_count' => $fileCount
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Optimize Redis memory
     */
    private function optimizeRedis(): void
    {
        try {
            $redis = Redis::connection();
            $redis->command('MEMORY', ['PURGE']);
        } catch (\Exception $e) {
            Log::warning('Redis optimization failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate operations per second
     */
    private function calculateOpsPerSecond(): float
    {
        // This would need time-based tracking for accurate calculation
        // For now, return a placeholder
        return 0.0;
    }

    /**
     * Calculate memory efficiency
     */
    private function calculateMemoryEfficiency(): string
    {
        $size = $this->getCacheSize();
        $stats = $this->getStats();
        
        if (!isset($size['total_bytes']) || $size['total_bytes'] === 0) {
            return 'N/A';
        }
        
        $avgItemSize = $size['total_bytes'] / max($stats['sets'], 1);
        
        return $this->formatBytes($avgItemSize) . ' per item';
    }

    /**
     * Assess cache health
     */
    private function assessCacheHealth(array $stats): string
    {
        $hitRate = $stats['hit_rate'];
        
        if ($hitRate >= 90) {
            return 'Excellent';
        } elseif ($hitRate >= 75) {
            return 'Good';
        } elseif ($hitRate >= 50) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
