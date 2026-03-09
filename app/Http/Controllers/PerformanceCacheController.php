<?php

namespace App\Http\Controllers;

use App\Services\PerformanceCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function set(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'value' => 'required',
                'ttl' => 'nullable|integer|min:0',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100',
                'compressed' => 'nullable|boolean',
                'priority' => 'nullable|in:low,normal,high'
            ]);

            $ttl = $validated['ttl'] ?? 3600;
            $options = [
                'tags' => $validated['tags'] ?? [],
                'compressed' => $validated['compressed'] ?? false,
                'priority' => $validated['priority'] ?? 'normal'
            ];

            $result = $this->cacheService->set(
                $validated['key'],
                $validated['value'],
                $ttl,
                $options
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Cache item set successfully' : 'Failed to set cache item'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set cache item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache item
     */
    public function get(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'default' => 'nullable',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100'
            ]);

            $options = [
                'tags' => $validated['tags'] ?? []
            ];

            $value = $this->cacheService->get(
                $validated['key'],
                $validated['default'] ?? null,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $validated['key'],
                    'value' => $value,
                    'found' => $value !== ($validated['default'] ?? null)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remember value with callback
     */
    public function remember(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'ttl' => 'nullable|integer|min:0',
                'callback' => 'required|string', // In real app, this would be handled differently
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100'
            ]);

            // For demo purposes, we'll use a simple callback
            $callback = function () {
                return time() . '_generated_value';
            };

            $ttl = $validated['ttl'] ?? 3600;
            $options = [
                'tags' => $validated['tags'] ?? []
            ];

            $value = $this->cacheService->remember(
                $validated['key'],
                $ttl,
                $callback,
                $options
            );

            return response()->json([
                'success' => true,
                'message' => 'Value remembered successfully',
                'data' => [
                    'key' => $validated['key'],
                    'value' => $value,
                    'ttl' => $ttl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remember value',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete cache item
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:100'
            ]);

            $options = [
                'tags' => $validated['tags'] ?? []
            ];

            $result = $this->cacheService->delete($validated['key'], $options);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Cache item deleted successfully' : 'Failed to delete cache item'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cache item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache by tags
     */
    public function clearByTags(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tags' => 'required|array|min:1',
                'tags.*' => 'required|string|max:100'
            ]);

            $result = $this->cacheService->clearByTags($validated['tags']);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Cache cleared by tags successfully' : 'Failed to clear cache by tags',
                'data' => [
                    'cleared_tags' => $validated['tags']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache by tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): JsonResponse
    {
        try {
            $result = $this->cacheService->clear();

            return response()->json([
                'success' => $result,
                'message' => $result ? 'All cache cleared successfully' : 'Failed to clear cache'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memoize function result
     */
    public function memoize(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'function' => 'required|string', // In real app, this would be handled differently
                'args' => 'nullable|array',
                'ttl' => 'nullable|integer|min:0'
            ]);

            // For demo purposes, we'll use a simple function
            $callback = function ($args) {
                return [
                    'result' => 'memoized_result_' . time(),
                    'args_received' => $args
                ];
            };

            $ttl = $validated['ttl'] ?? 3600;
            $args = $validated['args'] ?? [];

            $result = $this->cacheService->memoize($callback, $args, $ttl);

            return response()->json([
                'success' => true,
                'message' => 'Function result memoized successfully',
                'data' => [
                    'result' => $result,
                    'ttl' => $ttl,
                    'args' => $args
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to memoize function',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cache database query
     */
    public function cacheQuery(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query_key' => 'required|string|max:255',
                'query' => 'required|string', // In real app, this would be handled differently
                'ttl' => 'nullable|integer|min:0'
            ]);

            // For demo purposes, we'll use a simple query callback
            $queryCallback = function () {
                return [
                    'query_result' => 'sample_data_' . time(),
                    'timestamp' => now()->toISOString()
                ];
            };

            $ttl = $validated['ttl'] ?? 3600;

            $result = $this->cacheService->cacheQuery(
                $validated['query_key'],
                $queryCallback,
                $ttl
            );

            return response()->json([
                'success' => true,
                'message' => 'Query result cached successfully',
                'data' => [
                    'query_key' => $validated['query_key'],
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cache query',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'endpoint' => 'required|string|max:255',
                'ttl' => 'nullable|integer|min:0'
            ]);

            // For demo purposes, we'll use a simple API callback
            $apiCallback = function () {
                return [
                    'api_response' => 'sample_api_data_' . time(),
                    'status' => 'success',
                    'cached_at' => now()->toISOString()
                ];
            };

            $ttl = $validated['ttl'] ?? 300;

            $result = $this->cacheService->cacheApiResponse(
                $validated['endpoint'],
                $apiCallback,
                $ttl
            );

            return response()->json([
                'success' => true,
                'message' => 'API response cached successfully',
                'data' => [
                    'endpoint' => $validated['endpoint'],
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cache API response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cache computed value
     */
    public function cacheComputed(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'computation' => 'required|string', // In real app, this would be handled differently
                'ttl' => 'nullable|integer|min:0'
            ]);

            // For demo purposes, we'll use a simple computation callback
            $computeCallback = function () {
                return [
                    'computed_value' => 'computed_result_' . time(),
                    'computation_time' => '0.05s',
                    'computed_at' => now()->toISOString()
                ];
            };

            $ttl = $validated['ttl'] ?? 3600;

            $result = $this->cacheService->cacheComputed(
                $validated['key'],
                $computeCallback,
                $ttl
            );

            return response()->json([
                'success' => true,
                'message' => 'Computed value cached successfully',
                'data' => [
                    'key' => $validated['key'],
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cache computed value',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset cache statistics
     */
    public function resetStats(): JsonResponse
    {
        try {
            $this->cacheService->resetStats();

            return response()->json([
                'success' => true,
                'message' => 'Cache statistics reset successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset cache statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache information
     */
    public function getCacheInfo(): JsonResponse
    {
        try {
            $info = $this->cacheService->getCacheInfo();

            return response()->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Warm up cache
     */
    public function warmUp(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warmup_data' => 'required|array|min:1',
                'warmup_data.*.key' => 'required|string|max:255',
                'warmup_data.*.value' => 'required',
                'warmup_data.*.ttl' => 'nullable|integer|min:0',
                'warmup_data.*.options' => 'nullable|array'
            ]);

            $this->cacheService->warmUp($validated['warmup_data']);

            return response()->json([
                'success' => true,
                'message' => 'Cache warmup completed successfully',
                'data' => [
                    'items_count' => count($validated['warmup_data'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to warm up cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache size
     */
    public function getCacheSize(): JsonResponse
    {
        try {
            $size = $this->cacheService->getCacheSize();

            return response()->json([
                'success' => true,
                'data' => $size
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache size',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize cache
     */
    public function optimize(): JsonResponse
    {
        try {
            $optimizations = $this->cacheService->optimize();

            return response()->json([
                'success' => true,
                'message' => 'Cache optimization completed successfully',
                'data' => [
                    'optimizations' => $optimizations,
                    'optimizations_count' => count($optimizations)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate cache report
     */
    public function generateReport(): JsonResponse
    {
        try {
            $report = $this->cacheService->generateReport();

            return response()->json([
                'success' => true,
                'message' => 'Cache report generated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate cache report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache dashboard
     */
    public function getDashboard(): JsonResponse
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

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assess cache health
     */
    private function assessCacheHealth(array $stats): array
    {
        $hitRate = $stats['hit_rate'] ?? 0;
        $totalOps = array_sum($stats);

        $health = [
            'status' => 'Excellent',
            'score' => 100,
            'issues' => []
        ];

        if ($hitRate < 50) {
            $health['status'] = 'Poor';
            $health['score'] = 25;
            $health['issues'][] = 'Low hit rate - consider increasing TTL or optimizing cache keys';
        } elseif ($hitRate < 75) {
            $health['status'] = 'Fair';
            $health['score'] = 50;
            $health['issues'][] = 'Moderate hit rate - review cache strategy';
        } elseif ($hitRate < 90) {
            $health['status'] = 'Good';
            $health['score'] = 75;
        }

        if ($totalOps < 100) {
            $health['issues'][] = 'Low cache usage - verify cache implementation';
        }

        return $health;
    }

    /**
     * Get cache recommendations
     */
    private function getCacheRecommendations(array $stats, array $info, array $size): array
    {
        $recommendations = [];

        $hitRate = $stats['hit_rate'] ?? 0;
        $driver = $info['driver'] ?? 'unknown';

        if ($hitRate < 75) {
            $recommendations[] = 'Consider increasing cache TTL for frequently accessed data';
            $recommendations[] = 'Review cache key patterns for better命中率';
        }

        if ($driver === 'file' && isset($size['file_count']) && $size['file_count'] > 10000) {
            $recommendations[] = 'Consider switching to Redis or Memcached for better performance';
        }

        if ($driver === 'redis' && isset($info['redis_info']['used_memory'])) {
            $memoryUsage = $info['redis_info']['used_memory'];
            if (strpos($memoryUsage, 'GB') !== false) {
                $recommendations[] = 'Redis memory usage is high - consider data cleanup or memory increase';
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Cache performance is optimal';
        }

        return $recommendations;
    }
}
