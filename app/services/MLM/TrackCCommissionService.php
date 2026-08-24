<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Track C Commission Service — Milestone Reward Escrow (2% cap)
 * Accumulates 2% of payments into escrow; rewards at milestones
 */
class TrackCCommissionService
{
    use ServiceTenantTrait;

    private const ESCROW_MILESTONES = [
        50000  => 'Bronze Milestone',
        200000 => 'Silver Milestone',
        500000 => 'Gold Milestone',
        1000000 => 'Platinum Milestone',
    ];

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Compute Track C (Milestone Reward Escrow) commission
     *
     * @param int     $agentId       The executing agent's user_id
     * @param float   $amountReceived Payment amount received
     * @param float   $budgetCap      Budget cap for this track (2% of payment)
     * @param int     $bookingId      Booking ID
     * @param int     $receiptId      Receipt ID
     * @return array Track C result with distributed amount, cumulative escrow, milestone triggered
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

        // Credit the full 2% budget (capped by global cap enforcement)
        $escrowAmount = min($amountReceived * (2 / 100), $budgetCap);

        if ($escrowAmount > 0) {
            $isMissed = $this->checkMissedCommission($agentId);

            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, 2.0,
                $escrowAmount, 'team_bonus', 0, $bookingId, $receiptId,
                'Track C — Milestone escrow credit',
                $isMissed
            );
            $ledgerIds[] = $ledgerId;
            if (!$isMissed) $distributed += $escrowAmount;
        }

        // Check milestones
        $totalEscrow = $this->getAgentEscrowBalance($agentId);
        $milestoneTriggered = null;

        foreach (self::ESCROW_MILESTONES as $threshold => $label) {
            if ($totalEscrow >= $threshold) {
                $milestoneTriggered = ['threshold' => $threshold, 'label' => $label];
            }
        }

        return [
            'track'               => 'C',
            'label'               => 'Milestone Reward Escrow',
            'budget'              => $budgetCap,
            'distributed'         => round($distributed, 2),
            'remaining'           => round($budgetCap - $distributed, 2),
            'cumulative_escrow'   => round($totalEscrow, 2),
            'milestone_triggered' => $milestoneTriggered,
            'ledger_ids'          => $ledgerIds,
            'entries'             => count($ledgerIds),
        ];
    }

    /**
     * Get agent's cumulative escrow balance
     */
    private function getAgentEscrowBalance(int $agentId): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) AS total
                FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ? AND commission_type = 'team_bonus'
            ");
            $stmt->execute([$agentId]);
            return (float) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0.0;
        }
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