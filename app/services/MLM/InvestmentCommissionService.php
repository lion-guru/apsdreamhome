<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Investment Commission Service
 * Handles investment sale commissions (5% total: 3.5% direct, 1% L1, 0.5% L2)
 */
class InvestmentCommissionService
{
    use ServiceTenantTrait;

    private \PDO $pdo;
    private \App\Services\MLM\CommissionLedgerService $ledgerService;

    public function __construct(
        ?\PDO $pdo = null,
        ?\App\Services\MLM\CommissionLedgerService $ledgerService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService();
    }

    /**
     * Process investment sale commission
     *
     * Commission breakdown: 5% total
     * - 3.5% direct agent/referrer
     * - 1.0% L1 upline
     * - 0.5% L2 upline
     *
     * @param int   $investmentId   Investment ID
     * @param int   $investorUserId Investor's user_id
     * @param float $amount         Investment amount
     * @param int   $referrerUserId Referrer's user_id (direct agent/associate)
     * @return array{success: bool, distributed: float, entries: int, details: array, error?: string}
     */
    public function process(
        int $investmentId,
        int $investorUserId,
        float $amount,
        int $referrerUserId
    ): array {
        if ($amount <= 0 || $referrerUserId <= 0) {
            return ['success' => true, 'distributed' => 0, 'entries' => 0, 'details' => []];
        }

        $totalPool  = round($amount * 0.05, 2);  // 5% of investment
        $agentShare = round($amount * 0.035, 2); // 3.5% direct
        $l1Share    = round($amount * 0.01, 2);  // 1.0% L1
        $l2Share    = round($amount * 0.005, 2); // 0.5% L2
        // Rounding adjustment goes to agent
        $agentShare = round($totalPool - $l1Share - $l2Share, 2);

        $details = [];

        try {
            $this->pdo->beginTransaction();

            // 1. Direct agent/referrer — 3.5%
            if ($agentShare > 0) {
                $ledgerId = $this->writeLedger(
                    $referrerUserId,
                    $investorUserId,
                    $amount,
                    3.5,
                    $agentShare,
                    'investment_sale',
                    0,
                    0, // no booking_id for investments
                    $investmentId,
                    "Investment #{$investmentId} — 3.5% direct agent commission",
                    false
                );
                $this->incrementGbv($referrerUserId, $amount);
                $details[] = ['user_id' => $referrerUserId, 'level' => 0, 'pct' => 3.5, 'amount' => $agentShare, 'ledger_id' => $ledgerId];
            }

            // 2. Walk upline for L1 and L2
            $chain = $this->getUplineChain($referrerUserId, 2);
            $remaining = [$l1Share, $l2Share];
            $pcts      = [1.0, 0.5];

            foreach ($chain as $i => $upline) {
                if (!isset($remaining[$i]) || $remaining[$i] <= 0) {
                    break;
                }
                $ledgerId = $this->writeLedger(
                    $upline['user_id'],
                    $investorUserId,
                    $amount,
                    $pcts[$i],
                    $remaining[$i],
                    'investment_sale',
                    $i + 1,
                    0,
                    $investmentId,
                    "Investment #{$investmentId} — L" . ($i + 1) . " upline override ({$pcts[$i]}%)",
                    false
                );
                $this->incrementGbv($upline['user_id'], $amount);
                $details[] = ['user_id' => $upline['user_id'], 'level' => $i + 1, 'pct' => $pcts[$i], 'amount' => $remaining[$i], 'ledger_id' => $ledgerId];
            }

            $this->pdo->commit();

            $totalDistributed = array_sum(array_column($details, 'amount'));

            return [
                'success'      => true,
                'distributed'  => round($totalDistributed, 2),
                'entries'      => count($details),
                'details'      => $details,
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[InvestmentCommissionService] process FAILED: " . $e->getMessage());
            return ['success' => false, 'distributed' => 0, 'entries' => 0, 'details' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Build upline chain (up to $maxLevels deep) from mlm_network_tree
     */
    private function getUplineChain(int $agentId, int $maxLevels): array
    {
        $upline = [];
        $current = $agentId;
        $level = 0;

        while ($level < $maxLevels) {
            $stmt = $this->pdo->prepare("
                SELECT parent_id, level FROM mlm_network_tree
                WHERE associate_id = ? LIMIT 1
            ");
            $stmt->execute([$current]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || !$row['parent_id']) {
                break;
            }

            $level++;
            $parentId = (int) $row['parent_id'];

            $upline[] = [
                'user_id' => $parentId,
                'rank'    => '', // Rank not needed for investment commissions
                'level'   => $level,
            ];

            $current = $parentId;
        }

        return $upline;
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
        // Use shared CommissionLedgerService
        $ledgerService = new \App\Services\MLM\CommissionLedgerService();
        return $ledgerService->writeLedger(
            $beneficiaryId, $sourceId, $amountReceived, $pct, $amount,
            $type, $level, $bookingId, $receiptId, $notes, $isMissed
        );
    }

    /**
     * Increment agent's lifetime_sales after a payment
     */
    private function incrementGbv(int $agentId, float $amount): void
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE mlm_profiles
                SET lifetime_sales = lifetime_sales + ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$amount, $agentId]);
        } catch (\Throwable $e) {
            error_log("[InvestmentCommissionService] incrementGbv: " . $e->getMessage());
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