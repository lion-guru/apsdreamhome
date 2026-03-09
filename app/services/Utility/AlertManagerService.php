<?php

namespace App\Services\Utility;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Alert Manager Service
 * Handles comprehensive alert management with monitoring and notifications
 */
class AlertManagerService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $alertChannels = [];
    private array $alertTypes = [];
    private array $notificationQueue = [];

    // Alert types
    public const TYPE_SYSTEM = 'system';
    public const TYPE_SECURITY = 'security';
    public const TYPE_PERFORMANCE = 'performance';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_USER = 'user';

    // Alert priorities
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    // Alert statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'max_alerts_per_minute' => 100,
            'retry_failed_alerts' => true,
            'retry_attempts' => 3,
            'retry_delay' => 300, // 5 minutes
            'auto_acknowledge' => false,
            'alert_retention_days' => 30
        ], $config);
        
        $this->initializeAlertChannels();
        $this->initializeAlertTypes();
        $this->initializeAlertTables();
    }

    /**
     * Create alert
     */
    public function createAlert(string $type, string $message, array $data = [], int $priority = self::PRIORITY_NORMAL, array $channels = []): array
    {
        try {
            // Validate alert type
            if (!in_array($type, array_keys($this->alertTypes))) {
                return [
                    'success' => false,
                    'message' => 'Invalid alert type'
                ];
            }

            // Check rate limiting
            if (!$this->checkRateLimit($type)) {
                return [
                    'success' => false,
                    'message' => 'Rate limit exceeded for alert type'
                ];
            }

            // Create alert record
            $alertId = $this->createAlertRecord($type, $message, $data, $priority, $channels);

            // Queue for sending
            $this->queueAlertForSending($alertId);

            $this->logger->info("Alert created and queued", [
                'alert_id' => $alertId,
                'type' => $type,
                'priority' => $priority
            ]);

            return [
                'success' => true,
                'message' => 'Alert created successfully',
                'alert_id' => $alertId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to create alert", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create alert: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check system health and create alerts if needed
     */
    public function checkSystemHealth(): array
    {
        try {
            $healthIssues = [];
            $alertsCreated = 0;

            // Check database connection
            try {
                $this->db->fetchOne("SELECT 1");
            } catch (\Exception $e) {
                $healthIssues[] = [
                    'type' => self::TYPE_SYSTEM,
                    'message' => 'Database connection failed',
                    'priority' => self::PRIORITY_CRITICAL,
                    'data' => ['error' => $e->getMessage()]
                ];
            }

            // Check disk space
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage['percentage'] > 90) {
                $healthIssues[] = [
                    'type' => self::TYPE_SYSTEM,
                    'message' => 'Disk space critically low',
                    'priority' => self::PRIORITY_HIGH,
                    'data' => $diskUsage
                ];
            }

            // Check memory usage
            $memoryUsage = $this->getMemoryUsage();
            if ($memoryUsage['percentage'] > 85) {
                $healthIssues[] = [
                    'type' => self::TYPE_PERFORMANCE,
                    'message' => 'Memory usage high',
                    'priority' => self::PRIORITY_MEDIUM,
                    'data' => $memoryUsage
                ];
            }

            // Check error logs
            $recentErrors = $this->getRecentErrors();
            if (count($recentErrors) > 10) {
                $healthIssues[] = [
                    'type' => self::TYPE_SYSTEM,
                    'message' => 'High error rate detected',
                    'priority' => self::PRIORITY_HIGH,
                    'data' => ['error_count' => count($recentErrors)]
                ];
            }

            // Create alerts for health issues
            foreach ($healthIssues as $issue) {
                $result = $this->createAlert(
                    $issue['type'],
                    $issue['message'],
                    $issue['data'],
                    $issue['priority']
                );

                if ($result['success']) {
                    $alertsCreated++;
                }
            }

            return [
                'success' => true,
                'message' => "System health check completed",
                'issues_found' => count($healthIssues),
                'alerts_created' => $alertsCreated,
                'health_issues' => $healthIssues
            ];

        } catch (\Exception $e) {
            $this->logger->error("System health check failed", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'System health check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send alert notifications
     */
    public function sendAlertNotifications(): array
    {
        try {
            $processed = 0;
            $sent = 0;
            $failed = 0;
            $errors = [];

            // Get pending alerts
            $sql = "SELECT * FROM alert_queue 
                    WHERE status = ? 
                    ORDER BY priority DESC, created_at ASC 
                    LIMIT 50";
            
            $pendingAlerts = $this->db->fetchAll($sql, [self::STATUS_PENDING]);

            foreach ($pendingAlerts as $alert) {
                try {
                    $result = $this->sendSingleAlert($alert);
                    
                    if ($result['success']) {
                        $this->updateAlertQueueStatus($alert['id'], self::STATUS_SENT);
                        $sent++;
                    } else {
                        $this->updateAlertQueueStatus($alert['id'], self::STATUS_FAILED, $result['message']);
                        $failed++;
                        $errors[] = "Alert ID {$alert['id']}: {$result['message']}";
                    }
                    
                    $processed++;
                    
                } catch (\Exception $e) {
                    $this->updateAlertQueueStatus($alert['id'], self::STATUS_FAILED, $e->getMessage());
                    $failed++;
                    $errors[] = "Alert ID {$alert['id']}: {$e->getMessage()}";
                    $processed++;
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} alert notifications",
                'processed' => $processed,
                'sent' => $sent,
                'failed' => $failed,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to send alert notifications", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get alert statistics
     */
    public function getAlertStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total alerts
            $sql = "SELECT COUNT(*) as total FROM alerts";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stats['total_alerts'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Alerts by type
            $typeSql = "SELECT type, COUNT(*) as count FROM alerts";
            $typeParams = [];
            
            if (!empty($filters['date_from'])) {
                $typeSql .= " WHERE created_at >= ?";
                $typeParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $typeSql .= (empty($typeParams) ? " WHERE" : " AND") . " created_at <= ?";
                $typeParams[] = $filters['date_to'];
            }
            
            $typeSql .= " GROUP BY type";
            
            $typeStats = $this->db->fetchAll($typeSql, $typeParams);
            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['type']] = $stat['count'];
            }

            // Alerts by priority
            $prioritySql = "SELECT priority, COUNT(*) as count FROM alerts";
            $priorityParams = [];
            
            if (!empty($filters['date_from'])) {
                $prioritySql .= " WHERE created_at >= ?";
                $priorityParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $prioritySql .= (empty($priorityParams) ? " WHERE" : " AND") . " created_at <= ?";
                $priorityParams[] = $filters['date_to'];
            }
            
            $prioritySql .= " GROUP BY priority";
            
            $priorityStats = $this->db->fetchAll($prioritySql, $priorityParams);
            $stats['by_priority'] = [];
            foreach ($priorityStats as $stat) {
                $stats['by_priority'][$stat['priority']] = $stat['count'];
            }

            // Recent alerts
            $recentSql = "SELECT * FROM alerts ORDER BY created_at DESC LIMIT 20";
            $stats['recent_alerts'] = $this->db->fetchAll($recentSql);

            // Queue status
            $queueStats = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM alert_queue GROUP BY status");
            $stats['queue_status'] = [];
            foreach ($queueStats as $stat) {
                $stats['queue_status'][$stat['status']] = $stat['count'];
            }

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get alert stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(int $alertId, string $acknowledgedBy = ''): array
    {
        try {
            $sql = "UPDATE alerts 
                    SET status = ?, acknowledged_at = NOW(), acknowledged_by = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [self::STATUS_ACKNOWLEDGED, $acknowledgedBy, $alertId]);

            $this->logger->info("Alert acknowledged", [
                'alert_id' => $alertId,
                'acknowledged_by' => $acknowledgedBy
            ]);

            return [
                'success' => true,
                'message' => 'Alert acknowledged successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to acknowledge alert", [
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to acknowledge alert: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retry failed alerts
     */
    public function retryFailedAlerts(): array
    {
        try {
            $sql = "SELECT * FROM alert_queue 
                    WHERE status = ? AND retry_count < ? 
                    AND (last_retry_at IS NULL OR last_retry_at < DATE_SUB(NOW(), INTERVAL ? SECOND)) 
                    ORDER BY created_at ASC 
                    LIMIT 20";
            
            $failedAlerts = $this->db->fetchAll($sql, [
                self::STATUS_FAILED,
                $this->config['retry_attempts'],
                $this->config['retry_delay']
            ]);

            $retried = 0;
            $successCount = 0;
            $failureCount = 0;

            foreach ($failedAlerts as $alert) {
                try {
                    // Update retry count and timestamp
                    $this->db->execute(
                        "UPDATE alert_queue SET retry_count = retry_count + 1, last_retry_at = NOW() WHERE id = ?",
                        [$alert['id']]
                    );

                    // Try to send again
                    $result = $this->sendSingleAlert($alert);
                    
                    if ($result['success']) {
                        $this->updateAlertQueueStatus($alert['id'], self::STATUS_SENT);
                        $successCount++;
                    } else {
                        $failureCount++;
                    }
                    
                    $retried++;
                    
                } catch (\Exception $e) {
                    $failureCount++;
                    $retried++;
                }
            }

            return [
                'success' => true,
                'message' => "Retried {$retried} failed alerts",
                'retried' => $retried,
                'success_count' => $successCount,
                'failure_count' => $failureCount
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to retry alerts", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to retry alerts: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clean old alerts
     */
    public function cleanOldAlerts(int $days = 30): array
    {
        try {
            $sql = "DELETE FROM alerts WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $deletedRows = $this->db->execute($sql, [$days]);

            $sql = "DELETE FROM alert_queue WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $deletedQueueRows = $this->db->execute($sql, [$days]);

            $this->logger->info("Old alerts cleaned", [
                'days' => $days,
                'deleted_alerts' => $deletedRows,
                'deleted_queue_items' => $deletedQueueRows
            ]);

            return [
                'success' => true,
                'message' => "Cleaned alerts older than {$days} days",
                'deleted_alerts' => $deletedRows,
                'deleted_queue_items' => $deletedQueueRows
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to clean old alerts", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to clean old alerts: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeAlertChannels(): void
    {
        $this->alertChannels = [
            'email' => [
                'enabled' => true,
                'class' => 'EmailChannel',
                'config' => []
            ],
            'sms' => [
                'enabled' => true,
                'class' => 'SmsChannel',
                'config' => []
            ],
            'push' => [
                'enabled' => false,
                'class' => 'PushChannel',
                'config' => []
            ],
            'webhook' => [
                'enabled' => false,
                'class' => 'WebhookChannel',
                'config' => []
            ]
        ];
    }

    private function initializeAlertTypes(): void
    {
        $this->alertTypes = [
            self::TYPE_SYSTEM => [
                'name' => 'System Alert',
                'default_channels' => ['email'],
                'auto_acknowledge' => false
            ],
            self::TYPE_SECURITY => [
                'name' => 'Security Alert',
                'default_channels' => ['email', 'sms'],
                'auto_acknowledge' => false
            ],
            self::TYPE_PERFORMANCE => [
                'name' => 'Performance Alert',
                'default_channels' => ['email'],
                'auto_acknowledge' => true
            ],
            self::TYPE_BUSINESS => [
                'name' => 'Business Alert',
                'default_channels' => ['email'],
                'auto_acknowledge' => false
            ],
            self::TYPE_USER => [
                'name' => 'User Alert',
                'default_channels' => ['push'],
                'auto_acknowledge' => true
            ]
        ];
    }

    private function initializeAlertTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                data JSON,
                priority INT NOT NULL DEFAULT 2,
                status ENUM('pending', 'sent', 'failed', 'acknowledged') DEFAULT 'pending',
                acknowledged_by VARCHAR(255),
                acknowledged_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_priority (priority),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS alert_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_id INT NOT NULL,
                channel VARCHAR(50) NOT NULL,
                recipient VARCHAR(255),
                message TEXT NOT NULL,
                priority INT NOT NULL DEFAULT 2,
                status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
                error_message TEXT,
                retry_count INT DEFAULT 0,
                last_retry_at TIMESTAMP NULL,
                sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE,
                INDEX idx_status (status),
                INDEX idx_priority (priority),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function createAlertRecord(string $type, string $message, array $data, int $priority, array $channels): string
    {
        $sql = "INSERT INTO alerts (type, message, data, priority, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        
        $this->db->execute($sql, [$type, $message, json_encode($data), $priority]);
        
        return $this->db->lastInsertId();
    }

    private function queueAlertForSending(string $alertId): void
    {
        $alert = $this->db->fetchOne("SELECT * FROM alerts WHERE id = ?", [$alertId]);
        
        if ($alert) {
            $channels = !empty($alert['channels']) ? json_decode($alert['channels'], true) : 
                        $this->alertTypes[$alert['type']]['default_channels'] ?? ['email'];
            
            foreach ($channels as $channel) {
                if (isset($this->alertChannels[$channel]) && $this->alertChannels[$channel]['enabled']) {
                    $sql = "INSERT INTO alert_queue (alert_id, channel, message, priority, status, created_at) 
                            VALUES (?, ?, ?, ?, 'pending', NOW())";
                    
                    $this->db->execute($sql, [$alertId, $channel, $alert['message'], $alert['priority']]);
                }
            }
        }
    }

    private function sendSingleAlert(array $alert): array
    {
        // Mock implementation - would integrate with actual notification channels
        try {
            // Simulate sending
            usleep(100000); // 0.1 second delay to simulate network call
            
            $this->logger->info("Alert sent via channel", [
                'alert_id' => $alert['alert_id'],
                'channel' => $alert['channel']
            ]);
            
            return [
                'success' => true,
                'message' => 'Alert sent successfully'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send alert: ' . $e->getMessage()
            ];
        }
    }

    private function updateAlertQueueStatus(int $queueId, string $status, string $errorMessage = null): void
    {
        $sql = "UPDATE alert_queue 
                SET status = ?, error_message = ?, sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END 
                WHERE id = ?";
        
        $this->db->execute($sql, [$status, $errorMessage, $status, $queueId]);
    }

    private function checkRateLimit(string $type): bool
    {
        $sql = "SELECT COUNT(*) as count FROM alerts 
                WHERE type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        
        $count = $this->db->fetchOne($sql, [$type]) ?? 0;
        
        return $count < ($this->config['max_alerts_per_minute'] ?? 100);
    }

    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        
        return [
            'total' => $totalSpace,
            'free' => $freeSpace,
            'used' => $totalSpace - $freeSpace,
            'percentage' => $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0
        ];
    }

    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        // Get system memory if available
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        return [
            'current' => $memoryUsage,
            'peak' => $memoryPeak,
            'limit' => $memoryLimit,
            'percentage' => $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0
        ];
    }

    private function getRecentErrors(): array
    {
        // Mock implementation - would check actual error logs
        return [];
    }

    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }
        
        return $value;
    }
}
