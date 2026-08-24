<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Pipeline Service
 * Main commission pipeline orchestration - processes booking payments through all tracks
 */
class PipelineService
{
    use ServiceTenantTrait;

    private \PDO $pdo;
    private \App\Services\MLM\TrackACommissionService $trackA;
    private \App\Services\MLM\TrackBCommissionService $trackB;
    private \App\Services\MLM\TrackCCommissionService $trackC;
    private \App\Services\MLM\RoyaltyPoolService $royaltyPool;
    private \App\Services\MLM\SalaryIncentiveService $salaryIncentive;
    private \App\Services\MLM\CommissionLedgerService $ledgerService;
    private \App\Services\MLM\RankService $rankService;
    private \App\Services\MLM\BrokerCommissionService $brokerService;
    private \App\Services\MLM\InvestmentCommissionService $investmentService;

    public function __construct(
        ?\PDO $pdo = null,
        ?\App\Services\MLM\TrackACommissionService $trackA = null,
        ?\App\Services\MLM\TrackBCommissionService $trackB = null,
        ?\App\Services\MLM\TrackCCommissionService $trackC = null,
        ?\App\Services\MLM\RoyaltyPoolService $royaltyPool = null,
        ?\App\Services\MLM\SalaryIncentiveService $salaryIncentive = null,
        ?\App\Services\MLM\CommissionLedgerService $ledgerService = null,
        ?\App\Services\MLM\RankService $rankService = null,
        ?\App\Services\MLM\BrokerCommissionService $brokerService = null,
        ?\App\Services\MLM\InvestmentCommissionService $investmentService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->trackA = $trackA ?? new \App\Services\MLM\TrackACommissionService();
        $this->trackB = $trackB ?? new \App\Services\MLM\TrackBCommissionService();
        $this->trackC = $trackC ?? new \App\Services\MLM\TrackCCommissionService();
        $this->royaltyPool = $royaltyPool ?? new \App\Services\MLM\RoyaltyPoolService();
        $this->salaryIncentive = $salaryIncentive ?? new \App\Services\MLM\SalaryIncentiveService();
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService();
        $this->rankService = $rankService ?? new \App\Services\MLM\RankService();
        $this->brokerService = $brokerService ?? new \App\Services\MLM\BrokerCommissionService();
        $this->investmentService = $investmentService ?? new \App\Services\MLM\InvestmentCommissionService();
    }

    /**
     * Main pipeline: process a booking payment through all commission tracks
     *
     * @param int   $bookingId      plot_bookings.id
     * @param int   $receiptId      booking_payment_schedules.id (or 0 for token)
     * @param float $amountReceived Actual money received this receipt
     * @param int   $executingAgentId The agent/associate who triggered the booking
     * @return array Full commission breakdown
     */
    public function processPipeline(
        int $bookingId,
        int $receiptId,
        float $amountReceived,
        int $executingAgentId
    ): array {
        $this->pdo->beginTransaction();

        try {
            // Guard: nothing to process
            if ($amountReceived <= 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Amount received must be positive'];
            }

            // IDEMPOTENCY: Skip if commissions already exist for this booking+receipt
            $dupCheck = $this->pdo->prepare("
                SELECT COUNT(*) FROM mlm_commission_ledger 
                WHERE booking_id = ? AND receipt_id = ? AND status NOT IN ('cancelled','clawed_back')
            ");
            $dupCheck->execute([$bookingId, $receiptId]);
            if ((int) $dupCheck->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => true, 'skipped' => true, 'reason' => 'commissions_already_exist_for_this_receipt'];
            }

            // Resolve booking for telecaller & subsequent logic
            $booking = $this->fetchBooking($bookingId);

            // Resolve telecaller (if any) and compute incentives BEFORE agent bypass
            $telecaller = $this->resolveTelecallerForBooking($booking ?: []);
            $telecallerIncentive = 0.0;
            $telecallerLedgerIds = [];
            $maxTelecallerBudget = $amountReceived * 0.05; // Strict 5% cap to protect company 80% margins
            $telecallerBudgetUsed = 0.0;

            if ($telecaller) {
                $tcUserId = (int) $telecaller['user_id'];
                $plotArea = 0.0;
                $bookingValue = 0.0;
                if ($booking) {
                    $bookingValue = (float) ($booking['agreement_value'] ?? $booking['total_plot_value'] ?? 0.0);
                    if (!empty($booking['plot_id'])) {
                        $pStmt = $this->pdo->prepare("SELECT area_sqft FROM plots WHERE id = ? LIMIT 1");
                        $pStmt->execute([$booking['plot_id']]);
                        $plotArea = (float) $pStmt->fetchColumn();
                    }
                }

                if ($bookingValue <= 0) {
                    $bookingValue = 1.0;
                }

                if (isset($telecaller['telecaller_percent_rate']) && $telecaller['telecaller_percent_rate'] > 0) {
                    $percentRate = (float) $telecaller['telecaller_percent_rate'];
                    $telecallerIncentive = ($amountReceived * $percentRate) / 100.0;
                    $telecallerIncentive = min($telecallerIncentive, $maxTelecallerBudget);
                    $note = "Telecaller Percentage Incentive ({$percentRate}%, Capped)";
                    $rateUsed = $percentRate;
                } else {
                    if ($receiptId === 0) {
                        // Token payment -> flat incentive
                        $flatRate = (float) ($telecaller['telecaller_incentive_rate'] > 0 ? $telecaller['telecaller_incentive_rate'] : 1000.00);
                        $telecallerIncentive = min($flatRate, $maxTelecallerBudget);
                        $note = "Telecaller Token Conversion Incentive (Flat ₹" . number_format($flatRate) . ", Capped)";
                        $rateUsed = 0.0;
                    } else {
                        // Subsequent payment -> proportional sqft incentive
                        $sqftRate = (float) ($telecaller['telecaller_sqft_rate'] > 0 ? $telecaller['telecaller_sqft_rate'] : 10.00);
                        $totalSqftIncentive = $plotArea * $sqftRate;
                        $proportion = $amountReceived / $bookingValue;
                        $telecallerIncentive = min($totalSqftIncentive * $proportion, $maxTelecallerBudget);
                        $note = "Telecaller SqFt Incentive (₹" . $sqftRate . "/sqft proportional, total ₹" . number_format($totalSqftIncentive) . ", Capped)";
                        $rateUsed = $sqftRate;
                    }
                }

                if ($telecallerIncentive > 0.01) {
                    $ledgerId = $this->ledgerService->writeLedger(
                        $tcUserId, $tcUserId, $amountReceived, $rateUsed,
                        round($telecallerIncentive, 2), 'telecaller_bonus', 0, $bookingId, $receiptId,
                        $note
                    );
                    $telecallerLedgerIds[] = $ledgerId;
                    $telecallerBudgetUsed += $telecallerIncentive;
                }

                // Walk telecaller parent hierarchy up to 2 generations to award Team Lead overrides
                $currentParentId = !empty($telecaller['telecaller_parent_id']) ? (int) $telecaller['telecaller_parent_id'] : null;
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
                    $parentRecord = $pStmt->fetch(\PDO::FETCH_ASSOC);

                    if (!$parentRecord) {
                        break;
                    }

                    $parentPct = $levelRates[$lvl];
                    $parentAmt = $amountReceived * ($parentPct / 100);
                    $parentAmt = min($parentAmt, $maxTelecallerBudget - $telecallerBudgetUsed);

                    if ($parentAmt > 0.01) {
                        $ledgerId = $this->ledgerService->writeLedger(
                            $currentParentId, $tcUserId, $amountReceived, $parentPct,
                            round($parentAmt, 2), 'level_bonus', $lvl, $bookingId, $receiptId,
                            "Telecaller Team Lead Override (Level {$lvl}, {$parentPct}%, Capped)"
                        );
                        $telecallerLedgerIds[] = $ledgerId;
                        $telecallerIncentive += $parentAmt;
                        $telecallerBudgetUsed += $parentAmt;
                    }

                    $currentParentId = !empty($parentRecord['telecaller_parent_id']) ? (int) $parentRecord['telecaller_parent_id'] : null;
                }
            }

            // Independent Agent bypass — flat commission, no MLM upline
            $assocStmt = $this->pdo->prepare(
                "SELECT agent_track, agent_type, brokerage_model, brokerage_rate FROM associates WHERE user_id = ? LIMIT 1"
            );
            $assocStmt->execute([$executingAgentId]);
            $assocRecord = $assocStmt->fetch(\PDO::FETCH_ASSOC);

            if ($assocRecord && ($assocRecord['agent_track'] ?? 'mlm') === 'independent') {
                $result = $this->investmentService->process(
                    $bookingId, // using as investmentId for processIndependentAgentCommission
                    $receiptId,
                    $amountReceived,
                    $executingAgentId,
                    $assocRecord
                );
                $this->pdo->commit();
                return $result;
            }

            // Budget caps from active plan
            $caps = $this->rankService->getActivePlanCaps();
            $globalCap = $amountReceived * ($caps['global_cap'] / 100);
            $trackABudget = $amountReceived * ($caps['track_a'] / 100);
            $trackBBudget = $amountReceived * ($caps['track_b'] / 100);
            $trackCBudget = $amountReceived * ($caps['track_c'] / 100);

            // Run Track A (Slab Differential)
            $trackAResult = $this->trackA->compute($executingAgentId, $amountReceived, $trackABudget, $bookingId, $receiptId);

            // Run Track B (Performance Rollup)
            $trackBResult = $this->trackB->compute($executingAgentId, $amountReceived, $trackBBudget, $bookingId, $receiptId);

            // Run Track C (Milestone Escrow)
            $trackCResult = $this->trackC->compute($executingAgentId, $amountReceived, $trackCBudget, $bookingId, $receiptId);

            // Royalty Pool Contribution (2%)
            $royaltyResult = $this->royaltyPool->contribute($bookingId, $amountReceived);

            // Career Rewards Check
            $careerReward = $this->salaryIncentive->checkCareerRewards($executingAgentId);

            $this->pdo->commit();

            $totalDistributed = $trackAResult['distributed'] + $trackBResult['distributed'] + $trackCResult['distributed'] + $telecallerIncentive;

            return [
                'success'               => true,
                'booking_id'            => $bookingId,
                'receipt_id'            => $receiptId,
                'amount_received'       => $amountReceived,
                'agent_id'              => $executingAgentId,
                'agent_track'           => 'mlm',
                'global_cap'            => $globalCap,
                'total_distributed'     => round($totalDistributed, 2),
                'track_a'               => $trackAResult,
                'track_b'               => $trackBResult,
                'track_c'               => $trackCResult,
                'royalty_contribution'  => $royaltyResult,
                'telecaller_incentive'  => round($telecallerIncentive, 2),
                'telecaller_ledger_ids' => $telecallerLedgerIds,
                'career_reward'         => $careerReward,
            ];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[PipelineService] processPipeline FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve telecaller for a booking
     */
    private function resolveTelecallerForBooking(array $booking): ?array
    {
        if (empty($booking['telecaller_id'])) {
            return null;
        }
        $stmt = $this->pdo->prepare("
            SELECT user_id, telecaller_parent_id, telecaller_percent_rate, telecaller_incentive_rate, telecaller_sqft_rate
            FROM associates WHERE id = ? LIMIT 1
        ");
        $stmt->execute([$booking['telecaller_id']]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Fetch a booking row from plot_bookings
     */
    private function fetchBooking(int $bookingId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plot_bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
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