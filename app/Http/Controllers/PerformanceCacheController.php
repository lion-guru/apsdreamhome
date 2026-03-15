<?php

namespace App\Http\Controllers;

use App\Services\PerformanceCacheService;
use Exception;

/**
 * Controller for Performance Cache operations
 */
class PerformanceCacheController extends BaseController
{
    private PerformanceCacheService $cacheService;

    public function __construct(PerformanceCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Set cache item
     */
    public function setCache($request)
    {
        try {
            $data = $request['key'] ?? '';
            $value = $request['value'] ?? null;
            $ttl = (int)($request['ttl'] ?? 0);
            $tags = $request['tags'] ?? [];
            $compressed = $request['compressed'] ?? false;
            $priority = $request['priority'] ?? 'normal';

            $options = [
                'tags' => $tags,
                'compressed' => $compressed,
                'priority' => $priority
            ];

            $result = $this->cacheService->set($data, $value, $ttl, $options);

            $data = [
                'success' => $result,
                'message' => $result ? 'Cache item set successfully' : 'Failed to set cache item'
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to set cache item',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Get cache item
     */
    public function getCache($request)
    {
        try {
            $data = $request['key'] ?? '';
            $default = $request['default'] ?? null;
            $tags = $request['tags'] ?? [];

            $options = [
                'tags' => $tags
            ];

            $value = $this->cacheService->get($data, $default, $options);

            $data = [
                'success' => true,
                'data' => [
                    'key' => $data,
                    'value' => $value,
                    'found' => $value !== $default
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to get cache item',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Remember value with callback
     */
    public function remember($request)
    {
        try {
            $data = $request['key'] ?? '';
            $ttl = (int)($request['ttl'] ?? 3600);
            $callback = $request['callback'] ?? null;
            $tags = $request['tags'] ?? [];

            $options = [
                'tags' => $tags
            ];

            $value = $this->cacheService->remember($data, $ttl, $callback, $options);

            $data = [
                'success' => true,
                'message' => 'Value remembered successfully',
                'data' => [
                    'key' => $data,
                    'value' => $value,
                    'ttl' => $ttl
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to remember value',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Delete cache item
     */
    public function delete($request)
    {
        try {
            $data = $request['key'] ?? '';
            $tags = $request['tags'] ?? [];

            $options = [
                'tags' => $tags
            ];

            $result = $this->cacheService->delete($data, $options);

            $data = [
                'success' => $result,
                'message' => $result ? 'Cache item deleted successfully' : 'Failed to delete cache item'
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to delete cache item',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Clear cache by tags
     */
    public function clearByTags($request)
    {
        try {
            $tags = $request['tags'] ?? [];

            $result = $this->cacheService->clearByTags($tags);

            $data = [
                'success' => $result,
                'message' => $result ? 'Cache cleared by tags successfully' : 'Failed to clear cache by tags',
                'data' => [
                    'cleared_tags' => $tags
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to clear cache by tags',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Clear all cache
     */
    public function clear()
    {
        try {
            $result = $this->cacheService->clear();

            $data = [
                'success' => $result,
                'message' => $result ? 'All cache cleared successfully' : 'Failed to clear cache'
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Memoize function result
     */
    public function memoize($request)
    {
        try {
            $callback = $request['callback'] ?? null;
            $args = $request['args'] ?? [];
            $ttl = (int)($request['ttl'] ?? 3600);

            $result = $this->cacheService->memoize($callback, $args, $ttl);

            $data = [
                'success' => true,
                'message' => 'Function result memoized successfully',
                'data' => [
                    'result' => $result,
                    'ttl' => $ttl,
                    'args' => $args
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to memoize function',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Cache database query
     */
    public function cacheQuery($request)
    {
        try {
            $queryKey = $request['query_key'] ?? '';
            $queryCallback = $request['query_callback'] ?? null;
            $ttl = (int)($request['ttl'] ?? 3600);

            $result = $this->cacheService->cacheQuery($queryKey, $queryCallback, $ttl);

            $data = [
                'success' => true,
                'message' => 'Query result cached successfully',
                'data' => [
                    'query_key' => $queryKey,
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to cache query',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse($request)
    {
        try {
            $endpoint = $request['endpoint'] ?? '';
            $apiCallback = $request['api_callback'] ?? null;
            $ttl = (int)($request['ttl'] ?? 300);

            $result = $this->cacheService->cacheApiResponse($endpoint, $apiCallback, $ttl);

            $data = [
                'success' => true,
                'message' => 'API response cached successfully',
                'data' => [
                    'endpoint' => $endpoint,
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to cache API response',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Cache computed value
     */
    public function cacheComputed($request)
    {
        try {
            $data = $request['key'] ?? '';
            $computeCallback = $request['compute_callback'] ?? null;
            $ttl = (int)($request['ttl'] ?? 3600);

            $result = $this->cacheService->cacheComputed($data, $computeCallback, $ttl);

            $data = [
                'success' => true,
                'message' => 'Computed value cached successfully',
                'data' => [
                    'key' => $data,
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to cache computed value',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->cacheService->getStats();

            $data = [
                'success' => true,
                'data' => $stats
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to get cache statistics',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Reset cache statistics
     */
    public function resetStats()
    {
        try {
            $this->cacheService->resetStats();

            $data = [
                'success' => true,
                'message' => 'Cache statistics reset successfully'
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to reset cache statistics',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Get cache information
     */
    public function getCacheInfo()
    {
        try {
            $info = $this->cacheService->getCacheInfo();

            $data = [
                'success' => true,
                'data' => $info
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to get cache information',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Warm up cache
     */
    public function warmUp($request)
    {
        try {
            $warmupData = $request['warmup_data'] ?? [];

            $this->cacheService->warmUp($warmupData);

            $data = [
                'success' => true,
                'message' => 'Cache warmup completed successfully',
                'data' => [
                    'items_count' => count($warmupData)
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to warm up cache',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Get cache size
     */
    public function getCacheSize()
    {
        try {
            $size = $this->cacheService->getCacheSize();

            $data = [
                'success' => true,
                'data' => $size
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to get cache size',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Optimize cache
     */
    public function optimize()
    {
        try {
            $optimizations = $this->cacheService->optimize();

            $data = [
                'success' => true,
                'message' => 'Cache optimization completed successfully',
                'data' => [
                    'optimizations' => $optimizations,
                    'optimizations_count' => count($optimizations)
                ]
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to optimize cache',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Generate cache report
     */
    public function generateReport()
    {
        try {
            $report = $this->cacheService->generateReport();

            $data = [
                'success' => true,
                'message' => 'Cache report generated successfully',
                'data' => $report
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to generate cache report',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Get cache dashboard
     */
    public function getDashboard()
    {
        try {
            $stats = $this->cacheService->getStats();
            $info = $this->cacheService->getCacheInfo();
            $size = $this->cacheService->getCacheSize();

            $dashboard = [
                'statistics' => $stats,
                'cache_info' => $info,
                'cache_size' => $size,
                'health_status' => $this->assessCacheHealth($stats),
                'recommendations' => $this->getCacheRecommendations($stats, $info, $size)
            ];

            $data = [
                'success' => true,
                'data' => $dashboard
            ];

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => 'Failed to get cache dashboard',
                'error' => $e->getMessage()
            ];

            return $this->jsonResponse($data, 500);
        }
    }

    /**
     * Assess cache health
     */
    private function assessCacheHealth(array $stats): array
    {
        $hitRate = $stats['hit_rate'] ?? 0;
        $totalOps = array_sum($stats);
        $memoryUsage = $stats['memory_usage'] ?? 0;

        $healthScore = ($hitRate * 0.6) + ($memoryUsage < 80 ? 40 : 0);

        return [
            'score' => $healthScore,
            'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 60 ? 'warning' : 'critical'),
            'hit_rate' => $hitRate,
            'memory_usage' => $memoryUsage,
            'total_operations' => $totalOps
        ];
    }

    /**
     * Get cache recommendations
     */
    private function getCacheRecommendations(array $stats, array $info, array $size): array
    {
        $recommendations = [];

        $hitRate = $stats['hit_rate'] ?? 0;
        if ($hitRate < 70) {
            $recommendations[] = 'Consider increasing cache TTL for better hit rates';
        }

        $memoryUsage = $stats['memory_usage'] ?? 0;
        if ($memoryUsage > 85) {
            $recommendations[] = 'Cache memory usage is high, consider clearing old entries';
        }

        $totalSize = $size['total_size'] ?? 0;
        if ($totalSize > 1000000000) { // 1GB
            $recommendations[] = 'Cache size is large, consider implementing cache partitioning';
        }

        return $recommendations;
    }
}