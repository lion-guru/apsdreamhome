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
            $config = require $root . '/config/database.php';
            $pdo    = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
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
            $stmt = $this->pdo->prepare("
                INSERT INTO royalty_pool_contributions (booking_id, amount, contributed_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$bookingId, $contribution]);
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
            $contribStmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM royalty_pool_contributions");
            $totalContrib = (float)$contribStmt->fetchColumn();

            // Total already distributed
            $distStmt = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM royalty_pool_distributions");
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
            $guard = $this->pdo->prepare("
                SELECT COUNT(*) FROM royalty_pool_distributions WHERE month_year = ?
            ");
            $guard->execute([$monthYear]);
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

                // Write to royalty_pool_distributions
                $insStmt->execute([$agent['user_id'], $monthYear, $sharePct, $shareAmt]);

                // Write to mlm_commission_ledger
                $ledgerStmt->execute([
                    $agent['user_id'],
                    $shareAmt,
                    $poolBalance,  // sale_amount = total pool for reference
                    $sharePct,
                    $note,
                ]);

                // Broadcast notification
                try {
                    \App\Services\WebSocketBroadcaster::broadcastToUser($agent['user_id'], [
                        'event'   => 'royalty_credited',
                        'amount'  => $shareAmt,
                        'month'   => $monthYear,
                    ]);
                } catch (\Throwable $ex) {
                    // non-fatal
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
                SELECT rpd.*, u.name AS agent_name, mp.current_rank
                FROM royalty_pool_distributions rpd
                JOIN users u ON u.id = rpd.user_id
                LEFT JOIN mlm_profiles mp ON mp.user_id = rpd.user_id
            ";
            $params = [];
            if ($monthYear) {
                $sql    .= " WHERE rpd.month_year = ?";
                $params[] = $monthYear;
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
            $contrib = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM royalty_pool_contributions")->fetchColumn();
            $dist    = (float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM royalty_pool_distributions")->fetchColumn();
            $months  = (int)$this->pdo->query("SELECT COUNT(DISTINCT month_year) FROM royalty_pool_distributions")->fetchColumn();

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
                SELECT mp.user_id, mp.lifetime_sales, mp.current_rank, u.name
                FROM mlm_profiles mp
                JOIN users u ON u.id = mp.user_id
                WHERE mp.current_rank IN ($inClause)
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
