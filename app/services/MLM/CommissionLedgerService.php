<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Commission Ledger Service
 * Shared service for writing commission ledger entries across all MLM tracks
 * Handles plan snapshot capture, missed commission detection, and notifications
 */
class CommissionLedgerService
{
    use ServiceTenantTrait;

    private \PDO $pdo;
    private \App\Services\MLM\RankService $rankService;

    public function __construct(?\PDO $pdo = null, ?\App\Services\MLM\RankService $rankService = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->rankService = $rankService ?? new \App\Services\MLM\RankService();
    }

    /**
     * Write a commission ledger entry with full plan safety and notifications
     *
     * @param int    $beneficiaryId   User receiving the commission
     * @param int    $sourceId        User who generated the sale
     * @param float  $saleAmount      Total sale/payment amount
     * @param float  $pct             Commission percentage applied
     * @param float  $amount          Commission amount to credit
     * @param string $type            Commission type (direct_sale, level_bonus, performance_bonus, team_bonus, independent_agent, etc.)
     * @param int    $level           Upline level (0 = direct, 1+ = upline generation)
     * @param int    $bookingId       Booking ID (optional)
     * @param int    $receiptId       Receipt ID (optional)
     * @param string $notes           Description/notes
     * @param bool   $isMissed        Whether commission is missed due to inactive status
     * @return int Ledger row ID
     */
    public function writeLedger(
        int $beneficiaryId,
        int $sourceId,
        float $saleAmount,
        float $pct,
        float $amount,
        string $type,
        int $level,
        int $bookingId = 0,
        int $receiptId = 0,
        string $notes = '',
        bool $isMissed = false
    ): int {
        // Capture plan snapshot for this calculation (plan safety)
        $planSnapshot = $this->getActivePlanSnapshot();

        $status = $isMissed ? 'missed' : 'pending';
        
        // Dynamic check for inactive associate status to enforce missed commissions
        if (!$isMissed) {
            $isMissed = $this->checkMissedCommission($beneficiaryId);
            if ($isMissed) {
                $status = 'missed';
                $notes .= ' | [MISSED_DUE_TO_INACTIVE]';
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 booking_id, receipt_id, hold_until, created_at,
                 plan_id, plan_version, plan_snapshot, calculation_engine, tenant_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 45 DAY), NOW(),
                    ?, ?, ?, 'hybrid', ?)
        ");
        $stmt->execute([
            $beneficiaryId,
            $sourceId,
            $type,
            round($amount, 2),
            $level,
            $saleAmount,
            round($pct, 2),
            $status,
            $notes,
            $bookingId > 0 ? $bookingId : null,
            $receiptId > 0 ? $receiptId : null,
            $planSnapshot['plan_id'] ?? null,
            $planSnapshot['plan_version'] ?? null,
            $planSnapshot ? json_encode($planSnapshot) : null,
            $this->getTenantId(),
        ]);
        $ledgerId = (int) $this->pdo->lastInsertId();

        // Broadcast commission event via WebSocket + Push
        $this->broadcastCommissionEvent([
            'event'       => 'commission_credited',
            'ledger_id'   => $ledgerId,
            'beneficiary' => $beneficiaryId,
            'source'      => $sourceId,
            'type'        => $type,
            'amount'      => round($amount, 2),
            'level'       => $level,
            'created_at'  => date('Y-m-d H:i:s'),
        ], $beneficiaryId, $notes, round($amount, 2));

        return $ledgerId;
    }

    /**
     * Check if commission should be marked as missed due to inactive associate status
     */
    private function checkMissedCommission(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT status FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $status = $stmt->fetchColumn();
            return $status && $status !== 'active';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get active plan snapshot for plan safety
     * Falls back to hardcoded constants if table doesn't exist
     */
    private function getActivePlanSnapshot(): ?array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];

            $stmt = $db->prepare("SELECT id, version, global_cap_pct, track_a_pct, track_b_pct, track_c_pct, royalty_pool_pct, same_level_override_gen1, same_level_override_gen2, effective_date, expiry_date FROM mlm_commission_plans WHERE status = 'active'" . $tidSql . " ORDER BY version DESC LIMIT 1");
            $stmt->execute($params);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($plan) {
                return [
                    'plan_id'           => (int)$plan['id'],
                    'plan_version'      => (int)$plan['version'],
                    'global_cap_pct'    => (float)$plan['global_cap_pct'],
                    'track_a_pct'       => (float)$plan['track_a_pct'],
                    'track_b_pct'       => (float)$plan['track_b_pct'],
                    'track_c_pct'       => (float)$plan['track_c_pct'],
                    'royalty_pool_pct'  => (float)$plan['royalty_pool_pct'],
                    'same_level_gen1'   => (float)$plan['same_level_override_gen1'],
                    'same_level_gen2'   => (float)$plan['same_level_override_gen2'],
                    'effective_date'    => $plan['effective_date'],
                    'expiry_date'       => $plan['expiry_date'],
                ];
            }
        } catch (\Throwable $e) {
            error_log('CommissionLedgerService::getActivePlanSnapshot error: ' . $e->getMessage());
        }

        // Hardcoded fallback
        return [
            'plan_id'           => 1,
            'plan_version'      => 1,
            'global_cap_pct'    => 20.0,
            'track_a_pct'       => 15.0,
            'track_b_pct'       => 3.0,
            'track_c_pct'       => 2.0,
            'royalty_pool_pct'  => 2.0,
            'same_level_gen1'   => 2.0,
            'same_level_gen2'   => 1.0,
        ];
    }

    /**
     * Broadcast commission event via WebSocket + Push
     */
    private function broadcastCommissionEvent(array $payload, int $beneficiaryId, string $notes, float $amount): void
    {
        try {
            \App\Services\WebSocketBroadcaster::broadcastToUser($beneficiaryId, $payload);
            
            $pushService = new \App\Services\Communication\PushNotificationService();
            $pushService->sendToUser(
                $beneficiaryId,
                [
                    'title' => 'Commission Credited',
                    'body'  => '₹' . number_format($amount) . ' commission credited' . ($notes ? ' — ' . $notes : ''),
                    'data'  => $payload,
                ]
            );
        } catch (\Throwable $e) {
            error_log("[CommissionLedgerService] broadcast failed: " . $e->getMessage());
        }
    }

    protected function getTenantId(): int
    {
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }
}