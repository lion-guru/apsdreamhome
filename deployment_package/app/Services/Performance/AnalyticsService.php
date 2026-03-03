<?php
namespace App\Services\Performance;

use App\Services\Cache\RedisCacheService;
use App\Services\Database\DatabaseService;

class AnalyticsService
{
    private $cache;
    private $db;
    private $config;
    
    public function __construct()
    {
        $this->cache = new RedisCacheService();
        $this->db = new DatabaseService();
        $this->config = [
            'retention_days' => 30,
            'aggregation_intervals' => ['1m', '5m', '15m', '1h', '1d'],
            'metrics_to_track' => [
                'response_time', 'throughput', 'error_rate', 'cpu_usage',
                'memory_usage', 'disk_usage', 'network_io', 'active_users',
                'database_connections', 'cache_hit_rate', 'queue_size'
            ]
        ];
    }
    
    /**
     * Record performance metrics
     */
    public function recordMetrics($metrics)
    {
        $timestamp = time();
        $data = [
            'timestamp' => $timestamp,
            'date' => date('Y-m-d H:i:s', $timestamp),
            'metrics' => $metrics
        ];
        
        // Store in cache for real-time access
        $this->cache->set('performance:current', $data, 300);
        
        // Store in time series data
        $this->storeTimeSeriesData($data);
        
        // Store aggregated metrics
        $this->storeAggregatedMetrics($data);
        
        // Check for performance alerts
        $this->checkPerformanceAlerts($data);
        
        return true;
    }
    
    /**
     * Store time series data
     */
    private function storeTimeSeriesData($data)
    {
        $timestamp = $data['timestamp'];
        $metrics = $data['metrics'];
        
        foreach ($this->config['metrics_to_track'] as $metric) {
            if (isset($metrics[$metric])) {
                $key = "performance:timeseries:{$metric}";
                $value = $metrics[$metric];
                
                // Store in Redis sorted set for time series data
                $this->cache->zadd($key, $timestamp, json_encode([
                    'timestamp' => $timestamp,
                    'value' => $value
                ]));
                
                // Remove old data beyond retention period
                $cutoffTime = $timestamp - ($this->config['retention_days'] * 24 * 60 * 60);
                $this->cache->zremrangebyscore($key, 0, $cutoffTime);
            }
        }
    }
    
    /**
     * Store aggregated metrics
     */
    private function storeAggregatedMetrics($data)
    {
        $timestamp = $data['timestamp'];
        $metrics = $data['metrics'];
        
        foreach ($this->config['aggregation_intervals'] as $interval) {
            $bucketTime = $this->getBucketTime($timestamp, $interval);
            
            foreach ($this->config['metrics_to_track'] as $metric) {
                if (isset($metrics[$metric])) {
                    $key = "performance:aggregated:{$interval}:{$metric}:{$bucketTime}";
                    $value = $metrics[$metric];
                    
                    // Update aggregated values
                    $current = $this->cache->get($key) ?: [
                        'count' => 0,
                        'sum' => 0,
                        'min' => $value,
                        'max' => $value
                    ];
                    
                    $current['count']++;
                    $current['sum'] += $value;
                    $current['min'] = min($current['min'], $value);
                    $current['max'] = max($current['max'], $value);
                    
                    $this->cache->set($key, $current, $this->config['retention_days'] * 24 * 60 * 60);
                }
            }
        }
    }
    
    /**
     * Get bucket time for aggregation
     */
    private function getBucketTime($timestamp, $interval)
    {
        switch ($interval) {
            case '1m':
                return floor($timestamp / 60) * 60;
            case '5m':
                return floor($timestamp / 300) * 300;
            case '15m':
                return floor($timestamp / 900) * 900;
            case '1h':
                return floor($timestamp / 3600) * 3600;
            case '1d':
                return floor($timestamp / 86400) * 86400;
            default:
                return $timestamp;
        }
    }
    
    /**
     * Get performance metrics for time range
     */
    public function getMetrics($metric, $startTime, $endTime, $interval = '1m')
    {
        $key = "performance:timeseries:{$metric}";
        
        // Get data from Redis sorted set
        $data = $this->cache->zrangebyscore($key, $startTime, $endTime);
        
        $metrics = [];
        foreach ($data as $item) {
            $itemData = json_decode($item, true);
            $metrics[] = [
                'timestamp' => $itemData['timestamp'],
                'value' => $itemData['value']
            ];
        }
        
        // Aggregate by interval if needed
        if ($interval !== 'raw') {
            $metrics = $this->aggregateMetrics($metrics, $interval);
        }
        
        return $metrics;
    }
    
    /**
     * Aggregate metrics by interval
     */
    private function aggregateMetrics($metrics, $interval)
    {
        $aggregated = [];
        $intervalSeconds = $this->getIntervalSeconds($interval);
        
        foreach ($metrics as $metric) {
            $bucketTime = floor($metric['timestamp'] / $intervalSeconds) * $intervalSeconds;
            
            if (!isset($aggregated[$bucketTime])) {
                $aggregated[$bucketTime] = [
                    'timestamp' => $bucketTime,
                    'values' => [],
                    'count' => 0,
                    'sum' => 0,
                    'min' => $metric['value'],
                    'max' => $metric['value']
                ];
            }
            
            $aggregated[$bucketTime]['values'][] = $metric['value'];
            $aggregated[$bucketTime]['count']++;
            $aggregated[$bucketTime]['sum'] += $metric['value'];
            $aggregated[$bucketTime]['min'] = min($aggregated[$bucketTime]['min'], $metric['value']);
            $aggregated[$bucketTime]['max'] = max($aggregated[$bucketTime]['max'], $metric['value']);
        }
        
        // Calculate averages
        foreach ($aggregated as &$bucket) {
            $bucket['average'] = $bucket['sum'] / $bucket['count'];
            unset($bucket['values']);
        }
        
        return array_values($aggregated);
    }
    
    /**
     * Get interval in seconds
     */
    private function getIntervalSeconds($interval)
    {
        switch ($interval) {
            case '1m': return 60;
            case '5m': return 300;
            case '15m': return 900;
            case '1h': return 3600;
            case '1d': return 86400;
            default: return 60;
        }
    }
    
    /**
     * Get current performance metrics
     */
    public function getCurrentMetrics()
    {
        return $this->cache->get('performance:current') ?: [];
    }
    
    /**
     * Get performance summary
     */
    public function getPerformanceSummary($timeRange = 3600)
    {
        $endTime = time();
        $startTime = $endTime - $timeRange;
        
        $summary = [];
        
        foreach ($this->config['metrics_to_track'] as $metric) {
            $data = $this->getMetrics($metric, $startTime, $endTime);
            
            if (!empty($data)) {
                $values = array_column($data, 'value');
                
                $summary[$metric] = [
                    'current' => end($values),
                    'average' => array_sum($values) / count($values),
                    'min' => min($values),
                    'max' => max($values),
                    'p95' => $this->calculatePercentile($values, 95),
                    'p99' => $this->calculatePercentile($values, 99),
                    'trend' => $this->calculateTrend($values)
                ];
            }
        }
        
        return $summary;
    }
    
    /**
     * Calculate percentile
     */
    private function calculatePercentile($values, $percentile)
    {
        sort($values);
        $index = ($percentile / 100) * (count($values) - 1);
        
        if (floor($index) == $index) {
            return $values[$index];
        } else {
            $lower = $values[floor($index)];
            $upper = $values[ceil($index)];
            return $lower + (($upper - $lower) * ($index - floor($index)));
        }
    }
    
    /**
     * Calculate trend
     */
    private function calculateTrend($values)
    {
        if (count($values) < 2) {
            return 'stable';
        }
        
        $firstHalf = array_slice($values, 0, count($values) / 2);
        $secondHalf = array_slice($values, count($values) / 2);
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        $change = (($secondAvg - $firstAvg) / $firstAvg) * 100;
        
        if ($change > 5) {
            return 'increasing';
        } elseif ($change < -5) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }
    
    /**
     * Check performance alerts
     */
    private function checkPerformanceAlerts($data)
    {
        $metrics = $data['metrics'];
        $alerts = [];
        
        // Response time alert
        if (isset($metrics['response_time']) && $metrics['response_time'] > 1000) {
            $alerts[] = [
                'type' => 'response_time',
                'severity' => $metrics['response_time'] > 2000 ? 'critical' : 'warning',
                'message' => "Response time ({$metrics['response_time']}ms) is above threshold",
                'timestamp' => $data['timestamp']
            ];
        }
        
        // Error rate alert
        if (isset($metrics['error_rate']) && $metrics['error_rate'] > 5) {
            $alerts[] = [
                'type' => 'error_rate',
                'severity' => $metrics['error_rate'] > 10 ? 'critical' : 'warning',
                'message' => "Error rate ({$metrics['error_rate']}%) is above threshold",
                'timestamp' => $data['timestamp']
            ];
        }
        
        // CPU usage alert
        if (isset($metrics['cpu_usage']) && $metrics['cpu_usage'] > 80) {
            $alerts[] = [
                'type' => 'cpu_usage',
                'severity' => $metrics['cpu_usage'] > 90 ? 'critical' : 'warning',
                'message' => "CPU usage ({$metrics['cpu_usage']}%) is above threshold",
                'timestamp' => $data['timestamp']
            ];
        }
        
        // Memory usage alert
        if (isset($metrics['memory_usage']) && $metrics['memory_usage'] > 85) {
            $alerts[] = [
                'type' => 'memory_usage',
                'severity' => $metrics['memory_usage'] > 95 ? 'critical' : 'warning',
                'message' => "Memory usage ({$metrics['memory_usage']}%) is above threshold",
                'timestamp' => $data['timestamp']
            ];
        }
        
        // Store alerts
        if (!empty($alerts)) {
            foreach ($alerts as $alert) {
                $this->cache->lpush('performance:alerts', json_encode($alert));
                $this->cache->ltrim('performance:alerts', 0, 99); // Keep last 100 alerts
            }
        }
    }
    
    /**
     * Get recent alerts
     */
    public function getRecentAlerts($limit = 10)
    {
        $alerts = $this->cache->lrange('performance:alerts', 0, $limit - 1);
        
        return array_map(function($alert) {
            return json_decode($alert, true);
        }, $alerts);
    }
    
    /**
     * Get performance report
     */
    public function getPerformanceReport($timeRange = 3600)
    {
        $summary = $this->getPerformanceSummary($timeRange);
        $alerts = $this->getRecentAlerts();
        
        return [
            'summary' => $summary,
            'alerts' => $alerts,
            'time_range' => $timeRange,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Clean up old data
     */
    public function cleanup()
    {
        $cutoffTime = time() - ($this->config['retention_days'] * 24 * 60 * 60);
        
        foreach ($this->config['metrics_to_track'] as $metric) {
            $key = "performance:timeseries:{$metric}";
            $this->cache->zremrangebyscore($key, 0, $cutoffTime);
        }
        
        // Clean up aggregated data
        foreach ($this->config['aggregation_intervals'] as $interval) {
            foreach ($this->config['metrics_to_track'] as $metric) {
                $pattern = "performance:aggregated:{$interval}:{$metric}:*";
                $keys = $this->cache->keys($pattern);
                
                foreach ($keys as $key) {
                    $parts = explode(':', $key);
                    $bucketTime = end($parts);
                    
                    if ($bucketTime < $cutoffTime) {
                        $this->cache->del($key);
                    }
                }
            }
        }
    }
}
