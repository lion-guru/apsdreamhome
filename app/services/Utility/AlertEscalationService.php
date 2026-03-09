<?php

namespace App\Services\Utility;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Alert Escalation Service
 * Handles alert escalation with proper MVC patterns and security
 */
class AlertEscalationService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $escalationRules = [];

    // Alert severity levels
    public const SEVERITY_LOW = 1;
    public const SEVERITY_MEDIUM = 2;
    public const SEVERITY_HIGH = 3;
    public const SEVERITY_CRITICAL = 4;

    // Escalation statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_ESCALATED = 'escalated';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'max_escalation_levels' => 5,
            'escalation_timeout' => 3600, // 1 hour
            'auto_escalate' => true,
            'notification_channels' => ['email', 'sms', 'push']
        ], $config);
        
        $this->initializeEscalationRules();
        $this->initializeEscalationTables();
    }

    /**
     * Create alert
     */
    public function createAlert(string $type, string $message, array $data = [], int $severity = self::SEVERITY_LOW): array
    {
        try {
            $alertId = $this->createAlertRecord($type, $message, $data, $severity);
            
            // Process escalation rules
            $escalationResult = $this->processEscalationRules($alertId, $type, $data, $severity);
            
            // Send notifications
            $this->sendNotifications($alertId, $type, $message, $data, $severity);

            $this->logger->info("Alert created and processed", [
                'alert_id' => $alertId,
                'type' => $type,
                'severity' => $severity,
                'escalated' => $escalationResult['escalated']
            ]);

            return [
                'success' => true,
                'message' => 'Alert created successfully',
                'alert_id' => $alertId,
                'escalation' => $escalationResult
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
     * Process alert escalations
     */
    public function processEscalations(): array
    {
        try {
            $processed = 0;
            $escalated = 0;
            $errors = [];

            // Get pending escalations
            $sql = "SELECT * FROM alert_escalations 
                    WHERE status = ? AND next_escalation_at <= NOW() 
                    ORDER BY created_at ASC";
            
            $pendingEscalations = $this->db->fetchAll($sql, [self::STATUS_PENDING]);

            foreach ($pendingEscalations as $escalation) {
                try {
                    $result = $this->processSingleEscalation($escalation);
                    
                    if ($result['escalated']) {
                        $escalated++;
                    }
                    
                    $processed++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Escalation ID {$escalation['id']}: {$e->getMessage()}";
                    $this->logger->error("Failed to process escalation", [
                        'escalation_id' => $escalation['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} escalations",
                'processed' => $processed,
                'escalated' => $escalated,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to process escalations", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to process escalations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get alert by ID
     */
    public function getAlert(int $id): ?array
    {
        try {
            $sql = "SELECT a.*, 
                           (SELECT COUNT(*) FROM alert_escalations ae WHERE ae.alert_id = a.id) as escalation_count,
                           (SELECT MAX(ae.escalation_level) FROM alert_escalations ae WHERE ae.alert_id = a.id) as max_escalation_level
                    FROM alerts a 
                    WHERE a.id = ?";
            
            $alert = $this->db->fetchOne($sql, [$id]);
            
            if ($alert) {
                $alert['data'] = json_decode($alert['data'] ?? '{}', true) ?? [];
                $alert['escalations'] = $this->getAlertEscalations($id);
            }
            
            return $alert;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get alert", ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get alerts by filters
     */
    public function getAlerts(array $filters = []): array
    {
        try {
            $sql = "SELECT a.*, 
                           (SELECT COUNT(*) FROM alert_escalations ae WHERE ae.alert_id = a.id) as escalation_count,
                           (SELECT MAX(ae.escalation_level) FROM alert_escalations ae WHERE ae.alert_id = a.id) as max_escalation_level
                    FROM alerts a 
                    WHERE 1=1";
            
            $params = [];

            // Add filters
            if (!empty($filters['type'])) {
                $sql .= " AND a.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['severity'])) {
                $sql .= " AND a.severity = ?";
                $params[] = $filters['severity'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND a.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND a.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY a.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $alerts = $this->db->fetchAll($sql, $params);
            
            foreach ($alerts as &$alert) {
                $alert['data'] = json_decode($alert['data'] ?? '{}', true) ?? [];
            }
            
            return $alerts;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get alerts", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(int $alertId, string $resolution = ''): array
    {
        try {
            $sql = "UPDATE alerts 
                    SET status = ?, resolved_at = NOW(), resolution = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [self::STATUS_RESOLVED, $resolution, $alertId]);

            // Update escalations
            $this->updateEscalationsStatus($alertId, self::STATUS_RESOLVED);

            $this->logger->info("Alert resolved", [
                'alert_id' => $alertId,
                'resolution' => $resolution
            ]);

            return [
                'success' => true,
                'message' => 'Alert resolved successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to resolve alert", [
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Dismiss alert
     */
    public function dismissAlert(int $alertId, string $reason = ''): array
    {
        try {
            $sql = "UPDATE alerts 
                    SET status = ?, dismissed_at = NOW(), dismissal_reason = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [self::STATUS_DISMISSED, $reason, $alertId]);

            // Update escalations
            $this->updateEscalationsStatus($alertId, self::STATUS_DISMISSED);

            $this->logger->info("Alert dismissed", [
                'alert_id' => $alertId,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Alert dismissed successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to dismiss alert", [
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to dismiss alert: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get escalation statistics
     */
    public function getEscalationStats(array $filters = []): array
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
            
            $stats['total_alerts'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Alerts by status
            $statusSql = "SELECT status, COUNT(*) as count FROM alerts";
            $statusParams = [];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Alerts by severity
            $severitySql = "SELECT severity, COUNT(*) as count FROM alerts";
            $severityParams = [];
            
            if (!empty($filters['date_from'])) {
                $severitySql .= " WHERE created_at >= ?";
                $severityParams[] = $filters['date_from'];
            }
            
            $severitySql .= " GROUP BY severity";
            
            $severityStats = $this->db->fetchAll($severitySql, $severityParams);
            $stats['by_severity'] = [];
            foreach ($severityStats as $stat) {
                $stats['by_severity'][$stat['severity']] = $stat['count'];
            }

            // Escalation metrics
            $escalationSql = "SELECT 
                        COUNT(*) as total_escalations,
                        AVG(escalation_level) as avg_level,
                        MAX(escalation_level) as max_level
                    FROM alert_escalations";
            
            $escalationParams = [];
            
            if (!empty($filters['date_from'])) {
                $escalationSql .= " WHERE created_at >= ?";
                $escalationParams[] = $filters['date_from'];
            }
            
            $escalationStats = $this->db->fetchOne($escalationSql, $escalationParams);
            $stats['escalation_metrics'] = $escalationStats ?? [
                'total_escalations' => 0,
                'avg_level' => 0,
                'max_level' => 0
            ];

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get escalation stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Add escalation rule
     */
    public function addEscalationRule(string $name, array $conditions, array $actions): array
    {
        try {
            $sql = "INSERT INTO escalation_rules 
                    (name, conditions, actions, enabled, created_at) 
                    VALUES (?, ?, ?, 1, NOW())";
            
            $this->db->execute($sql, [
                $name,
                json_encode($conditions),
                json_encode($actions)
            ]);

            $ruleId = $this->db->lastInsertId();

            $this->logger->info("Escalation rule added", [
                'rule_id' => $ruleId,
                'name' => $name
            ]);

            return [
                'success' => true,
                'message' => 'Escalation rule added successfully',
                'rule_id' => $ruleId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to add escalation rule", [
                'name' => $name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add escalation rule: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeEscalationTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                data JSON,
                severity INT NOT NULL DEFAULT 1,
                status ENUM('pending', 'escalated', 'resolved', 'dismissed') DEFAULT 'pending',
                resolution TEXT,
                dismissal_reason TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP NULL,
                dismissed_at TIMESTAMP NULL,
                INDEX idx_type (type),
                INDEX idx_severity (severity),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS alert_escalations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_id INT NOT NULL,
                escalation_level INT NOT NULL,
                escalated_to VARCHAR(255),
                escalation_reason TEXT,
                status ENUM('pending', 'escalated', 'resolved', 'dismissed') DEFAULT 'pending',
                next_escalation_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE,
                INDEX idx_alert_id (alert_id),
                INDEX idx_status (status),
                INDEX idx_next_escalation (next_escalation_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS escalation_rules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                conditions JSON NOT NULL,
                actions JSON NOT NULL,
                enabled BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_enabled (enabled)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function initializeEscalationRules(): void
    {
        // Default escalation rules
        $defaultRules = [
            'critical_immediate' => [
                'conditions' => ['severity' => self::SEVERITY_CRITICAL],
                'actions' => ['immediate_escalation' => true, 'notify_all' => true]
            ],
            'high_escalation' => [
                'conditions' => ['severity' => self::SEVERITY_HIGH],
                'actions' => ['escalation_timeout' => 1800, 'max_level' => 3]
            ],
            'auto_resolve' => [
                'conditions' => ['type' => 'system_error', 'auto_resolve' => true],
                'actions' => ['auto_resolve_timeout' => 7200]
            ]
        ];

        foreach ($defaultRules as $name => $rule) {
            $sql = "INSERT IGNORE INTO escalation_rules (name, conditions, actions) VALUES (?, ?, ?)";
            $this->db->execute($sql, [$name, json_encode($rule['conditions']), json_encode($rule['actions'])]);
        }
    }

    private function createAlertRecord(string $type, string $message, array $data, int $severity): string
    {
        $sql = "INSERT INTO alerts (type, message, data, severity, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        
        $this->db->execute($sql, [$type, $message, json_encode($data), $severity]);
        
        return $this->db->lastInsertId();
    }

    private function processEscalationRules(string $alertId, string $type, array $data, int $severity): array
    {
        $escalated = false;
        $escalationLevel = 1;

        // Get applicable rules
        $sql = "SELECT * FROM escalation_rules WHERE enabled = 1";
        $rules = $this->db->fetchAll($sql);

        foreach ($rules as $rule) {
            $conditions = json_decode($rule['conditions'], true) ?? [];
            $actions = json_decode($rule['actions'], true) ?? [];

            if ($this->matchesConditions($conditions, $type, $data, $severity)) {
                if (!empty($actions['immediate_escalation'])) {
                    $this->createEscalation($alertId, $escalationLevel, 'immediate', 'Immediate escalation rule triggered');
                    $escalated = true;
                }
                
                if (!empty($actions['notify_all'])) {
                    $this->notifyAllChannels($alertId, $type, $data, $severity);
                }
            }
        }

        return ['escalated' => $escalated, 'level' => $escalationLevel];
    }

    private function matchesConditions(array $conditions, string $type, array $data, int $severity): bool
    {
        foreach ($conditions as $key => $value) {
            switch ($key) {
                case 'severity':
                    if ($severity !== $value) return false;
                    break;
                case 'type':
                    if ($type !== $value) return false;
                    break;
                case 'auto_resolve':
                    if (!isset($data[$key]) || $data[$key] !== $value) return false;
                    break;
            }
        }
        
        return true;
    }

    private function createEscalation(string $alertId, int $level, string $escalatedTo, string $reason): void
    {
        $nextEscalation = date('Y-m-d H:i:s', time() + $this->config['escalation_timeout']);
        
        $sql = "INSERT INTO alert_escalations 
                (alert_id, escalation_level, escalated_to, escalation_reason, status, next_escalation_at, created_at) 
                VALUES (?, ?, ?, ?, 'escalated', ?, NOW())";
        
        $this->db->execute($sql, [$alertId, $level, $escalatedTo, $reason, $nextEscalation]);
        
        // Update alert status
        $this->db->execute("UPDATE alerts SET status = 'escalated', updated_at = NOW() WHERE id = ?", [$alertId]);
    }

    private function sendNotifications(string $alertId, string $type, string $message, array $data, int $severity): void
    {
        // Mock notification implementation
        $this->logger->info("Alert notifications sent", [
            'alert_id' => $alertId,
            'type' => $type,
            'severity' => $severity,
            'channels' => $this->config['notification_channels']
        ]);
    }

    private function notifyAllChannels(string $alertId, string $type, array $data, int $severity): void
    {
        // Mock implementation for notifying all channels
        $this->logger->critical("All channels notified", [
            'alert_id' => $alertId,
            'type' => $type,
            'severity' => $severity
        ]);
    }

    private function processSingleEscalation(array $escalation): array
    {
        $escalated = false;
        $currentLevel = $escalation['escalation_level'];
        $maxLevel = $this->config['max_escalation_levels'];

        if ($currentLevel < $maxLevel) {
            $newLevel = $currentLevel + 1;
            $escalatedTo = "level_{$newLevel}_manager";
            $reason = "Auto-escalation from level {$currentLevel}";

            $this->createEscalation($escalation['alert_id'], $newLevel, $escalatedTo, $reason);
            $escalated = true;

            // Update current escalation status
            $this->db->execute(
                "UPDATE alert_escalations SET status = 'escalated' WHERE id = ?",
                [$escalation['id']]
            );
        }

        return ['escalated' => $escalated, 'new_level' => $escalated ? $currentLevel + 1 : $currentLevel];
    }

    private function getAlertEscalations(int $alertId): array
    {
        $sql = "SELECT * FROM alert_escalations WHERE alert_id = ? ORDER BY escalation_level ASC";
        return $this->db->fetchAll($sql, [$alertId]);
    }

    private function updateEscalationsStatus(int $alertId, string $status): void
    {
        $sql = "UPDATE alert_escalations SET status = ? WHERE alert_id = ?";
        $this->db->execute($sql, [$status, $alertId]);
    }
}
