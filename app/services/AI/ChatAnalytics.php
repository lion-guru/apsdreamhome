<?php

/**
 * ChatAnalytics — Tracks chatbot action usage, completion rates, drop-offs
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class ChatAnalytics
{
    use ServiceTenantTrait;

    private $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    /**
     * Log an action event
     */
    public function logEvent(string $event, string $action, string $sessionId, ?int $userId = null, array $meta = []): void
    {
        try {
            $this->db->query(
                "INSERT INTO chat_analytics (event_type, action, session_id, user_id, meta, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '' ) . ") VALUES (?, ?, ?, ?, ?, NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '' ) . ")",
                array_merge([$event, $action, $sessionId, $userId, json_encode($meta)], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
        } catch (\Exception $e) {
            error_log("ChatAnalytics log error: " . $e->getMessage());
        }
    }

    /**
     * Log action started
     */
    public function logStarted(string $action, string $sessionId, ?int $userId = null): void
    {
        $this->logEvent('started', $action, $sessionId, $userId);
    }

    /**
     * Log step completed
     */
    public function logStep(string $action, string $sessionId, int $step, int $totalSteps): void
    {
        $this->logEvent('step', $action, $sessionId, null, ['step' => $step, 'total' => $totalSteps]);
    }

    /**
     * Log action completed (confirmed & executed)
     */
    public function logCompleted(string $action, string $sessionId, bool $success, ?int $userId = null): void
    {
        $this->logEvent('completed', $action, $sessionId, $userId, ['success' => $success]);
    }

    /**
     * Log action cancelled
     */
    public function logCancelled(string $action, string $sessionId): void
    {
        $this->logEvent('cancelled', $action, $sessionId);
    }

    /**
     * Log drop-off (user abandoned at a step)
     */
    public function logDropoff(string $action, string $sessionId, int $step): void
    {
        $this->logEvent('dropoff', $action, $sessionId, null, ['step' => $step]);
    }

    /**
     * Get analytics dashboard data
     */
    public function getDashboard(int $days = 30): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Action usage counts
        $usage = $this->db->fetchAll(
            "SELECT action, 
                    SUM(CASE WHEN event_type='started' THEN 1 ELSE 0 END) as starts,
                    SUM(CASE WHEN event_type='completed' THEN 1 ELSE 0 END) as completions,
                    SUM(CASE WHEN event_type='cancelled' THEN 1 ELSE 0 END) as cancels,
                    SUM(CASE WHEN event_type='dropoff' THEN 1 ELSE 0 END) as dropoffs
             FROM chat_analytics 
             WHERE created_at >= ?" . $this->tenantSql() . "
             GROUP BY action
             ORDER BY starts DESC",
            [$since]
        );

        // Completion rate per action
        foreach ($usage as &$row) {
            $row['completion_rate'] = $row['starts'] > 0 
                ? round(($row['completions'] / $row['starts']) * 100, 1) 
                : 0;
        }

        // Daily trend
        $trend = $this->db->fetchAll(
            "SELECT DATE(created_at) as day, 
                    SUM(CASE WHEN event_type='started' THEN 1 ELSE 0 END) as starts,
                    SUM(CASE WHEN event_type='completed' THEN 1 ELSE 0 END) as completions
             FROM chat_analytics 
             WHERE created_at >= ?" . $this->tenantSql() . "
             GROUP BY DATE(created_at)
             ORDER BY day DESC",
            [$since]
        );

        // Drop-off by step
        $dropoffs = $this->db->fetchAll(
            "SELECT action, 
                    JSON_EXTRACT(meta, '$.step') as dropoff_step,
                    COUNT(*) as count
             FROM chat_analytics 
             WHERE event_type = 'dropoff' AND created_at >= ?" . $this->tenantSql() . "
             GROUP BY action, dropoff_step
             ORDER BY count DESC
             LIMIT 10",
            [$since]
        );

        // Total stats
        $totals = $this->db->fetch(
            "SELECT 
                SUM(CASE WHEN event_type='started' THEN 1 ELSE 0 END) as total_starts,
                SUM(CASE WHEN event_type='completed' THEN 1 ELSE 0 END) as total_completions,
                SUM(CASE WHEN event_type='cancelled' THEN 1 ELSE 0 END) as total_cancels,
                SUM(CASE WHEN event_type='dropoff' THEN 1 ELSE 0 END) as total_dropoffs
             FROM chat_analytics 
             WHERE created_at >= ?" . $this->tenantSql(),
            [$since]
        );

        return [
            'usage' => $usage,
            'trend' => $trend,
            'dropoffs' => $dropoffs,
            'totals' => $totals,
            'days' => $days,
        ];
    }

    /**
     * Create analytics table if not exists
     */
    public function ensureTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS chat_analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(20) NOT NULL,
                action VARCHAR(50) NOT NULL,
                session_id VARCHAR(100) NOT NULL,
                user_id INT UNSIGNED NULL,
                meta JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_action (action),
                INDEX idx_event (event_type),
                INDEX idx_created (created_at),
                INDEX idx_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
