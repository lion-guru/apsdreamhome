<?php

namespace App\Services\Alerts;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\LoggingService;
use App\Services\Communication\NotificationService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Alert Escalation Service - APS Dream Home
 * Modern alert management with escalation levels and notifications
 * Custom MVC implementation without Laravel dependencies
 * Matches real database schema: alerts (id, alert_type, severity, title, message, source, is_resolved, resolved_by, resolved_at)
 * and alert_escalations (id, alert_id, escalated_to, escalation_level, notes, tenant_id, created_at)
 */
class AlertEscalationService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $logger;
    private $notificationService;

    // Alert severity levels (match real alerts.severity enum)
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

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
    }

    /**
     * Create a new alert
     */
    public function createAlert($title, $message = '', $severity = self::LEVEL_INFO, $source = null, $category = null, $metadata = [])
    {
        if (empty($title)) {
            throw new InvalidArgumentException('Alert title is required');
        }

        // Valid severities match the real alerts.severity enum
        $validSeverities = ['info', 'warning', 'error', 'critical'];
        if (!in_array($severity, $validSeverities)) {
            $severity = self::LEVEL_INFO;
        }

        $tenantIns = $this->tenantInsertData();
        $insCols = array_merge(
            ['alert_type', 'title', 'message', 'severity', 'source'],
            array_keys($tenantIns)
        );
        $insVals = array_merge(
            [$category ?? 'system', $title, $message, $severity, $source],
            array_values($tenantIns)
        );
        $colStr = implode(', ', $insCols);
        $placeholders = implode(', ', array_fill(0, count($insVals), '?'));

        try {
            $this->database->execute("INSERT INTO alerts ($colStr) VALUES ($placeholders)", $insVals);
            $alertId = $this->database->lastInsertId();

            $this->logger->log("Alert created: $title (ID: $alertId, Severity: $severity)", 'info', 'alerts');

            // Start escalation for critical/emergency alerts
            if (in_array($severity, [self::LEVEL_CRITICAL, self::LEVEL_ERROR])) {
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
            // Insert into alert_escalations (real columns: alert_id, escalation_level, escalated_to, notes, tenant_id)
            $tenantIns = $this->tenantInsertData();
            $insCols = array_merge(
                ['alert_id', 'escalation_level', 'escalated_to', 'notes'],
                array_keys($tenantIns)
            );
            $insVals = array_merge(
                [$alertId, self::ESCALATION_LEVEL_1, null, json_encode($this->getEscalationRecipients(self::ESCALATION_LEVEL_1))],
                array_values($tenantIns)
            );
            $colStr = implode(', ', $insCols);
            $placeholders = implode(', ', array_fill(0, count($insVals), '?'));

            $this->database->execute("INSERT INTO alert_escalations ($colStr) VALUES ($placeholders)", $insVals);

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
     * Real alert_escalations doesn't have status/escalated_at/timeout_minutes
     * So we check if alert is not resolved and escalation was created long enough ago
     */
    public function processEscalations()
    {
        $processed = 0;

        try {
            // Find alerts that need escalation: not resolved, have escalation, 
            // and last escalation was created more than timeout ago
            $sql = "SELECT ae.*, a.title, a.message, a.severity
                    FROM alert_escalations ae
                    JOIN alerts a ON ae.alert_id = a.id
                    WHERE a.is_resolved = 0
                    AND ae.created_at < DATE_SUB(NOW(), INTERVAL 
                        CASE ae.escalation_level
                            WHEN 1 THEN 900
                            WHEN 2 THEN 1800
                            WHEN 3 THEN 3600
                            WHEN 4 THEN 7200
                            ELSE 900
                        END SECOND)";

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
            // Maximum escalation reached - mark as max escalated
            $this->markMaxEscalation($alertId, $currentLevel);
            return;
        }

        try {
            // Insert new escalation level
            $tenantIns = $this->tenantInsertData();
            $insCols = array_merge(
                ['alert_id', 'escalation_level', 'escalated_to', 'notes'],
                array_keys($tenantIns)
            );
            $insVals = array_merge(
                [$alertId, $nextLevel, null, json_encode($this->getEscalationRecipients($nextLevel))],
                array_values($tenantIns)
            );
            $colStr = implode(', ', $insCols);
            $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
            $this->database->execute("INSERT INTO alert_escalations ($colStr) VALUES ($placeholders)", $insVals);

            // Send escalation notification
            $this->sendEscalationNotification($alertId, $nextLevel);

            $this->logger->log("Alert escalated: $alertId to level $nextLevel", 'warning', 'alerts');

        } catch (Exception $e) {
            $this->logger->log("Error escalating alert $alertId: " . $e->getMessage(), 'error', 'alerts');
        }
    }

    /**
     * Acknowledge an alert (mark as resolved)
     */
    public function acknowledgeAlert($alertId, $userId)
    {
        try {
            $tenantSql = $this->tenantSql();
            $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $sql = "UPDATE alerts
                    SET is_resolved = 1, resolved_at = NOW(), resolved_by = ?
                    WHERE id = ?{$tenantSql}";
            $params = array_merge([$userId, $alertId], $tenantParam);
            $this->database->execute($sql, $params);

            // Also clear any pending escalations
            $this->database->execute(
                "DELETE FROM alert_escalations WHERE alert_id = ?",
                [$alertId]
            );

            $this->logger->log("Alert acknowledged/resolved: $alertId by user $userId", 'info', 'alerts');

            return true;

        } catch (Exception $e) {
            $this->logger->log("Error acknowledging alert $alertId: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }

    /**
     * Resolve an alert with resolution notes
     */
    public function resolveAlert($alertId, $userId, $resolution = '')
    {
        try {
            $tenantSql = $this->tenantSql();
            $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $sql = "UPDATE alerts
                    SET is_resolved = 1, resolved_at = NOW(), resolved_by = ?
                    WHERE id = ?{$tenantSql}";
            $params = array_merge([$userId, $alertId], $tenantParam);
            $this->database->execute($sql, $params);

            // Clear pending escalations
            $this->database->execute(
                "DELETE FROM alert_escalations WHERE alert_id = ?",
                [$alertId]
            );

            $this->logger->log("Alert resolved: $alertId by user $userId", 'info', 'alerts');

            return true;

        } catch (Exception $e) {
            $this->logger->log("Error resolving alert $alertId: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }

    /**
     * Get alerts by resolved status
     */
    public function getAlertsByStatus($resolved = 0, $limit = 50, $offset = 0)
    {
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $sql = "SELECT a.*, u1.name as resolved_by_name
                FROM alerts a
                LEFT JOIN users u1 ON a.resolved_by = u1.id
                WHERE a.is_resolved = ?{$tenantSql}
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        try {
            return $this->database->fetchAll($sql, array_merge([$resolved], $tenantParam, [(int)$limit, (int)$offset]));
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
        $tenantSql = $this->tenantSql();

        try {
            // Total alerts by severity
            $sql = "SELECT severity, COUNT(*) as count FROM alerts GROUP BY severity";
            if ($tenantSql) {
                $sql .= " WHERE tenant_id = " . $this->tenantId();
            }
            $results = $this->database->fetchAll($sql);
            $stats['by_severity'] = [];
            foreach ($results as $row) {
                $stats['by_severity'][$row['severity']] = $row['count'];
            }

            // Total alerts by resolved status
            $sql = "SELECT is_resolved, COUNT(*) as count FROM alerts GROUP BY is_resolved";
            if ($tenantSql) {
                $sql .= " WHERE tenant_id = " . $this->tenantId();
            }
            $results = $this->database->fetchAll($sql);
            $stats['by_resolved'] = [];
            foreach ($results as $row) {
                $stats['by_resolved'][$row['is_resolved'] ? 'resolved' : 'active'] = $row['count'];
            }

            // Recent alerts (24 hours)
            $sql = "SELECT COUNT(*) as count FROM alerts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            if ($tenantSql) {
                $sql .= " AND tenant_id = " . $this->tenantId();
            }
            $result = $this->database->fetchOne($sql);
            $stats['recent_24h'] = $result['count'] ?? 0;

            // Active escalations count
            $sql = "SELECT COUNT(DISTINCT ae.alert_id) as count 
                    FROM alert_escalations ae
                    JOIN alerts a ON ae.alert_id = a.id
                    WHERE a.is_resolved = 0";
            if ($tenantSql) {
                $sql .= " AND a.tenant_id = " . $this->tenantId();
            }
            $result = $this->database->fetchOne($sql);
            $stats['active_escalations'] = $result['count'] ?? 0;

        } catch (Exception $e) {
            $this->logger->log("Error fetching alert stats: " . $e->getMessage(), 'error', 'alerts');
        }

        return $stats;
    }

    /**
     * Get all alerts for admin display
     */
    public function getAllAlerts($limit = 100, $offset = 0)
    {
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $sql = "SELECT a.*, u.name as resolved_by_name,
                (SELECT COUNT(*) FROM alert_escalations ae WHERE ae.alert_id = a.id) as escalation_count
                FROM alerts a
                LEFT JOIN users u ON a.resolved_by = u.id
                WHERE 1=1{$tenantSql}
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        try {
            return $this->database->fetchAll($sql, array_merge($tenantParam, [(int)$limit, (int)$offset]));
        } catch (Exception $e) {
            $this->logger->log("Error fetching all alerts: " . $e->getMessage(), 'error', 'alerts');
            return [];
        }
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
            $message .= "Description: {$alert['message']}\n";
            $message .= "Severity: {$alert['severity']}\n";
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
        $sql = "SELECT * FROM alerts WHERE id = ?";
        try {
            return $this->database->fetchOne($sql, [$alertId]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Mark max escalation reached
     */
    private function markMaxEscalation($alertId, $level)
    {
        try {
            // Update the last escalation record with a note about max reached
            $sql = "UPDATE alert_escalations 
                    SET notes = CONCAT(COALESCE(notes, ''), '\n[Max escalation reached at level $level]')
                    WHERE alert_id = ? AND escalation_level = ?";
            $this->database->execute($sql, [$alertId, $level]);
        } catch (Exception $e) {
            $this->logger->log("Error marking max escalation: " . $e->getMessage(), 'error', 'alerts');
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
     * Clean up old resolved alerts
     */
    public function cleanupOldAlerts($daysOld = 30)
    {
        try {
            $tenantSql = $this->tenantSql();
            $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $sql = "DELETE FROM alerts WHERE is_resolved = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY){$tenantSql}";
            $params = array_merge([$daysOld], $tenantParam);
            $this->database->execute($sql, $params);

            // Also clean up orphaned escalations
            $tenantSql2 = $this->tenantSql();
            $tenantParam2 = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $sql2 = "DELETE ae FROM alert_escalations ae
                     LEFT JOIN alerts a ON ae.alert_id = a.id
                     WHERE a.id IS NULL{$tenantSql2}";
            $this->database->execute($sql2, $tenantParam2);

            $this->logger->log("Old alerts cleaned up", 'info', 'alerts');
            return true;
        } catch (Exception $e) {
            $this->logger->log("Error cleaning up old alerts: " . $e->getMessage(), 'error', 'alerts');
            return false;
        }
    }
}