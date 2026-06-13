<?php

namespace App\Services;

use PDO;
use Exception;

/**
 * Hybrid MLM & Salary Commission Engine
 * ──────────────────────────────────────
 * Raghunath Nagri project — global 20%-capped dual-track payout,
 * breakaway safeguard, and Diwali Dhamaka salary incentive audit.
 *
 * Architecture:
 *   Track A  — Slab Differential (15% of payment)
 *   Track B  — Continuous Performance Rollup Chain (3% of payment)
 *   Track C  — Milestone Reward Escrow Fund (2% of payment)
 *   TOTAL    — Max 20% of any incoming payment amount.
 *
 * Tables touched (read):
 *   plot_bookings, plots, colonies, users, mlm_profiles,
 *   mlm_network_tree, mlm_commission_ledger, mlm_commission_levels,
 *   commission_payouts, booking_payment_schedules
 *
 * Tables written:
 *   mlm_commission_ledger, mlm_profiles (totals),
 *   commission_payouts, mlm_rank_history
 */
class HybridCommissionEngine
{
    /** @var PDO */
    private $pdo;

    /* ================================================================
       SECTION 1 — RAGHUNATH NAGRI HISTORICAL PRICING STRUCTURE
       ================================================================ */

    /**
     * Official brochure / marketing-flyer pricing matrix.
     * All values are base ₹/SqFt BEFORE PLC.
     * 'corner_1500' and 'corner_1000' carry +10% PLC.
     */
    private const PRICING_MATRIX = [
        'block_a'       => [
            'label'      => 'Block A',
            'area_sqft'  => 1000,
            'base_rate'  => 950,
            'final_rate' => 950,
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'regular',
        ],
        'block_b'       => [
            'label'      => 'Block B',
            'area_sqft'  => 1000,
            'base_rate'  => 850,
            'final_rate' => 850,
            'emi_allowed'=> true,
            'payment_plan'=> 'emi_available',
            'premium_type'=> 'regular',
        ],
        'block_c'       => [
            'label'      => 'Block C',
            'area_sqft'  => 1000,
            'base_rate'  => 750,
            'final_rate' => 750,
            'emi_allowed'=> true,
            'payment_plan'=> 'emi_available',
            'premium_type'=> 'regular',
        ],
        'corner_1500'   => [
            'label'      => 'Corner 1500',
            'area_sqft'  => 1500,
            'base_rate'  => 1250,
            'final_rate' => 1375,   // 1250 × 1.10
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'commercial_corner',
            'plc_pct'    => 10,
        ],
        'corner_1000'   => [
            'label'      => 'Corner 1000',
            'area_sqft'  => 1000,
            'base_rate'  => 1000,
            'final_rate' => 1100,   // 1000 × 1.10
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'corner_c',
            'plc_pct'    => 10,
        ],
    ];

    /** Default booking / token amount per plot. */
    private const DEFAULT_BOOKING_AMOUNT = 51000;

    /* ================================================================
       SECTION 2 — HYBRID MLM PAYOUT MATRIX (Slab-Based Differential)
       ================================================================ */

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
       SECTION 3 — GLOBAL 20% PAYOUT CAP ALLOCATION
       ================================================================ */

    /** Total cap on payment value that may be distributed as commission. */
    private const GLOBAL_CAP_PCT    = 20;

    /** Track A: Slab differential — 15% of payment value. */
    private const TRACK_A_CAP_PCT   = 15;

    /** Track B: Performance rollup chain — 3% of payment value. */
    private const TRACK_B_CAP_PCT   = 3;

    /** Track C: Milestone reward escrow — 2% of payment value. */
    private const TRACK_C_CAP_PCT   = 2;

    /** Leadership same-level override rates (Breakaway Safeguard). */
    private const SAME_LEVEL_OVERRIDES = [
        1 => 1.5,   // Immediate upline — 1.5%
        2 => 1.0,   // Second identical rank upline — 1.0%
    ];

    /* ================================================================
       SECTION 4 — DIWALI DHAMAKA SALARY INCENTIVE TIERS
       ================================================================ */

    private const SALARY_TIERS = [
        ['volume_threshold' =>  1500000, 'window_days' =>  60, 'monthly_grant' =>  5000, 'months' =>  6],
        ['volume_threshold' =>  3000000, 'window_days' => 100, 'monthly_grant' =>  5000, 'months' => 12],
        ['volume_threshold' =>  5000000, 'window_days' => 150, 'monthly_grant' =>  8000, 'months' => 12],
        ['volume_threshold' =>  7500000, 'window_days' => 200, 'monthly_grant' => 12000, 'months' => 12],
        ['volume_threshold' => 10000000, 'window_days' => 300, 'monthly_grant' => 20000, 'months' => 12],
    ];

    /* ================================================================
       CONSTRUCTOR
       ================================================================ */

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $root   = dirname(__DIR__, 2);
            $config = require $root . '/config/database.php';
            $this->pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
    }

    /* ================================================================
       PUBLIC API — PRICING
       ================================================================ */

    /**
     * Return the full pricing matrix.
     */
    public function getPricingMatrix(): array
    {
        return self::PRICING_MATRIX;
    }

    /**
     * Resolve a block key (case-insensitive) to its pricing entry.
     *
     * @param string $blockKey  e.g. 'block_a', 'Corner 1500', 'corner_1000'
     * @return array|null
     */
    public function getBlockPricing(string $blockKey): ?array
    {
        $normalised = $this->normaliseBlockKey($blockKey);
        return self::PRICING_MATRIX[$normalised] ?? null;
    }

    /**
     * Compute the total plot value for a given block key and optional area override.
     */
    public function calculatePlotValue(string $blockKey, ?float $areaOverride = null): array
    {
        $block = $this->getBlockPricing($blockKey);
        if ($block === null) {
            return ['success' => false, 'error' => "Unknown block key: {$blockKey}"];
        }

        $area       = $areaOverride ?? $block['area_sqft'];
        $totalValue = $block['final_rate'] * $area;

        return [
            'success'          => true,
            'block'            => $block['label'],
            'area_sqft'        => $area,
            'base_rate'        => $block['base_rate'],
            'final_rate'       => $block['final_rate'],
            'plc_pct'          => $block['plc_pct'] ?? 0,
            'emi_allowed'      => $block['emi_allowed'],
            'booking_amount'   => self::DEFAULT_BOOKING_AMOUNT,
            'total_plot_value' => $totalValue,
        ];
    }

    /**
     * Return the default booking / token amount.
     */
    public function getDefaultBookingAmount(): float
    {
        return self::DEFAULT_BOOKING_AMOUNT;
    }

    /* ================================================================
       PUBLIC API — MASTER COMMISSION PIPELINE
       ================================================================ */

    /**
     * Master entry point.  Called whenever a payment receipt is generated
     * (Booking Token OR Monthly EMI).
     *
     * Runs on the ACTUAL $amountReceived — NOT the full plot valuation.
     *
     * @param int    $bookingId        plot_bookings.id
     * @param int    $receiptId        booking_payment_schedules.id (or 0 for token)
     * @param float  $amountReceived   Actual money received this receipt
     * @param int    $executingAgentId The agent / associate who triggered the booking
     * @return array  Full commission breakdown
     */
    public function processPipelineCommission(
        int   $bookingId,
        int   $receiptId,
        float $amountReceived,
        int   $executingAgentId
    ): array {
        $this->pdo->beginTransaction();

        try {
            // ── 0. Guard: nothing to process ──────────────────────
            if ($amountReceived <= 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Amount received must be positive'];
            }

            // ── 1. Compute global cap envelope ────────────────────
            $totalCap    = $amountReceived * (self::GLOBAL_CAP_PCT / 100);
            $trackABudget = $amountReceived * (self::TRACK_A_CAP_PCT / 100);
            $trackBBudget = $amountReceived * (self::TRACK_B_CAP_PCT / 100);
            $trackCBudget = $amountReceived * (self::TRACK_C_CAP_PCT / 100);

            // ── 2. Resolve booking + agent context ────────────────
            $booking  = $this->fetchBooking($bookingId);
            $agentGbv = $this->getAgentGbv($executingAgentId);

            // ── 3. PATH A — Slab Differential (15% budget) ────────
            $trackAResult = $this->computeTrackA(
                $executingAgentId,
                $amountReceived,
                $trackABudget,
                $bookingId,
                $receiptId
            );

            // ── 4. PATH B — Performance Rollup Chain (3% budget) ──
            $trackBResult = $this->computeTrackB(
                $executingAgentId,
                $amountReceived,
                $trackBBudget,
                $bookingId,
                $receiptId
            );

            // ── 5. PATH C — Milestone Escrow (2% budget) ──────────
            $trackCResult = $this->computeTrackC(
                $executingAgentId,
                $amountReceived,
                $trackCBudget,
                $bookingId,
                $receiptId
            );

            // ── 6. Sum actuals and enforce hard cap ───────────────
            $totalActual = $trackAResult['distributed'] + $trackBResult['distributed'] + $trackCResult['distributed'];

            if ($totalActual > $totalCap) {
                // Pro-rata clip across tracks
                $factor     = $totalCap / $totalActual;
                $trackAResult = $this->clipTrack($trackAResult, $factor);
                $trackBResult = $this->clipTrack($trackBResult, $factor);
                $trackCResult = $this->clipTrack($trackCResult, $factor);
                $totalActual  = $totalCap;
            }

            // ── 7. Update agent GBV in mlm_profiles ───────────────
            $this->incrementGbv($executingAgentId, $amountReceived);

            $this->pdo->commit();

            return [
                'success'           => true,
                'booking_id'        => $bookingId,
                'receipt_id'        => $receiptId,
                'amount_received'   => $amountReceived,
                'agent_id'          => $executingAgentId,
                'global_cap'        => $totalCap,
                'total_distributed' => $totalActual,
                'track_a'           => $trackAResult,
                'track_b'           => $trackBResult,
                'track_c'           => $trackCResult,
                'ledger_ids'        => array_merge(
                    $trackAResult['ledger_ids'] ?? [],
                    $trackBResult['ledger_ids'] ?? [],
                    $trackCResult['ledger_ids'] ?? []
                ),
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[HybridCommissionEngine] processPipelineCommission FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ================================================================
       PUBLIC API — DIWALI DHAMAKA SALARY INCENTIVE
       ================================================================ */

    /**
     * Check whether an agent qualifies for a Diwali Dhamaka monthly salary grant.
     *
     * Queries the agent's total confirmed sales volume within each rolling
     * window and returns the highest qualifying tier (if any).
     *
     * @param  int    $agentId   users.id
     * @return array             Eligibility result + tier details
     */
    public function checkSalaryIncentiveEligibility(int $agentId): array
    {
        try {
            $eligible   = false;
            $bestTier   = null;

            // Gather all confirmed bookings where this agent is the associate
            $stmt = $this->pdo->prepare("
                SELECT pb.created_at, COALESCE(pb.agreement_value, pb.total_plot_value, 0) AS sale_value
                FROM plot_bookings pb
                WHERE pb.associate_id = ?
                  AND pb.status NOT IN ('cancelled')
                  AND pb.created_at IS NOT NULL
                ORDER BY pb.created_at DESC
            ");
            $stmt->execute([$agentId]);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sales)) {
                return ['eligible' => false, 'reason' => 'No confirmed bookings found', 'tiers' => self::SALARY_TIERS];
            }

            $now = new \DateTime();

            foreach (self::SALARY_TIERS as $tier) {
                $windowStart = clone $now;
                $windowStart->modify("-{$tier['window_days']} days");

                $volumeInWindow = 0;
                foreach ($sales as $sale) {
                    $saleDate = new \DateTime($sale['created_at']);
                    if ($saleDate >= $windowStart) {
                        $volumeInWindow += (float) $sale['sale_value'];
                    }
                }

                if ($volumeInWindow >= $tier['volume_threshold']) {
                    $eligible = true;
                    $bestTier = $tier;
                    $bestTier['volume_achieved'] = $volumeInWindow;
                    $bestTier['total_grant_value'] = $tier['monthly_grant'] * $tier['months'];
                    break; // tiers are ordered highest first; first match is best
                }
            }

            return [
                'eligible' => $eligible,
                'agent_id' => $agentId,
                'tier'     => $bestTier,
                'tiers'    => self::SALARY_TIERS,
            ];

        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] checkSalaryIncentiveEligibility FAILED: " . $e->getMessage());
            return ['eligible' => false, 'error' => $e->getMessage(), 'tiers' => self::SALARY_TIERS];
        }
    }

    /* ================================================================
       PUBLIC API — RANK RESOLVER
       ================================================================ */

    /**
     * Determine the current rank slug for an agent based on their lifetime GBV.
     */
    public function resolveRank(int $agentId): string
    {
        $gbv = $this->getAgentGbv($agentId);

        $rank = 'associate';
        foreach (self::RANK_SLABS as $slug => $slab) {
            if ($gbv >= $slab['min_gbv']) {
                if ($slab['max_gbv'] === 0 || $gbv <= $slab['max_gbv']) {
                    $rank = $slug;
                } elseif ($gbv > $slab['max_gbv'] && $slab['max_gbv'] > 0) {
                    // continue to next slab
                    continue;
                }
                $rank = $slug;
            }
        }
        return $rank;
    }

    /**
     * Return rank display name.
     */
    public function getRankName(string $rankSlug): string
    {
        return self::RANK_NAMES[$rankSlug] ?? ucfirst(str_replace('_', ' ', $rankSlug));
    }

    /**
     * Get the commission rate for a given rank.
     */
    public function getRankRate(string $rankSlug): float
    {
        return self::RANK_SLABS[$rankSlug]['rate'] ?? 0;
    }

    /* ================================================================
       PUBLIC API — LOOKUP HELPERS
       ================================================================ */

    public function getRankSlabs(): array
    {
        return self::RANK_SLABS;
    }

    public function getSalaryTiers(): array
    {
        return self::SALARY_TIERS;
    }

    /**
     * Fetch ledger entries for an agent within an optional date range.
     */
    public function getAgentLedger(int $agentId, ?string $from = null, ?string $to = null): array
    {
        $where  = "WHERE beneficiary_user_id = ?";
        $params = [$agentId];

        if ($from) {
            $where  .= " AND created_at >= ?";
            $params[] = $from;
        }
        if ($to) {
            $where  .= " AND created_at <= ?";
            $params[] = $to;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM mlm_commission_ledger {$where} ORDER BY created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the upline chain for an agent.
     * Walks mlm_network_tree.parent_id and returns up to $maxLevels ancestors.
     */
    public function getUplineChain(int $agentId, int $maxLevels = 7): array
    {
        $chain   = [];
        $current = $agentId;

        for ($i = 0; $i < $maxLevels; $i++) {
            $stmt = $this->pdo->prepare("
                SELECT mnt.parent_id, mnt.sponsor_id, u.name, u.id AS user_id
                FROM mlm_network_tree mnt
                JOIN users u ON u.id = mnt.parent_id
                WHERE mnt.associate_id = ?
                LIMIT 1
            ");
            $stmt->execute([$current]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['parent_id'])) {
                break;
            }

            $chain[] = [
                'level'  => $i + 1,
                'user_id'=> (int) $row['parent_id'],
                'name'   => $row['name'],
                'rank'   => $this->resolveRank((int) $row['parent_id']),
            ];

            $current = (int) $row['parent_id'];
        }

        return $chain;
    }

    /* ================================================================
       PRIVATE — TRACK A: SLAB DIFFERENTIAL
       ================================================================ */

    private function computeTrackA(
        int    $agentId,
        float  $amountReceived,
        float  $budgetCap,
        int    $bookingId,
        int    $receiptId
    ): array {
        $ledgerIds  = [];
        $distributed = 0.0;

        // Resolve the executing agent's own rank and rate
        $agentRank = $this->resolveRank($agentId);
        $agentRate = self::RANK_SLABS[$agentRank]['rate'];

        // Build upline chain (up to 7 levels deep)
        $upline = $this->getUplineChain($agentId, 7);

        // The agent gets the first slice: their own rank rate × amountReceived
        $agentSlice = $amountReceived * ($agentRate / 100);
        if ($agentSlice > 0 && $distributed + $agentSlice <= $budgetCap) {
            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, $agentRate,
                $agentSlice, 'direct_sale', 1, $bookingId, $receiptId,
                'Track A — Direct agent slab commission'
            );
            $ledgerIds[]   = $ledgerId;
            $distributed  += $agentSlice;
        }

        // Traverse upline, computing differential gap at each generation
        $prevRate = $agentRate;
        foreach ($upline as $gen) {
            $uplineRank = $gen['rank'];
            $uplineRate = self::RANK_SLABS[$uplineRank]['rate'];
            $userId     = $gen['user_id'];

            // ── BREAKAWAY SAFEGUARD ──
            // If senior's rate equals immediate downline's rate (same rank),
            // apply Leadership Same-Level Override instead of differential.
            if ($uplineRate === $prevRate) {
                $overridePct = self::SAME_LEVEL_OVERRIDES[$gen['level']] ?? 0;
                $overrideAmt = $amountReceived * ($overridePct / 100);

                if ($overrideAmt > 0 && $distributed + $overrideAmt <= $budgetCap) {
                    $ledgerId = $this->writeLedger(
                        $userId, $agentId, $amountReceived, $overridePct,
                        $overrideAmt, 'level_bonus', $gen['level'], $bookingId, $receiptId,
                        "Track A — Same-level override ({$this->getRankName($uplineRank)}, Gen {$gen['level']})"
                    );
                    $ledgerIds[]   = $ledgerId;
                    $distributed  += $overrideAmt;
                }
                // Do NOT update $prevRate — keep it same so Gen 2+ also triggers
                // the same-level path if they share the same rank.
                continue;
            }

            // Standard differential: senior rate minus the rate immediately below them
            $differential = $uplineRate - $prevRate;
            if ($differential > 0) {
                $diffAmt = $amountReceived * ($differential / 100);
                if ($distributed + $diffAmt <= $budgetCap) {
                    $ledgerId = $this->writeLedger(
                        $userId, $agentId, $amountReceived, $differential,
                        $diffAmt, 'level_bonus', $gen['level'], $bookingId, $receiptId,
                        "Track A — Differential ({$this->getRankName($uplineRank)} {$uplineRate}% − {$prevRate}%)"
                    );
                    $ledgerIds[]   = $ledgerId;
                    $distributed  += $diffAmt;
                }
            }

            $prevRate = $uplineRate;
        }

        return [
            'track'       => 'A',
            'label'       => 'Slab Differential',
            'budget'      => $budgetCap,
            'distributed' => round($distributed, 2),
            'remaining'   => round($budgetCap - $distributed, 2),
            'ledger_ids'  => $ledgerIds,
            'entries'     => count($ledgerIds),
        ];
    }

    /* ================================================================
       PRIVATE — TRACK B: CONTINUOUS PERFORMANCE ROLLUP CHAIN
       ================================================================ */

    /**
     * Track B awards a rolling performance bonus that accumulates based on
     * consecutive months of qualifying sales.  The agent earns an additional
     * 0.3% per qualifying consecutive month (max 3%) from the 3% budget.
     *
     * Qualifying month = agent (or any downline) generates >= ₹50,000 in
     * confirmed bookings that month.
     */
    private function computeTrackB(
        int    $agentId,
        float  $amountReceived,
        float  $budgetCap,
        int    $bookingId,
        int    $receiptId
    ): array {
        $ledgerIds  = [];
        $distributed = 0.0;

        // Count consecutive qualifying months (including current)
        $consecutive = $this->countConsecutiveQualifyingMonths($agentId);

        // Bonus: 0.3% per qualifying month, capped at 3% total
        $bonusPct = min($consecutive * 0.3, 3.0);
        $bonusAmt = $amountReceived * ($bonusPct / 100);

        if ($bonusAmt > 0 && $bonusAmt <= $budgetCap) {
            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, $bonusPct,
                $bonusAmt, 'performance_bonus', 0, $bookingId, $receiptId,
                "Track B — Performance rollup ({$consecutive} consecutive months, {$bonusPct}%)"
            );
            $ledgerIds[]   = $ledgerId;
            $distributed  += $bonusAmt;
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

    /* ================================================================
       PRIVATE — TRACK C: MILESTONE REWARD ESCROW
       ================================================================ */

    /**
     * Track C accumulates 2% of every payment into an escrow bucket.
     * When the bucket crosses predefined milestone thresholds, a lump-sum
     * reward is credited to the agent.
     *
     * Milestones: ₹50K / ₹2L / ₹5L / ₹10L cumulative escrow.
     */
    private const ESCROW_MILESTONES = [
        50000  => 'Bronze Milestone',
        200000 => 'Silver Milestone',
        500000 => 'Gold Milestone',
        1000000 => 'Platinum Milestone',
    ];

    private function computeTrackC(
        int    $agentId,
        float  $amountReceived,
        float  $budgetCap,
        int    $bookingId,
        int    $receiptId
    ): array {
        $ledgerIds  = [];
        $distributed = 0.0;

        // Credit the full 2% budget (capped by global cap enforcement)
        $escrowAmount = min($amountReceived * (self::TRACK_C_CAP_PCT / 100), $budgetCap);

        if ($escrowAmount > 0) {
            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, self::TRACK_C_CAP_PCT,
                $escrowAmount, 'team_bonus', 0, $bookingId, $receiptId,
                'Track C — Milestone escrow credit'
            );
            $ledgerIds[]   = $ledgerId;
            $distributed  += $escrowAmount;
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

    /* ================================================================
       PRIVATE — DATABASE HELPERS
       ================================================================ */

    /**
     * Write a single commission row to mlm_commission_ledger.
     *
     * @return int ledger row id
     */
    private function writeLedger(
        int    $beneficiaryId,
        int    $sourceId,
        float  $saleAmount,
        float  $pct,
        float  $amount,
        string $type,
        int    $level,
        int    $bookingId,
        int    $receiptId,
        string $notes
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 property_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
        ");
        $stmt->execute([
            $beneficiaryId,
            $sourceId,
            $type,
            round($amount, 2),
            $level,
            $saleAmount,
            round($pct, 2),
            $notes,
            null, // property_id — booking tracked via notes, not FK
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Fetch a booking row from plot_bookings.
     */
    private function fetchBooking(int $bookingId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plot_bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get an agent's cumulative Group Business Volume from mlm_profiles.
     */
    private function getAgentGbv(int $agentId): float
    {
        $stmt = $this->pdo->prepare("SELECT lifetime_sales FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$agentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float) $row['lifetime_sales'] : 0.0;
    }

    /**
     * Increment an agent's lifetime_sales after a payment.
     */
    private function incrementGbv(int $agentId, float $amount): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE mlm_profiles
            SET lifetime_sales = lifetime_sales + ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $agentId]);
    }

    /**
     * Count consecutive months (including current) where the agent had at
     * least ₹50,000 in confirmed bookings.
     */
    private function countConsecutiveQualifyingMonths(int $agentId): int
    {
        // Resolve user_id → associates.id (associate_id FK references associates.id, not users.id)
        $assocStmt = $this->pdo->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
        $assocStmt->execute([$agentId]);
        $assocRow = $assocStmt->fetch(PDO::FETCH_ASSOC);
        $associateId = $assocRow ? (int) $assocRow['id'] : $agentId;

        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(pb.created_at, '%Y-%m') AS ym,
                   SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)) AS month_total
            FROM plot_bookings pb
            WHERE pb.associate_id = ?
              AND pb.status NOT IN ('cancelled')
            GROUP BY ym
            ORDER BY ym DESC
            LIMIT 12
        ");
        $stmt->execute([$associateId]);
        $months = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $consecutive = 0;
        $expected    = new \DateTime('first day of this month');

        foreach ($months as $row) {
            $ym       = $row['ym'];
            $monthTot = (float) $row['month_total'];
            $expectedYmd = $expected->format('Y-m');

            if ($ym === $expectedYmd && $monthTot >= 50000) {
                $consecutive++;
                $expected->modify('-1 month');
            } else {
                break;
            }
        }

        return $consecutive;
    }

    /**
     * Get an agent's cumulative Track C escrow balance from the ledger.
     */
    public function getAgentEscrowBalance(int $agentId): float
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM mlm_commission_ledger
            WHERE beneficiary_user_id = ?
              AND commission_type = 'team_bonus'
              AND notes LIKE '%Track C%'
              AND status != 'cancelled'
        ");
        $stmt->execute([$agentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Clip a track result by a pro-rata factor when the global cap is breached.
     */
    private function clipTrack(array $track, float $factor): array
    {
        $track['distributed'] = round($track['distributed'] * $factor, 2);
        $track['remaining']   = round($track['remaining'] * $factor, 2);
        $track['clipped']     = true;
        return $track;
    }

    /* ================================================================
       PRIVATE — BLOCK KEY NORMALISER
       ================================================================ */

    /**
     * Normalise various block key formats to the canonical PRICING_MATRIX key.
     *
     * Accepts: 'block_a', 'Block A', 'A', 'BLOCK_A', 'corner_1500', 'Corner 1500',
     *          'corner_1000', 'Corner 1000', 'COMMERCIAL_CORNER', etc.
     */
    private function normaliseBlockKey(string $key): string
    {
        $k = strtolower(trim($key));

        // Direct map
        if (isset(self::PRICING_MATRIX[$k])) {
            return $k;
        }

        // Strip 'block ' / 'block_' prefix and map to block_x
        $stripped = preg_replace('/^block\s*/i', '', $k);
        if (isset(self::PRICING_MATRIX['block_' . $stripped])) {
            return 'block_' . $stripped;
        }

        // Corner variants
        if (preg_match('/corner.*1500/i', $k)) {
            return 'corner_1500';
        }
        if (preg_match('/corner.*1000/i', $k)) {
            return 'corner_1000';
        }

        // Commercial corner alias
        if (preg_match('/commercial.*corner/i', $k)) {
            return 'corner_1500';
        }

        return $k; // fallback — will return null from getBlockPricing
    }
}
