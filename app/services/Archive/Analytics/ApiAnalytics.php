<?php

namespace App\Services\Legacy\Analytics;

use App\Core\App;
use Exception;
use Redis;

/**
 * API Analytics Manager
 * Tracks and analyzes API usage patterns and performance metrics
 */
class ApiAnalytics {
    private $logger;
    private $db;
    private $cache;
    private $config;

    public function __construct() {
        $this->db = App::database();
        // Use a generic logger or the one from App if available
        $this->logger = App::make('logger') ?? new \App\Services\Legacy\SecurityLogger();
        $this->initializeCache();
        $this->loadConfig();
    }

    /**
     * Initialize Redis cache
     */
    private function initializeCache() {
        try {
            if (class_exists('Redis')) {
                $this->cache = new Redis();
                $this->cache->connect(
                    getenv('REDIS_HOST') ?: 'localhost',
                    getenv('REDIS_PORT') ?: 6379
                );
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Redis connection failed, using database fallback');
            }
            $this->cache = null;
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig() {
        $this->config = [
            'metrics_retention_days' => 90,
            'cache_ttl' => 300, // 5 minutes
            'aggregation_intervals' => [
                'minute' => 60,
                'hour' => 3600,
                'day' => 86400
            ]
        ];
    }

    /**
     * Record API request
     */
    public function recordRequest($endpoint, $method, $apiKey, $responseTime, $statusCode, $ipAddress) {
        try {
            // Record in database
            $sql = "INSERT INTO api_requests (
                    endpoint, method, api_key_id, response_time, status_code,
                    ip_address, timestamp
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $this->db->execute($sql, [
                $endpoint, $method, $apiKey, $responseTime, $statusCode, $ipAddress
            ]);

            // Update real-time metrics in cache
            if ($this->cache) {
                $this->updateCacheMetrics($endpoint, $method, $responseTime, $statusCode);
            }

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to record API request', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Update cache metrics
     */
    private function updateCacheMetrics($endpoint, $method, $responseTime, $statusCode) {
        $timestamp = time();
        $minute = floor($timestamp / 60) * 60;

        // Increment request counters
        $this->cache->incr("api:requests:total:{$minute}");
        $this->cache->incr("api:requests:endpoint:{$endpoint}:{$minute}");
        $this->cache->incr("api:requests:method:{$method}:{$minute}");

        // Track response times
        $this->cache->zAdd(
            "api:response_times:{$endpoint}:{$minute}",
            $responseTime,
            \App\Helpers\SecurityHelper::generateRandomString(16, false)
        );

        // Track status codes
        $this->cache->incr("api:status:{$statusCode}:{$minute}");

        // Set expiry for all keys
        $this->cache->expire("api:requests:total:{$minute}", 86400);
        $this->cache->expire("api:requests:endpoint:{$endpoint}:{$minute}", 86400);
        $this->cache->expire("api:requests:method:{$method}:{$minute}", 86400);
        $this->cache->expire("api:response_times:{$endpoint}:{$minute}", 86400);
        $this->cache->expire("api:status:{$statusCode}:{$minute}", 86400);
    }

    /**
     * Get request volume metrics
     */
    public function getRequestVolume($interval = 'hour', $limit = 24) {
        $intervalSeconds = $this->config['aggregation_intervals'][$interval] ?? 3600;

        $sql = "
            SELECT
                DATE_FORMAT(timestamp,
                    CASE
                        WHEN ? = 'minute' THEN '%Y-%m-%d %H:%i:00'
                        WHEN ? = 'hour' THEN '%Y-%m-%d %H:00:00'
                        ELSE '%Y-%m-%d 00:00:00'
                    END
                ) as period,
                COUNT(*) as requests,
                COUNT(DISTINCT api_key_id) as unique_users,
                COUNT(DISTINCT endpoint) as unique_endpoints,
                AVG(response_time) as avg_response_time,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors
            FROM api_requests
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY period
            ORDER BY period DESC
            LIMIT ?
        ";

        $days = ceil(($intervalSeconds * $limit) / 86400);
        return $this->db->fetchAll($sql, [$interval, $interval, $days, $limit]);
    }

    /**
     * Get endpoint performance metrics
     */
    public function getEndpointMetrics($days = 7) {
        $sql = "
            SELECT
                endpoint,
                COUNT(*) as requests,
                AVG(response_time) as avg_response_time,
                MAX(response_time) as max_response_time,
                MIN(response_time) as min_response_time,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors,
                COUNT(DISTINCT api_key_id) as unique_users
            FROM api_requests
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY endpoint
            ORDER BY requests DESC
        ";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Get user activity metrics
     */
    public function getUserMetrics($days = 7) {
        $sql = "
            SELECT
                api_key_id,
                COUNT(*) as requests,
                COUNT(DISTINCT endpoint) as unique_endpoints,
                AVG(response_time) as avg_response_time,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors
            FROM api_requests
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY api_key_id
            ORDER BY requests DESC
        ";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Get error metrics
     */
    public function getErrorMetrics($days = 7) {
        $sql = "
            SELECT
                status_code,
                COUNT(*) as count,
                COUNT(DISTINCT api_key_id) as affected_users,
                COUNT(DISTINCT endpoint) as affected_endpoints
            FROM api_requests
            WHERE
                timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND status_code >= 400
            GROUP BY status_code
            ORDER BY count DESC
        ";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics($minutes = 5) {
        if (!$this->cache) {
            return $this->getFallbackRealTimeMetrics($minutes);
        }

        $now = time();
        $metrics = [];

        for ($i = 0; $i < $minutes; $i++) {
            $minute = floor(($now - ($i * 60)) / 60) * 60;

            $metrics[] = [
                'timestamp' => date('Y-m-d H:i:s', $minute),
                'requests' => (int)$this->cache->get("api:requests:total:{$minute}") ?: 0,
                'response_times' => $this->getResponseTimeStats($minute),
                'status_codes' => $this->getStatusCodeStats($minute)
            ];
        }

        return $metrics;
    }

    /**
     * Get response time statistics for a specific minute
     */
    private function getResponseTimeStats($minute) {
        $times = $this->cache->zRange("api:response_times:*:{$minute}", 0, -1, true);

        if (empty($times)) {
            return ['avg' => 0, 'min' => 0, 'max' => 0];
        }

        return [
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times)
        ];
    }

    /**
     * Get status code statistics for a specific minute
     */
    private function getStatusCodeStats($minute) {
        $stats = [];
        $pattern = "api:status:*:{$minute}";

        $keys = $this->cache->keys($pattern);
        if ($keys) {
            foreach ($keys as $key) {
                preg_match('/api:status:(\d+):/', $key, $matches);
                if (isset($matches[1])) {
                    $code = $matches[1];
                    $stats[$code] = (int)$this->cache->get($key);
                }
            }
        }

        return $stats;
    }

    /**
     * Fallback method for real-time metrics when cache is unavailable
     */
    private function getFallbackRealTimeMetrics($minutes) {
        $sql = "
            SELECT
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:00') as timestamp,
                COUNT(*) as requests,
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as errors
            FROM api_requests
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            GROUP BY timestamp
            ORDER BY timestamp DESC
        ";

        return $this->db->fetchAll($sql, [$minutes]);
    }

    /**
     * Clean up old metrics
     */
    public function cleanupOldMetrics() {
        $days = $this->config['metrics_retention_days'];

        $sql = "DELETE FROM api_requests
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";

        $this->db->execute($sql, [$days]);

        if ($this->logger) {
            $this->logger->info('Cleaned up old API metrics');
        }
    }
}
