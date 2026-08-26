<?php

namespace App\Services;

use PDO;
use Exception;

/**
 * RoyaltyPoolService — DEPRECATED
 * ──────────────────
 * This service used royalty_pool_contributions / royalty_pool_distributions tables
 * which are now empty (0 rows). All royalty pool logic is handled by
 * HybridCommissionEngine::distributeRoyaltyPool() which uses mlm_royalty_pool
 * and mlm_royalty_contributions tables.
 *
 * The cron script scripts/run_royalty_pool.php calls HybridCommissionEngine directly.
 *
 * This file is kept for backward compatibility only. Do NOT add new code here.
 *
 * @deprecated Use HybridCommissionEngine::distributeRoyaltyPool() instead
 */
class RoyaltyPoolService
{
    use \App\Traits\ServiceTenantTrait;

    /** Minimum rank slugs eligible for royalty distribution (inclusive). */
    private const ELIGIBLE_RANKS = ['vice_president', 'president', 'site_manager'];

    /** @var PDO */
    private $pdo;

    /** @var HybridCommissionEngine */
    private $engine;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            $root   = dirname(__DIR__, 2);
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
        $this->pdo    = $pdo;
        $this->engine = new HybridCommissionEngine($pdo);
    }

    /* ─────────────────────────────────────────────────────────────────
       1. CONTRIBUTE — record 2% of incoming payment to pool
    ───────────────────────────────────────────────────────────────── */

    /**
     * Record a royalty pool contribution (2% of $paymentAmount).
     * This is idempotent for the same booking_id + receipt combo.
     *
     * @param int   $bookingId
     * @param float $paymentAmount  Actual cash received
     * @return float  Amount contributed
     */
    public function contributeToPool(int $bookingId, float $paymentAmount): float
    {
        $contribution = round($paymentAmount * 0.02, 2);
        if ($contribution <= 0) {
            return 0.0;
        }

        try {
            // Ensure table exists (fail silently if not — migration pending)
            $tenantIns = $this->tenantInsertData();
            $insCols = array_merge(['booking_id', 'amount', 'contributed_at'], array_keys($tenantIns));
            $insVals = array_merge([$bookingId, $contribution], array_values($tenantIns));
            $colStr = implode(', ', $insCols);
            $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
            $stmt = $this->pdo->prepare("INSERT INTO royalty_pool_contributions ($colStr) VALUES ($placeholders)");
            $stmt->execute($insVals);
        } catch (Exception $e) {
            error_log("[RoyaltyPoolService] contributeToPool failed: " . $e->getMessage());
        }

        return $contribution;
    }

    /* ─────────────────────────────────────────────────────────────────
       2. POOL BALANCE — undistributed total
    ───────────────────────────────────────────────────────────────── */

    /**
     * Get total undistributed royalty pool balance.
     */
    public function getPoolBalance(): float
    {
        try {
            // Total contributed
            $tenantSql = $this->tenantSql();
            $contribStmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM royalty_pool_contributions{$tenantSql}");
            $totalContrib = (float)$contribStmt->fetchColumn();

            // Total already distributed
            $tenantSql2 = $this->tenantSql();
            $distStmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM royalty_pool_distributions{$tenantSql2}");
            $totalDist = (float)$distStmt->fetchColumn();

            return max(0.0, $totalContrib - $totalDist);
        } catch (Exception $e) {
            error_log("[RoyaltyPoolService] getPoolBalance failed: " . $e->getMessage());
            return 0.0;
        }
    }

    /* ─────────────────────────────────────────────────────────────────
       3. DISTRIBUTE — monthly cron entry point
    ───────────────────────────────────────────────────────────────── */

    /**
     * Distribute accumulated royalty pool to eligible agents.
     *
     * Called by the monthly cron (last day of month or 1st of next month).
     * Eligible agents: Vice President, President, Site Manager rank.
     * Distribution is proportional to each eligible agent's lifetime GBV.
     *
     * @param  string|null $monthYear  'YYYY-MM' — defaults to current month
     * @return array{success:bool, month:string, pool_balance:float, recipients:int, distributed:float}
     */
    public function distributeMonthlyRoyalty(?string $monthYear = null): array
    {
        $monthYear = $monthYear ?? date('Y-m');

        try {
            // Guard: already distributed for this month?
            $tenantSql = $this->tenantSql();
            $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
            $guard = $this->pdo->prepare("
                SELECT COUNT(*) FROM royalty_pool_distributions WHERE month_year = ?{$tenantSql}
            ");
            $guard->execute(array_merge([$monthYear], $tenantParam));
            if ((int)$guard->fetchColumn() > 0) {
                return [
                    'success'     => false,
                    'error'       => "Royalty already distributed for {$monthYear}",
                    'month'       => $monthYear,
                    'pool_balance'=> 0,
                    'recipients'  => 0,
                    'distributed' => 0,
                ];
            }

            $poolBalance = $this->getPoolBalance();
            if ($poolBalance < 1) {
                return [
                    'success'     => false,
                    'error'       => 'Pool balance is zero — nothing to distribute',
                    'month'       => $monthYear,
                    'pool_balance'=> 0,
                    'recipients'  => 0,
                    'distributed' => 0,
                ];
            }

            // ── Fetch eligible agents and their GBV ──────────────────
            $eligibleAgents = $this->getEligibleAgents();
            if (empty($eligibleAgents)) {
                return [
                    'success'     => false,
                    'error'       => 'No eligible agents (VP or above) found',
                    'month'       => $monthYear,
                    'pool_balance'=> $poolBalance,
                    'recipients'  => 0,
                    'distributed' => 0,
                ];
            }

            // ── Calculate proportional shares ────────────────────────
            $totalGbv = array_sum(array_column($eligibleAgents, 'lifetime_sales'));
            if ($totalGbv <= 0) {
                // Equal split if no GBV data
                $totalGbv = count($eligibleAgents);
                foreach ($eligibleAgents as &$a) {
                    $a['lifetime_sales'] = 1;
                }
                unset($a);
            }

            $this->pdo->beginTransaction();

            $totalDistributed = 0.0;
            $recipientCount   = 0;

            $insStmt = $this->pdo->prepare("
                INSERT INTO royalty_pool_distributions
                    (user_id, month_year, share_pct, amount, distributed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $ledgerStmt = $this->pdo->prepare("
                INSERT INTO mlm_commission_ledger
                    (beneficiary_user_id, source_user_id, commission_type, amount,
                     level, sale_amount, commission_percentage, status, notes,
                     property_id, created_at)
                VALUES (?, 0, 'royalty_pool', ?, 0, ?, ?, 'approved', ?, NULL, NOW())
            ");

            foreach ($eligibleAgents as $agent) {
                $gbv        = (float)$agent['lifetime_sales'];
                $sharePct   = $totalGbv > 0 ? round(($gbv / $totalGbv) * 100, 4) : 0;
                $shareAmt   = round($poolBalance * ($gbv / $totalGbv), 2);

                if ($shareAmt < 0.01) {
                    continue;
                }

                $note = "Royalty Pool Distribution — {$monthYear} — {$sharePct}% share";

                // Write to royalty_pool_distributions with tenant scoping
                $tenantIns = $this->tenantInsertData();
                $insTenantCols = array_keys($tenantIns);
                $insTenantVals = array_values($tenantIns);
                $distInsCols = array_merge(['user_id', 'month_year', 'share_pct', 'amount', 'distributed_at'], $insTenantCols);
                $distInsVals = array_merge([$agent['user_id'], $monthYear, $sharePct, $shareAmt], $insTenantVals);
                $distColStr = implode(', ', $distInsCols);
                $distPlaceholders = implode(', ', array_fill(0, count($distInsVals), '?'));
                $insStmt->execute($distInsVals);

                // Write to mlm_commission_ledger with tenant scoping
                $ledgerTenantCols = array_keys($tenantIns);
                $ledgerTenantVals = array_values($tenantIns);
                $ledgerInsCols = array_merge(['beneficiary_user_id', 'source_user_id', 'commission_type', 'amount',
                    'level', 'sale_amount', 'commission_percentage', 'status', 'notes', 'property_id', 'created_at'], $ledgerTenantCols);
                $ledgerInsVals = array_merge([$agent['user_id'], 0, 'royalty_pool', $shareAmt,
                    0, $poolBalance, $sharePct, 'approved', $note, null], $ledgerTenantVals);
                $ledgerColStr = implode(', ', $ledgerInsCols);
                $ledgerPlaceholders = implode(', ', array_fill(0, count($ledgerInsVals), '?'));
                $ledgerStmt->execute($ledgerInsVals);

                // Broadcast notification
                try {
                    \App\Services\WebSocketBroadcaster::broadcastToUser($agent['user_id'], [
                        'event'   => 'royalty_credited',
                        'amount'  => $shareAmt,
                        'month'   => $monthYear,
                    ]);
                } catch (\Throwable $ex) {
                // non-fatal
                error_log($ex->getMessage());
                }

                $totalDistributed += $shareAmt;
                $recipientCount++;
            }

            $this->pdo->commit();

            return [
                'success'      => true,
                'month'        => $monthYear,
                'pool_balance' => $poolBalance,
                'recipients'   => $recipientCount,
                'distributed'  => round($totalDistributed, 2),
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[RoyaltyPoolService] distributeMonthlyRoyalty FAILED: " . $e->getMessage());
            return [
                'success'     => false,
                'error'       => $e->getMessage(),
                'month'       => $monthYear,
                'pool_balance'=> 0,
                'recipients'  => 0,
                'distributed' => 0,
            ];
        }
    }

    /* ─────────────────────────────────────────────────────────────────
       4. HISTORY — past distributions
    ───────────────────────────────────────────────────────────────── */

    /**
     * Get distribution history for a given month or all months.
     */
    public function getDistributionHistory(?string $monthYear = null): array
    {
        try {
            $sql = "
                SELECT rpd.*, u.name AS agent_name, mp.current_level AS current_rank
                FROM royalty_pool_distributions rpd
                JOIN users u ON u.id = rpd.user_id
                LEFT JOIN mlm_profiles mp ON mp.user_id = rpd.user_id
            ";
            $params = [];
            if ($monthYear) {
                $sql    .= " WHERE rpd.month_year = ?{$this->tenantSql()}";
                $params = array_merge([$monthYear], $this->tenantId() > 1 ? [$this->tenantId()] : []);
            } else {
                $sql .= "{$this->tenantSql()}";
                if ($this->tenantId() > 1) {
                    $params[] = $this->tenantId();
                }
            }
            $sql .= " ORDER BY rpd.distributed_at DESC LIMIT 500";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[RoyaltyPoolService] getDistributionHistory failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Summary stats for the royalty pool.
     */
    public function getPoolStats(): array
    {
        try {
            $tenantSql = $this->tenantSql();
            $contrib = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM royalty_pool_contributions{$tenantSql}")->fetchColumn();
            $dist    = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM royalty_pool_distributions{$tenantSql}")->fetchColumn();
            $months  = (int)$this->pdo->query("SELECT COUNT(DISTINCT month_year) FROM royalty_pool_distributions{$tenantSql}")->fetchColumn();

            return [
                'total_contributed' => $contrib,
                'total_distributed' => $dist,
                'undistributed'     => max(0, $contrib - $dist),
                'distribution_months' => $months,
            ];
        } catch (Exception $e) {
            error_log("[RoyaltyPoolService] getPoolStats failed: " . $e->getMessage());
            return ['total_contributed' => 0, 'total_distributed' => 0, 'undistributed' => 0, 'distribution_months' => 0];
        }
    }

    /* ─────────────────────────────────────────────────────────────────
       PRIVATE HELPERS
    ───────────────────────────────────────────────────────────────── */

    /**
     * Fetch all agents at Vice President rank or above with their GBV.
     */
    private function getEligibleAgents(): array
    {
        try {
            $inClause = implode(',', array_fill(0, count(self::ELIGIBLE_RANKS), '?'));
            $stmt = $this->pdo->prepare("
                SELECT mp.user_id, mp.lifetime_sales, mp.current_level AS current_rank, u.name
                FROM mlm_profiles mp
                JOIN users u ON u.id = mp.user_id
                WHERE mp.current_level IN ($inClause)
                  AND mp.lifetime_sales > 0
                ORDER BY mp.lifetime_sales DESC
            ");
            $stmt->execute(self::ELIGIBLE_RANKS);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[RoyaltyPoolService] getEligibleAgents failed: " . $e->getMessage());
            return [];
        }
    }
}
