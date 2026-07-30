<?php

namespace App\Services;

use PDO;

/**
 * Commission Simulator — What-If Analysis Engine
 * ────────────────────────────────────────────────
 * Compares commission plans, simulates full upline chain payouts,
 * and provides side-by-side what-if analysis.
 *
 * Pure computation — no DB writes (except read plan data).
 */
class CommissionSimulator
{
    /** @var PDO */
    private $pdo;

    /** @var CommissionPlanService */
    private $planService;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
        $this->planService = new CommissionPlanService($this->pdo);
    }

    /**
     * Simulate a single plan payout for a given sale amount and seller rank.
     *
     * @param float $saleAmount    Payment/plot sale amount in ₹
     * @param int   $planId        Commission plan ID
     * @param int   $sellerRankIdx 0-based rank index (0=Associate, 1=Sr. Assoc, etc.)
     * @param array $uplineRanks   Optional array of upline rank indices [0,1,2,...]
     * @return array Full breakdown
     */
    public function simulateSale(float $saleAmount, int $planId, int $sellerRankIdx, array $uplineRanks = []): array
    {
        $plan = $this->planService->getPlanById($planId);
        if (!$plan) return ['success' => false, 'error' => 'Plan not found'];

        $levels = $plan['levels'];
        if (empty($levels)) return ['success' => false, 'error' => 'Plan has no levels'];

        $sellerLevel = $levels[$sellerRankIdx] ?? null;
        if (!$sellerLevel) return ['success' => false, 'error' => 'Invalid rank index'];

        $sellerRate = (float)$sellerLevel['direct_commission'];
        $globalCap = $saleAmount * ((float)$plan['global_cap_pct'] / 100);
        $trackABudget = $saleAmount * ((float)$plan['track_a_pct'] / 100);
        $trackBBudget = $saleAmount * ((float)$plan['track_b_pct'] / 100);
        $trackCBudget = $saleAmount * ((float)$plan['track_c_pct'] / 100);
        $royaltyContrib = $saleAmount * ((float)$plan['royalty_pool_pct'] / 100);

        $result = [
            'success' => true,
            'plan' => [
                'id' => $plan['id'],
                'name' => $plan['plan_name'],
                'version' => $plan['version'],
            ],
            'sale_amount' => $saleAmount,
            'seller_rank' => $sellerLevel['level_name'],
            'seller_rate' => $sellerRate,
            'global_cap' => round($globalCap, 2),
            'track_a_budget' => round($trackABudget, 2),
            'track_b_budget' => round($trackBBudget, 2),
            'track_c_budget' => round($trackCBudget, 2),
            'royalty_contribution' => round($royaltyContrib, 2),
            'track_a_entries' => [],
            'track_b_entries' => [],
            'track_c_entries' => [],
            'track_a_total' => 0,
            'track_b_total' => 0,
            'track_c_total' => 0,
            'total_distributed' => 0,
            'upline_chain' => [],
        ];

        // ── Track A: Slab Differential ──
        $distributed = 0.0;

        // Direct agent slice
        $agentSlice = $saleAmount * ($sellerRate / 100);
        $alloc = min($agentSlice, max(0.0, $trackABudget - $distributed));
        if ($alloc > 0.01) {
            $result['track_a_entries'][] = [
                'label' => "Direct Sale ({$sellerLevel['level_name']}, {$sellerRate}%)",
                'rate' => $sellerRate,
                'amount' => round($alloc, 2),
                'type' => 'direct_sale',
            ];
            $distributed += $alloc;
        }

        // Build upline chain from plan levels
        $prevRate = $sellerRate;
        $sameRankCount = 0;

        // Determine upline levels (all levels above seller)
        $uplineLevels = [];
        for ($i = $sellerRankIdx + 1; $i < count($levels); $i++) {
            $uplineLevels[] = $levels[$i];
        }

        // If specific upline ranks provided, filter
        if (!empty($uplineRanks)) {
            $filtered = [];
            foreach ($uplineRanks as $rankIdx) {
                if (isset($levels[$rankIdx]) && $rankIdx > $sellerRankIdx) {
                    $filtered[] = $levels[$rankIdx];
                }
            }
            $uplineLevels = $filtered;
        }

        $sameLevelOverrideGen1 = (float)$plan['same_level_override_gen1'];
        $sameLevelOverrideGen2 = (float)$plan['same_level_override_gen2'];

        foreach ($uplineLevels as $upline) {
            if ($distributed >= $trackABudget) break;

            $uplineRate = (float)$upline['direct_commission'];
            $remaining = max(0.0, $trackABudget - $distributed);

            if ($uplineRate == $prevRate) {
                // Same-rank breakaway safeguard
                $sameRankCount++;
                $overridePct = ($sameRankCount === 1) ? $sameLevelOverrideGen1 : (($sameRankCount === 2) ? $sameLevelOverrideGen2 : 0.0);
                if ($overridePct > 0) {
                    $overrideAmt = $saleAmount * ($overridePct / 100);
                    $alloc = min($overrideAmt, $remaining);
                    if ($alloc > 0.01) {
                        $result['track_a_entries'][] = [
                            'label' => "Same-Level Override ({$upline['level_name']}, Gen {$sameRankCount})",
                            'rate' => $overridePct,
                            'amount' => round($alloc, 2),
                            'type' => 'override',
                        ];
                        $distributed += $alloc;
                    }
                }
                continue;
            }

            // Standard differential
            $differential = $uplineRate - $prevRate;
            if ($differential > 0) {
                $diffAmt = $saleAmount * ($differential / 100);
                $alloc = min($diffAmt, $remaining);
                if ($alloc > 0.01) {
                    $result['track_a_entries'][] = [
                        'label' => "Differential ({$upline['level_name']} {$uplineRate}% − {$prevRate}%)",
                        'rate' => round($differential, 2),
                        'amount' => round($alloc, 2),
                        'type' => 'differential',
                    ];
                    $distributed += $alloc;
                }
                $prevRate = $uplineRate;
            }
        }

        $result['track_a_total'] = round($distributed, 2);

        // ── Track B: Performance Rollup Chain ──
        $teamCommission = (float)$sellerLevel['team_commission'];
        $rollupAmt = $saleAmount * ($teamCommission / 100);
        $allocB = min($rollupAmt, $trackBBudget);
        if ($allocB > 0.01) {
            $result['track_b_entries'][] = [
                'label' => "Performance Rollup ({$sellerLevel['level_name']}, {$teamCommission}%)",
                'rate' => $teamCommission,
                'amount' => round($allocB, 2),
                'type' => 'rollup',
            ];
        }
        $result['track_b_total'] = round(min($allocB, $trackBBudget), 2);

        // ── Track C: Milestone Rewards ──
        $levelBonus = (float)$sellerLevel['level_bonus'];
        $milestoneAmt = $saleAmount * ($levelBonus / 100);
        $allocC = min($milestoneAmt, $trackCBudget);
        if ($allocC > 0.01) {
            $result['track_c_entries'][] = [
                'label' => "Milestone Reward ({$sellerLevel['level_name']}, {$levelBonus}%)",
                'rate' => $levelBonus,
                'amount' => round($allocC, 2),
                'type' => 'milestone',
            ];
        }
        $result['track_c_total'] = round(min($allocC, $trackCBudget), 2);

        // ── Monthly bonuses (estimated) ──
        $matchingBonus = (float)$sellerLevel['matching_bonus'];
        $leadershipBonus = (float)$sellerLevel['leadership_bonus'];
        $performanceBonus = (float)$sellerLevel['performance_bonus'];

        $result['monthly_bonuses'] = [
            'matching' => ['rate' => $matchingBonus, 'estimated' => round($saleAmount * ($matchingBonus / 100), 2)],
            'leadership' => ['rate' => $leadershipBonus, 'estimated' => round($saleAmount * ($leadershipBonus / 100), 2)],
            'performance' => ['rate' => $performanceBonus, 'estimated' => round($saleAmount * ($performanceBonus / 100), 2)],
        ];

        $result['total_distributed'] = round(
            $result['track_a_total'] + $result['track_b_total'] + $result['track_c_total'], 2
        );

        $result['payout_ratio'] = $saleAmount > 0
            ? round(($result['total_distributed'] / $saleAmount) * 100, 2)
            : 0;

        $result['remaining_cap'] = round(max(0, $globalCap - $result['total_distributed']), 2);

        return $result;
    }

    /**
     * Compare two plans side by side for a given sale scenario.
     */
    public function comparePlans(float $saleAmount, int $planIdA, int $planIdB, int $sellerRankIdx): array
    {
        $simA = $this->simulateSale($saleAmount, $planIdA, $sellerRankIdx);
        $simB = $this->simulateSale($saleAmount, $planIdB, $sellerRankIdx);

        $comparison = [
            'sale_amount' => $saleAmount,
            'seller_rank_index' => $sellerRankIdx,
            'plan_a' => $simA,
            'plan_b' => $simB,
            'differences' => [],
        ];

        if ($simA['success'] && $simB['success']) {
            $diffs = [];
            $diffs['direct_commission'] = [
                'plan_a' => $simA['seller_rate'],
                'plan_b' => $simB['seller_rate'],
                'diff' => round($simB['seller_rate'] - $simA['seller_rate'], 2),
            ];
            $diffs['total_distributed'] = [
                'plan_a' => $simA['total_distributed'],
                'plan_b' => $simB['total_distributed'],
                'diff' => round($simB['total_distributed'] - $simA['total_distributed'], 2),
            ];
            $diffs['global_cap'] = [
                'plan_a' => $simA['global_cap'],
                'plan_b' => $simB['global_cap'],
                'diff' => round($simB['global_cap'] - $simA['global_cap'], 2),
            ];
            $diffs['payout_ratio'] = [
                'plan_a' => $simA['payout_ratio'],
                'plan_b' => $simB['payout_ratio'],
                'diff' => round($simB['payout_ratio'] - $simA['payout_ratio'], 2),
            ];
            $comparison['differences'] = $diffs;
        }

        return $comparison;
    }

    /**
     * Run a bulk simulation: multiple sale amounts × all ranks for a single plan.
     * Returns a matrix of payouts.
     */
    public function bulkSimulate(float $saleAmount, int $planId): array
    {
        $plan = $this->planService->getPlanById($planId);
        if (!$plan) return ['success' => false, 'error' => 'Plan not found'];

        $results = [];
        foreach ($plan['levels'] as $idx => $level) {
            $results[] = $this->simulateSale($saleAmount, $planId, $idx);
        }

        return [
            'success' => true,
            'plan' => ['id' => $plan['id'], 'name' => $plan['plan_name'], 'version' => $plan['version']],
            'sale_amount' => $saleAmount,
            'rank_results' => $results,
        ];
    }

    /**
     * Sensitivity analysis: how does payout change with varying sale amounts for a rank.
     */
    public function sensitivityAnalysis(int $planId, int $sellerRankIdx, array $amounts = []): array
    {
        if (empty($amounts)) {
            $amounts = [500000, 1000000, 1500000, 2000000, 3000000, 5000000, 7500000, 10000000];
        }

        $results = [];
        foreach ($amounts as $amt) {
            $sim = $this->simulateSale($amt, $planId, $sellerRankIdx);
            if ($sim['success']) {
                $results[] = [
                    'amount' => $amt,
                    'total_payout' => $sim['total_distributed'],
                    'payout_pct' => $sim['payout_ratio'],
                    'track_a' => $sim['track_a_total'],
                    'track_b' => $sim['track_b_total'],
                    'track_c' => $sim['track_c_total'],
                ];
            }
        }

        return [
            'success' => true,
            'plan_id' => $planId,
            'rank_index' => $sellerRankIdx,
            'results' => $results,
        ];
    }
}
