<?php

namespace App\Services;

use PDO;
use Exception;
use App\Core\Middleware\TenantContext;

/**
 * Hybrid MLM & Salary Commission Engine (Facade)
 * ──────────────────────────────────────
 * Raghunath Nagri project — global 20%-capped dual-track payout,
 * breakaway safeguard, and Diwali Dhamaka salary incentive audit.
 *
 * This class now acts as a facade delegating to focused service classes:
 *   - PricingService        — Pricing matrix, plot value calculations
 *   - RankService           — Rank slabs, names, rates, salary tiers, plan caps
 *   - TrackACommissionService — Slab Differential (15%) with breakaway safeguard
 *   - TrackBCommissionService — Performance Rollup Chain (3%)
 *   - TrackCCommissionService — Milestone Reward Escrow (2%)
 *   - RoyaltyPoolService    — 2% pool contributions & distribution
 *   - SalaryIncentiveService — Salary grants, career rewards, monthly maintenance
 *   - PipelineService       — Main commission pipeline orchestration
 *   - AgentLedgerService    — Agent GBV, upline chain, ledger queries, reversals
 *   - BrokerCommissionService — Freelance broker cascading commissions
 *   - InvestmentCommissionService — Investment sale commissions
 *   - CommissionLedgerService — Shared ledger writing with plan safety & notifications
 */
class HybridCommissionEngine
{
    /** @var PDO */
    private $pdo;

    // Service instances
    private \App\Services\MLM\PricingService $pricingService;
    private \App\Services\MLM\RankService $rankService;
    private \App\Services\MLM\TrackACommissionService $trackA;
    private \App\Services\MLM\TrackBCommissionService $trackB;
    private \App\Services\MLM\TrackCCommissionService $trackC;
    private \App\Services\MLM\RoyaltyPoolService $royaltyPool;
    private \App\Services\MLM\SalaryIncentiveService $salaryIncentive;
    private \App\Services\MLM\PipelineService $pipeline;
    private \App\Services\MLM\AgentLedgerService $agentLedger;
    private \App\Services\MLM\BrokerCommissionService $brokerService;
    private \App\Services\MLM\InvestmentCommissionService $investmentService;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();

        // Initialize service instances
        $this->pricingService    = new \App\Services\MLM\PricingService();
        $this->rankService       = new \App\Services\MLM\RankService();
        $this->trackA            = new \App\Services\MLM\TrackACommissionService();
        $this->trackB            = new \App\Services\MLM\TrackBCommissionService();
        $this->trackC            = new \App\Services\MLM\TrackCCommissionService();
        $this->royaltyPool       = new \App\Services\MLM\RoyaltyPoolService();
        $this->salaryIncentive   = new \App\Services\MLM\SalaryIncentiveService();
        $this->pipeline          = new \App\Services\MLM\PipelineService();
        $this->agentLedger       = new \App\Services\MLM\AgentLedgerService();
        $this->brokerService     = new \App\Services\MLM\BrokerCommissionService();
        $this->investmentService = new \App\Services\MLM\InvestmentCommissionService();
    }

    /* ================================================================
       PUBLIC API — PRICING (delegates to PricingService)
       ================================================================ */

    public function getPricingMatrix(): array
    {
        return $this->pricingService->getPricingMatrix();
    }

    public function getBlockPricing(string $blockKey): ?array
    {
        return $this->pricingService->getBlockPricing($blockKey);
    }

    public function calculatePlotValue(string $blockKey, ?float $areaOverride = null): array
    {
        return $this->pricingService->calculatePlotValue($blockKey, $areaOverride);
    }

    public function getDefaultBookingAmount(): float
    {
        return $this->pricingService->getDefaultBookingAmount();
    }

    /* ================================================================
       PUBLIC API — MASTER COMMISSION PIPELINE (delegates to PipelineService)
       ================================================================ */

    public function processPipelineCommission(
        int $bookingId,
        int $receiptId,
        float $amountReceived,
        int $executingAgentId
    ): array {
        return $this->pipeline->processPipeline($bookingId, $receiptId, $amountReceived, $executingAgentId);
    }

    /* ================================================================
       PUBLIC API — SALARY INCENTIVE & CAREER REWARDS (delegates to SalaryIncentiveService)
       ================================================================ */

    public function checkSalaryIncentiveEligibility(int $agentId): array
    {
        return $this->salaryIncentive->checkSalaryIncentiveEligibility($agentId);
    }

    public function activateSalaryGrants(int $agentId): bool
    {
        return $this->salaryIncentive->activateSalaryGrant($agentId);
    }

    public function processMonthlySalaryGrants(string $monthYear): array
    {
        return $this->salaryIncentive->processMonthlyGrants($monthYear);
    }

    public function checkMonthlySalaryMaintenance(int $userId, string $monthYear): bool
    {
        $result = $this->salaryIncentive->checkMonthlyMaintenance($userId, $monthYear);
        return $result['eligible'] ?? false;
    }

    /* ================================================================
       PUBLIC API — RANK & RANK SLABS (delegates to RankService)
       ================================================================ */

    public function resolveRank(int $agentId): string
    {
        return $this->rankService->resolveRank($agentId);
    }

    public function getRankName(string $rankSlug): string
    {
        return $this->rankService->getRankName($rankSlug);
    }

    public function getRankRate(string $rankSlug): float
    {
        return $this->rankService->getRankRate($rankSlug);
    }

    public function getRankSlabs(): array
    {
        return $this->rankService->getRankSlabs();
    }

    public function getSalaryTiers(): array
    {
        return $this->rankService->getSalaryTiers();
    }

    /* ================================================================
       PUBLIC API — AGENT LEDGER & UPLINE (delegates to AgentLedgerService)
       ================================================================ */

    public function getAgentLedger(int $agentId, ?string $from = null, ?string $to = null): array
    {
        return $this->agentLedger->getAgentLedger($agentId, $from, $to);
    }

    public function getUplineChain(int $agentId, int $maxLevels = 7): array
    {
        return $this->agentLedger->getUplineChain($agentId, $maxLevels);
    }

    public function getAgentEscrowBalance(int $agentId): float
    {
        return $this->agentLedger->getAgentEscrowBalance($agentId);
    }

    /* ================================================================
       PUBLIC API — ROYALTY POOL (delegates to RoyaltyPoolService)
       ================================================================ */

    public function contributeToRoyaltyPool(int $bookingId, float $amountReceived): array
    {
        return $this->royaltyPool->contribute($bookingId, $amountReceived);
    }

    public function distributeRoyaltyPool(string $monthYear): array
    {
        return $this->royaltyPool->distribute($monthYear);
    }

    public function getRoyaltyPoolStatus(?string $monthYear = null): array
    {
        return $this->royaltyPool->getStatus($monthYear);
    }

    /* ================================================================
       PUBLIC API — CAREER REWARDS (delegates to SalaryIncentiveService)
       ================================================================ */

    public function checkCareerRewards(int $agentId): array
    {
        return $this->salaryIncentive->checkCareerRewards($agentId);
    }

    /* ================================================================
       PUBLIC API — PLAN SNAPSHOT (delegates to RankService)
       ================================================================ */

    public function getActivePlanSnapshot(): ?array
    {
        return $this->rankService->getActivePlanSnapshot();
    }

    public function loadRankSlabsFromDb(): array
    {
        return $this->rankService->loadRankSlabsFromDb();
    }

    public function simulatePayout(float $saleAmount, string $sellerRankSlug): array
    {
        // This requires the full pipeline with a specific agent context
        // For simulation, we use the track services directly with mock data
        $caps = $this->rankService->getActivePlanCaps();
        $trackABudget = $saleAmount * ($caps['track_a'] / 100);
        $trackBBudget = $saleAmount * ($caps['track_b'] / 100);
        $trackCBudget = $saleAmount * ($caps['track_c'] / 100);

        // We need an agent ID for simulation - use 1 as default
        $trackA = $this->trackA->compute(1, $saleAmount, $trackABudget, 0, 0);
        $trackB = $this->trackB->compute(1, $saleAmount, $trackBBudget, 0, 0);
        $trackC = $this->trackC->compute(1, $saleAmount, $trackCBudget, 0, 0);

        return [
            'track_a' => $trackA,
            'track_b' => $trackB,
            'track_c' => $trackC,
            'total_distributed' => round($trackA['distributed'] + $trackB['distributed'] + $trackC['distributed'], 2),
        ];
    }

    public function checkPromotionEligibility(int $agentId, string $targetRankSlug): array
    {
        // This would require more complex logic - delegate to salary incentive service
        $currentRank = $this->rankService->resolveRank($agentId);
        $gbv = $this->getAgentGbv($agentId);
        $rankSlabs = $this->rankService->loadRankSlabsFromDb();
        $targetSlab = $rankSlabs[$targetRankSlug] ?? null;

        if (!$targetSlab) {
            return ['success' => false, 'error' => 'Target rank not found'];
        }

        $eligible = $gbv >= $targetSlab['min_gbv'];

        return [
            'success' => true,
            'eligible' => $eligible,
            'current_rank' => $this->rankService->getRankName($currentRank),
            'target_rank' => $this->rankService->getRankName($targetRankSlug),
            'current_gbv' => $gbv,
            'required_gbv' => $targetSlab['min_gbv'],
            'shortfall' => max(0, $targetSlab['min_gbv'] - $gbv),
        ];
    }

    public function checkMonthlyMaintenance(int $agentId, string $monthYear = null): array
    {
        $monthYear = $monthYear ?: date('Y-m');
        $result = $this->salaryIncentive->checkMonthlyMaintenance($agentId, $monthYear);
        return $result;
    }

    public function investmentSale(
        int $investmentId,
        int $investorUserId,
        float $amount,
        int $referrerUserId
    ): array {
        return $this->investmentService->process($investmentId, $investorUserId, $amount, $referrerUserId);
    }

    public function reverseBookingCommissions(int $bookingId, string $reason = 'Booking cancelled'): array
    {
        // Delegate to AgentLedgerService for reversal logic
        // This requires implementing the reversal logic in AgentLedgerService
        return ['success' => false, 'error' => 'Not yet implemented in facade'];
    }

    public function reverseInvestmentCommissions(int $investmentId, string $reason = 'Investment cancelled'): array
    {
        return ['success' => false, 'error' => 'Not yet implemented in facade'];
    }

    public function getBrokerDownlineRate(int $parentUserId, int $childUserId): ?array
    {
        return $this->brokerService->getBrokerDownlineRate($parentUserId, $childUserId);
    }

    public function getBrokerOwnRate(int $userId): ?array
    {
        return $this->brokerService->getBrokerOwnRate($userId);
    }

    public function calcBrokerCommission(
        float $plotValue,
        float $areaSqft,
        string $commType,
        float $commValue,
        float $amountReceived = null
    ): float {
        return $this->brokerService->calcBrokerCommission($plotValue, $areaSqft, $commType, $commValue, $amountReceived);
    }

    public function processFreelanceBrokerCommission(
        int $sellingUserId,
        float $plotValue,
        float $areaSqft,
        int $maxLevels = 10
    ): array {
        return $this->brokerService->processFreelanceBrokerCommission($sellingUserId, $plotValue, $areaSqft, $maxLevels);
    }

    // Helper methods that need to be implemented in facade
    protected function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    // Helper methods that need to be implemented (used by simulation and other methods)
    public function getAgentGbv(int $agentId): float
    {
        return $this->agentLedger->getGbv($agentId);
    }

    public function incrementGbv(int $agentId, float $amount): void
    {
        $this->agentLedger->incrementGbv($agentId, $amount);
    }

    public function getAgentStatus(int $userId): string
    {
        return $this->agentLedger->getAgentStatus($userId);
    }

    public function verifyUplineSideVolume(int $uplineUserId, string $month): bool
    {
        return $this->agentLedger->verifyUplineSideVolume($uplineUserId, $month);
    }

    protected function fetchBooking(int $bookingId): ?array
    {
        // This is used internally - delegate to agent ledger service
        return $this->agentLedger->fetchBooking($bookingId);
    }

    private function resolveTelecallerForBooking(array $booking): ?array
    {
        // This is used by PipelineService - delegate there
        return $this->pipeline->resolveTelecallerForBooking($booking);
    }
}