<?php

namespace App\Services;

use PDO;
use Exception;
use App\Core\Middleware\TenantContext;

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
        1 => 2.0,   // Immediate upline — 2.0%
        2 => 1.0,   // Second identical rank upline — 1.0%
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

    /* ================================================================
       CONSTRUCTOR
       ================================================================ */

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    protected function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
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
     * Normalise a block key string into its canonical matrix key.
     * Accepts: 'block_a', 'Block A', 'A', 'BLOCK_A', 'Corner 1500',
     *          'corner_1500', 'COMMERCIAL_CORNER', 'corner_1000', 'Corner 1000', 'b', etc.
     */
    private function normaliseBlockKey(string $input): string
    {
        $s = strtolower(trim($input));

        // Direct match
        if (isset(self::PRICING_MATRIX[$s])) {
            return $s;
        }

        // 'block_a', 'block_b', 'block_c' — strip 'block_' prefix variant
        // single letter: 'a','b','c'
        if (preg_match('/^block[_ ]?([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }
        if (preg_match('/^([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }

        // corner_1500 aliases
        if (in_array($s, ['corner_1500', 'corner 1500', 'commercial_corner', 'corner1500'], true)) {
            return 'corner_1500';
        }

        // corner_1000 aliases
        if (in_array($s, ['corner_1000', 'corner 1000', 'corner1000'], true)) {
            return 'corner_1000';
        }

        // 'block a' → block_a, 'block b' → block_b, etc.
        if (preg_match('/^block ([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }

        return $s; // Let caller handle null
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

            // ── 0a. IDEMPOTENCY: Skip if commissions already exist for this booking+receipt ──
            $dupCheck = $this->pdo->prepare("
                SELECT COUNT(*) FROM mlm_commission_ledger 
                WHERE booking_id = ? AND receipt_id = ? AND status NOT IN ('cancelled','clawed_back')
            ");
            $dupCheck->execute([$bookingId, $receiptId]);
            if ((int)$dupCheck->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => true, 'skipped' => true, 'reason' => 'commissions_already_exist_for_this_receipt'];
            }

            // ── 0b. Resolve booking for telecaller & subsequent logic ──
            $booking  = $this->fetchBooking($bookingId);

            // ── 0c. Resolve telecaller (if any) and compute incentives BEFORE agent bypass ──
            $telecaller = $this->resolveTelecallerForBooking($booking ?: []);
            $telecallerIncentive = 0.0;
            $telecallerLedgerIds = [];
            $maxTelecallerBudget = $amountReceived * 0.05; // Strict 5% cap to protect company 80% margins
            $telecallerBudgetUsed = 0.0;

            if ($telecaller) {
                $tcUserId = (int)$telecaller['user_id'];
                $plotArea = 0.0;
                $bookingValue = 0.0;
                if ($booking) {
                    $bookingValue = (float)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? 0.0);
                    if (!empty($booking['plot_id'])) {
                        $pStmt = $this->pdo->prepare("SELECT area_sqft FROM plots WHERE id = ? LIMIT 1");
                        $pStmt->execute([$booking['plot_id']]);
                        $plotArea = (float)$pStmt->fetchColumn();
                    }
                }

                if ($bookingValue <= 0) {
                    $bookingValue = 1.0;
                }

                if (isset($telecaller['telecaller_percent_rate']) && $telecaller['telecaller_percent_rate'] > 0) {
                    $percentRate = (float)$telecaller['telecaller_percent_rate'];
                    $telecallerIncentive = ($amountReceived * $percentRate) / 100.0;
                    $telecallerIncentive = min($telecallerIncentive, $maxTelecallerBudget);
                    $note = "Telecaller Percentage Incentive ({$percentRate}%, Capped)";
                    $rateUsed = $percentRate;
                } else {
                    if ($receiptId === 0) {
                        // Token payment -> flat incentive
                        $flatRate = (float)($telecaller['telecaller_incentive_rate'] > 0 ? $telecaller['telecaller_incentive_rate'] : 1000.00);
                        $telecallerIncentive = min($flatRate, $maxTelecallerBudget);
                        $note = "Telecaller Token Conversion Incentive (Flat ₹" . number_format($flatRate) . ", Capped)";
                        $rateUsed = 0.0;
                    } else {
                        // Subsequent payment -> proportional sqft incentive
                        $sqftRate = (float)($telecaller['telecaller_sqft_rate'] > 0 ? $telecaller['telecaller_sqft_rate'] : 10.00);
                        $totalSqftIncentive = $plotArea * $sqftRate;
                        $proportion = $amountReceived / $bookingValue;
                        $telecallerIncentive = min($totalSqftIncentive * $proportion, $maxTelecallerBudget);
                        $note = "Telecaller SqFt Incentive (₹" . $sqftRate . "/sqft proportional, total ₹" . number_format($totalSqftIncentive) . ", Capped)";
                        $rateUsed = $sqftRate;
                    }
                }

                if ($telecallerIncentive > 0.01) {
                    $ledgerId = $this->writeLedger(
                        $tcUserId, $tcUserId, $amountReceived, $rateUsed,
                        round($telecallerIncentive, 2), 'telecaller_bonus', 0, $bookingId, $receiptId,
                        $note
                    );
                    $telecallerLedgerIds[] = $ledgerId;
                    $telecallerBudgetUsed += $telecallerIncentive;
                }

                // Walk telecaller parent hierarchy up to 2 generations to award Team Lead overrides
                $currentParentId = !empty($telecaller['telecaller_parent_id']) ? (int)$telecaller['telecaller_parent_id'] : null;
                $levelRates = [1 => 2.0, 2 => 1.0];
                for ($lvl = 1; $lvl <= 2; $lvl++) {
                    if (!$currentParentId || $telecallerBudgetUsed >= $maxTelecallerBudget) {
                        break;
                    }

                    $pStmt = $this->pdo->prepare("
                        SELECT a.user_id, a.telecaller_parent_id 
                        FROM associates a 
                        WHERE a.user_id = ? LIMIT 1
                    ");
                    $pStmt->execute([$currentParentId]);
                    $parentRecord = $pStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$parentRecord) {
                        break;
                    }

                    $parentPct = $levelRates[$lvl];
                    $parentAmt = $amountReceived * ($parentPct / 100);
                    $parentAmt = min($parentAmt, $maxTelecallerBudget - $telecallerBudgetUsed);

                    if ($parentAmt > 0.01) {
                        $ledgerId = $this->writeLedger(
                            $currentParentId, $tcUserId, $amountReceived, $parentPct,
                            round($parentAmt, 2), 'level_bonus', $lvl, $bookingId, $receiptId,
                            "Telecaller Team Lead Override (Level {$lvl}, {$parentPct}%, Capped)"
                        );
                        $telecallerLedgerIds[] = $ledgerId;
                        $telecallerIncentive += $parentAmt;
                        $telecallerBudgetUsed += $parentAmt;
                    }

                    $currentParentId = !empty($parentRecord['telecaller_parent_id']) ? (int)$parentRecord['telecaller_parent_id'] : null;
                }
            }

            // ── 0d. Independent Agent bypass — flat commission, no MLM upline ──
            $assocStmt = $this->pdo->prepare(
                "SELECT agent_track, agent_type, brokerage_model, brokerage_rate FROM associates WHERE user_id = ? LIMIT 1"
            );
            $assocStmt->execute([$executingAgentId]);
            $assocRecord = $assocStmt->fetch(PDO::FETCH_ASSOC);

            if ($assocRecord && ($assocRecord['agent_track'] ?? 'mlm') === 'independent') {
                $result = $this->processIndependentAgentCommission(
                    $bookingId,
                    $receiptId,
                    $amountReceived,
                    $executingAgentId,
                    $assocRecord
                );
                $this->pdo->commit();
                return $result;
            }

            // ── 0c. Dual System Bypass (Freelance Broker vs Salaried) ──
            $agentType = $assocRecord['agent_type'] ?? 'mlm';

            if ($agentType === 'salaried') {
                // Salaried agents get paid monthly payroll via SalariedAgentService.
                // We do NOT write direct MLM commissions to the ledger for them.
                $this->pdo->commit();
                return ['success' => true, 'skipped' => true, 'reason' => 'salaried_agent_payroll_handled_separately'];
            }

            if ($agentType === 'freelance_broker') {
                // Freelance Broker cascading commission
                $plotValue = 0.0;
                $areaSqft = 0.0;
                $booking = $this->fetchBooking($bookingId);
                if ($booking) {
                    $plotValue = (float)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? 0.0);
                    if (!empty($booking['plot_id'])) {
                        $pStmt = $this->pdo->prepare("SELECT area_sqft FROM plots WHERE id = ? LIMIT 1");
                        $pStmt->execute([$booking['plot_id']]);
                        $areaSqft = (float)$pStmt->fetchColumn();
                    }
                }

                // Process cascading margins prorated against the payment amount received
                $fbResult = $this->processFreelanceBrokerCommission($executingAgentId, $plotValue, $areaSqft, 10, $amountReceived);

                if ($fbResult['success'] && !empty($fbResult['chain'])) {
                    foreach ($fbResult['chain'] as $node) {
                        $note = "Freelance Broker Cascading (Rate: " . $node['own_rate'] . ($node['comm_type'] === 'percentage' ? '%' : ' Rs/SqFt') . ")";
                        $this->writeLedger(
                            $node['user_id'],
                            $executingAgentId,
                            $amountReceived,
                            $node['own_rate'],
                            $node['margin'],
                            'freelance_broker',
                            $node['level'],
                            $bookingId,
                            $receiptId,
                            $note
                        );
                    }
                }
                
                // Track C equivalent (Royalty pool) is still collected from the payment, even for brokers
                $this->contributeToRoyaltyPool($bookingId, $amountReceived);

                $this->pdo->commit();
                return ['success' => true, 'type' => 'freelance_broker', 'details' => $fbResult];
            }

            // ── 1. Compute global cap envelope ────────────────────
            $caps = $this->getActivePlanCaps();
            $totalCap    = $amountReceived * ($caps['global_cap'] / 100);
            $trackABudget = $amountReceived * ($caps['track_a'] / 100);
            $trackBBudget = $amountReceived * ($caps['track_b'] / 100);
            $trackCBudget = $amountReceived * ($caps['track_c'] / 100);

            // ── 2. Resolve agent context ────────────────
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

            // ── 6. Royalty Pool Contribution (2% — outside 20% cap) ──
            $royaltyResult = $this->contributeToRoyaltyPool($bookingId, $amountReceived);

            // ── 7. Career Rewards check (DB-driven, no writes to cap) ──
            $careerResult = $this->checkCareerRewards($executingAgentId);

            // ── 8. Sum actuals and enforce hard cap (Tracks A+B+C + Telecaller) ──
            $totalActual = $trackAResult['distributed'] + $trackBResult['distributed'] + $trackCResult['distributed'] + $telecallerIncentive;

            if ($totalActual > $totalCap) {
                // Pro-rata clip across tracks
                $factor       = $totalCap / $totalActual;
                $trackAResult = $this->clipTrack($trackAResult, $factor);
                $trackBResult = $this->clipTrack($trackBResult, $factor);
                $trackCResult = $this->clipTrack($trackCResult, $factor);
                if ($telecaller) {
                    $telecallerIncentive = round($telecallerIncentive * $factor, 2);
                }
                $totalActual  = $totalCap;

                // Update ledger entries in the database to reflect the clipped amount
                $allLedgerIds = array_merge(
                    $trackAResult['ledger_ids'] ?? [],
                    $trackBResult['ledger_ids'] ?? [],
                    $trackCResult['ledger_ids'] ?? [],
                    $telecallerLedgerIds
                );

                if (!empty($allLedgerIds)) {
                    $updateStmt = $this->pdo->prepare("
                        UPDATE mlm_commission_ledger 
                        SET amount = ROUND(amount * ?, 2)
                        WHERE id = ?
                    ");
                    foreach ($allLedgerIds as $lId) {
                        $updateStmt->execute([$factor, $lId]);
                    }
                }
            }

            // ── 9. Update agent GBV in mlm_profiles ───────────────
            $this->incrementGbv($executingAgentId, $amountReceived);

            // Check and activate salary grants for direct seller and uplines
            try {
                $this->activateSalaryGrants($executingAgentId);
                $upline = $this->getUplineChain($executingAgentId, 7);
                foreach ($upline as $gen) {
                    $this->activateSalaryGrants($gen['user_id']);
                }
            } catch (\Throwable $sgEx) {
                error_log("[HybridCommissionEngine] activateSalaryGrants failed: " . $sgEx->getMessage());
            }

            $this->pdo->commit();

            return [
                'success'            => true,
                'booking_id'         => $bookingId,
                'receipt_id'         => $receiptId,
                'amount_received'    => $amountReceived,
                'agent_id'           => $executingAgentId,
                'global_cap'         => $totalCap,
                'total_distributed'  => $totalActual,
                'track_a'            => $trackAResult,
                'track_b'            => $trackBResult,
                'track_c'            => $trackCResult,
                'telecaller'         => $telecaller ? [
                    'user_id'     => $telecaller['user_id'],
                    'name'        => $telecaller['name'],
                    'incentive'   => round($telecallerIncentive, 2),
                    'ledger_ids'  => $telecallerLedgerIds,
                ] : null,
                'royalty_contribution' => $royaltyResult,
                'career_reward'      => $careerResult,
                'ledger_ids'         => array_merge(
                    $trackAResult['ledger_ids'] ?? [],
                    $trackBResult['ledger_ids'] ?? [],
                    $trackCResult['ledger_ids'] ?? [],
                    $telecallerLedgerIds
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

            // Resolve associates.id from users.id (agentId)
            $assocStmt = $this->pdo->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
            $assocStmt->execute([$agentId]);
            $assocId = $assocStmt->fetchColumn();

            if (!$assocId) {
                return ['eligible' => false, 'reason' => 'Associate profile not found', 'tiers' => self::SALARY_TIERS];
            }

            // Gather all confirmed bookings where this agent is the associate
            $stmt = $this->pdo->prepare("
                SELECT pb.created_at, COALESCE(pb.agreement_value, pb.total_plot_value, 0) AS sale_value
                FROM plot_bookings pb
                WHERE pb.associate_id = ?
                  AND pb.status NOT IN ('cancelled')
                  AND pb.created_at IS NOT NULL
                ORDER BY pb.created_at DESC
            ");
            $stmt->execute([$assocId]);
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sales)) {
                return ['eligible' => false, 'reason' => 'No confirmed bookings found', 'tiers' => self::SALARY_TIERS];
            }

            $now = new \DateTime();

            $reversedTiers = array_reverse(self::SALARY_TIERS, true);
            foreach ($reversedTiers as $index => $tier) {
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
                    $bestTier['tier_index'] = $index;
                    $bestTier['volume_achieved'] = $volumeInWindow;
                    $bestTier['total_grant_value'] = $tier['monthly_grant'] * $tier['months'];
                    break;
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

    /**
     * Evaluate salary grant eligibility and activate the highest eligible tier if not already active.
     */
    public function activateSalaryGrants(int $agentId): bool
    {
        $eligibility = $this->checkSalaryIncentiveEligibility($agentId);
        if (!$eligibility['eligible'] || empty($eligibility['tier'])) {
            return false;
        }

        $tier = $eligibility['tier'];
        $tierIndex = (int)$tier['tier_index'];
        $volumeThreshold = (float)$tier['volume_threshold'];
        $monthlyAmount = (float)$tier['monthly_grant'];
        $monthsTotal = (int)$tier['months'];

        // Check if this tier has already been activated for this user
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM mlm_salary_grants 
            WHERE user_id = ? AND tier_index = ?
        ");
        $stmt->execute([$agentId, $tierIndex]);
        $exists = (int)$stmt->fetchColumn() > 0;

        if ($exists) {
            return false;
        }

        // Insert new grant
        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_salary_grants 
            (user_id, tier_index, volume_threshold, monthly_amount, months_total, months_paid, status, activated_at, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 0, 'active', NOW(), NOW(), NOW())
        ");
        $stmt->execute([
            $agentId,
            $tierIndex,
            $volumeThreshold,
            $monthlyAmount,
            $monthsTotal
        ]);

        return true;
    }

    /**
     * Process monthly salary grants for a given month-year (YYYY-MM).
     * Pays out active grants to the commission ledger under type 'salary'.
     */
    public function processMonthlySalaryGrants(string $monthYear): array
    {
        $processed = 0;
        $errors = [];

        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            return ['success' => false, 'error' => 'Invalid month format, must be YYYY-MM'];
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM mlm_salary_grants WHERE status = 'active'");
            $grants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($grants as $grant) {
                // We run each grant payout in its own transaction to isolate errors
                $this->pdo->beginTransaction();
                try {
                    // Check if already paid this month
                    $checkStmt = $this->pdo->prepare("
                        SELECT COUNT(*) FROM mlm_commission_ledger 
                        WHERE beneficiary_user_id = ? 
                          AND commission_type = 'salary' 
                          AND DATE_FORMAT(created_at, '%Y-%m') = ?
                          AND status NOT IN ('cancelled')
                    ");
                    $checkStmt->execute([$grant['user_id'], $monthYear]);
                    if ((int)$checkStmt->fetchColumn() > 0) {
                        $this->pdo->rollBack();
                        continue;
                    }

                    // Check monthly active maintenance target (new recruitment or plot booking)
                    $isMaintainer = $this->checkMonthlySalaryMaintenance((int)$grant['user_id'], $monthYear);
                    if (!$isMaintainer) {
                        $this->pdo->rollBack();
                        error_log("Skipped monthly salary grant for User ID {$grant['user_id']} in {$monthYear} due to inactivity.");
                        continue;
                    }

                    $tierIndex = $grant['tier_index'];
                    $monthsPaid = (int)$grant['months_paid'];
                    $monthsTotal = (int)$grant['months_total'];
                    $monthlyAmount = (float)$grant['monthly_amount'];

                    $notes = "Monthly Salary Grant (Tier " . ($tierIndex + 1) . ", Month " . ($monthsPaid + 1) . "/{$monthsTotal}) for {$monthYear}";

                    $this->writeLedger(
                        (int)$grant['user_id'],
                        (int)$grant['user_id'],
                        0.0,
                        0.0,
                        $monthlyAmount,
                        'salary',
                        0,
                        0,
                        0,
                        $notes
                    );

                    $newMonthsPaid = $monthsPaid + 1;
                    $newStatus = ($newMonthsPaid >= $monthsTotal) ? 'completed' : 'active';
                    
                    $updateStmt = $this->pdo->prepare("
                        UPDATE mlm_salary_grants 
                        SET months_paid = ?, 
                            status = ?, 
                            last_paid_at = NOW(), 
                            updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$newMonthsPaid, $newStatus, $grant['id']]);

                    $this->pdo->commit();
                    $processed++;
                } catch (\Throwable $e) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    $errors[] = "Grant ID {$grant['id']} failed: " . $e->getMessage();
                }
            }

            return [
                'success' => true,
                'processed' => $processed,
                'errors' => $errors
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if an associate qualifies for their monthly salary installment by remaining active.
     * They must have either:
     * 1. At least 1 direct plot booking (not cancelled) in the target month.
     * 2. Recruited at least 1 new associate who bought a joining package in that month.
     *
     * @param int $userId
     * @param string $monthYear  Format 'YYYY-MM'
     * @return bool
     */
    public function checkMonthlySalaryMaintenance(int $userId, string $monthYear): bool
    {
        try {
            // Check Activity A: Direct plot booking in this month
            $assocStmt = $this->pdo->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
            $assocStmt->execute([$userId]);
            $assocId = $assocStmt->fetchColumn();

            if ($assocId) {
                $bookingStmt = $this->pdo->prepare("
                    SELECT COUNT(*) 
                    FROM plot_bookings 
                    WHERE associate_id = ? 
                      AND status NOT IN ('cancelled')
                      AND DATE_FORMAT(created_at, '%Y-%m') = ?
                ");
                $bookingStmt->execute([$assocId, $monthYear]);
                if ((int)$bookingStmt->fetchColumn() > 0) {
                    return true;
                }
            }

            // Check Activity B: Recruited at least 1 new associate who bought a package this month
            $recruitStmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM users u
                JOIN mlm_associate_registrations r ON r.user_id = u.id
                WHERE u.referred_by = ?
                  AND r.payment_status = 'paid'
                  AND DATE_FORMAT(r.registered_at, '%Y-%m') = ?
            ");
            $recruitStmt->execute([$userId, $monthYear]);
            if ((int)$recruitStmt->fetchColumn() > 0) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            error_log("[HybridCommissionEngine] checkMonthlySalaryMaintenance failed: " . $e->getMessage());
            return false; // Safest fallback: fail closed to protect corporate margins
        }
    }

    /* ================================================================
       PUBLIC API — RANK RESOLVER
     * Determine the current rank slug for an agent based on their lifetime GBV.
     */
    public function resolveRank(int $agentId): string
    {
        $gbv = $this->getAgentGbv($agentId);
        $slabs = $this->loadRankSlabsFromDb();

        $rank = 'associate';
        foreach ($slabs as $slug => $slab) {
            if ($gbv >= $slab['min_gbv']) {
                if ($slab['max_gbv'] == 0 || $gbv <= $slab['max_gbv']) {
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
        $slabs = $this->loadRankSlabsFromDb();
        if (isset($slabs[$rankSlug])) {
            return $slabs[$rankSlug]['rank_name'];
        }
        return self::RANK_NAMES[$rankSlug] ?? ucfirst(str_replace('_', ' ', $rankSlug));
    }

    /**
     * Get the commission rate for a given rank.
     * Reads from DB first, falls back to hardcoded constants.
     */
    public function getRankRate(string $rankSlug): float
    {
        $dbSlabs = $this->loadRankSlabsFromDb();
        if (isset($dbSlabs[$rankSlug])) {
            return (float)($dbSlabs[$rankSlug]['commission_rate'] ?? 0);
        }
        return self::RANK_SLABS[$rankSlug]['rate'] ?? 0;
    }

    /* ================================================================
       PUBLIC API — LOOKUP HELPERS
       ================================================================ */

    public function getRankSlabs(): array
    {
        return $this->loadRankSlabsFromDb();
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

        // Load rank slabs from DB (falls back to hardcoded if empty)
        $rankSlabs = $this->loadRankSlabsFromDb();

        // Resolve the executing agent's own rank and rate
        $agentRank = $this->resolveRank($agentId);
        $agentRate = $rankSlabs[$agentRank]['commission_rate'] ?? (self::RANK_SLABS[$agentRank]['rate'] ?? 0);

        // Build upline chain (up to 7 levels deep)
        $upline = $this->getUplineChain($agentId, 7);

        // The agent gets the first slice: their own rank rate × amountReceived
        $agentSlice = $amountReceived * ($agentRate / 100);
        if ($agentSlice > 0) {
            $alloc = min($agentSlice, max(0.0, $budgetCap - $distributed));
            if ($alloc > 0.01) {
                // Check status for missed commission FOMO strategy
                $agentStatus = $this->getAgentStatus($agentId);
                $isMissed = !in_array($agentStatus, ['active']);

                $ledgerId = $this->writeLedger(
                    $agentId, $agentId, $amountReceived, $agentRate,
                    round($alloc, 2), 'direct_sale', 1, $bookingId, $receiptId,
                    'Track A — Direct agent slab commission',
                    $isMissed
                );
                $ledgerIds[]   = $ledgerId;
                if (!$isMissed) $distributed  += $alloc;
            }
        }

        // Traverse upline, computing differential gap at each generation
        $prevRate = $agentRate;
        foreach ($upline as $gen) {
            $uplineRank = $gen['rank'];
            $uplineRate = $rankSlabs[$uplineRank]['commission_rate'] ?? (self::RANK_SLABS[$uplineRank]['rate'] ?? 0);
            $userId     = $gen['user_id'];

            // Cap reached — stop
            if ($distributed >= $budgetCap) {
                break;
            }

            $remaining = max(0.0, $budgetCap - $distributed);

            // ── BREAKAWAY SAFEGUARD ──
            // If senior's rate equals immediate downline's rate (same rank),
            // apply Leadership Same-Level Override instead of differential.
            if ($uplineRate === $prevRate) {
                // Fetch booking details for date
                $booking = $this->fetchBooking($bookingId);
                $bookingMonth = date('Y-m');
                if ($booking && !empty($booking['created_at'])) {
                    $bookingMonth = date('Y-m', strtotime($booking['created_at']));
                }

                // Verify upline meets ₹50,000 monthly side-volume requirement
                if ($this->verifyUplineSideVolume($userId, $bookingMonth)) {
                    $overridePct = self::SAME_LEVEL_OVERRIDES[$gen['level']] ?? 0;
                    $overrideAmt = $amountReceived * ($overridePct / 100);

                    if ($overrideAmt > 0) {
                        $alloc = min($overrideAmt, $remaining);
                        if ($alloc > 0.01) {
                            $agentStatus = $this->getAgentStatus($userId);
                            $isMissed = !in_array($agentStatus, ['active']);

                            $ledgerId = $this->writeLedger(
                                $userId, $agentId, $amountReceived, $overridePct,
                                round($alloc, 2), 'level_bonus', $gen['level'], $bookingId, $receiptId,
                                "Track A — Same-level override ({$this->getRankName($uplineRank)}, Gen {$gen['level']})",
                                $isMissed
                            );
                            $ledgerIds[]   = $ledgerId;
                            if (!$isMissed) $distributed  += $alloc;
                        }
                    }
                }
                // Do NOT update $prevRate — keep it same so Gen 2+ also triggers
                // the same-level path if they share the same rank.
                continue;
            }

            // Standard differential: senior rate minus the rate immediately below them
            $differential = $uplineRate - $prevRate;
            if ($differential > 0) {
                $diffAmt = $amountReceived * ($differential / 100);
                $alloc = min($diffAmt, $remaining);
                if ($alloc > 0.01) {
                    $agentStatus = $this->getAgentStatus($userId);
                    $isMissed = !in_array($agentStatus, ['active']);

                    $ledgerId = $this->writeLedger(
                        $userId, $agentId, $amountReceived, $differential,
                        round($alloc, 2), 'level_bonus', $gen['level'], $bookingId, $receiptId,
                        "Track A — Differential ({$this->getRankName($uplineRank)} {$uplineRate}% − {$prevRate}%)",
                        $isMissed
                    );
                    $ledgerIds[]   = $ledgerId;
                    if (!$isMissed) $distributed  += $alloc;
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
            $agentStatus = $this->getAgentStatus($agentId);
            $isMissed = !in_array($agentStatus, ['active']);

            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, $bonusPct,
                $bonusAmt, 'performance_bonus', 0, $bookingId, $receiptId,
                "Track B — Performance rollup ({$consecutive} consecutive months, {$bonusPct}%)",
                $isMissed
            );
            $ledgerIds[]   = $ledgerId;
            if (!$isMissed) $distributed  += $bonusAmt;
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
            $agentStatus = $this->getAgentStatus($agentId);
            $isMissed = !in_array($agentStatus, ['active']);

            $ledgerId = $this->writeLedger(
                $agentId, $agentId, $amountReceived, self::TRACK_C_CAP_PCT,
                $escrowAmount, 'team_bonus', 0, $bookingId, $receiptId,
                'Track C — Milestone escrow credit',
                $isMissed
            );
            $ledgerIds[]   = $ledgerId;
            if (!$isMissed) $distributed  += $escrowAmount;
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
       PRIVATE — INDEPENDENT AGENT COMMISSION
       ================================================================ */

    /**
     * Process flat commission for an independent agent.
     *
     * Independent agents bypass the entire MLM differential upline walk.
     * Their commission is calculated from brokerage_model + brokerage_rate:
     *  - flat_percentage: brokerage_rate% of payment amount
     *  - flat_rate_sqft:  brokerage_rate × plot area (proportional to cash received)
     *
     * Commission is written directly to mlm_commission_ledger with level=0
     * (no upline chain). Escrow portion (2%) still goes to Track C.
     *
     * @return array Same shape as processPipelineCommission for API compat
     */
    private function processIndependentAgentCommission(
        int   $bookingId,
        int   $receiptId,
        float $amountReceived,
        int   $agentUserId,
        array $assocRecord
    ): array {
        $model = $assocRecord['brokerage_model'] ?? 'flat_percentage';
        $rate  = (float) ($assocRecord['brokerage_rate'] ?? 0);

        // Compute flat commission
        if ($model === 'flat_rate_sqft' && $rate > 0) {
            // Get plot area from booking → plot
            $areaStmt = $this->pdo->prepare("
                SELECT COALESCE(p.area_sqft, 0) AS area_sqft
                FROM plot_bookings pb
                JOIN plots p ON p.id = pb.plot_id
                WHERE pb.id = ?
                LIMIT 1
            ");
            $areaStmt->execute([$bookingId]);
            $area = (float) $areaStmt->fetchColumn();
            $flatCommission = $area * $rate;
        } else {
            // flat_percentage or fallback
            $pct = $rate > 0 ? $rate : 8.0; // default 8% if not configured
            $flatCommission = $amountReceived * ($pct / 100);
        }

        // Ensure we don't exceed the payment amount
        $flatCommission = min($flatCommission, $amountReceived);

        // Write single ledger entry — level 0 (no upline)
        $ledgerId = $this->writeLedger(
            $agentUserId,
            $agentUserId,
            $amountReceived,
            $rate,
            $flatCommission,
            'independent_agent',
            0,
            $bookingId,
            $receiptId,
            "Independent agent commission — model: {$model}, rate: {$rate}"
        );

        // Track C: Escrow portion (2%) still applies to independent agents
        $escrowAmount = $amountReceived * (self::TRACK_C_CAP_PCT / 100);
        $this->contributeToRoyaltyPool($bookingId, $amountReceived);

        // Increment agent GBV (for stats, not differential)
        $this->incrementGbv($agentUserId, $amountReceived);

        return [
            'success'            => true,
            'booking_id'         => $bookingId,
            'receipt_id'         => $receiptId,
            'amount_received'    => $amountReceived,
            'agent_id'           => $agentUserId,
            'agent_track'        => 'independent',
            'brokerage_model'    => $model,
            'brokerage_rate'     => $rate,
            'global_cap'         => 0,
            'total_distributed'  => round($flatCommission, 2),
            'track_a'            => ['distributed' => 0, 'entries' => []],
            'track_b'            => ['distributed' => 0, 'entries' => []],
            'track_c'            => ['distributed' => round($escrowAmount, 2), 'entries' => [['label' => 'Escrow (independent)', 'amount' => $escrowAmount]]],
            'royalty_contribution' => ['contributed' => true],
            'career_reward'      => null,
            'ledger_ids'         => [$ledgerId],
        ];
    }

    /* ================================================================
       PRIVATE — DATABASE HELPERS
       ================================================================ */

    /**
     * Write a single commission row to mlm_commission_ledger.
     * PLAN SAFETY: Captures plan snapshot so past entries are immutable.
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
        string $notes,
        bool   $isMissed = false
    ): int {
        // Capture plan snapshot for this calculation
        $planSnapshot = $this->getActivePlanSnapshot();

        $status = $isMissed ? 'missed' : 'pending';
        if ($isMissed) {
            $notes .= ' | [MISSED_DUE_TO_INACTIVE]';
        } else {
            // Dynamic check for inactive associate status to enforce missed commissions
            try {
                $stmtStatus = $this->pdo->prepare("SELECT status FROM associates WHERE user_id = ? LIMIT 1");
                $stmtStatus->execute([$beneficiaryId]);
                $userStatus = $stmtStatus->fetchColumn();
                if ($userStatus && $userStatus !== 'active') {
                    $status = 'missed';
                    $notes .= ' | [MISSED_DUE_TO_INACTIVE]';
                }
            } catch (\Throwable $e) {
                // Ignore DB error and fallback to pending
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 property_id, booking_id, receipt_id, hold_until, created_at,
                 plan_id, plan_version, plan_snapshot, calculation_engine, tenant_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 45 DAY), NOW(),
                    ?, ?, ?, 'hybrid', ?)
        ");
        $stmt->execute([
            $beneficiaryId,
            $sourceId,
            $type,
            round($amount, 2),
            $level,
            $saleAmount,
            round($pct, 2),
            $status,
            $notes,
            null, // property_id
            $bookingId > 0 ? $bookingId : null,
            $receiptId > 0 ? $receiptId : null,
            $planSnapshot['plan_id'] ?? null,
            $planSnapshot['plan_version'] ?? null,
            $planSnapshot ? json_encode($planSnapshot) : null,
            $this->getTenantId(),
        ]);
        $ledgerId = (int) $this->pdo->lastInsertId();

        // Broadcast commission event via WebSocket + Push
        try {
            $payload = [
                'event'       => 'commission_credited',
                'ledger_id'   => $ledgerId,
                'beneficiary' => $beneficiaryId,
                'source'      => $sourceId,
                'type'        => $type,
                'amount'      => round($amount, 2),
                'level'       => $level,
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            \App\Services\WebSocketBroadcaster::broadcastToUser($beneficiaryId, $payload);
            $pushService = new \App\Services\Communication\PushNotificationService();
            $pushService->sendToUser(
                $beneficiaryId,
                [
                    'title' => 'Commission Credited',
                    'body'  => '₹' . number_format(round($amount, 2)) . ' ' . ucfirst($type) . ' commission credited',
                    'data'  => $payload,
                ]
            );
        } catch (\Throwable $e) {
            error_log("[HybridCommissionEngine] broadcast failed: " . $e->getMessage());
        }

        return $ledgerId;
    }

    /**
     * Get agent status, cached per request
     */
    private function getAgentStatus(int $userId): string {
        if (isset($this->agentStatusCache[$userId])) {
            return $this->agentStatusCache[$userId];
        }
        $stmt = $this->pdo->prepare("SELECT status FROM associates WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $status = $stmt->fetchColumn();
        $this->agentStatusCache[$userId] = $status ?: 'inactive';
        return $this->agentStatusCache[$userId];
    }
    
    // Agent status cache to avoid repetitive queries
    private array $agentStatusCache = [];

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
    /* ================================================================
       PUBLIC API — ROYALTY POOL (2% Global Site Manager Pool)
       ================================================================ */

    /**
     * Royalty pool contribution rate (% of each payment).
     * Site Manager global pool: 2% of all company sales volume.
     */
    private const ROYALTY_POOL_PCT = 2.0;

    /** Minimum monthly GBV for a Site Manager to qualify for royalty pool share. */
    private const ROYALTY_QUALIFICATION_GBV = 5000000; // ₹50 Lakhs

    /**
     * Contribute 2% of a payment to the monthly royalty pool.
     * Called inside processPipelineCommission after Track A/B/C.
     *
     * @param int   $bookingId
     * @param float $amountReceived
     * @return array{success: bool, contribution: float, pool_total: float}
     */
    public function contributeToRoyaltyPool(int $bookingId, float $amountReceived): array
    {
        try {
            $caps = $this->getActivePlanCaps();
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
            if ((int)$stmt->fetchColumn() === 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO mlm_royalty_contributions (month_year, booking_id, payment_amount, contribution_amount, tenant_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$monthYear, $bookingId, $amountReceived, $contribution, $this->getTenantId()]);
            }

            // Fetch updated pool total
            $stmt = $this->pdo->prepare("SELECT total_pool_amount FROM mlm_royalty_pool WHERE month_year = ?");
            $stmt->execute([$monthYear]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $poolTotal = $row ? (float) $row['total_pool_amount'] : $contribution;

            return [
                'success'      => true,
                'contribution' => $contribution,
                'pool_total'   => $poolTotal,
            ];
        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] contributeToRoyaltyPool FAILED: " . $e->getMessage());
            return ['success' => false, 'contribution' => 0, 'pool_total' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Month-end: distribute the royalty pool equally among qualified Site Managers.
     * A Site Manager qualifies if their monthly GBV >= ₹50 Lakhs.
     *
     * @param string $monthYear  Format: YYYY-MM
     * @return array{success: bool, pool_amount: float, qualified_managers: int, per_share: float, ledger_ids: array}
     */
    public function distributeRoyaltyPool(string $monthYear): array
    {
        $this->pdo->beginTransaction();
        try {
            // Fetch pool total
            $stmt = $this->pdo->prepare("SELECT * FROM mlm_royalty_pool WHERE month_year = ? FOR UPDATE");
            $stmt->execute([$monthYear]);
            $pool = $stmt->fetch(PDO::FETCH_ASSOC);

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
            $qualified = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $managerCount = count($qualified);
            if ($managerCount === 0) {
                // No qualified managers — keep pool accumulating
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'No qualified Site Managers for ' . $monthYear . ' (need ≥₹50L GBV each)'];
            }

            $perShare = round($poolAmount / $managerCount, 2);
            $ledgerIds = [];

            foreach ($qualified as $mgr) {
                $planSnapshot = $this->getActivePlanSnapshot();
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
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[HybridCommissionEngine] distributeRoyaltyPool FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get current month's royalty pool status.
     */
    public function getRoyaltyPoolStatus(?string $monthYear = null): array
    {
        $monthYear = $monthYear ?: date('Y-m');
        $stmt = $this->pdo->prepare("SELECT * FROM mlm_royalty_pool WHERE month_year = ?");
        $stmt->execute([$monthYear]);
        $pool = $stmt->fetch(PDO::FETCH_ASSOC);

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
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

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

    /* ================================================================
       PUBLIC API — CAREER REWARDS
       ================================================================ */

    /**
     * Check if an agent has crossed a new rank threshold and award career reward.
     *
     * @param int $agentId  users.id
     * @return array{success: bool, new_rank: bool, reward: array|null}
     */
    public function checkCareerRewards(int $agentId): array
    {
        try {
            $currentRank = $this->resolveRank($agentId);
            $gbv = $this->getAgentGbv($agentId);

            // Check if reward already awarded for this rank
            $stmt = $this->pdo->prepare("
                SELECT id FROM mlm_career_rewards
                WHERE user_id = ? AND rank_slug = ? AND status != 'cancelled' AND tenant_id = ?
                LIMIT 1
            ");
            $stmt->execute([$agentId, $currentRank, $this->getTenantId()]);
            if ($stmt->fetch()) {
                return ['success' => true, 'new_rank' => false, 'reward' => null, 'message' => 'Reward already awarded for ' . $currentRank];
            }

            // Load rank details from DB (fallback to hardcoded)
            $rankSlabs = $this->loadRankSlabsFromDb();
            $slab = $rankSlabs[$currentRank] ?? null;

            if (!$slab || empty($slab['reward_name'])) {
                return ['success' => true, 'new_rank' => false, 'reward' => null, 'message' => 'No reward defined for ' . $currentRank];
            }

            // Award the reward
            $stmt = $this->pdo->prepare("
                INSERT INTO mlm_career_rewards (tenant_id, user_id, rank_slug, reward_name, reward_value, gbv_at_award, status, awarded_at)
                VALUES (?, ?, ?, ?, ?, ?, 'awarded', NOW())
            ");
            $stmt->execute([
                $this->getTenantId(),
                $agentId,
                $currentRank,
                $slab['reward_name'],
                $slab['reward_value'] ?? 0,
                $gbv,
            ]);

            return [
                'success'  => true,
                'new_rank' => true,
                'reward'   => [
                    'rank'         => $currentRank,
                    'rank_name'    => $slab['rank_name'],
                    'reward_name'  => $slab['reward_name'],
                    'reward_value' => $slab['reward_value'],
                    'gbv'          => $gbv,
                ],
            ];
        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] checkCareerRewards FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get active plan snapshot for ledger entries.
     * Returns full plan data + rates + caps as immutable snapshot.
     * This ensures past commission entries are NEVER affected by future plan changes.
     */
    public function getActivePlanSnapshot(): ?array
    {
        try {
            $plan = $this->pdo->query("
                SELECT p.*, GROUP_CONCAT(
                    CONCAT(l.level_name, ':', l.direct_commission, ':', l.team_commission, ':',
                           l.level_bonus, ':', l.matching_bonus, ':', l.leadership_bonus, ':',
                           l.performance_bonus, ':', l.monthly_target)
                    ORDER BY l.level_order SEPARATOR '|'
                ) as levels_data
                FROM mlm_commission_plans p
                LEFT JOIN mlm_plan_levels l ON l.plan_id = p.id
                WHERE p.status = 'active'
                GROUP BY p.id
                ORDER BY p.version DESC
                LIMIT 1
            ")->fetch(PDO::FETCH_ASSOC);

            if (!$plan) return null;

            $snapshot = [
                'plan_id' => (int)$plan['id'],
                'plan_name' => $plan['plan_name'],
                'plan_code' => $plan['plan_code'],
                'plan_version' => (int)$plan['version'],
                'plan_type' => $plan['plan_type'],
                'effective_date' => $plan['effective_date'],
                'global_cap_pct' => (float)$plan['global_cap_pct'],
                'track_a_pct' => (float)$plan['track_a_pct'],
                'track_b_pct' => (float)$plan['track_b_pct'],
                'track_c_pct' => (float)$plan['track_c_pct'],
                'royalty_pool_pct' => (float)$plan['royalty_pool_pct'],
                'same_level_override_gen1' => (float)$plan['same_level_override_gen1'],
                'same_level_override_gen2' => (float)$plan['same_level_override_gen2'],
                'snapshot_taken_at' => date('Y-m-d H:i:s'),
            ];

            // Parse levels from GROUP_CONCAT
            $levels = [];
            if (!empty($plan['levels_data'])) {
                foreach (explode('|', $plan['levels_data']) as $levelStr) {
                    $parts = explode(':', $levelStr);
                    if (count($parts) >= 8) {
                        $levels[] = [
                            'level_name' => $parts[0],
                            'direct_commission' => (float)$parts[1],
                            'team_commission' => (float)$parts[2],
                            'level_bonus' => (float)$parts[3],
                            'matching_bonus' => (float)$parts[4],
                            'leadership_bonus' => (float)$parts[5],
                            'performance_bonus' => (float)$parts[6],
                            'monthly_target' => (float)$parts[7],
                        ];
                    }
                }
            }
            $snapshot['levels'] = $levels;

            return $snapshot;
        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] getActivePlanSnapshot FAILED: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active plan's cap percentages for calculations.
     * Reads from DB plan table, falls back to hardcoded constants.
     */
    private function getActivePlanCaps(): array
    {
        $tenantId = $this->getTenantId();
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_key, setting_value 
                FROM mlm_settings 
                WHERE tenant_id = ?
            ");
            $stmt->execute([$tenantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rows) {
                $settings = [];
                foreach ($rows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }

                // Map mlm_settings keys to the expected array structure
                return [
                    'global_cap'       => (float)($settings['global_cap_pct'] ?? self::GLOBAL_CAP_PCT),
                    'track_a'          => (float)($settings['track_a_pct'] ?? self::TRACK_A_CAP_PCT),
                    'track_b'          => (float)($settings['track_b_pct'] ?? self::TRACK_B_CAP_PCT),
                    'track_c'          => (float)($settings['track_c_pct'] ?? self::TRACK_C_CAP_PCT),
                    'royalty_pool'     => (float)($settings['royalty_pool_pct'] ?? self::ROYALTY_POOL_PCT ?? 2.0),
                    'same_level_gen1'  => (float)($settings['same_level_override_gen1'] ?? self::SAME_LEVEL_OVERRIDES[1]),
                    'same_level_gen2'  => (float)($settings['same_level_override_gen2'] ?? self::SAME_LEVEL_OVERRIDES[2]),
                ];
            }
        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] getActivePlanCaps FAILED: " . $e->getMessage());
        }

        // Fallback to hardcoded constants
        return [
            'global_cap'       => self::GLOBAL_CAP_PCT,
            'track_a'          => self::TRACK_A_CAP_PCT,
            'track_b'          => self::TRACK_B_CAP_PCT,
            'track_c'          => self::TRACK_C_CAP_PCT,
            'royalty_pool'     => defined('self::ROYALTY_POOL_PCT') ? self::ROYALTY_POOL_PCT : 2.0,
            'same_level_gen1'  => self::SAME_LEVEL_OVERRIDES[1],
            'same_level_gen2'  => self::SAME_LEVEL_OVERRIDES[2],
        ];
    }

    /**
     * Load rank slabs from the mlm_rank_slabs table.
     * Falls back to hardcoded RANK_SLABS if the table is empty.
     */
    public function loadRankSlabsFromDb(): array
    {
        $tenantId = $this->getTenantId();
        try {
            $stmt = $this->pdo->prepare("
                SELECT rank_name, min_qualifying_volume, direct_sale_pct, reward_item
                FROM mlm_rank_benefits
                WHERE is_active = 1 AND tenant_id = ?
                ORDER BY rank_order ASC
            ");
            $stmt->execute([$tenantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return $this->getHardcodedSlabs();
            }

            // Map UI enum names to our internal slugs
            $slugMap = [
                'Ass.'         => 'associate',
                'Sr. Ass.'     => 'sr_associate',
                'BDM'          => 'bdm',
                'Sr. BDM'      => 'sr_bdm',
                'V.P.'         => 'vice_president',
                'President'    => 'president',
                'Site Manager' => 'site_manager'
            ];

            $slabs = [];
            $numRows = count($rows);

            for ($i = 0; $i < $numRows; $i++) {
                $row = $rows[$i];
                $slug = $slugMap[$row['rank_name']] ?? strtolower(str_replace([' ', '.'], ['_', ''], $row['rank_name']));
                
                // Max GBV is the Min GBV of the next rank, or 0 if it's the top rank
                $maxGbv = ($i < $numRows - 1) ? (float)$rows[$i + 1]['min_qualifying_volume'] : 0.0;

                $slabs[$slug] = [
                    'rank_slug'       => $slug,
                    'rank_name'       => self::RANK_NAMES[$slug] ?? $row['rank_name'],
                    'min_gbv'         => (float)$row['min_qualifying_volume'],
                    'max_gbv'         => $maxGbv,
                    'commission_rate' => (float)$row['direct_sale_pct'],
                    'reward_name'     => $row['reward_item'],
                    'reward_value'    => null, // Deprecated/Not tracked here
                ];
            }
            return $slabs;
        } catch (Exception $e) {
            error_log("[HybridCommissionEngine] loadRankSlabsFromDb FAILED: " . $e->getMessage());
            return $this->getHardcodedSlabs();
        }
    }

    private function getHardcodedSlabs(): array
    {
        $slabs = [];
        foreach (self::RANK_SLABS as $slug => $slab) {
            $slabs[$slug] = [
                'rank_slug'       => $slug,
                'rank_name'       => self::RANK_NAMES[$slug] ?? $slug,
                'min_gbv'         => $slab['min_gbv'],
                'max_gbv'         => $slab['max_gbv'],
                'commission_rate' => $slab['rate'],
                'reward_name'     => null,
                'reward_value'    => null,
            ];
        }
        return $slabs;
    }

    /* ================================================================
       PUBLIC API — PAYOUT SIMULATOR (for Admin Calculator View)
       ================================================================ */

    /**
     * Simulate the full commission payout for a given sale amount and seller rank.
     * Pure computation — no DB writes. Used by the admin calculator view.
     *
     * @param float  $saleAmount     The payment/plot sale amount
     * @param string $sellerRankSlug The seller's current rank slug
     * @return array Full breakdown of all tracks + royalty + totals
     */
    public function simulatePayout(float $saleAmount, string $sellerRankSlug): array
    {
        $slabs = $this->loadRankSlabsFromDb();
        $sellerSlab = $slabs[$sellerRankSlug] ?? null;
        if (!$sellerSlab) {
            return ['success' => false, 'error' => 'Unknown rank: ' . $sellerRankSlug];
        }

        $sellerRate = (float) $sellerSlab['commission_rate'];
        $caps = $this->getActivePlanCaps();
        $results = [
            'success'          => true,
            'sale_amount'      => $saleAmount,
            'seller_rank'      => $sellerRankSlug,
            'seller_rate'      => $sellerRate,
            'global_cap'       => round($saleAmount * ($caps['global_cap'] / 100), 2),
            'track_a_budget'   => round($saleAmount * ($caps['track_a'] / 100), 2),
            'track_b_budget'   => round($saleAmount * ($caps['track_b'] / 100), 2),
            'track_c_budget'   => round($saleAmount * ($caps['track_c'] / 100), 2),
            'royalty_contribution' => round($saleAmount * ($caps['global_cap'] > 0 ? 2 : 2), 2),
            'track_a_entries'  => [],
            'track_b_entries'  => [],
            'track_c_entries'  => [],
            'track_a_total'    => 0,
            'track_b_total'    => 0,
            'track_c_total'    => 0,
            'total_distributed'=> 0,
        ];

        // ── Track A: Slab Differential ──
        $distributed = 0.0;
        $budgetCap = $results['track_a_budget'];

        // Direct agent slice
        $agentSlice = $saleAmount * ($sellerRate / 100);
        $alloc = min($agentSlice, max(0.0, $budgetCap - $distributed));
        if ($alloc > 0.01) {
            $results['track_a_entries'][] = [
                'label'    => "Direct Sale ({$sellerSlab['rank_name']}, {$sellerRate}%)",
                'rate'     => $sellerRate,
                'amount'   => round($alloc, 2),
                'type'     => 'direct_sale',
            ];
            $distributed += $alloc;
        }

        // Build upline chain (simulate 7 levels)
        $prevRate = $sellerRate;
        $uplineRates = [];
        foreach ($slabs as $slug => $slab) {
            $rate = (float) $slab['commission_rate'];
            if ($rate > $sellerRate) {
                $uplineRates[] = ['slug' => $slug, 'name' => $slab['rank_name'], 'rate' => $rate];
            }
        }
        // Sort by rate ascending
        usort($uplineRates, fn($a, $b) => $a['rate'] <=> $b['rate']);

        $sameRankCount = 0;
        foreach ($uplineRates as $idx => $upline) {
            if ($distributed >= $budgetCap) break;

            $remaining = max(0.0, $budgetCap - $distributed);

            if ($upline['rate'] === $prevRate) {
                // Same-rank breakaway safeguard
                $sameRankCount++;
                $overridePct = ($sameRankCount === 1) ? 2.0 : (($sameRankCount === 2) ? 1.0 : 0.0);
                if ($overridePct > 0) {
                    $overrideAmt = $saleAmount * ($overridePct / 100);
                    $alloc = min($overrideAmt, $remaining);
                    if ($alloc > 0.01) {
                        $results['track_a_entries'][] = [
                            'label'    => "Same-Level Override ({$upline['name']}, Gen {$sameRankCount})",
                            'rate'     => $overridePct,
                            'amount'   => round($alloc, 2),
                            'type'     => 'override',
                        ];
                        $distributed += $alloc;
                    }
                }
                continue;
            }

            // Standard differential
            $differential = $upline['rate'] - $prevRate;
            if ($differential > 0) {
                $diffAmt = $saleAmount * ($differential / 100);
                $alloc = min($diffAmt, $remaining);
                if ($alloc > 0.01) {
                    $results['track_a_entries'][] = [
                        'label'    => "Differential ({$upline['name']} {$upline['rate']}% − {$prevRate}%)",
                        'rate'     => $differential,
                        'amount'   => round($alloc, 2),
                        'type'     => 'differential',
                    ];
                    $distributed += $alloc;
                }
            }
            $prevRate = $upline['rate'];
        }

        $results['track_a_total'] = round($distributed, 2);

        // ── Track B: Performance Rollup (3%) ──
        $trackBAmt = round($saleAmount * 0.009, 2); // 0.9% qualifying bonus
        $results['track_b_entries'][] = [
            'label' => 'Performance Rollup (3 consecutive months)',
            'rate'  => 0.9,
            'amount'=> min($trackBAmt, $results['track_b_budget']),
        ];
        $results['track_b_total'] = min($trackBAmt, $results['track_b_budget']);

        // ── Track C: Milestone Escrow (2%) ──
        $trackCAmt = round($saleAmount * (self::TRACK_C_CAP_PCT / 100), 2);
        $results['track_c_entries'][] = [
            'label' => 'Milestone Escrow Credit',
            'rate'  => self::TRACK_C_CAP_PCT,
            'amount'=> min($trackCAmt, $results['track_c_budget']),
        ];
        $results['track_c_total'] = min($trackCAmt, $results['track_c_budget']);

        // ── Totals ──
        $results['total_distributed'] = round(
            $results['track_a_total'] + $results['track_b_total'] + $results['track_c_total'],
            2
        );
        $results['overhead_pct'] = round(($results['total_distributed'] / $saleAmount) * 100, 2);
        $results['company_retains'] = round($saleAmount - $results['total_distributed'] - $results['royalty_contribution'], 2);
        $results['company_retains_pct'] = round(($results['company_retains'] / $saleAmount) * 100, 2);

        return $results;
    }

    /**
     * Resolve the telecaller associated with a booking.
     * Checks if the booking customer's phone or email matches a lead assigned to a telecaller.
     */
    private function resolveTelecallerForBooking(array $booking): ?array
    {
        $phone = '';
        $email = '';

        if (!empty($booking['customer_phone'])) {
            $phone = $booking['customer_phone'];
        }
        if (!empty($booking['customer_email'])) {
            $email = $booking['customer_email'];
        }

        if ((empty($phone) || empty($email)) && !empty($booking['customer_id'])) {
            $custStmt = $this->pdo->prepare("SELECT phone, email FROM users WHERE id = ? LIMIT 1");
            $custStmt->execute([$booking['customer_id']]);
            $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
            if ($cust) {
                if (empty($phone)) $phone = $cust['phone'] ?? '';
                if (empty($email)) $email = $cust['email'] ?? '';
            }
        }

        if ((empty($phone) || empty($email)) && !empty($booking['user_id'])) {
            $custStmt = $this->pdo->prepare("SELECT phone, email FROM users WHERE id = ? LIMIT 1");
            $custStmt->execute([$booking['user_id']]);
            $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
            if ($cust) {
                if (empty($phone)) $phone = $cust['phone'] ?? '';
                if (empty($email)) $email = $cust['email'] ?? '';
            }
        }

        if (empty($phone) && empty($email)) {
            return null;
        }

        $leadStmt = $this->pdo->prepare("
            SELECT l.assigned_to 
            FROM leads l
            JOIN users u ON u.id = l.assigned_to
            WHERE u.role = 'telecaller'
              AND (
                (l.phone != '' AND l.phone = ?) OR 
                (l.email != '' AND l.email = ?)
              )
            LIMIT 1
        ");
        $leadStmt->execute([$phone, $email]);
        $telecallerUserId = $leadStmt->fetchColumn();

        if (!$telecallerUserId) {
            return null;
        }

        $tcStmt = $this->pdo->prepare("
            SELECT e.*, u.name, u.email 
            FROM employees e
            JOIN users u ON u.id = e.user_id
            WHERE e.user_id = ? AND e.status = 'active' LIMIT 1
        ");
        $tcStmt->execute([$telecallerUserId]);
        $telecaller = $tcStmt->fetch(PDO::FETCH_ASSOC);

        if ($telecaller) {
            // Map new HR schema to existing logic
            if (($telecaller['incentive_model'] ?? 'salary_only') !== 'salary_only') {
                $rate = (float)($telecaller['commission_rate'] ?? 0);
                $type = $telecaller['commission_type'] ?? 'percentage';

                if ($type === 'flat') {
                    // It's a flat rate per sqft
                    $telecaller['telecaller_sqft_rate'] = $rate;
                    $telecaller['telecaller_incentive_rate'] = 0; // Or some flat token amount if needed
                } else {
                    // Percentage based: we'll handle this in the computation by overriding rate
                    $telecaller['telecaller_sqft_rate'] = 0; // Marker for percentage
                    $telecaller['telecaller_percent_rate'] = $rate;
                }
            } else {
                return null; // Salary only, no commission
            }
            return $telecaller;
        }

        // Fallback to old associates table
        $tcStmt = $this->pdo->prepare("
            SELECT a.*, u.name, u.email 
            FROM associates a
            JOIN users u ON u.id = a.user_id
            WHERE a.user_id = ? LIMIT 1
        ");
        $tcStmt->execute([$telecallerUserId]);
        $telecaller = $tcStmt->fetch(PDO::FETCH_ASSOC);

        return $telecaller ?: null;
    }

    /**
     * Verify if an upline sponsor qualifies for a same-rank override by generating
     * at least ₹50,000 in monthly side volume (personal sales + monthly leg volumes excluding largest leg)
     * during the month of the booking.
     */
    private function verifyUplineSideVolume(int $uplineUserId, string $month): bool
    {
        $assocStmt = $this->pdo->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
        $assocStmt->execute([$uplineUserId]);
        $assocRow = $assocStmt->fetch(PDO::FETCH_ASSOC);
        $associateId = $assocRow ? (int)$assocRow['id'] : $uplineUserId;

        $personalSalesStmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS personal_sales
            FROM plot_bookings pb
            WHERE pb.associate_id = ?
              AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
              AND pb.status NOT IN ('cancelled', 'refunded')
        ");
        $personalSalesStmt->execute([$associateId, $month]);
        $personalSales = (float)$personalSalesStmt->fetchColumn();

        $directChildrenStmt = $this->pdo->prepare("
            SELECT DISTINCT associate_id AS user_id
            FROM mlm_network_tree
            WHERE parent_id = ?
        ");
        $directChildrenStmt->execute([$uplineUserId]);
        $directChildren = $directChildrenStmt->fetchAll(PDO::FETCH_COLUMN);

        $legVolumes = [];
        foreach ($directChildren as $childUserId) {
            if ($childUserId === null) continue; // skip null associate_id rows
            $childUserId = (int)$childUserId;
            $descendents = $this->getDownlineUserIds($childUserId);
            $descendents[] = $childUserId;

            $inClause = implode(',', array_fill(0, count($descendents), '?'));
            $assocIdsStmt = $this->pdo->prepare("
                SELECT id FROM associates WHERE user_id IN ($inClause)
            ");
            $assocIdsStmt->execute($descendents);
            $assocIds = $assocIdsStmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($assocIds)) {
                $legVolumes[] = 0.0;
                continue;
            }

            $inAssocClause = implode(',', array_fill(0, count($assocIds), '?'));
            $legVolStmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS volume
                FROM plot_bookings pb
                WHERE pb.associate_id IN ($inAssocClause)
                  AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                  AND pb.status NOT IN ('cancelled', 'refunded')
            ");
            $params = array_merge($assocIds, [$month]);
            $legVolStmt->execute($params);
            $legVolStmt->fetch(PDO::FETCH_ASSOC);
            $legVolumes[] = (float)$legVolStmt->fetchColumn();
        }

        if (empty($legVolumes)) {
            $sideVolume = $personalSales;
        } else {
            rsort($legVolumes);
            array_shift($legVolumes);
            $sideVolume = $personalSales + array_sum($legVolumes);
        }

        return $sideVolume >= 50000.0;
    }

    /**
     * Recursively fetch all downline user IDs under a user.
     */
    private function getDownlineUserIds(int $userId): array
    {
        $downline = [];
        $toProcess = [$userId];

        while (!empty($toProcess)) {
            $currentBatch = implode(',', array_fill(0, count($toProcess), '?'));
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT associate_id
                FROM mlm_network_tree
                WHERE parent_id IN ($currentBatch)
            ");
            $stmt->execute($toProcess);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $toProcess = [];
            foreach ($children as $cId) {
                $cId = (int)$cId;
                if (!in_array($cId, $downline) && $cId !== $userId) {
                    $downline[] = $cId;
                    $toProcess[] = $cId;
                }
            }
        }

        return $downline;
    }

    /* ================================================================
       SECTION 10 — POLICY GUARD DELEGATION
       ================================================================ */

    /**
     * Check if an agent qualifies for a rank promotion under sustainability rules.
     *
     * Delegates to MlmPolicyGuard which enforces:
     *  - 50% Max Leg Rule (no single leg >50% of adjusted GBV)
     *  - Minimum PBV (10% of target rank GBV from personal sales)
     *  - Active monthly maintenance (consecutive months with ≥₹50K GBV)
     *
     * @param int    $agentId         users.id
     * @param string $targetRankSlug  Target rank slug (e.g. 'sr_associate', 'bdm')
     * @return array{eligible: bool, adjusted_gbv: float, personal_sales: float, reason: string, warnings: string[]}
     */
    public function checkPromotionEligibility(int $agentId, string $targetRankSlug): array
    {
        try {
            $guard = new MlmPolicyGuard($this->pdo);
            return $guard->checkPromotionEligibility($agentId, $targetRankSlug);
        } catch (Exception $e) {
            error_log("PolicyGuard delegation failed: " . $e->getMessage());
            return [
                'eligible'      => false,
                'adjusted_gbv'  => 0,
                'personal_sales'=> 0,
                'reason'        => 'Policy guard unavailable: ' . $e->getMessage(),
                'warnings'      => [],
            ];
        }
    }

    /**
     * Check consecutive monthly maintenance for an agent.
     *
     * @param int $agentId users.id
     * @return array{consecutive_months: int, qualifying_months: int, total_volume: float}
     */
    public function checkMonthlyMaintenance(int $agentId): array
    {
        try {
            $guard = new MlmPolicyGuard($this->pdo);
            return $guard->checkMonthlyMaintenance($agentId);
        } catch (Exception $e) {
            error_log("PolicyGuard maintenance check failed: " . $e->getMessage());
            return ['consecutive_months' => 0, 'qualifying_months' => 0, 'total_volume' => 0.0];
        }
    }

    /* ================================================================
       SECTION — INVESTMENT SALE COMMISSION (3% pool)
       ================================================================ */

    /**
     * Process commission on an investment sale (3% of investment amount).
     *
     * Split:
     *   2.0% — Direct agent/sponsor who referred the investment
     *   0.7% — L1 upline (agent's parent in network tree)
     *   0.3% — L2 upline (agent's grandparent in network tree)
     *
     * No global 20% cap applied — investment commissions are independent
     * of plot-sale commissions. Written to mlm_commission_ledger with
     * commission_type = 'investment_sale'.
     *
     * @param int   $investmentId    investments.id
     * @param int   $investorUserId  users.id of the person who invested
     * @param float $amount          principal investment amount
     * @param int   $referrerUserId  users.id of the referring agent/associate (may be 0 if self)
     * @return array{success: bool, distributed: float, entries: int, details: array}
     */
    public function investmentSale(
        int   $investmentId,
        int   $investorUserId,
        float $amount,
        int   $referrerUserId
    ): array {
        if ($amount <= 0 || $referrerUserId <= 0) {
            return ['success' => true, 'distributed' => 0, 'entries' => 0, 'details' => []];
        }

        $totalPool    = round($amount * 0.05, 2);  // 5% of investment
        $agentShare   = round($amount * 0.035, 2);  // 3.5% direct
        $l1Share      = round($amount * 0.01, 2); // 1.0% L1
        $l2Share      = round($amount * 0.005, 2); // 0.5% L2
        // Rounding adjustment goes to agent
        $agentShare   = round($totalPool - $l1Share - $l2Share, 2);

        $details = [];

        try {
            $this->pdo->beginTransaction();

            // 1. Direct agent/referrer — 3.5%
            if ($agentShare > 0) {
                $agentStatus = $this->getAgentStatus($referrerUserId);
                $isMissed = !in_array($agentStatus, ['active']);
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
                    $isMissed
                );
                if (!$isMissed) $this->incrementGbv($referrerUserId, $amount);
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
                $agentStatus = $this->getAgentStatus($upline['user_id']);
                $isMissed = !in_array($agentStatus, ['active']);
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
                    $isMissed
                );
                if (!$isMissed) $this->incrementGbv($upline['user_id'], $amount);
                $details[] = ['user_id' => $upline['user_id'], 'level' => $i + 1, 'pct' => $pcts[$i], 'amount' => $remaining[$i], 'ledger_id' => $ledgerId];
            }

            $this->pdo->commit();

            $totalDistributed = array_sum(array_column($details, 'amount'));

            return [
                'success'      => true,
                'distributed'  => $totalDistributed,
                'entries'      => count($details),
                'details'      => $details,
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[HybridCommissionEngine] investmentSale FAILED: " . $e->getMessage());
            return ['success' => false, 'distributed' => 0, 'entries' => 0, 'details' => [], 'error' => $e->getMessage()];
        }
    }

    /* ================================================================
       SECTION — COMMISSION REVERSAL (cancellation / refund)
       ================================================================ */

    /**
     * Reverse all commissions for a cancelled booking.
     *
     * Creates offsetting 'cancelled' ledger entries for each original
     * commission row, and debits the agent's lifetime_sales accordingly.
     *
     * @param int    $bookingId    plot_bookings.id
     * @param string $reason       Reason for cancellation (appears in notes)
     * @return array{success: bool, reversed: int, total_reversed: float, entries: array}
     */
    public function reverseBookingCommissions(int $bookingId, string $reason = 'Booking cancelled'): array
    {
        try {
            $this->pdo->beginTransaction();

            // Fetch all pending/approved ledger entries for this booking
            $stmt = $this->pdo->prepare("
                SELECT id, beneficiary_user_id, amount, commission_type, level
                FROM mlm_commission_ledger
                WHERE booking_id = ?
                  AND status IN ('pending', 'approved')
            ");
            $stmt->execute([$bookingId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $this->pdo->commit();
                return ['success' => true, 'reversed' => 0, 'total_reversed' => 0, 'entries' => []];
            }

            $totalReversed = 0.0;
            $reversedEntries = [];

            $updateStmt = $this->pdo->prepare("
                UPDATE mlm_commission_ledger
                SET status = 'cancelled', updated_at = NOW(), notes = CONCAT(notes, ' | REVERSED: ', ?)
                WHERE id = ?
            ");

            $debitStmt = $this->pdo->prepare("
                UPDATE mlm_profiles
                SET lifetime_sales = GREATEST(0, lifetime_sales - ?),
                    updated_at = NOW()
                WHERE user_id = ?
            ");

            foreach ($rows as $row) {
                // Mark original entry as cancelled
                $updateStmt->execute([$reason, $row['id']]);

                // Debit lifetime_sales (negative commission)
                $debitStmt->execute([(float)$row['amount'], $row['beneficiary_user_id']]);

                $totalReversed += (float)$row['amount'];
                $reversedEntries[] = [
                    'ledger_id'      => (int)$row['id'],
                    'user_id'        => (int)$row['beneficiary_user_id'],
                    'amount'         => (float)$row['amount'],
                    'type'           => $row['commission_type'],
                ];
            }

            $this->pdo->commit();

            return [
                'success'        => true,
                'reversed'       => count($rows),
                'total_reversed' => round($totalReversed, 2),
                'entries'        => $reversedEntries,
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[HybridCommissionEngine] reverseBookingCommissions FAILED: " . $e->getMessage());
            return ['success' => false, 'reversed' => 0, 'total_reversed' => 0, 'entries' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Reverse all commissions for a cancelled investment.
     *
     * Investment commissions are stored in mlm_commission_ledger with
     * commission_type='investment_sale' and receipt_id=investmentId.
     * This method marks them 'cancelled' and debits lifetime_sales.
     *
     * @param int    $investmentId  investments.id
     * @param string $reason        Reason for reversal (appears in notes)
     * @return array{success: bool, reversed: int, total_reversed: float, entries: array}
     */
    public function reverseInvestmentCommissions(int $investmentId, string $reason = 'Investment cancelled'): array
    {
        try {
            $this->pdo->beginTransaction();

            // Investment commissions are stored with receipt_id = investmentId
            $stmt = $this->pdo->prepare("
                SELECT id, beneficiary_user_id, amount, commission_type, level
                FROM mlm_commission_ledger
                WHERE receipt_id = ?
                  AND commission_type = 'investment_sale'
                  AND status IN ('pending', 'approved')
            ");
            $stmt->execute([$investmentId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $this->pdo->commit();
                return ['success' => true, 'reversed' => 0, 'total_reversed' => 0, 'entries' => []];
            }

            $totalReversed = 0.0;
            $reversedEntries = [];

            $updateStmt = $this->pdo->prepare("
                UPDATE mlm_commission_ledger
                SET status = 'cancelled', updated_at = NOW(), notes = CONCAT(notes, ' | REVERSED: ', ?)
                WHERE id = ?
            ");

            $debitStmt = $this->pdo->prepare("
                UPDATE mlm_profiles
                SET lifetime_sales = GREATEST(0, lifetime_sales - ?),
                    updated_at = NOW()
                WHERE user_id = ?
            ");

            foreach ($rows as $row) {
                $updateStmt->execute([$reason, $row['id']]);
                $debitStmt->execute([(float)$row['amount'], $row['beneficiary_user_id']]);

                $totalReversed += (float)$row['amount'];
                $reversedEntries[] = [
                    'ledger_id'      => (int)$row['id'],
                    'user_id'        => (int)$row['beneficiary_user_id'],
                    'amount'         => (float)$row['amount'],
                    'type'           => $row['commission_type'],
                ];
            }

            $this->pdo->commit();

            return [
                'success'        => true,
                'reversed'       => count($rows),
                'total_reversed' => round($totalReversed, 2),
                'entries'        => $reversedEntries,
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[HybridCommissionEngine] reverseInvestmentCommissions FAILED: " . $e->getMessage());
            return ['success' => false, 'reversed' => 0, 'total_reversed' => 0, 'entries' => [], 'error' => $e->getMessage()];
        }
    }

    /* ================================================================
       SECTION — FREELANCE BROKER CASCADING COMMISSION
       ================================================================

       Business rule:
         - A freelance broker sets their own commission rate (% or ₹/SqFt).
         - They can assign a LOWER rate to each of their downline members.
         - Broker margin = broker's own rate − rate given to downline.
         - Rates are stored in `associate_downline_rates`.
         - Fallback: if no custom rate found, uses RANK_SLABS GBV-based rate.
    */

    /**
     * Get the active custom rate a parent broker has set for a specific child.
     *
     * @param int    $parentUserId  The broker setting the rate
     * @param int    $childUserId   The downline member receiving this rate
     * @return array|null  ['commission_type'=>'percentage'|'per_sqft', 'commission_value'=>float] or null
     */
    public function getBrokerDownlineRate(int $parentUserId, int $childUserId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT commission_type, commission_value
                FROM associate_downline_rates
                WHERE parent_user_id = ?
                  AND child_user_id  = ?
                  AND effective_from <= CURDATE()
                  AND (effective_to IS NULL OR effective_to >= CURDATE())
                ORDER BY effective_from DESC
                LIMIT 1
            ");
            $stmt->execute([$parentUserId, $childUserId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log('[HybridCommissionEngine] getBrokerDownlineRate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the broker's own commission rate (from associates table).
     *
     * @param int $userId
     * @return array|null  ['commission_type'=>..., 'commission_value'=>float]
     */
    public function getBrokerOwnRate(int $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT commission_type, commission_value, agent_type
                FROM associates
                WHERE user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log('[HybridCommissionEngine] getBrokerOwnRate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate the commission amount for a single plot sale based on
     * custom broker rates (percentage or per_sqft) with area support.
     *
     * @param float  $plotValue    Total sale value of the plot in Rs.
     * @param float  $areaSqft     Area of the plot in SqFt.
     * @param string $commType     'percentage' or 'per_sqft'
     * @param float  $commValue    The rate value (% or Rs/SqFt)
     * @return float               Gross commission amount in Rs.
     */
    public function calcBrokerCommission(
        float  $plotValue,
        float  $areaSqft,
        string $commType,
        float  $commValue,
        float  $amountReceived = null
    ): float {
        if ($commType === 'per_sqft') {
            $totalComm = $commValue * $areaSqft;
            if ($amountReceived !== null && $plotValue > 0) {
                return round($totalComm * ($amountReceived / $plotValue), 2);
            }
            return round($totalComm, 2);
        }
        // percentage
        if ($amountReceived !== null) {
            return round(($commValue / 100.0) * $amountReceived, 2);
        }
        return round(($commValue / 100.0) * $plotValue, 2);
    }

    /**
     * Process the full cascading commission chain for a freelance broker network.
     *
     * Walk up the mlm_network_tree from the selling agent.
     * At each level, look for a custom downline rate set by the parent for that child.
     * Broker margin at each level = parent_rate − child_rate (differential).
     * If no custom rate exists at any level, falls back to RANK_SLABS differential.
     *
     * Does NOT write to DB — returns a breakdown array for the caller to persist.
     *
     * @param int   $sellingUserId  The associate who made the plot sale
     * @param float $plotValue      Total plot sale value in Rs.
     * @param float $areaSqft       Plot area in SqFt.
     * @param int   $maxLevels      How many upline levels to walk (default 10)
     * @return array [
     *   'success'     => bool,
     *   'chain'       => [['user_id'=>int, 'gross'=>float, 'net'=>float, 'type'=>string], ...],
     *   'total_gross' => float,
     * ]
     */
    public function processFreelanceBrokerCommission(
        int   $sellingUserId,
        float $plotValue,
        float $areaSqft,
        int   $maxLevels = 10,
        float $amountReceived = null
    ): array {
        try {
            // ── 1. Fetch the ancestor chain (selling agent + uplines) ──────────────
            $stmt = $this->pdo->prepare("
                SELECT t.user_id, t.parent_id, t.level_depth
                FROM mlm_network_tree t
                WHERE t.user_id = ?
                   OR t.user_id IN (
                        SELECT parent_id FROM mlm_network_tree
                        WHERE user_id = ?
                   )
                ORDER BY t.level_depth ASC
                LIMIT ?
            ");
            $stmt->execute([$sellingUserId, $sellingUserId, $maxLevels + 1]);
            $chain = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($chain)) {
                return ['success' => false, 'error' => 'No network tree record for user ' . $sellingUserId, 'chain' => []];
            }

            // ── 2. Walk the chain, resolving rates ────────────────────────────────
            $breakdown    = [];
            $totalGross   = 0.0;
            $prevChildRate = null; // rate that the previous (lower) person had — for differential

            foreach ($chain as $node) {
                $uid = (int)$node['user_id'];

                // Get this member's own rate
                $ownRateRow = $this->getBrokerOwnRate($uid);
                if (!$ownRateRow || empty($ownRateRow['commission_value'])) {
                    continue; // No rate configured, skip
                }

                $commType  = $ownRateRow['commission_type'] ?? 'percentage';
                $ownRate   = (float)$ownRateRow['commission_value'];

                // Gross commission for this node at their OWN rate
                $grossOwn = $this->calcBrokerCommission($plotValue, $areaSqft, $commType, $ownRate, $amountReceived);

                // Gross commission at the CHILD's rate (what the chain already cost below)
                $grossChild = $prevChildRate !== null
                    ? $this->calcBrokerCommission($plotValue, $areaSqft, $commType, $prevChildRate, $amountReceived)
                    : 0.0;

                // Margin = what this node earns = own_rate_commission - child_rate_commission
                $margin = max(0.0, $grossOwn - $grossChild);

                if ($margin > 0) {
                    $breakdown[] = [
                        'user_id'   => $uid,
                        'level'     => (int)$node['level_depth'],
                        'comm_type' => $commType,
                        'own_rate'  => $ownRate,
                        'gross'     => round($grossOwn, 2),
                        'margin'    => round($margin, 2),
                        'type'      => 'broker_cascading',
                    ];
                    $totalGross += $margin;
                }

                $prevChildRate = $ownRate;
            }

            return [
                'success'     => true,
                'chain'       => $breakdown,
                'total_gross' => round($totalGross, 2),
            ];

        } catch (Exception $e) {
            error_log('[HybridCommissionEngine] processFreelanceBrokerCommission: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'chain' => []];
        }
    }
}
