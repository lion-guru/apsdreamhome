<?php
namespace App\Services\Monitoring;

use App\Services\Cache\RedisCacheService;

class MetricsCollectorService
{
    private $cache;
    private $metrics = [];
    private $config;
    
    public function __construct()
    {
        $this->cache = new RedisCacheService();
        $this->config = [
            'retention_period' => 3600, // 1 hour
            'collection_interval' => 30, // 30 seconds
            'max_data_points' => 120 // 2 hours worth of data at 30s intervals
        ];
    }
    
    /**
     * Collect system metrics
     */
    public function collectSystemMetrics()
    {
        $metrics = [
            'timestamp' => time(),
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'network_io' => $this->getNetworkIO(),
            'load_average' => $this->getLoadAverage(),
            'process_count' => $this->getProcessCount(),
            'uptime' => $this->getSystemUptime()
        ];
        
        $this->storeMetrics('system', $metrics);
        
        return $metrics;
    }
    
    /**
     * Collect application metrics
     */
    public function collectApplicationMetrics()
    {
        $metrics = [
            'timestamp' => time(),
            'active_users' => $this->getActiveUsers(),
            'request_count' => $this->getRequestCount(),
            'response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'throughput' => $this->getThroughput(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'database_connections' => $this->getDatabaseConnections(),
            'queue_size' => $this->getQueueSize()
        ];
        
        $this->storeMetrics('application', $metrics);
        
        return $metrics;
    }
    
    /**
     * Collect business metrics
     */
    public function collectBusinessMetrics()
    {
        $metrics = [
            'timestamp' => time(),
            'new_users' => $this->getNewUsers(),
            'active_properties' => $this->getActiveProperties(),
            'property_views' => $this->getPropertyViews(),
            'inquiries' => $this->getInquiries(),
            'conversions' => $this->getConversions(),
            'revenue' => $this->getRevenue(),
            'user_engagement' => $this->getUserEngagement()
        ];
        
        $this->storeMetrics('business', $metrics);
        
        return $metrics;
    }
    
    /**
     * Get CPU usage
     */
    private function getCpuUsage()
    {
        // Get CPU usage from /proc/loadavg (Linux) or WMI (Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->getWindowsCpuUsage();
        } else {
            return $this->getLinuxCpuUsage();
        }
    }
    
    /**
     * Get Linux CPU usage
     */
    private function getLinuxCpuUsage()
    {
        $load = sys_getloadavg();
        return $load ? round($load[0] * 100, 2) : 0;
    }
    
    /**
     * Get Windows CPU usage
     */
    private function getWindowsCpuUsage()
    {
        // Use WMI to get CPU usage (simplified)
        $output = shell_exec('wmic cpu get loadpercentage /value');
        
        if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
            return (float) $matches[1];
        }
        
        return 0;
    }
    
    /**
     * Get memory usage
     */
    private function getMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));
        
        if ($memoryLimit > 0) {
            return round(($memoryUsage / $memoryLimit) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * Get disk usage
     */
    private function getDiskUsage()
    {
        $totalSpace = disk_total_space(BASE_PATH);
        $freeSpace = disk_free_space(BASE_PATH);
        
        if ($totalSpace > 0) {
            $usedSpace = $totalSpace - $freeSpace;
            return round(($usedSpace / $totalSpace) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * Get network I/O
     */
    private function getNetworkIO()
    {
        // This would require system-specific implementation
        // For now, return a placeholder
        return rand(10, 50); // Placeholder
    }
    
    /**
     * Get load average
     */
    private function getLoadAverage()
    {
        $load = sys_getloadavg();
        return $load ? $load[0] : 0;
    }
    
    /**
     * Get process count
     */
    private function getProcessCount()
    {
        // This would require system-specific implementation
        return rand(50, 200); // Placeholder
    }
    
    /**
     * Get system uptime
     */
    private function getSystemUptime()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->getWindowsUptime();
        } else {
            return $this->getLinuxUptime();
        }
    }
    
    /**
     * Get Linux uptime
     */
    private function getLinuxUptime()
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = explode(' ', $uptime)[0];
        
        return round($seconds);
    }
    
    /**
     * Get Windows uptime
     */
    private function getWindowsUptime()
    {
        $output = shell_exec('wmic os get lastbootuptime /value');
        
        if (preg_match('/LastBootuptime=(.+)/', $output, $matches)) {
            $bootTime = $matches[1];
            $bootTimestamp = strtotime(substr($bootTime, 0, 14));
            
            return time() - $bootTimestamp;
        }
        
        return 0;
    }
    
    /**
     * Get active users
     */
    private function getActiveUsers()
    {
        // This would query your user session store
        return $this->cache->get('active_users_count') ?? 0;
    }
    
    /**
     * Get request count
     */
    private function getRequestCount()
    {
        return $this->cache->get('request_count') ?? 0;
    }
    
    /**
     * Get average response time
     */
    private function getAverageResponseTime()
    {
        $responseTimes = $this->cache->get('response_times') ?? [];
        
        if (empty($responseTimes)) {
            return 0;
        }
        
        return round(array_sum($responseTimes) / count($responseTimes), 2);
    }
    
    /**
     * Get error rate
     */
    private function getErrorRate()
    {
        $totalRequests = $this->getRequestCount();
        $errorCount = $this->cache->get('error_count') ?? 0;
        
        if ($totalRequests > 0) {
            return round(($errorCount / $totalRequests) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * Get throughput
     */
    private function getThroughput()
    {
        $requests = $this->cache->get('requests_per_minute') ?? [];
        
        return array_sum($requests);
    }
    
    /**
     * Get cache hit rate
     */
    private function getCacheHitRate()
    {
        $stats = $this->cache->getStats();
        
        return $stats['hit_rate'] ?? 0;
    }
    
    /**
     * Get database connections
     */
    private function getDatabaseConnections()
    {
        // This would query your database connection pool
        return rand(5, 20); // Placeholder
    }
    
    /**
     * Get queue size
     */
    private function getQueueSize()
    {
        // This would query your message queue
        return rand(10, 100); // Placeholder
    }
    
    /**
     * Get new users
     */
    private function getNewUsers()
    {
        $today = date('Y-m-d');
        return $this->cache->get('new_users_' . $today) ?? 0;
    }
    
    /**
     * Get active properties
     */
    private function getActiveProperties()
    {
        return $this->cache->get('active_properties_count') ?? 0;
    }
    
    /**
     * Get property views
     */
    private function getPropertyViews()
    {
        $today = date('Y-m-d');
        return $this->cache->get('property_views_' . $today) ?? 0;
    }
    
    /**
     * Get inquiries
     */
    private function getInquiries()
    {
        $today = date('Y-m-d');
        return $this->cache->get('inquiries_' . $today) ?? 0;
    }
    
    /**
     * Get conversions
     */
    private function getConversions()
    {
        $today = date('Y-m-d');
        return $this->cache->get('conversions_' . $today) ?? 0;
    }
    
    /**
     * Get revenue
     */
    private function getRevenue()
    {
        $today = date('Y-m-d');
        return $this->cache->get('revenue_' . $today) ?? 0;
    }
    
    /**
     * Get user engagement
     */
    private function getUserEngagement()
    {
        // This would calculate engagement based on user activity
        return rand(60, 90); // Placeholder
    }
    
    /**
     * Store metrics in cache
     */
    private function storeMetrics($type, $metrics)
    {
        $key = "metrics:{$type}";
        
        // Get existing metrics
        $existingMetrics = $this->cache->get($key) ?? [];
        
        // Add new metrics
        $existingMetrics[] = $metrics;
        
        // Keep only recent metrics
        if (count($existingMetrics) > $this->config['max_data_points']) {
            $existingMetrics = array_slice($existingMetrics, -$this->config['max_data_points']);
        }
        
        // Store in cache
        $this->cache->set($key, $existingMetrics, $this->config['retention_period']);
    }
    
    /**
     * Get metrics for chart
     */
    public function getMetricsForChart($type, $timeRange = 3600)
    {
        $key = "metrics:{$type}";
        $metrics = $this->cache->get($key) ?? [];
        
        // Filter by time range
        $cutoffTime = time() - $timeRange;
        $filteredMetrics = array_filter($metrics, function($metric) use ($cutoffTime) {
            return $metric['timestamp'] >= $cutoffTime;
        });
        
        return array_values($filteredMetrics);
    }
    
    /**
     * Get current metrics summary
     */
    public function getCurrentMetricsSummary()
    {
        $systemMetrics = $this->collectSystemMetrics();
        $appMetrics = $this->collectApplicationMetrics();
        $businessMetrics = $this->collectBusinessMetrics();
        
        return [
            'system' => $systemMetrics,
            'application' => $appMetrics,
            'business' => $businessMetrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Parse size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
    
    /**
     * Generate metrics report
     */
    public function generateMetricsReport($timeRange = 3600)
    {
        $systemMetrics = $this->getMetricsForChart('system', $timeRange);
        $appMetrics = $this->getMetricsForChart('application', $timeRange);
        $businessMetrics = $this->getMetricsForChart('business', $timeRange);
        
        $report = [
            'time_range' => $timeRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'system' => [
                'avg_cpu_usage' => $this->calculateAverage($systemMetrics, 'cpu_usage'),
                'avg_memory_usage' => $this->calculateAverage($systemMetrics, 'memory_usage'),
                'avg_disk_usage' => $this->calculateAverage($systemMetrics, 'disk_usage'),
                'max_load_average' => $this->calculateMax($systemMetrics, 'load_average')
            ],
            'application' => [
                'avg_response_time' => $this->calculateAverage($appMetrics, 'response_time'),
                'avg_error_rate' => $this->calculateAverage($appMetrics, 'error_rate'),
                'total_requests' => $this->calculateSum($appMetrics, 'request_count'),
                'avg_throughput' => $this->calculateAverage($appMetrics, 'throughput')
            ],
            'business' => [
                'total_new_users' => $this->calculateSum($businessMetrics, 'new_users'),
                'total_property_views' => $this->calculateSum($businessMetrics, 'property_views'),
                'total_inquiries' => $this->calculateSum($businessMetrics, 'inquiries'),
                'total_conversions' => $this->calculateSum($businessMetrics, 'conversions'),
                'total_revenue' => $this->calculateSum($businessMetrics, 'revenue')
            ]
        ];
        
        return $report;
    }
    
    /**
     * Calculate average of metric values
     */
    private function calculateAverage($metrics, $field)
    {
        if (empty($metrics)) {
            return 0;
        }
        
        $values = array_column($metrics, $field);
        return round(array_sum($values) / count($values), 2);
    }
    
    /**
     * Calculate maximum of metric values
     */
    private function calculateMax($metrics, $field)
    {
        if (empty($metrics)) {
            return 0;
        }
        
        $values = array_column($metrics, $field);
        return max($values);
    }
    
    /**
     * Calculate sum of metric values
     */
    private function calculateSum($metrics, $field)
    {
        if (empty($metrics)) {
            return 0;
        }
        
        $values = array_column($metrics, $field);
        return array_sum($values);
    }
}
