<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

/**
 * AutoPayoutService
 * Processes one-click bulk commission payouts for eligible users.
 */
class AutoPayoutService
{
    use \App\Traits\ServiceTenantTrait;

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
        try {
            $this->db->query("SELECT 1 FROM mlm_commission_ledger LIMIT 1");
        } catch (\Throwable $e) {
            error_log('[AutoPayoutService] mlm_commission_ledger not accessible: ' . $e->getMessage());
        }
    }

    /**
     * Get all users with pending, unpaid commissions.
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
                JOIN users u ON c.beneficiary_user_id = u.id
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

        // Create a batch record (real mlm_payout_batches schema)
        $batchNumber = 'APS-MPB-' . date('Ym') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
        $periodYear = (int) date('Y');
        $periodMonth = (int) date('n');
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');
        $mpbCols = "batch_number, period_year, period_month, period_start, period_end, total_associates, total_gross_amount, total_net_amount, status, prepared_by, created_at";
        $mpbVals = "?, ?, ?, ?, ?, ?, ?, ?, 'processing', ?, NOW()";
        $mpbParams = [$batchNumber, $periodYear, $periodMonth, $periodStart, $periodEnd, $totalAgents, $totalAmount, $totalAmount, $initiatedBy];
        if ($this->tenantId() > 1) {
            $mpbCols .= ", tenant_id";
            $mpbVals .= ", ?";
            $mpbParams[] = $this->tenantId();
        }
        $batchSql = "INSERT INTO mlm_payout_batches ({$mpbCols}) VALUES ({$mpbVals})";
        $this->db->query($batchSql, $mpbParams);
        $batchId = $this->db->lastInsertId();

        // Mark all pending commissions as paid
        $agentIds = array_column($pending, 'user_id');
        $placeholders = implode(',', array_fill(0, count($agentIds), '?'));
        $updateMclSql = "UPDATE mlm_commission_ledger SET status = 'paid', updated_at = NOW()
                         WHERE beneficiary_user_id IN ($placeholders) AND status = 'pending'"
                       . $this->tenantSql();
        $mclParams = $agentIds;
        if ($this->tenantId() > 1) $mclParams[] = $this->tenantId();
        $this->db->query($updateMclSql, $mclParams);

        // Update batch as completed
        $mpbUpdateSql = "UPDATE mlm_payout_batches SET status = 'completed', processed_by = ?, payment_date = CURDATE(), updated_at = NOW() WHERE id = ?";
        $mpbUpdateSql .= $this->tenantSql();
        $mpbUpParams = [$initiatedBy, $batchId];
        if ($this->tenantId() > 1) $mpbUpParams[] = $this->tenantId();
        $this->db->query($mpbUpdateSql, $mpbUpParams);

        $this->logger->info("Auto payout processed: Batch #$batchId — $totalAgents users, ₹$totalAmount");

        return [
            'success' => true,
            'batch_id' => $batchId,
            'total_agents' => $totalAgents,
            'total_amount' => $totalAmount,
            'message' => "Payout processed successfully for $totalAgents users totalling ₹$totalAmount"
        ];
    }

    /**
     * Get recent payout batch history.
     */
    public function getPayoutHistory()
    {
        $sql = "SELECT b.*, u.name as initiated_by_name
                FROM mlm_payout_batches b
                LEFT JOIN users u ON b.prepared_by = u.id
                ORDER BY b.created_at DESC
                LIMIT 20";
        return $this->db->fetchAll($sql) ?? [];
    }
}
