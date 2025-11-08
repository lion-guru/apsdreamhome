<?php
/**
 * Performance Monitor & Analytics
 * Tracks application performance, cache usage, and system health
 */

namespace App\Core;

class PerformanceMonitor
{
    private static $instance = null;
    private $metrics = [];
    private $startTime;
    private $memoryStart;

    private function __construct()
    {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage(true);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Record a metric
     */
    public function record($key, $value, $tags = [])
    {
        $this->metrics[] = [
            'key' => $key,
            'value' => $value,
            'timestamp' => microtime(true),
            'tags' => $tags
        ];
    }

    /**
     * Record execution time for a function/block
     */
    public function time($key, callable $callback, $tags = [])
    {
        $start = microtime(true);
        $result = $callback();
        $executionTime = microtime(true) - $start;

        $this->record($key, $executionTime, array_merge($tags, ['type' => 'execution_time']));
        return $result;
    }

    /**
     * Record memory usage
     */
    public function memory($key, $tags = [])
    {
        $memory = memory_get_usage(true);
        $this->record($key, $memory, array_merge($tags, ['type' => 'memory']));
    }

    /**
     * Record cache hit/miss
     */
    public function cache($key, $hit, $tags = [])
    {
        $this->record($key, $hit ? 1 : 0, array_merge($tags, ['type' => 'cache', 'result' => $hit ? 'hit' : 'miss']));
    }

    /**
     * Record database query
     */
    public function query($query, $executionTime, $rows = null, $tags = [])
    {
        $this->record('db_query', $executionTime, array_merge($tags, [
            'type' => 'database',
            'query_type' => $this->getQueryType($query),
            'rows_affected' => $rows
        ]));
    }

    /**
     * Get current performance metrics
     */
    public function getMetrics()
    {
        return [
            'execution_time' => microtime(true) - $this->startTime,
            'memory_usage' => memory_get_usage(true) - $this->memoryStart,
            'memory_peak' => memory_get_peak_usage(true),
            'metrics_count' => count($this->metrics),
            'detailed_metrics' => $this->metrics
        ];
    }

    /**
     * Get cache performance statistics
     */
    public function getCacheStats()
    {
        $cacheMetrics = array_filter($this->metrics, function($metric) {
            return isset($metric['tags']['type']) && $metric['tags']['type'] === 'cache';
        });

        $hits = array_filter($cacheMetrics, function($metric) {
            return isset($metric['tags']['result']) && $metric['tags']['result'] === 'hit';
        });

        $misses = array_filter($cacheMetrics, function($metric) {
            return isset($metric['tags']['result']) && $metric['tags']['result'] === 'miss';
        });

        $total = count($cacheMetrics);
        $hitCount = count($hits);
        $missCount = count($misses);

        return [
            'total_requests' => $total,
            'hits' => $hitCount,
            'misses' => $missCount,
            'hit_ratio' => $total > 0 ? ($hitCount / $total) * 100 : 0
        ];
    }

    /**
     * Get database performance statistics
     */
    public function getDatabaseStats()
    {
        $dbMetrics = array_filter($this->metrics, function($metric) {
            return isset($metric['tags']['type']) && $metric['tags']['type'] === 'database';
        });

        if (empty($dbMetrics)) {
            return ['total_queries' => 0, 'avg_execution_time' => 0, 'slow_queries' => 0];
        }

        $executionTimes = array_column($dbMetrics, 'value');
        $slowQueries = array_filter($executionTimes, function($time) {
            return $time > 1.0; // Queries taking more than 1 second
        });

        return [
            'total_queries' => count($dbMetrics),
            'avg_execution_time' => array_sum($executionTimes) / count($executionTimes),
            'slow_queries' => count($slowQueries),
            'max_execution_time' => max($executionTimes)
        ];
    }

    /**
     * Export metrics to JSON for monitoring
     */
    public function exportMetrics()
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'performance' => $this->getMetrics(),
            'cache' => $this->getCacheStats(),
            'database' => $this->getDatabaseStats()
        ];
    }

    /**
     * Determine query type from SQL
     */
    private function getQueryType($query)
    {
        $query = strtolower(trim($query));

        if (strpos($query, 'select') === 0) {
            return 'SELECT';
        } elseif (strpos($query, 'insert') === 0) {
            return 'INSERT';
        } elseif (strpos($query, 'update') === 0) {
            return 'UPDATE';
        } elseif (strpos($query, 'delete') === 0) {
            return 'DELETE';
        } elseif (strpos($query, 'create') === 0) {
            return 'CREATE';
        } elseif (strpos($query, 'alter') === 0) {
            return 'ALTER';
        } elseif (strpos($query, 'drop') === 0) {
            return 'DROP';
        }

        return 'OTHER';
    }
}

/**
 * Global performance monitoring functions
 */
function performance()
{
    return PerformanceMonitor::getInstance();
}

function benchmark(callable $callback, $label = 'unnamed')
{
    return performance()->time($label, $callback);
}

function cache_hit($key)
{
    performance()->cache($key, true);
}

function cache_miss($key)
{
    performance()->cache($key, false);
}

?>
