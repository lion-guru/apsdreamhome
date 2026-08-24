<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Track B Commission Service — Performance Rollup Chain (3% cap)
 * Awards rolling performance bonus based on consecutive qualifying months
 */
class TrackBCommissionService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Compute Track B (Performance Rollup Chain) commission
     *
     * @param int     $agentId       The executing agent's user_id
     * @param float   $amountReceived Payment amount received
     * @param float   $budgetCap      Budget cap for this track (3% of payment)
     * @param int     $bookingId      Booking ID
     * @param int     $receiptId      Receipt ID
     * @return array Track B result with distributed amount, remaining, consecutive months, bonus pct
     */
    public function compute(
        int $agentId,
        float $amountReceived,
        float $budgetCap,
        int $bookingId,
        int $receiptId
    ): array {
        $ledgerIds = [];
        $distributed = 0.0;

        // Count consecutive qualifying months (including current)
        $consecutive = $this->countConsecutiveQualifyingMonths($agentId);

        // Bonus: 0.3% per qualifying month, capped at 3% total
        $bonusPct = min($consecutive * 0.3, 3.0);
        $bonusAmt = $amountReceived * ($bonusPct / 100);

        if ($bonusAmt > 0 && $bonusAmt <= $budgetCap) {
            $isMissed = $this->checkMissedCommission($agentId);

            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, $bonusPct,
                $bonusAmt, 'performance_bonus', 0, $bookingId, $receiptId,
                "Track B — Performance rollup ({$consecutive} consecutive months, {$bonusPct}%)",
                $isMissed
            );
            $ledgerIds[] = $ledgerId;
            if (!$isMissed) $distributed += $bonusAmt;
        }

        return [
            'track'              => 'B',
            'label'              => 'Performance Rollup Chain',
            'budget'             => $budgetCap,
            'distributed'        => round($distributed, 2),
            'remaining'          => round($budgetCap - $distributed, 2),
            'consecutive_months' => $consecutive,
            'bonus_pct'          => $bonusPct,
            'ledger_ids'         => $ledgerIds,
            'entries'            => count($ledgerIds),
        ];
    }

    /**
     * Count consecutive qualifying months (including current)
     * Qualifying month = agent (or any downline) generates >= ₹50,000 in confirmed bookings
     */
    private function countConsecutiveQualifyingMonths(int $agentId): int
    {
        $consecutive = 0;
        $currentMonth = date('Y-m');

        // Check current month first
        if ($this->isQualifyingMonth($agentId, $currentMonth)) {
            $consecutive++;
            // Check previous months
            $checkMonth = date('Y-m', strtotime('-1 month', strtotime($currentMonth . '-01')));
            while ($this->isQualifyingMonth($agentId, $checkMonth)) {
                $consecutive++;
                $checkMonth = date('Y-m', strtotime('-1 month', strtotime($checkMonth . '-01')));
            }
        }

        return $consecutive;
    }

    /**
     * Check if agent had qualifying sales in a given month
     * Qualifying = agent or downline generated >= ₹50,000 in confirmed bookings
     */
    private function isQualifyingMonth(int $agentId, string $month): bool
    {
        try {
            // Get all downline user IDs including self
            $downlineIds = $this->getDownlineUserIds($agentId);
            $downlineIds[] = $agentId;
            $placeholders = implode(',', array_fill(0, count($downlineIds), '?'));

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS total_volume
                FROM plot_bookings pb
                WHERE pb.associate_id IN ($placeholders)
                AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                AND pb.status IN ('confirmed', 'completed')
            ");
            $params = array_merge($downlineIds, [$month]);
            $stmt->execute($params);
            $totalVolume = (float) $stmt->fetchColumn();

            return $totalVolume >= 50000;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get all downline user IDs for an agent (from mlm_network_tree)
     */
    private function getDownlineUserIds(int $agentId): array
    {
        $ids = [];
        $queue = [$agentId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $stmt = $this->pdo->prepare("
                SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?
            ");
            $stmt->execute([$current]);
            $children = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($children as $childId) {
                $ids[] = (int) $childId;
                $queue[] = (int) $childId;
            }
        }

        return $ids;
    }

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

    private function writeLedger(
        int $beneficiaryId,
        int $sourceId,
        float $amountReceived,
        float $pct,
        float $amount,
        string $type,
        int $level,
        int $bookingId,
        int $receiptId,
        string $notes,
        bool $isMissed
    ): int {
        $db = \App\Core\Database\Database::getInstance()->getConnection();

        $status = $isMissed ? 'missed' : 'pending';

        $stmt = $db->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 booking_id, receipt_id, hold_until, created_at, tenant_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 45 DAY), NOW(), ?)
        ");
        $stmt->execute([
            $beneficiaryId,
            $sourceId,
            $type,
            round($amount, 2),
            $level,
            $amountReceived,
            round($pct, 2),
            $status,
            $notes,
            $bookingId,
            $receiptId,
            $this->getTenantId(),
        ]);
        return (int) $db->lastInsertId();
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