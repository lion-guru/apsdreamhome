<?php

namespace App\Http\Controllers;

use App\Services\Performance\PerformanceService;
use App\Http\Controllers\Controller;

/**
 * Performance Controller
 * Handles performance and caching operations
 */
class PerformanceController extends Controller
{
    private PerformanceService $performanceService;

    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
        $this->middleware('auth');
    }

    /**
     * Display performance dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->performanceService->getStats();
            
            return view('performance.dashboard', compact('stats'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load performance dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Get cache value
     */
    public function getCache()
    {
        try {
            $key = request('key');
            $default = request('default');
            
            $value = $this->performanceService->get($key, $default);
            
            return response()->json([
                'success' => true, 
                'data' => [
                    'key' => $key,
                    'value' => $value,
                    'found' => $value !== $default
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Set cache value
     */
    public function setCache()
    {
        try {
            $key = request('key');
            $value = request('value');
            $ttl = request('ttl', 3600);
            
            $success = $this->performanceService->set($key, $value, $ttl);
            
            return response()->json([
                'success' => $success, 
                'message' => $success ? 'Cache set successfully' : 'Failed to set cache'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete cache value
     */
    public function deleteCache()
    {
        try {
            $key = request('key');
            $success = $this->performanceService->delete($key);
            
            return response()->json([
                'success' => $success, 
                'message' => $success ? 'Cache deleted successfully' : 'Failed to delete cache'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        try {
            $success = $this->performanceService->clear();
            
            return response()->json([
                'success' => $success, 
                'message' => $success ? 'Cache cleared successfully' : 'Failed to clear cache'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->performanceService->getStats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Optimize cache performance
     */
    public function optimize()
    {
        try {
            $optimizations = $this->performanceService->optimize();
            
            return response()->json([
                'success' => true, 
                'message' => 'Cache optimization completed',
                'data' => $optimizations
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cache database query
     */
    public function cacheQuery()
    {
        try {
            $sql = request('sql');
            $params = request('params', []);
            $ttl = request('ttl', 3600);
            
            // Security: Only allow SELECT queries
            if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Only SELECT queries are allowed for caching'
                ]);
            }
            
            $result = $this->performanceService->cacheQuery($sql, $params, $ttl);
            
            return response()->json([
                'success' => true, 
                'data' => [
                    'sql' => $sql,
                    'params' => $params,
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cache function result
     */
    public function cacheFunction()
    {
        try {
            $functionName = request('function');
            $args = request('args', []);
            $ttl = request('ttl', 3600);
            
            // Security: Only allow predefined safe functions
            $allowedFunctions = [
                'time', 'date', 'strtotime', 'md5', 'sha1', 'json_encode',
                'count', 'array_sum', 'array_merge', 'implode', 'explode'
            ];
            
            if (!in_array($functionName, $allowedFunctions)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Function not allowed for caching'
                ]);
            }
            
            $result = $this->performanceService->cacheFunction(
                $functionName, 
                $args, 
                $ttl, 
                'func_' . $functionName
            );
            
            return response()->json([
                'success' => true, 
                'data' => [
                    'function' => $functionName,
                    'args' => $args,
                    'result' => $result,
                    'ttl' => $ttl
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Test cache performance
     */
    public function testPerformance()
    {
        try {
            $testKey = 'performance_test_' . time();
            $testValue = ['test' => true, 'timestamp' => time(), 'data' => range(1, 100)];
            
            // Test set
            $setStart = microtime(true);
            $setResult = $this->performanceService->set($testKey, $testValue, 60);
            $setTime = (microtime(true) - $setStart) * 1000;
            
            // Test get
            $getStart = microtime(true);
            $getResult = $this->performanceService->get($testKey);
            $getTime = (microtime(true) - $getStart) * 1000;
            
            // Test delete
            $deleteStart = microtime(true);
            $deleteResult = $this->performanceService->delete($testKey);
            $deleteTime = (microtime(true) - $deleteStart) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'set' => [
                        'success' => $setResult,
                        'time_ms' => round($setTime, 3)
                    ],
                    'get' => [
                        'success' => $getResult === $testValue,
                        'time_ms' => round($getTime, 3)
                    ],
                    'delete' => [
                        'success' => $deleteResult,
                        'time_ms' => round($deleteTime, 3)
                    ],
                    'total_time_ms' => round($setTime + $getTime + $deleteTime, 3)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get performance metrics
     */
    public function getMetrics()
    {
        try {
            $stats = $this->performanceService->getStats();
            
            // Calculate additional metrics
            $metrics = [
                'cache_hit_ratio' => $this->calculateHitRatio($stats),
                'average_response_time' => $this->calculateAverageResponseTime($stats),
                'memory_usage' => $this->getMemoryUsage(),
                'cache_efficiency' => $this->calculateCacheEfficiency($stats)
            ];
            
            return response()->json([
                'success' => true, 
                'data' => array_merge($stats, $metrics)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Calculate cache hit ratio
     */
    private function calculateHitRatio(array $stats): float
    {
        // This would be calculated from actual hit/miss statistics
        // For now, return a placeholder
        return 0.85; // 85% hit ratio
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime(array $stats): float
    {
        // This would be calculated from actual response time statistics
        return 45.5; // 45.5ms average
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'formatted_current' => $this->formatBytes(memory_get_usage(true)),
            'formatted_peak' => $this->formatBytes(memory_get_peak_usage(true))
        ];
    }

    /**
     * Calculate cache efficiency
     */
    private function calculateCacheEfficiency(array $stats): float
    {
        // This would be calculated from actual efficiency metrics
        return 0.92; // 92% efficiency
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
