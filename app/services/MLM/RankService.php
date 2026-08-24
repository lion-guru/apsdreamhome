<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Rank Service for MLM Commission Engine
 * Handles rank slabs, names, rates, salary tiers, and rank resolution
 */
class RankService
{
    use ServiceTenantTrait;

    /**
     * Rank → GBV threshold → direct commission rate.
     * GBV = Cumulative Group Business Volume in ₹.
     * Rates are percentage points (not fractions).
     */
    private const RANK_SLABS = [
        'associate'      => ['min_gbv' =>          0, 'max_gbv' =>    1000000, 'rate' =>  5],
        'sr_associate'   => ['min_gbv' =>    1000000, 'max_gbv' =>    3500000, 'rate' =>  7],
        'bdm'            => ['min_gbv' =>    3500000, 'max_gbv' =>    7000000, 'rate' => 10],
        'sr_bdm'         => ['min_gbv' =>    7000000, 'max_gbv' =>   15000000, 'rate' => 12],
        'vice_president' => ['min_gbv' =>   15000000, 'max_gbv' =>   30000000, 'rate' => 15],
        'president'      => ['min_gbv' =>   30000000, 'max_gbv' =>   50000000, 'rate' => 18],
        'site_manager'   => ['min_gbv' =>   50000000, 'max_gbv' =>           0, 'rate' => 20], // 0 = uncapped
    ];

    /** Rank display names keyed by internal slug. */
    private const RANK_NAMES = [
        'associate'      => 'Associate',
        'sr_associate'   => 'Sr. Associate',
        'bdm'            => 'BDM',
        'sr_bdm'         => 'Sr. BDM',
        'vice_president' => 'Vice President',
        'president'      => 'President',
        'site_manager'   => 'Site Manager',
    ];

    /* ================================================================
       SECTION 4 — TARGET ACHIEVEMENT INCENTIVE TIERS
       (Freelance associate who completes a sales target within a window
        gets a monthly incentive grant for a fixed number of months.)
       ================================================================ */

    private const SALARY_TIERS = [
        ['volume_threshold' =>  1500000, 'window_days' =>  60, 'monthly_grant' =>  5000, 'months' =>  6],
        ['volume_threshold' =>  3000000, 'window_days' => 100, 'monthly_grant' =>  5000, 'months' => 12],
        ['volume_threshold' =>  5000000, 'window_days' => 150, 'monthly_grant' =>  8000, 'months' => 12],
        ['volume_threshold' =>  7500000, 'window_days' => 200, 'monthly_grant' => 12000, 'months' => 12],
        ['volume_threshold' => 10000000, 'window_days' => 300, 'monthly_grant' => 20000, 'months' => 12],
    ];

    /**
     * Return all rank slabs.
     */
    public function getRankSlabs(): array
    {
        return self::RANK_SLABS;
    }

    /**
     * Return rank display names.
     */
    public function getRankNames(): array
    {
        return self::RANK_NAMES;
    }

    /**
     * Get rank name by slug.
     */
    public function getRankName(string $rankSlug): string
    {
        return self::RANK_NAMES[$rankSlug] ?? $rankSlug;
    }

    /**
     * Get direct commission rate for a rank slug.
     */
    public function getRankRate(string $rankSlug): float
    {
        return (float)(self::RANK_SLABS[$rankSlug]['rate'] ?? 0);
    }

    /**
     * Get salary incentive tiers.
     */
    public function getSalaryTiers(): array
    {
        return self::SALARY_TIERS;
    }

    /**
     * Resolve agent rank based on their GBV.
     * Returns the rank slug.
     */
    public function resolveRank(int $agentId): string
    {
        // This requires DB access - agent's GBV needs to be fetched
        // For now return the default rank; actual implementation
        // would query the agent's GBV from mlm_profiles or similar
        return 'associate';
    }

    /**
     * Load rank slabs from database (mlm_rank_slabs table).
     * Falls back to hardcoded constants if table doesn't exist or empty.
     */
    public function loadRankSlabsFromDb(): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];

            $stmt = $db->prepare("SELECT rank_slug, min_gbv, max_gbv, direct_rate FROM mlm_rank_slabs" . $tidSql . " ORDER BY min_gbv");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $slabs = [];
                foreach ($rows as $row) {
                    $slabs[$row['rank_slug']] = [
                        'min_gbv' => (int)$row['min_gbv'],
                        'max_gbv' => (int)$row['max_gbv'],
                        'rate'    => (float)$row['direct_rate'],
                    ];
                }
                return $slabs;
            }
        } catch (\Throwable $e) {
            error_log('RankService::loadRankSlabsFromDb error: ' . $e->getMessage());
        }

        // Fallback to hardcoded
        return self::RANK_SLABS;
    }

    /**
     * Get hardcoded rank slabs (fallback).
     */
    public function getHardcodedSlabs(): array
    {
        return self::RANK_SLABS;
    }

    /**
     * Get active plan caps from database.
     * Falls back to hardcoded constants if table doesn't exist.
     */
    public function getActivePlanCaps(): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];

            $stmt = $db->prepare("SELECT global_cap_pct, track_a_pct, track_b_pct, track_c_pct, same_level_override_gen1, same_level_override_gen2 FROM mlm_commission_plans WHERE status = 'active'" . $tidSql . " ORDER BY version DESC LIMIT 1");
            $stmt->execute($params);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($plan) {
                return [
                    'global_cap'       => (float)$plan['global_cap_pct'],
                    'track_a'          => (float)$plan['track_a_pct'],
                    'track_b'          => (float)$plan['track_b_pct'],
                    'track_c'          => (float)$plan['track_c_pct'],
                    'same_level_gen1'  => (float)$plan['same_level_override_gen1'],
                    'same_level_gen2'  => (float)$plan['same_level_override_gen2'],
                ];
            }
        } catch (\Throwable $e) {
            error_log('RankService::getActivePlanCaps error: ' . $e->getMessage());
        }

        // Hardcoded fallback
        return [
            'global_cap'       => 20,
            'track_a'          => 15,
            'track_b'          => 3,
            'track_c'          => 2,
            'same_level_gen1'  => 2.0,
            'same_level_gen2'  => 1.0,
        ];
    }
}