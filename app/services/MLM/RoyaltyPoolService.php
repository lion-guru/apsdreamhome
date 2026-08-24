<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Royalty Pool Service
 * Handles 2% royalty pool contributions, accumulation, and distribution to qualified Site Managers
 */
class RoyaltyPoolService
{
    use ServiceTenantTrait;

    private const ROYALTY_QUALIFICATION_GBV = 5000000; // ₹50 Lakhs

    private \PDO $pdo;
    private \App\Services\MLM\CommissionLedgerService $ledgerService;
    private \App\Services\MLM\RankService $rankService;

    public function __construct(
        ?\PDO $pdo = null,
        ?\App\Services\MLM\CommissionLedgerService $ledgerService = null,
        ?\App\Services\MLM\RankService $rankService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService();
        $this->rankService = $rankService ?? new \App\Services\MLM\RankService();
    }

    /**
     * Contribute 2% of payment to the monthly royalty pool
     *
     * @param int   $bookingId
     * @param float $amountReceived
     * @return array{success: bool, contribution: float, pool_total: float, error?: string}
     */
    public function contribute(int $bookingId, float $amountReceived): array
    {
        try {
            $caps = $this->rankService->getActivePlanCaps();
            $contribution = round($amountReceived * ($caps['royalty_pool'] / 100), 2);
            if ($contribution <= 0) {
                return ['success' => true, 'contribution' => 0, 'pool_total' => 0];
            }

            $monthYear = date('Y-m');

            // Upsert the monthly pool accumulator
            $stmt = $this->pdo->prepare("
                INSERT INTO mlm_royalty_pool (month_year, total_pool_amount, tenant_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE total_pool_amount = total_pool_amount + VALUES(total_pool_amount)
            ");
            $stmt->execute([$monthYear, $contribution, $this->getTenantId()]);

            // Log the individual contribution for audit trail (skip if already contributed this month)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM mlm_royalty_contributions WHERE month_year = ? AND booking_id = ?
            ");
            $stmt->execute([$monthYear, $bookingId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO mlm_royalty_contributions (month_year, booking_id, payment_amount, contribution_amount, tenant_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$monthYear, $bookingId, $amountReceived, $contribution, $this->getTenantId()]);
            }

            // Fetch updated pool total
            $stmt = $this->pdo->prepare("SELECT total_pool_amount FROM mlm_royalty_pool WHERE month_year = ?");
            $stmt->execute([$monthYear]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $poolTotal = $row ? (float) $row['total_pool_amount'] : $contribution;

            return [
                'success'      => true,
                'contribution' => $contribution,
                'pool_total'   => $poolTotal,
            ];
        } catch (\Throwable $e) {
            error_log("[RoyaltyPoolService] contribute FAILED: " . $e->getMessage());
            return ['success' => false, 'contribution' => 0, 'pool_total' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Month-end: distribute the royalty pool equally among qualified Site Managers.
     * A Site Manager qualifies if their monthly GBV >= ₹50 Lakhs.
     *
     * @param string $monthYear  Format: YYYY-MM
     * @return array{success: bool, pool_amount: float, qualified_managers: int, per_share: float, ledger_ids: array, error?: string}
     */
    public function distribute(string $monthYear): array
    {
        $this->pdo->beginTransaction();
        try {
            // Fetch pool total
            $stmt = $this->pdo->prepare("SELECT * FROM mlm_royalty_pool WHERE month_year = ? FOR UPDATE");
            $stmt->execute([$monthYear]);
            $pool = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$pool || (float) $pool['total_pool_amount'] <= 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'No pool found or pool is empty for ' . $monthYear];
            }

            if ($pool['distributed_status'] === 'distributed') {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Pool already distributed for ' . $monthYear];
            }

            $poolAmount = (float) $pool['total_pool_amount'];

            // Find qualified Site Managers: monthly GBV >= ₹50L
            $stmt = $this->pdo->prepare("
                SELECT
                    pb.associate_id,
                    u.id AS user_id,
                    COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS monthly_gbv
                FROM plot_bookings pb
                JOIN associates a ON a.id = pb.associate_id
                JOIN users u ON u.id = a.user_id
                WHERE YEAR(pb.created_at) = YEAR(CONCAT(?, '-01'))
                  AND MONTH(pb.created_at) = MONTH(CONCAT(?, '-01'))
                  AND pb.status NOT IN ('cancelled', 'refunded')
                GROUP BY pb.associate_id, u.id
                HAVING monthly_gbv >= ?
            ");
            $stmt->execute([$monthYear, $monthYear, self::ROYALTY_QUALIFICATION_GBV]);
            $qualified = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $managerCount = count($qualified);
            if ($managerCount === 0) {
                // No qualified managers — keep pool accumulating
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'No qualified Site Managers for ' . $monthYear . ' (need ≥₹50L GBV each)'];
            }

            $perShare = round($poolAmount / $managerCount, 2);
            $ledgerIds = [];

            foreach ($qualified as $mgr) {
                $planSnapshot = $this->rankService->getActivePlanSnapshot();
                $stmt = $this->pdo->prepare("
                    INSERT INTO mlm_commission_ledger
                        (beneficiary_user_id, source_user_id, commission_type, amount, sale_amount,
                         commission_percentage, status, notes, created_at,
                         plan_id, plan_version, plan_snapshot, calculation_engine, tenant_id)
                    VALUES (?, ?, 'royalty_pool', ?, ?, 0, 'pending', ?, NOW(),
                            ?, ?, ?, 'hybrid', ?)
                ");
                $note = "Site Manager Royalty Pool — {$monthYear} share (Pool: ₹" . number_format($poolAmount) . " ÷ {$managerCount} managers)";
                $stmt->execute([
                    $mgr['user_id'], $mgr['user_id'], $perShare, $poolAmount, $note,
                    $planSnapshot['plan_id'] ?? null,
                    $planSnapshot['plan_version'] ?? null,
                    $planSnapshot ? json_encode($planSnapshot) : null,
                    $this->getTenantId(),
                ]);
                $ledgerIds[] = $this->pdo->lastInsertId();
            }

            // Mark pool as distributed
            $stmt = $this->pdo->prepare("
                UPDATE mlm_royalty_pool
                SET distributed_status = 'distributed',
                    distributed_at = NOW(),
                    total_qualified_managers = ?,
                    per_manager_share = ?
                WHERE month_year = ? AND tenant_id = ?
            ");
            $stmt->execute([$managerCount, $perShare, $monthYear, $this->getTenantId()]);

            $this->pdo->commit();

            return [
                'success'             => true,
                'pool_amount'         => $poolAmount,
                'qualified_managers'  => $managerCount,
                'per_share'           => $perShare,
                'ledger_ids'          => $ledgerIds,
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[RoyaltyPoolService] distribute FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get current month's royalty pool status.
     */
    public function getStatus(?string $monthYear = null): array
    {
        $monthYear = $monthYear ?: date('Y-m');
        $stmt = $this->pdo->prepare("SELECT * FROM mlm_royalty_pool WHERE month_year = ?");
        $stmt->execute([$monthYear]);
        $pool = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pool) {
            return [
                'month_year'           => $monthYear,
                'total_pool_amount'    => 0,
                'qualified_managers'   => 0,
                'per_manager_share'    => 0,
                'distributed_status'   => 'accumulating',
                'contributions_count'  => 0,
            ];
        }

        // Count contributions
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(contribution_amount),0) AS total FROM mlm_royalty_contributions WHERE month_year = ?");
        $stmt->execute([$monthYear]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'month_year'           => $monthYear,
            'total_pool_amount'    => (float) $pool['total_pool_amount'],
            'qualified_managers'   => (int) $pool['total_qualified_managers'],
            'per_manager_share'    => (float) $pool['per_manager_share'],
            'distributed_status'   => $pool['distributed_status'],
            'distributed_at'       => $pool['distributed_at'] ?? null,
            'contributions_count'  => (int) ($stats['cnt'] ?? 0),
            'contributions_total'  => (float) ($stats['total'] ?? 0),
        ];
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