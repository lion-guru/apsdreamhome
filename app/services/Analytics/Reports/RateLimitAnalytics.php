<?php

// S467: the namespace did not match the file path, so PSR-4 could never
// resolve this class - App\Services\Analytics\Reports\RateLimitAnalytics
// maps to app/Services/Analytics/Reports/RateLimitAnalytics.php, while the
// class declared App\Services\Legacy\Analytics.  The class was
// unreachable by BOTH names, i.e. dead code that could never run.
namespace App\Services\Analytics\Reports;

use App\Core\App;
use Exception;
use Redis;

/**
 * Rate Limit Analytics Manager
 * Tracks and analyzes API rate limiting patterns and violations
 */
class RateLimitAnalytics {
    private $logger;
    private $db;
    private $cache;
    private $config;

    public function __construct() {
        $this->db = App::database();
        // S467: `App::make()` does not exist on App\Core\App, so this line
        // raised "Call to undefined method App\Core\App::make()" and the
        // constructor - and therefore the whole service - was unreachable.
        // The calls below use warning()/error()/info()/alert()/log(), so the
        // stand-in implements exactly those.
        $this->logger = new class {
            private function write(string $level, $msg, array $ctx = []): void {
                error_log('[RateLimitAnalytics] ' . $level . ': ' . (is_scalar($msg) ? (string)$msg : json_encode($msg))
                    . ($ctx ? ' ' . json_encode($ctx) : ''));
            }
            public function log($msg, array $ctx = []): void { $this->write('LOG', $msg, $ctx); }
            public function warning($msg, array $ctx = []): void { $this->write('WARNING', $msg, $ctx); }
            public function error($msg, array $ctx = []): void { $this->write('ERROR', $msg, $ctx); }
            public function info($msg, array $ctx = []): void { $this->write('INFO', $msg, $ctx); }
            public function alert($msg, array $ctx = []): void { $this->write('ALERT', $msg, $ctx); }
        };
        $this->initializeCache();
        $this->loadConfig();
        $this->ensureTables();
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
            'retention_days' => 90,
            'cache_ttl' => 300, // 5 minutes
            'alert_threshold' => 0.8, // 80% of limit
            'violation_threshold' => 5, // Violations before action
            'aggregation_intervals' => [
                'minute' => 60,
                'hour' => 3600,
                'day' => 86400
            ]
        ];
    }

    /**
     * Ensure required tables exist
     */
    private function ensureTables() {
        // S467: the two CREATE TABLE statements were MISSING from this
        // method (a bulk edit had stripped them), so it executed the string
        // "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4" as SQL -> a syntax error
        // -> the constructor threw -> the service could never be built.
        //
        // The tables are created by the idempotent migration
        // database/migrations/create_rate_limit_analytics_tables_s467.php.
        // Here we only VERIFY them and say so if they are missing.
        foreach (['rate_limit_events', 'rate_limit_violations'] as $table) {
            try {
                $row = $this->db->fetchOne(
                    "SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                    [$table]
                );
                if ((int)($row['c'] ?? 0) === 0) {
                    $this->logger->warning("Table {$table} is missing - run database/migrations/create_rate_limit_analytics_tables_s467.php");
                }
            } catch (Exception $e) {
                $this->logger->warning("Could not verify {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Record rate limit event
     */
    public function recordEvent($apiKeyId, $endpoint, $requests, $limit, $windowSize) {
        try {
            // Record in database
            $sql = "INSERT INTO rate_limit_events
                    (api_key_id, endpoint, requests, limit_value, window_size)
                    VALUES (?, ?, ?, ?, ?)";

            $this->db->execute($sql, [$apiKeyId, $endpoint, $requests, $limit, $windowSize]);

            // Update real-time metrics in cache
            if ($this->cache) {
                $this->updateCacheMetrics($apiKeyId, $endpoint, $requests, $limit);
            }

            // Check for threshold alerts
            $this->checkThresholds($apiKeyId, $endpoint, $requests, $limit);

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to record rate limit event', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Record rate limit violation
     */
    public function recordViolation($apiKeyId, $endpoint, $requests, $limit, $ipAddress) {
        try {
            $sql = "INSERT INTO rate_limit_violations
                    (api_key_id, endpoint, requests, limit_value, ip_address)
                    VALUES (?, ?, ?, ?, ?)";

            $this->db->execute($sql, [$apiKeyId, $endpoint, $requests, $limit, $ipAddress]);

            // Check violation patterns
            $this->checkViolationPatterns($apiKeyId);

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to record rate limit violation', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Update cache metrics
     */
    private function updateCacheMetrics($apiKeyId, $endpoint, $requests, $limit) {
        $timestamp = time();
        $minute = floor($timestamp / 60) * 60;

        // Track requests per minute
        $this->cache->incrBy("rate:requests:{$apiKeyId}:{$endpoint}:{$minute}", $requests);

        // Track utilization
        $utilization = $requests / $limit;
        $this->cache->zAdd(
            "rate:utilization:{$minute}",
            $utilization,
            "{$apiKeyId}:{$endpoint}"
        );

        // Set expiry
        $this->cache->expire("rate:requests:{$apiKeyId}:{$endpoint}:{$minute}", 86400);
        $this->cache->expire("rate:utilization:{$minute}", 86400);
    }

    /**
     * Check rate limit thresholds
     */
    private function checkThresholds($apiKeyId, $endpoint, $requests, $limit) {
        $utilization = $requests / $limit;

        if ($utilization >= $this->config['alert_threshold']) {
            if ($this->logger) {
                $this->logger->warning('Rate limit threshold reached', [
                    'apiKeyId' => $apiKeyId,
                    'endpoint' => $endpoint,
                    'utilization' => $utilization
                ]);
            }

            // Trigger webhook event if available
            if (class_exists('App\Services\Legacy\WebhookManager')) {
                $webhookManager = App::make('webhook_manager');
                if ($webhookManager) {
                    $webhookManager->triggerEvent('rate_limit.threshold', [
                        'apiKeyId' => $apiKeyId,
                        'endpoint' => $endpoint,
                        'requests' => $requests,
                        'limit' => $limit,
                        'utilization' => $utilization
                    ]);
                }
            }
        }
    }

    /**
     * Check violation patterns
     */
    private function checkViolationPatterns($apiKeyId) {
        $sql = "SELECT COUNT(*) as violations
                FROM rate_limit_violations
                WHERE api_key_id = ?
                AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $result = $this->db->fetchOne($sql, [$apiKeyId]);

        if ($result && $result['violations'] >= $this->config['violation_threshold']) {
            if ($this->logger) {
                $this->logger->alert('Excessive rate limit violations', [
                    'api_key_id' => $apiKeyId,
                    'violations' => $result['violations']
                ]);
            }

            // Take action (e.g., temporary suspension)
            $this->handleExcessiveViolations($apiKeyId);
        }
    }

    /**
     * Handle excessive violations
     */
    private function handleExcessiveViolations($apiKeyId) {
        // Update all unresolved violations
        $sql = "UPDATE rate_limit_violations
                SET action_taken = 'temporary_suspension',
                    resolved = TRUE,
                    resolution_notes = 'Automatic suspension due to excessive violations'
                WHERE api_key_id = ?
                AND resolved = FALSE";

        $this->db->execute($sql, [$apiKeyId]);

        // Trigger webhook event if available
        if (class_exists('App\Services\Legacy\WebhookManager')) {
            $webhookManager = App::make('webhook_manager');
            if ($webhookManager) {
                $webhookManager->triggerEvent('rate_limit.excessive_violations', [
                    'api_key_id' => $apiKeyId,
                    'action' => 'temporary_suspension'
                ]);
            }
        }
    }

    /**
     * Get rate limit trends
     */
    public function getRateLimitTrends($interval = 'hour', $limit = 24) {
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
                COUNT(*) as events,
                SUM(CASE WHEN requests >= limit_value THEN 1 ELSE 0 END) as violations,
                AVG(requests / limit_value) as avg_utilization,
                COUNT(DISTINCT api_key_id) as affected_users,
                COUNT(DISTINCT endpoint) as affected_endpoints
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY period
            ORDER BY period DESC
            LIMIT ?
        ";

        $days = ceil(($intervalSeconds * $limit) / 86400);
        return $this->db->fetchAll($sql, [$interval, $interval, $days, $limit]);
    }

    /**
     * Get endpoint rate limit metrics
     */
    public function getEndpointMetrics($days = 7) {
        $sql = "
            SELECT
                endpoint,
                COUNT(*) as events,
                SUM(CASE WHEN requests >= limit_value THEN 1 ELSE 0 END) as violations,
                AVG(requests / limit_value) as avg_utilization,
                MAX(requests / limit_value) as max_utilization,
                COUNT(DISTINCT api_key_id) as unique_users
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY endpoint
            ORDER BY events DESC
        ";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Get user rate limit metrics
     */
    public function getUserMetrics($days = 7) {
        $sql = "
            SELECT
                api_key_id,
                COUNT(*) as events,
                SUM(CASE WHEN requests >= limit_value THEN 1 ELSE 0 END) as violations,
                AVG(requests / limit_value) as avg_utilization,
                COUNT(DISTINCT endpoint) as endpoints_affected
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY api_key_id
            ORDER BY events DESC
        ";

        return $this->db->fetchAll($sql, [$days]);
    }

    /**
     * Get violation history
     */
    public function getViolationHistory($apiKeyId = null, $limit = 100) {
        $sql = "
            SELECT *
            FROM rate_limit_violations
            WHERE 1=1
        ";

        $params = [];
        if ($apiKeyId) {
            $sql .= " AND api_key_id = ?";
            $params[] = $apiKeyId;
        }

        $sql .= " ORDER BY timestamp DESC LIMIT ?";
        $params[] = (int)$limit;

        return $this->db->fetchAll($sql, $params);
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

            // Get high utilization endpoints
            $highUtilization = $this->cache->zRangeByScore(
                "rate:utilization:{$minute}",
                $this->config['alert_threshold'],
                1,
                ['WITHSCORES' => true]
            );

            $metrics[] = [
                'timestamp' => date('Y-m-d H:i:s', $minute),
                'high_utilization' => $highUtilization
            ];
        }

        return $metrics;
    }

    /**
     * Get fallback real-time metrics
     */
    private function getFallbackRealTimeMetrics($minutes) {
        $sql = "
            SELECT
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:00') as period,
                COUNT(*) as events,
                SUM(CASE WHEN requests >= limit_value THEN 1 ELSE 0 END) as violations,
                AVG(requests / limit_value) as avg_utilization
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            GROUP BY period
            ORDER BY period DESC
        ";

        return $this->db->fetchAll($sql, [$minutes]);
    }

    /**
     * Clean up old data
     */
    public function cleanupOldData() {
        $days = $this->config['retention_days'];

        // Clean up events
        $this->db->execute("
            DELETE FROM rate_limit_events
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)
        ", [$days]);

        // Clean up resolved violations
        $this->db->execute("
            DELETE FROM rate_limit_violations
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND resolved = TRUE
        ", [$days]);

        if ($this->logger) {
            $this->logger->info('Cleaned up old rate limit data');
        }
    }
}
