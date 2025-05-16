<?php
/**
 * Rate Limit Analytics Manager
 * Tracks and analyzes API rate limiting patterns and violations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';

class RateLimitAnalytics {
    private $logger;
    private $con;
    private $cache;
    private $config;

    public function __construct($database_connection = null) {
        $this->con = $database_connection ?? getDbConnection();
        $this->logger = new SecurityLogger();
        $this->initializeCache();
        $this->loadConfig();
        $this->ensureTables();
    }

    /**
     * Initialize Redis cache
     */
    private function initializeCache() {
        try {
            $this->cache = new Redis();
            $this->cache->connect(
                getenv('REDIS_HOST') ?: 'localhost',
                getenv('REDIS_PORT') ?: 6379
            );
        } catch (Exception $e) {
            $this->logger->warning('Redis connection failed, using database fallback');
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
        // Rate limit events table
        $this->con->query("
            CREATE TABLE IF NOT EXISTS rate_limit_events (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                // SECURITY: Sensitive information removed_id VARCHAR(36) NOT NULL,
                endpoint VARCHAR(255) NOT NULL,
                requests INT NOT NULL,
                limit_value INT NOT NULL,
                window_size INT NOT NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_// SECURITY: Sensitive information removed (// SECURITY: Sensitive information removed_id),
                INDEX idx_endpoint (endpoint),
                INDEX idx_timestamp (timestamp)
            )
        ");

        // Rate limit violations table
        $this->con->query("
            CREATE TABLE IF NOT EXISTS rate_limit_violations (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                // SECURITY: Sensitive information removed_id VARCHAR(36) NOT NULL,
                endpoint VARCHAR(255) NOT NULL,
                requests INT NOT NULL,
                limit_value INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                action_taken VARCHAR(50) NULL,
                resolved BOOLEAN DEFAULT FALSE,
                resolution_notes TEXT NULL,
                INDEX idx_// SECURITY: Sensitive information removed (// SECURITY: Sensitive information removed_id),
                INDEX idx_endpoint (endpoint),
                INDEX idx_timestamp (timestamp)
            )
        ");
    }

    /**
     * Record rate limit event
     */
    public function recordEvent($apiKeyId, $endpoint, $requests, $limit, $windowSize) {
        try {
            // Record in database
            $stmt = $this->con->prepare("
                INSERT INTO rate_limit_events 
                (// SECURITY: Sensitive information removed_id, endpoint, requests, limit_value, window_size)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param('ssiii', 
                $apiKeyId, $endpoint, $requests, $limit, $windowSize
            );
            $stmt->execute();

            // Update real-time metrics in cache
            if ($this->cache) {
                $this->updateCacheMetrics($apiKeyId, $endpoint, $requests, $limit);
            }

            // Check for threshold alerts
            $this->checkThresholds($apiKeyId, $endpoint, $requests, $limit);

        } catch (Exception $e) {
            $this->logger->error('Failed to record rate limit event', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record rate limit violation
     */
    public function recordViolation($apiKeyId, $endpoint, $requests, $limit, $ipAddress) {
        try {
            $stmt = $this->con->prepare("
                INSERT INTO rate_limit_violations 
                (// SECURITY: Sensitive information removed_id, endpoint, requests, limit_value, ip_address)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param('ssiis', 
                $apiKeyId, $endpoint, $requests, $limit, $ipAddress
            );
            $stmt->execute();

            // Check violation patterns
            $this->checkViolationPatterns($apiKeyId);

        } catch (Exception $e) {
            $this->logger->error('Failed to record rate limit violation', [
                'error' => $e->getMessage()
            ]);
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
            $this->logger->warning('Rate limit threshold reached', [
                '// SECURITY: Sensitive information removed' => $apiKeyId,
                'endpoint' => $endpoint,
                'utilization' => $utilization
            ]);

            // Trigger webhook event if available
            if (class_exists('WebhookManager')) {
                global $webhookManager;
                $webhookManager->triggerEvent('rate_limit.threshold', [
                    '// SECURITY: Sensitive information removed' => $apiKeyId,
                    'endpoint' => $endpoint,
                    'requests' => $requests,
                    'limit' => $limit,
                    'utilization' => $utilization
                ]);
            }
        }
    }

    /**
     * Check violation patterns
     */
    private function checkViolationPatterns($apiKeyId) {
        // Count recent violations
        $stmt = $this->con->prepare("
            SELECT COUNT(*) as violations
            FROM rate_limit_violations
            WHERE // SECURITY: Sensitive information removed_id = ?
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $stmt->bind_param('s', $apiKeyId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['violations'] >= $this->config['violation_threshold']) {
            $this->logger->alert('Excessive rate limit violations', [
                '// SECURITY: Sensitive information removed' => $apiKeyId,
                'violations' => $result['violations']
            ]);

            // Take action (e.g., temporary suspension)
            $this->handleExcessiveViolations($apiKeyId);
        }
    }

    /**
     * Handle excessive violations
     */
    private function handleExcessiveViolations($apiKeyId) {
        // Update all unresolved violations
        $stmt = $this->con->prepare("
            UPDATE rate_limit_violations
            SET action_taken = 'temporary_suspension',
                resolved = TRUE,
                resolution_notes = 'Automatic suspension due to excessive violations'
            WHERE // SECURITY: Sensitive information removed_id = ?
            AND resolved = FALSE
        ");
        
        $stmt->bind_param('s', $apiKeyId);
        $stmt->execute();

        // Trigger webhook event if available
        if (class_exists('WebhookManager')) {
            global $webhookManager;
            $webhookManager->triggerEvent('rate_limit.excessive_violations', [
                '// SECURITY: Sensitive information removed' => $apiKeyId,
                'action' => 'temporary_suspension'
            ]);
        }
    }

    /**
     * Get rate limit trends
     */
    public function getRateLimitTrends($interval = 'hour', $limit = 24) {
        $intervalSeconds = $this->config['aggregation_intervals'][$interval];
        
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
                COUNT(DISTINCT // SECURITY: Sensitive information removed_id) as affected_users,
                COUNT(DISTINCT endpoint) as affected_endpoints
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY period
            ORDER BY period DESC
            LIMIT ?
        ";

        $stmt = $this->con->prepare($sql);
        $days = ceil(($intervalSeconds * $limit) / 86400);
        $stmt->bind_param('ssii', $interval, $interval, $days, $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
                COUNT(DISTINCT // SECURITY: Sensitive information removed_id) as unique_users
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY endpoint
            ORDER BY events DESC
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param('i', $days);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get user rate limit metrics
     */
    public function getUserMetrics($days = 7) {
        $sql = "
            SELECT 
                // SECURITY: Sensitive information removed_id,
                COUNT(*) as events,
                SUM(CASE WHEN requests >= limit_value THEN 1 ELSE 0 END) as violations,
                AVG(requests / limit_value) as avg_utilization,
                COUNT(DISTINCT endpoint) as endpoints_affected
            FROM rate_limit_events
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY // SECURITY: Sensitive information removed_id
            ORDER BY events DESC
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param('i', $days);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        
        if ($apiKeyId) {
            $sql .= " AND // SECURITY: Sensitive information removed_id = ?";
        }
        
        $sql .= " ORDER BY timestamp DESC LIMIT ?";
        
        $stmt = $this->con->prepare($sql);
        
        if ($apiKeyId) {
            $stmt->bind_param('si', $apiKeyId, $limit);
        } else {
            $stmt->bind_param('i', $limit);
        }
        
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param('i', $minutes);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Clean up old data
     */
    public function cleanupOldData() {
        $days = $this->config['retention_days'];
        
        // Clean up events
        $stmt = $this->con->prepare("
            DELETE FROM rate_limit_events 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->bind_param('i', $days);
        $stmt->execute();
        
        // Clean up resolved violations
        $stmt = $this->con->prepare("
            DELETE FROM rate_limit_violations 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND resolved = TRUE
        ");
        $stmt->bind_param('i', $days);
        $stmt->execute();
        
        $this->logger->info('Cleaned up old rate limit data');
    }
}

// Create global rate limit analytics instance
$rateLimitAnalytics = new RateLimitAnalytics($con ?? null);

