<?php
/**
 * MLM Policy Guard — Sustainability Safeguards
 *
 * Prevents single-leg parasite promotions by enforcing:
 *  - 50% Max Leg Rule (no single leg contributes >50% of adjusted GBV)
 *  - Minimum PBV (Personal Business Volume) of 10% of target rank
 *  - Active monthly maintenance volume checks
 *
 * Used by rank promotion cron and HybridCommissionEngine.
 */

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class MlmPolicyGuard
{
    use ServiceTenantTrait;
    /** @var PDO|null */
    private $pdo;

    /** Minimum PBV as % of target rank GBV */
    private const MIN_PBV_PCT = 10.0;

    /** Max single-leg contribution as % of adjusted GBV */
    private const MAX_LEG_PCT = 50.0;

    /** Minimum monthly GBV for a qualifying month (Track B threshold) */
    private const MIN_MONTHLY_GBV = 50000;

    /** Rank thresholds for promotion eligibility (GBV) */
    private const RANK_THRESHOLDS = [
        'associate'     => 0,
        'sr_associate'  => 1000000,    // ₹10L
        'bdm'           => 3500000,    // ₹35L
        'sr_bdm'        => 7000000,    // ₹70L
        'vice_president'=> 15000000,   // ₹1.5Cr
        'president'     => 30000000,   // ₹3Cr
        'site_manager'  => 50000000,   // ₹5Cr
    ];

    /** Rank display names */
    private const RANK_NAMES = [
        'associate'      => 'Associate',
        'sr_associate'   => 'Sr. Associate',
        'bdm'            => 'BDM',
        'sr_bdm'         => 'Sr. BDM',
        'vice_president' => 'Vice President',
        'president'      => 'President',
        'site_manager'   => 'Site Manager',
    ];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if (!$this->pdo) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $this->pdo = method_exists($db, 'getPdo') ? $db->getPdo() : $db;
            } catch (Exception $e) {
                $this->pdo = null;
            }
        }
    }

    /* ================================================================
       PUBLIC API
       ================================================================ */

    /**
     * Check if an associate qualifies for a rank promotion.
     *
     * Enforces:
     *  1. Personal Business Volume (PBV) ≥ 10% of target rank GBV
     *  2. Adjusted GBV (with 50% max-leg cap) ≥ target rank GBV
     *  3. Active monthly maintenance (≥₹50K/month for consecutive months)
     *
     * @param int    $userId          users.id
     * @param string $targetRankSlug  Target rank to check eligibility for
     * @return array{eligible: bool, adjusted_gbv: float, personal_sales: float, reason: string, warnings: string[]}
     */
    public function checkPromotionEligibility(int $userId, string $targetRankSlug): array
    {
        $targetGbv = self::RANK_THRESHOLDS[$targetRankSlug] ?? 0;
        if ($targetGbv <= 0) {
            return ['eligible' => false, 'reason' => "Unknown rank: {$targetRankSlug}"];
        }

        // 1. Get Personal Business Volume (PBV) from plot_bookings
        $personalSales = $this->getPbv($userId);
        $minPbvRequired = $targetGbv * (self::MIN_PBV_PCT / 100);

        if ($personalSales < $minPbvRequired) {
            return [
                'eligible'       => false,
                'personal_sales' => $personalSales,
                'adjusted_gbv'   => 0,
                'reason'         => "Insufficient personal sales. Required: ₹" . number_format($minPbvRequired) . ", Achieved: ₹" . number_format($personalSales),
                'warnings'       => [],
            ];
        }

        // 2. Fetch all direct downline legs and their volume
        $legs = $this->getDownlineLegs($userId);
        $adjustedGbv = $personalSales;
        $maxLegAllowed = $targetGbv * (self::MAX_LEG_PCT / 100);
        $warnings = [];

        foreach ($legs as $leg) {
            $volume = (float) $leg['leg_volume'];
            if ($volume > $maxLegAllowed) {
                $adjustedGbv += $maxLegAllowed;
                $warnings[] = "Leg (User ID: {$leg['user_id']}, Name: {$leg['name']}) volume capped at 50% limit (₹" . number_format($maxLegAllowed) . "). Actual: ₹" . number_format($volume);
            } else {
                $adjustedGbv += $volume;
            }
        }

        if ($adjustedGbv >= $targetGbv) {
            return [
                'eligible'       => true,
                'personal_sales' => $personalSales,
                'adjusted_gbv'   => $adjustedGbv,
                'reason'         => '',
                'warnings'       => $warnings,
            ];
        }

        return [
            'eligible'       => false,
            'personal_sales' => $personalSales,
            'adjusted_gbv'   => $adjustedGbv,
            'reason'         => "Adjusted GBV ₹" . number_format($adjustedGbv) . " is below required ₹" . number_format($targetGbv),
            'warnings'       => $warnings,
        ];
    }

    /**
     * Check active monthly maintenance — how many consecutive months
     * the associate or their team had ≥₹50K GBV.
     *
     * @param int $userId
     * @return array{consecutive_months: int, qualifying_months: int, total_volume: float}
     */
    public function checkMonthlyMaintenance(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("
            SELECT
                DATE_FORMAT(pb.created_at, '%Y-%m') AS ym,
                COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS month_total
            FROM plot_bookings pb
            JOIN associates a ON a.id = pb.associate_id
            WHERE a.user_id = ?
              AND pb.status NOT IN ('cancelled', 'refunded')
            " . ($tid > 1 ? " AND pb.tenant_id = ?" : "") . "
            GROUP BY ym
            ORDER BY ym DESC
            LIMIT 12
        ");
        $stmt->execute($tid > 1 ? [$userId, $tid] : [$userId]);
        $months = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $consecutive = 0;
        $qualifying = 0;
        $totalVolume = 0.0;
        $expected = new \DateTime('first day of this month');

        foreach ($months as $row) {
            $ym = $row['ym'];
            $monthTot = (float) $row['month_total'];
            $totalVolume += $monthTot;

            if ($monthTot >= self::MIN_MONTHLY_GBV) {
                $qualifying++;
            }

            $expectedYmd = $expected->format('Y-m');
            if ($ym === $expectedYmd && $monthTot >= self::MIN_MONTHLY_GBV) {
                $consecutive++;
                $expected->modify('-1 month');
            } elseif ($ym < $expectedYmd) {
                break;
            }
        }

        return [
            'consecutive_months' => $consecutive,
            'qualifying_months'  => $qualifying,
            'total_volume'       => $totalVolume,
        ];
    }

    /**
     * Get comprehensive promotion report for an associate.
     *
     * @param int $userId
     * @return array Rank eligibility details for all ranks
     */
    public function getPromotionReport(int $userId): array
    {
        $currentRank = $this->resolveCurrentRank($userId);
        $results = [];

        foreach (self::RANK_THRESHOLDS as $slug => $gbv) {
            if ($gbv <= 0) continue;
            $eligibility = $this->checkPromotionEligibility($userId, $slug);
            $results[$slug] = [
                'rank_name'        => self::RANK_NAMES[$slug] ?? $slug,
                'gbv_required'     => $gbv,
                'eligible'         => $eligibility['eligible'],
                'adjusted_gbv'     => $eligibility['adjusted_gbv'],
                'personal_sales'   => $eligibility['personal_sales'],
                'reason'           => $eligibility['reason'],
                'warnings'         => $eligibility['warnings'],
                'is_current'       => ($slug === $currentRank),
                'is_higher'        => array_search($slug, array_keys(self::RANK_THRESHOLDS)) >
                                      array_search($currentRank, array_keys(self::RANK_THRESHOLDS)),
            ];
        }

        $maintenance = $this->checkMonthlyMaintenance($userId);

        return [
            'current_rank'  => $currentRank,
            'current_name'  => self::RANK_NAMES[$currentRank] ?? $currentRank,
            'ranks'         => $results,
            'maintenance'   => $maintenance,
        ];
    }

    /**
     * Get the display name for a rank slug.
     */
    public function getRankName(string $slug): string
    {
        return self::RANK_NAMES[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
    }

    /**
     * Get all rank thresholds.
     */
    public function getRankThresholds(): array
    {
        return self::RANK_THRESHOLDS;
    }

    /* ================================================================
       PRIVATE HELPERS
       ================================================================ */

    /**
     * Get Personal Business Volume (PBV) — total direct sales by the associate.
     */
    private function getPbv(int $userId): float
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0)
            FROM plot_bookings pb
            JOIN associates a ON a.id = pb.associate_id
            WHERE a.user_id = ?
              AND pb.status NOT IN ('cancelled', 'refunded')"
            . ($tid > 1 ? " AND pb.tenant_id = ?" : "") . "
        ");
        $stmt->execute($tid > 1 ? [$userId, $tid] : [$userId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Get all direct downline legs and their cumulative volume.
     */
    private function getDownlineLegs(int $userId): array
    {
        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("
            SELECT
                mnt.associate_id AS user_id,
                u.name,
                COALESCE(mp.lifetime_sales, 0) AS leg_volume
            FROM mlm_network_tree mnt
            JOIN users u ON u.id = mnt.associate_id
            LEFT JOIN mlm_profiles mp ON mp.user_id = mnt.associate_id
            WHERE mnt.parent_id = ?"
            . ($tid > 1 ? " AND mnt.tenant_id = ?" : "") . "
        ");
        $stmt->execute($tid > 1 ? [$userId, $tid] : [$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resolve current rank from mlm_profiles.current_level.
     */
    private function resolveCurrentRank(int $userId): string
    {
        $stmt = $this->pdo->prepare("SELECT current_level FROM mlm_profiles WHERE user_id = ?" . $this->tenantSql() . " LIMIT 1");
        $stmt->execute([$userId]);
        $level = $stmt->fetchColumn();
        return $level ?: 'associate';
    }
}
