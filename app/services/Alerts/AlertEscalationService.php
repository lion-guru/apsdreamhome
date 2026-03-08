<?php

namespace App\Services\Alerts;

use App\Core\Database\Database;
use App\Services\LoggingService;
use App\Services\NotificationService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Alert Escalation Service - APS Dream Home
 * Modern alert management with escalation levels and notifications
 * Custom MVC implementation without Laravel dependencies
 */
class AlertEscalationService
{
    private $database;
    private $logger;
    private $notificationService;
    
    // Alert levels
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_EMERGENCY = 'emergency';
    
    // Escalation levels
    const ESCALATION_LEVEL_1 = 1; // 15 minutes
    const ESCALATION_LEVEL_2 = 2; // 30 minutes
    const ESCALATION_LEVEL_3 = 3; // 60 minutes
    const ESCALATION_LEVEL_4 = 4; // 120 minutes
    
    private $escalationTimeouts = [
        self::ESCALATION_LEVEL_1 => 900,   // 15 minutes
        self::ESCALATION_LEVEL_2 => 1800,  // 30 minutes
        self::ESCALATION_LEVEL_3 => 3600,  // 60 minutes
        self::ESCALATION_LEVEL_4 => 7200   // 120 minutes
    ];
    
    private $escalationRecipients = [
        self::ESCALATION_LEVEL_1 => ['assigned_user'],
        self::ESCALATION_LEVEL_2 => ['team_lead'],
        self::ESCALATION_LEVEL_3 => ['department_head'],
        self::ESCALATION_LEVEL_4 => ['all_admins']
    ];

    public function __construct($database = null, $logger = null, $notificationService = null)
    {
        $this->database = $database ?: Database::getInstance();
        $this->logger = $logger ?: LoggingService::getInstance();
        $this->notificationService = $notificationService ?: new NotificationService();
        $this->createAlertTables();
    }

    /**
     * Create alert management tables
     */
    private function createAlertTables()
    {
        try {
            // Alerts table
            $sql = "CREATE TABLE IF NOT EXISTS alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_id VARCHAR(100) NOT NULL UNIQUE,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                level ENUM('info','warning','critical','emergency') DEFAULT 'info',
                source VARCHAR(100),
                category VARCHAR(100),
                status ENUM('active','acknowledged','resolved','escalated') DEFAULT 'active',
                priority INT DEFAULT 2,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                acknowledged_at TIMESTAMP NULL,
                acknowledged_by BIGINT(20) UNSIGNED NULL,
                resolved_at TIMESTAMP NULL,
                resolved_by BIGINT(20) UNSIGNED NULL,
                metadata JSON,
                FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
            )";
            $this->database->query($sql);

            // Alert escalations table
            $sql = "CREATE TABLE IF NOT EXISTS alert_escalations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_id VARCHAR(100) NOT NULL,
                escalation_level INT DEFAULT 1,
                escalated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                escalated_by BIGINT(20) UNSIGNED NULL,
                timeout_minutes INT DEFAULT 15,
                status ENUM('pending','acknowledged','timeout','escalated') DEFAULT 'pending',
                notified_users JSON,
                response_required BOOLEAN DEFAULT TRUE,
                FOREIGN KEY (escalated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_alert_escalations_alert_id (alert_id),
                INDEX idx_alert_escalations_status (status)
            )";
            $this->database->query($sql);

        } catch (Exception $e) {
            $this->logger->log("Error creating alert tables: " . $e->getMessage(), 'error', 'alerts');
            throw new RuntimeException("Failed to create alert tables: " . $e->getMessage());
        }
    }

    /**
     * Create a new alert
     */
    public function createAlert($title, $description = '', $level = self::LEVEL_INFO, $source = null, $category = null, $metadata = [])
    {
        if (empty($title)) {
            throw new InvalidArgumentException('Alert title is required');
        }

        $alertId = $this->generateAlertId();
        
        $sql = "INSERT INTO alerts (alert_id, title, description, level, source, category, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->database->execute($sql, [
                $alertId,
                $title,
                $description,
                $level,
                $source,
                $category,
                json_encode($metadata)
            ]);
            
            $this->logger->log("Alert created: $title (ID: $alertId, Level: $level)", 'info', 'alerts');
            
            // Start escalation if critical or emergency
            if (in_array($level, [self::LEVEL_CRITICAL, self::LEVEL_EMERGENCY])) {
                $this->startEscalation($alertId);
            }
            
            return $alertId;
            
        } catch (Exception $e) {
            $this->logger->log("Error creating alert: " . $e->getMessage(), 'error', 'alerts');
            throw new RuntimeException("Failed to create alert: " . $e->getMessage());
        }
    }

    /**
     * Start escalation for an alert
     */
    public function startEscalation($alertId)
    {
        try {
            $sql = "INSERT INTO alert_escalations (alert_id, escalation_level, timeout_minutes, notified_users)
                    VALUES (?, ?, ?, ?)";
            
            $notifiedUsers = json_encode($this->getEscalationRecipients(self::ESCALATION_LEVEL_1));
            $timeout = $this->escalationTimeouts[self::ESCALATION_LEVEL_1];
            
            $this->database->execute($sql, [
                $alertId,
                self::ESCALATION_LEVEL_1,
                $timeout,
                $notifiedUsers
            ]);
            
            // Send initial notifications
            $this->sendEscalationNotification($alertId, self::ESCALATION_LEVEL_1);
            
            $this->logger->log("Escalation started for alert: $alertId", 'info', 'alerts');
            
        } catch (Exception $e) {
            $this->logger->log("Error starting escalation for alert $alertId: " . $e->getMessage(), 'error', 'alerts');
            throw new RuntimeException("Failed to start escalation: " . $e->getMessage());
        }
    }

    /**
     * Process pending escalations
     */
    public function processEscalations()
    {
        $processed = 0;
        
        try {
            // Find timed out escalations
            $sql = "SELECT ae.*, a.title, a.description, a.level
                    FROM alert_escalations ae
                    JOIN alerts a ON ae.alert_id = a.alert_id
                    WHERE ae.status = 'pending' 
                    AND ae.escalated_at < DATE_SUB(NOW(), INTERVAL ae.timeout_minutes MINUTE)";
            
            $escalations = $this->database->fetchAll($sql);
            
            foreach ($escalations as $escalation) {
                $this->escalateAlert($escalation['alert_id'], $escalation['escalation_level']);
                $processed++;
            }
            
            $this->logger->log("Processed $processed timed out escalations", 'info', 'alerts');
            
        } catch (Exception $e) {
            $this->logger->log("Error processing escalations: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $processed;
    }

    /**
     * Escalate an alert to the next level
     */
    private function escalateAlert($alertId, $currentLevel)
    {
        $nextLevel = $currentLevel + 1;
        
        if ($nextLevel > self::ESCALATION_LEVEL_4) {
            // Maximum escalation reached
            $this->markEscalationTimeout($alertId, $currentLevel);
            return;
        }
        
        try {
            // Mark current escalation as escalated
            $sql = "UPDATE alert_escalations SET status = 'escalated' 
                    WHERE alert_id = ? AND escalation_level = ?";
            $this->database->execute($sql, [$alertId, $currentLevel]);
            
            // Create new escalation
            $sql = "INSERT INTO alert_escalations (alert_id, escalation_level, timeout_minutes, notified_users)
                    VALUES (?, ?, ?, ?)";
            
            $notifiedUsers = json_encode($this->getEscalationRecipients($nextLevel));
            $timeout = $this->escalationTimeouts[$nextLevel];
            
            $this->database->execute($sql, [$alertId, $nextLevel, $timeout, $notifiedUsers]);
            
            // Update alert status
            $sql = "UPDATE alerts SET status = 'escalated' WHERE alert_id = ?";
            $this->database->execute($sql, [$alertId]);
            
            // Send escalation notification
            $this->sendEscalationNotification($alertId, $nextLevel);
            
            $this->logger->log("Alert escalated: $alertId to level $nextLevel", 'warning', 'alerts');
            
        } catch (Exception $e) {
            $this->logger->log("Error escalating alert $alertId: " . $e->getMessage(), 'error', 'alerts');
        }
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledgeAlert($alertId, $userId)
    {
        try {
            $sql = "UPDATE alerts 
                    SET status = 'acknowledged', acknowledged_at = NOW(), acknowledged_by = ?
                    WHERE alert_id = ?";
            
            $this->database->execute($sql, [$userId, $alertId]);
            
            // Stop escalations
            $sql = "UPDATE alert_escalations SET status = 'acknowledged' 
                    WHERE alert_id = ? AND status = 'pending'";
            $this->database->execute($sql, [$alertId]);
            
            $this->logger->log("Alert acknowledged: $alertId by user $userId", 'info', 'alerts');
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->log("Error acknowledging alert $alertId: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }

    /**
     * Resolve an alert
     */
    public function resolveAlert($alertId, $userId, $resolution = '')
    {
        try {
            $sql = "UPDATE alerts 
                    SET status = 'resolved', resolved_at = NOW(), resolved_by = ?, description = ?
                    WHERE alert_id = ?";
            
            $this->database->execute($sql, [$userId, $resolution, $alertId]);
            
            // Stop escalations
            $sql = "UPDATE alert_escalations SET status = 'acknowledged' 
                    WHERE alert_id = ? AND status = 'pending'";
            $this->database->execute($sql, [$alertId]);
            
            $this->logger->log("Alert resolved: $alertId by user $userId", 'info', 'alerts');
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->log("Error resolving alert $alertId: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }

    /**
     * Get alerts by status
     */
    public function getAlertsByStatus($status = 'active', $limit = 50, $offset = 0)
    {
        $sql = "SELECT a.*, u1.name as acknowledged_by_name, u2.name as resolved_by_name
                FROM alerts a
                LEFT JOIN users u1 ON a.acknowledged_by = u1.id
                LEFT JOIN users u2 ON a.resolved_by = u2.id
                WHERE a.status = ?
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";
        
        try {
            return $this->database->fetchAll($sql, [$status, (int)$limit, (int)$offset]);
        } catch (Exception $e) {
            $this->logger->log("Error fetching alerts: " . $e->getMessage(), 'error', 'alerts');
            return [];
        }
    }

    /**
     * Get alert statistics
     */
    public function getAlertStats()
    {
        $stats = [];
        
        try {
            // Total alerts by status
            $sql = "SELECT status, COUNT(*) as count FROM alerts GROUP BY status";
            $results = $this->database->fetchAll($sql);
            $stats['by_status'] = [];
            foreach ($results as $row) {
                $stats['by_status'][$row['status']] = $row['count'];
            }
            
            // Total alerts by level
            $sql = "SELECT level, COUNT(*) as count FROM alerts GROUP BY level";
            $results = $this->database->fetchAll($sql);
            $stats['by_level'] = [];
            foreach ($results as $row) {
                $stats['by_level'][$row['level']] = $row['count'];
            }
            
            // Recent alerts (24 hours)
            $sql = "SELECT COUNT(*) as count FROM alerts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $result = $this->database->fetchOne($sql);
            $stats['recent_24h'] = $result['count'] ?? 0;
            
            // Pending escalations
            $sql = "SELECT COUNT(*) as count FROM alert_escalations WHERE status = 'pending'";
            $result = $this->database->fetchOne($sql);
            $stats['pending_escalations'] = $result['count'] ?? 0;
            
        } catch (Exception $e) {
            $this->logger->log("Error fetching alert stats: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $stats;
    }

    /**
     * Send escalation notification
     */
    private function sendEscalationNotification($alertId, $escalationLevel)
    {
        try {
            $alert = $this->getAlert($alertId);
            if (!$alert) return;
            
            $recipients = $this->getEscalationRecipients($escalationLevel);
            $subject = "Alert Escalation: {$alert['title']} (Level $escalationLevel)";
            $message = "Alert has been escalated to level $escalationLevel\n\n";
            $message .= "Title: {$alert['title']}\n";
            $message .= "Description: {$alert['description']}\n";
            $message .= "Level: {$alert['level']}\n";
            $message .= "Created: {$alert['created_at']}\n";
            
            // Send notifications to recipients
            foreach ($recipients as $recipient) {
                $this->notificationService->sendNotification([
                    'type' => 'email',
                    'to' => $recipient,
                    'subject' => $subject,
                    'message' => $message,
                    'priority' => 'high'
                ]);
            }
            
        } catch (Exception $e) {
            $this->logger->log("Error sending escalation notification: " . $e->getMessage(), 'error', 'alerts');
        }
    }

    /**
     * Get escalation recipients for level
     */
    private function getEscalationRecipients($level)
    {
        // This would typically fetch users based on roles
        // For now, return placeholder emails
        $recipients = [
            self::ESCALATION_LEVEL_1 => ['assigned_user@example.com'],
            self::ESCALATION_LEVEL_2 => ['team_lead@example.com'],
            self::ESCALATION_LEVEL_3 => ['department_head@example.com'],
            self::ESCALATION_LEVEL_4 => ['admin1@example.com', 'admin2@example.com']
        ];
        
        return $recipients[$level] ?? [];
    }

    /**
     * Get alert by ID
     */
    private function getAlert($alertId)
    {
        $sql = "SELECT * FROM alerts WHERE alert_id = ?";
        try {
            return $this->database->fetchOne($sql, [$alertId]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Mark escalation as timeout
     */
    private function markEscalationTimeout($alertId, $level)
    {
        try {
            $sql = "UPDATE alert_escalations SET status = 'timeout' 
                    WHERE alert_id = ? AND escalation_level = ?";
            $this->database->execute($sql, [$alertId, $level]);
        } catch (Exception $e) {
            $this->logger->log("Error marking escalation timeout: " . $e->getMessage(), 'error', 'alerts');
        }
    }

    /**
     * Generate unique alert ID
     */
    private function generateAlertId()
    {
        return 'alert_' . uniqid() . '_' . time();
    }

    /**
     * Clean up old alerts
     */
    public function cleanupOldAlerts($daysOld = 30)
    {
        try {
            $sql = "DELETE FROM alerts WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$daysOld]);
            
            $sql = "DELETE FROM alert_escalations WHERE escalated_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$daysOld]);
            
            $this->logger->log("Old alerts cleaned up", 'info', 'alerts');
            return true;
        } catch (Exception $e) {
            $this->logger->log("Error cleaning up old alerts: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }
}
