<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\SystemLogger;
use Exception;

/**
 * ColonyFeasibilityService — Full colony pricing feasibility engine.
 *
 * Pricing formula (from brochure spec):
 *   C = (L + R + D + A) / SaleableArea     ← cost-basis per SqFt
 *   P = C / (1 - 0.50) = 2C               ← selling price per SqFt
 *
 * Where:
 *   L = land acquisition cost
 *   R = registry + stamp duty cost
 *   D = development cost (infrastructure)
 *   A = approvals cost (NOCs, permissions)
 *   O = G&A overhead (office, staff, marketing, brokerage) — NOT in C
 *
 * Overhead breakdown:
 *   MLM commissions  25%  (worst-case 26.4%, capped at 25%)
 *   G&A overhead      5%
 *   Profit margin    20%
 *   Total            50%  →  Markup = 1/(1 - 0.50) = 2.0
 *
 * Saleable area yield defaults to 60% (40% eaten by roads, parks, amenities).
 *
 * Every calculation is logged to `colony_pricing_feasibility` for audit.
 */
class ColonyFeasibilityService
{
    /** @var Database|null */
    private $db;

    /** @var SystemLogger|null */
    private $logger;

    /** @var \PDO|null */
    private $pdo;

    // ── Default overhead percentages ────────────────────────────
    private const DEFAULT_MLM_PCT    = 25.0;
    private const DEFAULT_GA_PCT     = 5.0;
    private const DEFAULT_PROFIT_PCT = 20.0;
    private const DEFAULT_YIELD_PCT  = 60.0;

    public function __construct()
    {
        try {
            $this->db     = Database::getInstance();
            $this->pdo    = $this->db->getPdo();
            $this->logger = new SystemLogger();
        } catch (Exception $e) {
            $this->db     = null;
            $this->pdo    = null;
            $this->logger = null;
        }
    }

    // ================================================================
    //  PUBLIC API
    // ================================================================

    /**
     * Calculate full feasibility pricing for a colony.
     *
     * Pulls land cost, registry/stamp duty, development costs, approvals,
     * and saleable area from the DB. Computes cost-basis and selling price
     * using the markup formula. Logs to `colony_pricing_feasibility`.
     *
     * @param int   $colonyId
     * @param array $overrides  Optional overrides: total_raw_area_sqft, yield_pct,
     *                          target_profit_pct, office_overhead_pct, mlm_budget_pct
     * @return array
     */
    public function calculateFeasibility(int $colonyId, array $overrides = []): array
    {
        try {
            // ── 1. Cost components from DB ──────────────────────
            $landCost      = $this->getLandCost($colonyId);
            $registryCost  = $this->getRegistryCost($colonyId);
            $devCosts      = $this->getDevCostsByCategory($colonyId);
            $approvalCost  = $this->getApprovalCost($colonyId);
            $gaCost        = $this->getGACost($colonyId);
            $totalDev      = array_sum($devCosts);

            // ── 2. Area ────────────────────────────────────────
            $totalRawArea = (float) ($overrides['total_raw_area_sqft'] ?? $this->getRawLandArea($colonyId));
            $yieldPct     = (float) ($overrides['yield_pct'] ?? self::DEFAULT_YIELD_PCT);
            $saleableArea = $totalRawArea * ($yieldPct / 100.0);

            // Also try plot-based saleable area as cross-check
            $plotSaleableArea = $this->getTotalPlotArea($colonyId);

            // ── 3. Overhead percentages ────────────────────────
            $profitPct = (float) ($overrides['target_profit_pct'] ?? self::DEFAULT_PROFIT_PCT);
            $gaPct     = (float) ($overrides['office_overhead_pct'] ?? self::DEFAULT_GA_PCT);
            $mlmPct    = (float) ($overrides['mlm_budget_pct'] ?? self::DEFAULT_MLM_PCT);
            $totalOverheadPct = $mlmPct + $gaPct + $profitPct;

            // ── 4. Cost-basis C = (L + R + D + A) / SaleableArea ─
            $totalCostBasis = $landCost + $registryCost + $totalDev + $approvalCost;
            $rawCostPPSF    = ($saleableArea > 0) ? round($totalCostBasis / $saleableArea, 2) : 0;

            // ── 5. Selling price P = C / (1 - overhead%) ──────
            $markupFactor    = ($totalOverheadPct < 100) ? round(1.0 / (1.0 - $totalOverheadPct / 100.0), 4) : 0;
            $recommendedPPSF = round($rawCostPPSF * $markupFactor, 2);

            // Total G&A in rupees (for reference)
            $totalGaRupees  = $gaCost;
            $gaPerSqft      = ($saleableArea > 0) ? round($gaCost / $saleableArea, 2) : 0;

            // ── 6. Colony-level totals for revenue projection ───
            $totalRevenue   = round($recommendedPPSF * $plotSaleableArea, 2);
            $totalProfit    = round($totalRevenue - ($totalCostBasis + $gaCost), 2);
            $profitMarginActual = ($totalRevenue > 0) ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

            $result = [
                'success'               => true,
                'colony_id'             => $colonyId,

                // Area
                'total_raw_area_sqft'   => $totalRawArea,
                'yield_pct'             => $yieldPct,
                'saleable_area_sqft'    => round($saleableArea, 2),
                'plot_saleable_area_sqft' => round($plotSaleableArea, 2),

                // Cost components
                'land_cost'             => round($landCost, 2),
                'registry_cost'         => round($registryCost, 2),
                'development_cost'      => round($totalDev, 2),
                'development_by_type'   => $devCosts,
                'approval_cost'         => round($approvalCost, 2),
                'ga_cost'               => round($gaCost, 2),
                'ga_per_sqft'           => $gaPerSqft,

                // Cost-basis
                'total_cost_basis'      => round($totalCostBasis, 2),
                'raw_cost_per_sqft'     => $rawCostPPSF,

                // Pricing
                'mlm_budget_pct'        => $mlmPct,
                'office_overhead_pct'   => $gaPct,
                'profit_margin_pct'     => $profitPct,
                'total_overhead_pct'    => round($totalOverheadPct, 1),
                'markup_factor'         => $markupFactor,
                'recommended_price_ppsf' => $recommendedPPSF,

                // Revenue projection
                'total_revenue_projected' => $totalRevenue,
                'total_profit_projected'  => $totalProfit,
                'profit_margin_actual_pct' => $profitMarginActual,
            ];

            // ── 7. Log to audit table ──────────────────────────
            $this->logFeasibility($result, $overrides);

            return $result;

        } catch (Exception $e) {
            $this->logError('calculateFeasibility failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get the full feasibility audit log for a colony.
     *
     * @param int $colonyId
     * @param int $limit
     * @return array
     */
    public function getFeasibilityHistory(int $colonyId, int $limit = 20): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT f.*, u.name AS created_by_name
                FROM colony_pricing_feasibility f
                LEFT JOIN users u ON u.id = f.created_by
                WHERE f.colony_id = :cid
                ORDER BY f.created_at DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':cid', $colonyId, \PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->logError('getFeasibilityHistory failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Quick preview: calculate without persisting to audit table.
     *
     * @param int   $colonyId
     * @param array $overrides
     * @return array
     */
    public function previewFeasibility(int $colonyId, array $overrides = []): array
    {
        try {
            $landCost     = $this->getLandCost($colonyId);
            $registryCost = $this->getRegistryCost($colonyId);
            $devCosts     = $this->getDevCostsByCategory($colonyId);
            $approvalCost = $this->getApprovalCost($colonyId);
            $gaCost       = $this->getGACost($colonyId);
            $totalDev     = array_sum($devCosts);

            $totalRawArea = (float) ($overrides['total_raw_area_sqft'] ?? $this->getRawLandArea($colonyId));
            $yieldPct     = (float) ($overrides['yield_pct'] ?? self::DEFAULT_YIELD_PCT);
            $saleableArea = $totalRawArea * ($yieldPct / 100.0);
            $plotArea     = $this->getTotalPlotArea($colonyId);

            $profitPct = (float) ($overrides['target_profit_pct'] ?? self::DEFAULT_PROFIT_PCT);
            $gaPct     = (float) ($overrides['office_overhead_pct'] ?? self::DEFAULT_GA_PCT);
            $mlmPct    = (float) ($overrides['mlm_budget_pct'] ?? self::DEFAULT_MLM_PCT);
            $totalOverheadPct = $mlmPct + $gaPct + $profitPct;

            $totalCostBasis = $landCost + $registryCost + $totalDev + $approvalCost;
            $rawCostPPSF    = ($saleableArea > 0) ? round($totalCostBasis / $saleableArea, 2) : 0;
            $markupFactor   = ($totalOverheadPct < 100) ? round(1.0 / (1.0 - $totalOverheadPct / 100.0), 4) : 0;
            $recommendedPPSF = round($rawCostPPSF * $markupFactor, 2);

            return [
                'success'                => true,
                'total_cost_basis'       => round($totalCostBasis, 2),
                'saleable_area_sqft'     => round($saleableArea, 2),
                'raw_cost_per_sqft'      => $rawCostPPSF,
                'markup_factor'          => $markupFactor,
                'recommended_price_ppsf' => $recommendedPPSF,
                'total_revenue'          => round($recommendedPPSF * $plotArea, 2),
                'breakdown' => [
                    'land'      => round($landCost, 2),
                    'registry'  => round($registryCost, 2),
                    'dev'       => round($totalDev, 2),
                    'approvals' => round($approvalCost, 2),
                    'ga_rupees' => round($gaCost, 2),
                ],
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all colonies with their latest feasibility pricing (for comparison table).
     *
     * @return array
     */
    public function getAllColoniesFeasibility(): array
    {
        try {
            $colonies = $this->db->fetchAll(
                "SELECT id, name, total_plots, available_plots, starting_price FROM colonies WHERE is_active = 1 ORDER BY name"
            );

            $results = [];
            foreach ($colonies as $colony) {
                $cid = (int) $colony['id'];
                $latest = $this->getLatestFeasibility($cid);
                $results[] = [
                    'colony_id'           => $cid,
                    'name'                => $colony['name'],
                    'total_plots'         => (int) $colony['total_plots'],
                    'available_plots'     => (int) $colony['available_plots'],
                    'current_starting_price' => (float) $colony['starting_price'],
                    'recommended_price'   => $latest ? (float) $latest['recommended_price_ppsf'] : null,
                    'cost_basis'          => $latest ? (float) $latest['raw_cost_per_sqft'] : null,
                    'last_calculated'     => $latest ? $latest['created_at'] : null,
                ];
            }

            return ['success' => true, 'colonies' => $results];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PRIVATE — DATA LOOKUP
    // ================================================================

    /**
     * Get land acquisition cost for a colony.
     */
    private function getLandCost(int $colonyId): float
    {
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(acquisition_cost), 0) AS total
             FROM land_acquisitions WHERE colony_id = :cid AND status IN ('registered', 'active', 'sold', 'under_development')",
            ['cid' => $colonyId]
        );
        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Fallback: colony land_cost column
        $row = $this->tryFetch(
            "SELECT COALESCE(land_cost, 0) AS total FROM colonies WHERE id = :cid",
            ['cid' => $colonyId]
        );
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Get registry + stamp duty costs for a colony.
     *
     * These are company-side costs paid at the time of land purchase
     * (stamp duty, registration fees, legal charges for land transfer).
     * Stored in colony_development_costs with cost_type 'approval_fee' or 'legal',
     * OR estimated as 7% of land cost (standard UP stamp duty + registration).
     */
    private function getRegistryCost(int $colonyId): float
    {
        // Try explicit registry/legal costs from development_costs
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM colony_development_costs
             WHERE colony_id = :cid
               AND cost_type IN ('approval_fee', 'legal')",
            ['cid' => $colonyId]
        );

        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Estimate: 7% of land cost (UP stamp duty 5% + registration 2%)
        $landCost = $this->getLandCost($colonyId);
        return round($landCost * 0.07, 2);
    }

    /**
     * Get development costs grouped by category for cost breakdown.
     *
     * Categories:
     *   - infrastructure: road, electricity, water, sewerage, street_light, drainage
     *   - compound: compound_wall, gate, security
     *   - landscape: landscaping
     *   - approvals: approval_fee, legal
     *   - commercial: brokerage, marketing, office_setup, staff
     *   - other: other
     */
    private function getDevCostsByCategory(int $colonyId): array
    {
        $rows = $this->tryFetchAll(
            "SELECT cost_type, amount
             FROM colony_development_costs
             WHERE colony_id = :cid
             ORDER BY cost_type",
            ['cid' => $colonyId]
        ) ?: [];

        $byType = [];
        foreach ($rows as $r) {
            $type = $r['cost_type'] ?? 'unknown';
            $byType[$type] = ($byType[$type] ?? 0) + (float) $r['amount'];
        }

        // Round
        return array_map(fn($v) => round($v, 2), $byType);
    }

    /**
     * Get approval/NOC costs for a colony.
     *
     * This is the SAME as registry in our DB — stored as 'approval_fee'.
     * Separated here so that if we get real approval data later, we can
     * distinguish registry (land transfer) from approvals (NOCs, environmental).
     */
    private function getApprovalCost(int $colonyId): float
    {
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM colony_development_costs
             WHERE colony_id = :cid AND cost_type = 'approval_fee'",
            ['cid' => $colonyId]
        );
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Get G&A overhead costs (office, staff, marketing, brokerage).
     */
    private function getGACost(int $colonyId): float
    {
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM colony_development_costs
             WHERE colony_id = :cid
               AND cost_type IN ('brokerage', 'marketing', 'office_setup', 'staff', 'other')",
            ['cid' => $colonyId]
        );
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Get total raw land area for the colony (from land_acquisitions or colonies).
     */
    private function getRawLandArea(int $colonyId): float
    {
        // Try land_acquisitions first
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(land_area), 0) AS total
             FROM land_acquisitions WHERE colony_id = :cid AND status != 'cancelled'",
            ['cid' => $colonyId]
        );
        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Fallback: sum of all plot areas / yield (estimate raw area)
        $plotArea = $this->getTotalPlotArea($colonyId);
        if ($plotArea > 0) {
            return round($plotArea / (self::DEFAULT_YIELD_PCT / 100.0), 2);
        }

        return 0.0;
    }

    /**
     * Get total plot area (saleable) for a colony.
     */
    private function getTotalPlotArea(int $colonyId): float
    {
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(area_sqft), 0) AS total FROM plots WHERE colony_id = :cid",
            ['cid' => $colonyId]
        );
        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Get the latest feasibility record for a colony.
     */
    private function getLatestFeasibility(int $colonyId): ?array
    {
        return $this->tryFetch(
            "SELECT * FROM colony_pricing_feasibility
             WHERE colony_id = :cid
             ORDER BY created_at DESC LIMIT 1",
            ['cid' => $colonyId]
        );
    }

    // ================================================================
    //  PRIVATE — AUDIT LOG
    // ================================================================

    /**
     * Write a feasibility calculation to the audit table.
     */
    private function logFeasibility(array $data, array $overrides): void
    {
        try {
            $createdBy = (int) ($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);

            $stmt = $this->pdo->prepare("
                INSERT INTO colony_pricing_feasibility
                (colony_id, total_raw_area_sqft, saleable_area_yield_pct, saleable_area_sqft,
                 land_cost_total, registry_cost_total, development_cost_total, approvals_cost_total,
                 raw_cost_basis_ppsf, target_profit_pct, office_overhead_pct, mlm_budget_pct,
                 markup_factor, recommended_price_ppsf, applied_price_ppsf, notes, created_by)
                VALUES
                (:cid, :raw_area, :yield_pct, :saleable,
                 :land, :registry, :dev, :approval,
                 :cost_ppsf, :profit_pct, :ga_pct, :mlm_pct,
                 :markup, :rec_ppsf, :applied_ppsf, :notes, :created_by)
            ");

            $stmt->execute([
                ':cid'         => $data['colony_id'],
                ':raw_area'    => $data['total_raw_area_sqft'],
                ':yield_pct'   => $data['yield_pct'],
                ':saleable'    => $data['saleable_area_sqft'],
                ':land'        => $data['land_cost'],
                ':registry'    => $data['registry_cost'],
                ':dev'         => $data['development_cost'],
                ':approval'    => $data['approval_cost'],
                ':cost_ppsf'   => $data['raw_cost_per_sqft'],
                ':profit_pct'  => $data['profit_margin_pct'],
                ':ga_pct'      => $data['office_overhead_pct'],
                ':mlm_pct'     => $data['mlm_budget_pct'],
                ':markup'      => $data['markup_factor'],
                ':rec_ppsf'    => $data['recommended_price_ppsf'],
                ':applied_ppsf' => $data['recommended_price_ppsf'],
                ':notes'       => $overrides['_notes'] ?? null,
                ':created_by'  => $createdBy,
            ]);
        } catch (Exception $e) {
            $this->logError('logFeasibility failed', ['error' => $e->getMessage()]);
        }
    }

    // ================================================================
    //  PRIVATE — SAFE DB HELPERS
    // ================================================================

    private function tryFetch(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            $this->logError('tryFetch failed', ['error' => $e->getMessage(), 'sql' => mb_substr($sql, 0, 120)]);
            return null;
        }
    }

    private function tryFetchAll(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            $this->logError('tryFetchAll failed', ['error' => $e->getMessage(), 'sql' => mb_substr($sql, 0, 120)]);
            return null;
        }
    }

    private function logError(string $msg, array $ctx = []): void
    {
        try {
            if ($this->logger) {
                $this->logger->error($msg, $ctx);
            }
        } catch (Exception $ignored) {}
    }
}
