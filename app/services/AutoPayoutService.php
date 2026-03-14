<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

/**
 * AutoPayoutService
 * Processes one-click bulk commission payouts for eligible agents.
 */
class AutoPayoutService
{
    protected $db;
    protected $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new \App\Services\LoggingService();
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS mlm_payout_batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            initiated_by INT NOT NULL,
            total_agents INT DEFAULT 0,
            total_amount DECIMAL(15, 2) DEFAULT 0,
            status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            INDEX idx_status (status),
            INDEX idx_initiated_by (initiated_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
    }

    /**
     * Get all agents with pending, unpaid commissions.
     */
    public function getPendingPayouts()
    {
        $sql = "SELECT 
                    u.id as user_id,
                    u.name,
                    u.email,
                    u.phone,
                    SUM(c.amount) as total_pending,
                    COUNT(c.id) as pending_count
                FROM mlm_commission_ledger c
                JOIN users u ON c.user_id = u.id
                WHERE c.status = 'pending'
                GROUP BY u.id, u.name, u.email, u.phone
                HAVING total_pending > 0
                ORDER BY total_pending DESC";

        return $this->db->fetchAll($sql) ?? [];
    }

    /**
     * Trigger a one-click payout for all pending commissions.
     * Creates a payout batch and marks commissions as paid.
     */
    public function processPayouts($initiatedBy)
    {
        $pending = $this->getPendingPayouts();

        if (empty($pending)) {
            return ['success' => false, 'message' => 'No pending payouts found'];
        }

        $totalAmount = array_sum(array_column($pending, 'total_pending'));
        $totalAgents = count($pending);

        // Create a batch record
        $batchSql = "INSERT INTO mlm_payout_batches (initiated_by, total_agents, total_amount, status)
                     VALUES (?, ?, ?, 'processing')";
        $this->db->query($batchSql, [$initiatedBy, $totalAgents, $totalAmount]);
        $batchId = $this->db->lastInsertId();

        // Mark all pending commissions as paid
        $agentIds = array_column($pending, 'user_id');
        $placeholders = implode(',', array_fill(0, count($agentIds), '?'));
        $updateSql = "UPDATE mlm_commission_ledger SET status = 'paid', paid_at = NOW()
                      WHERE user_id IN ($placeholders) AND status = 'pending'";
        $this->db->query($updateSql, $agentIds);

        // Update batch as completed
        $this->db->query(
            "UPDATE mlm_payout_batches SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$batchId]
        );

        $this->logger->info("Auto payout processed: Batch #$batchId — $totalAgents agents, ₹$totalAmount");

        return [
            'success' => true,
            'batch_id' => $batchId,
            'total_agents' => $totalAgents,
            'total_amount' => $totalAmount,
            'message' => "Payout processed successfully for $totalAgents agents totalling ₹$totalAmount"
        ];
    }

    /**
     * Get recent payout batch history.
     */
    public function getPayoutHistory()
    {
        $sql = "SELECT b.*, u.name as initiated_by_name
                FROM mlm_payout_batches b
                JOIN users u ON b.initiated_by = u.id
                ORDER BY b.created_at DESC
                LIMIT 20";
        return $this->db->fetchAll($sql) ?? [];
    }
}
