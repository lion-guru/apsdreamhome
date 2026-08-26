<?php

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

/**
 * MlmRoyaltyService — Monthly Royalty Pool Distribution Engine
 * ─────────────────────────────────────────────────────────────
 * Business Rules:
 *   1. Track C (2% from each booking payment) accumulates in royalty_pool_contributions.
 *   2. At month-end, admin triggers distribution → all Royalty Director+ level members
 *      receive a proportional share of the month's pool.
 *   3. Shareholder-level members get a separate leadership bonus configured in mlm_rank_slabs.
 *   4. Distribution records saved in royalty_pool_distributions.
 *
 * Tables Used:
 *   mlm_rank_slabs, mlm_profiles, royalty_pool_contributions,
 *   royalty_pool_distributions, users, mlm_network_tree
 */
class MlmRoyaltyService
{
    use ServiceTenantTrait;

    /** @var PDO */
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    /* ================================================================
       RANK SLAB ADMIN — CRUD (fully DB-driven, no code changes needed)
       ================================================================ */

    /**
     * Get all MLM ranks ordered by sort_order.
     * This is the SINGLE SOURCE OF TRUTH — admin edits these, engine reads these.
     */
    public function getAllRanks(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM mlm_rank_slabs
                ORDER BY sort_order ASC, min_gbv ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] getAllRanks: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single rank by ID.
     */
    public function getRank(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM mlm_rank_slabs WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] getRank: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update rank slab parameters from admin panel.
     * Allows changing GBV thresholds, commission %, royalty eligibility, rewards — no code change needed.
     */
    public function updateRank(int $id, array $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE mlm_rank_slabs SET
                    rank_name                = ?,
                    min_gbv                  = ?,
                    max_gbv                  = ?,
                    commission_rate          = ?,
                    reward_name              = ?,
                    reward_value             = ?,
                    is_active                = ?,
                    updated_at               = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['rank_name'],
                (float)($data['min_gbv']                ?? 0),
                (float)($data['max_gbv']                ?? 0),
                (float)($data['commission_rate']         ?? 0),
                $data['reward_name']        ?? null,
                (float)($data['reward_value'] ?? 0),
                (int)($data['is_active']      ?? 1),
                $id,
            ]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] updateRank: ' . $e->getMessage());
            return false;
        }
    }

    /* ================================================================
       ROYALTY POOL — MONTHLY CALCULATION & DISTRIBUTION
       ================================================================ */

    /**
     * Get total royalty pool collected for a given month.
     * Pool = SUM of all royalty_pool_contributions in that month.
     *
     * @param int $month  1-12
     * @param int $year
     * @return float Total pool amount in Rs.
     */
    public function getMonthlyPoolTotal(int $month, int $year): float
    {
        try {
            $monthStr = sprintf('%04d-%02d', $year, $month);
            $stmt     = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0)
                FROM royalty_pool_contributions
                WHERE DATE_FORMAT(contributed_at, '%Y-%m') = ?
            ");
            $stmt->execute([$monthStr]);
            return (float)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] getMonthlyPoolTotal: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get all associates currently at royalty-eligible ranks.
     * Returns user_id, name, rank_slug, gbv, royalty_pool_share_pct.
     */
    public function getEligibleRoyaltyMembers(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    mp.user_id,
                    u.name,
                    mp.current_level AS current_rank_slug,
                    mp.lifetime_sales AS total_gbv,
                    rs.commission_rate,
                    rs.reward_value
                FROM mlm_profiles mp
                JOIN users u ON u.id = mp.user_id
                JOIN mlm_rank_slabs rs ON rs.rank_slug = mp.current_level
                WHERE rs.is_active = 1
                  AND rs.commission_rate > 0
                ORDER BY mp.lifetime_sales DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] getEligibleRoyaltyMembers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Distribute the monthly royalty pool to all eligible members.
     * Proportional split based on each member's royalty_pool_share_pct (set per rank).
     * Members at the same rank share that rank's pool % equally.
     *
     * @param int $month
     * @param int $year
     * @return array {success, total_pool, distributed_to, per_member_amounts[]}
     */
    public function distributeMonthlyPool(int $month, int $year): array
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);

        try {
            $totalPool = $this->getMonthlyPoolTotal($month, $year);
            if ($totalPool <= 0) {
                return ['success' => false, 'error' => 'No royalty pool contributions found for ' . $monthStr, 'total_pool' => 0, 'distributed_to' => 0];
            }

            $members = $this->getEligibleRoyaltyMembers();
            if (empty($members)) {
                return ['success' => false, 'error' => 'No royalty-eligible members found', 'total_pool' => $totalPool, 'distributed_to' => 0];
            }

            // Group by rank slug to find how many members share each rank's pool %
            $rankGroups = [];
            foreach ($members as $m) {
                $slug = $m['current_rank_slug'];
                if (!isset($rankGroups[$slug])) {
                    $rankGroups[$slug] = ['share_pct' => (float)$m['commission_rate'], 'members' => []];
                }
                $rankGroups[$slug]['members'][] = $m;
            }

            // Calculate per-member amounts
            $distributions = [];
            foreach ($rankGroups as $slug => $group) {
                $rankPoolAmt  = $totalPool * ($group['share_pct'] / 100);
                $memberCount  = count($group['members']);
                $perMember    = $memberCount > 0 ? $rankPoolAmt / $memberCount : 0;

                foreach ($group['members'] as $m) {
                    $distributions[] = [
                        'user_id'    => (int)$m['user_id'],
                        'name'       => $m['name'],
                        'rank_slug'  => $slug,
                        'share_pct'  => round($group['share_pct'] / $memberCount, 4),
                        'amount'     => round($perMember, 2),
                    ];
                }
            }

            // Persist to royalty_pool_distributions
            $this->pdo->beginTransaction();

            // Delete any existing distribution for this month (idempotent re-run)
            $this->pdo->prepare("DELETE FROM royalty_pool_distributions WHERE month_year = ?")->execute([$monthStr]);

            $insStmt = $this->pdo->prepare("
                INSERT INTO royalty_pool_distributions
                    (user_id, month_year, share_pct, amount, distributed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($distributions as $d) {
                $insStmt->execute([
                    $d['user_id'],
                    $monthStr,
                    $d['share_pct'],
                    $d['amount'],
                ]);
            }

            $this->pdo->commit();

            return [
                'success'        => true,
                'month'          => $monthStr,
                'total_pool'     => round($totalPool, 2),
                'distributed_to' => count($distributions),
                'breakdown'      => $distributions,
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[MlmRoyaltyService] distributeMonthlyPool: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'total_pool' => 0, 'distributed_to' => 0];
        }
    }

    /**
     * Get distribution records for a given month (for display in admin panel).
     */
    public function getMonthlyDistribution(int $month, int $year): array
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.*, u.name, u.email
                FROM royalty_pool_distributions d
                JOIN users u ON u.id = d.user_id
                WHERE d.month_year = ?
                ORDER BY d.amount DESC
            ");
            $stmt->execute([$monthStr]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] getMonthlyDistribution: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Determine which rank a user belongs to based on their current GBV.
     * Uses mlm_rank_slabs as source of truth — admin can change thresholds anytime.
     *
     * @param float $gbv  User's lifetime Group Business Volume
     * @return array|null rank row or null
     */
    public function resolveRankByGbv(float $gbv): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM mlm_rank_slabs
                WHERE is_active = 1
                  AND min_gbv <= ?
                  AND (max_gbv = 0 OR max_gbv >= ?)
                ORDER BY min_gbv DESC
                LIMIT 1
            ");
            $stmt->execute([$gbv, $gbv]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[MlmRoyaltyService] resolveRankByGbv: ' . $e->getMessage());
            return null;
        }
    }
}
