<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\SystemLogger;
use Exception;

/**
 * ColonyPricingService — Calculates and applies plot pricing for a colony.
 *
 * Pricing is derived from:
 *   1. Land acquisition cost  (land_acquisitions / acquisitions table)
 *   2. Development costs      (colony_development_costs table)
 *   3. Saleable area          (SUM(area_sqft) FROM plots)
 *
 * Premiums are applied on top of the base price for:
 *   - Corner plots (+10 %)
 *   - Park-facing plots (+15 %)
 *   - Wide road frontage (>= 40 ft, +8 %)
 *
 * All public methods are null-safe (return arrays, never throw).
 */
class ColonyPricingService
{
    /** @var Database */
    private $db;

    /** @var SystemLogger */
    private $logger;

    /** @var \PDO|null */
    private $pdo;

    // ── Default premium percentages ─────────────────────────────
    private const PREMIUM_CORNER      = 0.10;
    private const PREMIUM_PARK_FACING = 0.15;
    private const PREMIUM_WIDE_ROAD   = 0.08;
    private const WIDE_ROAD_THRESHOLD = 40.0;

    public function __construct()
    {
        try {
            $this->db     = Database::getInstance();
            $this->pdo    = $this->db->getPdo();
            $this->logger = new SystemLogger();
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('ColonyPricingService init failed', ['error' => $e->getMessage()]);
            }
            $this->db     = null;
            $this->pdo    = null;
            $this->logger = null;
        }
    }

    // ================================================================
    //  PUBLIC API
    // ================================================================

    /**
     * Calculate the colony-wide pricing breakdown.
     *
     * Looks up land acquisition cost, all development cost line-items,
     * and the total saleable plot area, then derives base_price_per_sqft.
     *
     * @param int $colonyId
     * @return array{
     *     success: bool,
     *     land_cost: float,
     *     development_cost_by_type: array,
     *     total_development: float,
     *     total_cost: float,
     *     saleable_area: float,
     *     base_price_per_sqft: float,
     *     error?: string
     * }
     */
    public function calculateColonyPricing(int $colonyId): array
    {
        try {
            // ── 1. Land acquisition cost ──────────────────────
            $landCost = $this->getLandCost($colonyId);

            // ── 2. Development costs by type ──────────────────
            $devCosts = $this->getDevelopmentCosts($colonyId);
            $totalDev = 0.0;
            $devByType = [];
            foreach ($devCosts as $row) {
                $typeName = $row['cost_type'] ?? 'unknown';
                $amount   = (float) ($row['amount'] ?? 0);
                $devByType[$typeName] = ($devByType[$typeName] ?? 0) + $amount;
                $totalDev += $amount;
            }

            // ── 3. Total saleable area ────────────────────────
            $saleableArea = $this->getTotalSaleableArea($colonyId);

            if ($saleableArea <= 0) {
                return [
                    'success'                => false,
                    'error'                  => 'No plots found for this colony or total saleable area is zero.',
                    'land_cost'              => $landCost,
                    'development_cost_by_type' => $devByType,
                    'total_development'      => $totalDev,
                    'total_cost'             => $landCost + $totalDev,
                    'saleable_area'          => 0,
                    'base_price_per_sqft'    => 0,
                ];
            }

            $totalCost = $landCost + $totalDev;
            $rawCostPerSqft = $totalCost / $saleableArea;

            // Markup formula: Selling Price = Cost / (1 - Overhead%)
            // Overhead breakdown: MLM commissions 25% + G&A 5% + Profit 20% = 50%
            $mlmOverheadPct    = 0.25;  // worst-case 26.4%, capped at 25% for pricing
            $gaOverheadPct     = 0.05;
            $profitMarginPct   = 0.20;
            $totalOverheadPct  = $mlmOverheadPct + $gaOverheadPct + $profitMarginPct;
            $markupFactor      = 1.0 / (1.0 - $totalOverheadPct);
            $basePrice         = round($rawCostPerSqft * $markupFactor, 2);

            $this->logger->info('Colony pricing calculated', [
                'colony_id'         => $colonyId,
                'land_cost'         => $landCost,
                'total_development' => $totalDev,
                'saleable_area'     => $saleableArea,
                'raw_cost_per_sqft' => round($rawCostPerSqft, 2),
                'markup_factor'     => round($markupFactor, 4),
                'base_price_per_sqft' => $basePrice,
            ]);

            return [
                'success'                => true,
                'land_cost'              => round($landCost, 2),
                'development_cost_by_type' => $devByType,
                'total_development'      => round($totalDev, 2),
                'total_cost'             => round($totalCost, 2),
                'saleable_area'          => round($saleableArea, 2),
                'raw_cost_per_sqft'      => round($rawCostPerSqft, 2),
                'markup_factor'          => round($markupFactor, 4),
                'total_overhead_pct'     => round($totalOverheadPct * 100, 1),
                'mlm_overhead_pct'       => $mlmOverheadPct * 100,
                'ga_overhead_pct'        => $gaOverheadPct * 100,
                'profit_margin_pct'      => $profitMarginPct * 100,
                'base_price_per_sqft'    => $basePrice,
            ];
        } catch (Exception $e) {
            $this->logger->error('calculateColonyPricing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Apply a base price (with optional premium overrides) to every plot
     * in the colony and update the colony's starting_price.
     *
     * @param int   $colonyId
     * @param float $basePricePerSqft   The calculated base price
     * @param array $premiums           Optional overrides, e.g. ['corner_plot' => 0.12]
     * @return array{
     *     success: bool,
     *     plots_updated: int,
     *     min_price: float,
     *     max_price: float,
     *     avg_price: float,
     *     error?: string
     * }
     */
    public function applyPricingToColony(int $colonyId, float $basePricePerSqft, array $premiums = []): array
    {
        try {
            if ($basePricePerSqft <= 0) {
                return ['success' => false, 'error' => 'Base price must be greater than zero'];
            }

            // Merge caller premiums with defaults
            $cornerPct      = $premiums['corner_plot']      ?? self::PREMIUM_CORNER;
            $parkPct        = $premiums['park_facing']      ?? self::PREMIUM_PARK_FACING;
            $wideRoadPct    = $premiums['road_width_ft']    ?? self::PREMIUM_WIDE_ROAD;
            $wideRoadThresh = $premiums['wide_road_threshold'] ?? self::WIDE_ROAD_THRESHOLD;

            // Fetch all plots for this colony
            $plots = $this->db->fetchAll(
                "SELECT id, area_sqft, corner_plot, park_facing, road_width_ft
                 FROM plots WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );

            if (empty($plots)) {
                return ['success' => false, 'error' => 'No plots found for this colony'];
            }

            $this->db->beginTransaction();

            $updateSql = "UPDATE plots SET
                base_price_per_sqft = :base,
                price_per_sqft      = :price,
                total_price         = :total,
                updated_at          = NOW()
                WHERE id = :id";

            $stmt = $this->pdo->prepare($updateSql);

            $minPrice = PHP_FLOAT_MAX;
            $maxPrice = 0.0;
            $totalValue = 0.0;

            foreach ($plots as $plot) {
                $premiumMultiplier = 1.0;

                if (!empty($plot['corner_plot'])) {
                    $premiumMultiplier += $cornerPct;
                }
                if (!empty($plot['park_facing'])) {
                    $premiumMultiplier += $parkPct;
                }
                if ((float) ($plot['road_width_ft'] ?? 0) >= $wideRoadThresh) {
                    $premiumMultiplier += $wideRoadPct;
                }

                $pricePerSqft = round($basePricePerSqft * $premiumMultiplier, 2);
                $totalPrice   = round($pricePerSqft * (float) $plot['area_sqft'], 2);

                // Get old prices before update
                $oldPlot = $this->db->fetch(
                    "SELECT price_per_sqft, total_price FROM plots WHERE id = ?",
                    [$plot['id']]
                );
                $oldPricePerSqft = (float) ($oldPlot['price_per_sqft'] ?? 0);
                $oldTotalPrice   = (float) ($oldPlot['total_price'] ?? 0);

                $stmt->execute([
                    ':base'  => $basePricePerSqft,
                    ':price' => $pricePerSqft,
                    ':total' => $totalPrice,
                    ':id'    => $plot['id'],
                ]);

                if ($totalPrice < $minPrice) {
                    $minPrice = $totalPrice;
                }
                if ($totalPrice > $maxPrice) {
                    $maxPrice = $totalPrice;
                }
                $totalValue += $totalPrice;

                // Log individual plot price change to price_history
                $this->insertPriceHistory(
                    (int) $plot['id'],
                    $colonyId,
                    $oldPricePerSqft,
                    $pricePerSqft,
                    $oldTotalPrice,
                    $totalPrice,
                    'bulk_update',
                    "Colony pricing applied: base ₹{$basePricePerSqft}/sqft, premium multiplier {$premiumMultiplier}",
                    (int) ($_SESSION['user_id'] ?? 0)
                );
            }

            $avgPrice = count($plots) > 0 ? round($totalValue / count($plots), 2) : 0;

            // Update colony starting_price with the minimum plot price
            $this->db->execute(
                "UPDATE colonies SET starting_price = :sp WHERE id = :cid",
                ['sp' => $minPrice, 'cid' => $colonyId]
            );

            $this->db->commit();

            $this->logger->info('Pricing applied to colony', [
                'colony_id'     => $colonyId,
                'plots_updated' => count($plots),
                'min_price'     => $minPrice,
                'max_price'     => $maxPrice,
                'avg_price'     => $avgPrice,
            ]);

            return [
                'success'       => true,
                'plots_updated' => count($plots),
                'min_price'     => $minPrice,
                'max_price'     => $maxPrice,
                'avg_price'     => $avgPrice,
                'total_value'   => round($totalValue, 2),
            ];
        } catch (Exception $e) {
            $this->safeRollback();
            $this->logger->error('applyPricingToColony failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Comprehensive financial summary for a colony.
     *
     * @param int $colonyId
     * @return array{
     *     success: bool,
     *     colony_name: string,
     *     land_cost: float,
     *     development_costs: array,
     *     total_development: float,
     *     total_investment: float,
     *     total_saleable_area: float,
     *     price_per_sqft_min: float,
     *     price_per_sqft_max: float,
     *     price_per_sqft_avg: float,
     *     total_plot_value: float,
     *     potential_revenue: float,
     *     roi_pct: float,
     *     plots_total: int,
     *     plots_priced: int,
     *     error?: string
     * }
     */
    public function getColonyFinancialSummary(int $colonyId): array
    {
        try {
            // ── Colony info ───────────────────────────────────
            $colony = $this->db->fetch(
                "SELECT id, colony_name, total_area_acres, total_plots, available_plots, starting_price
                 FROM colonies WHERE id = :cid",
                ['cid' => $colonyId]
            );

            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            // ── Land cost ─────────────────────────────────────
            $landCost = $this->getLandCost($colonyId);

            // ── Development costs ─────────────────────────────
            $devCosts = $this->getDevelopmentCosts($colonyId);
            $devByType = [];
            $totalDev  = 0.0;
            foreach ($devCosts as $row) {
                $typeName = $row['cost_type'] ?? 'unknown';
                $amount   = (float) ($row['amount'] ?? 0);
                $devByType[$typeName] = ($devByType[$typeName] ?? 0) + $amount;
                $totalDev += $amount;
            }

            // ── Plot stats ────────────────────────────────────
            $plotStats = $this->db->fetch(
                "SELECT
                    COUNT(*)                          AS plots_total,
                    COUNT(CASE WHEN price_per_sqft > 0 THEN 1 END) AS plots_priced,
                    COALESCE(SUM(area_sqft), 0)       AS total_saleable_area,
                    COALESCE(MIN(price_per_sqft), 0)  AS price_per_sqft_min,
                    COALESCE(MAX(price_per_sqft), 0)  AS price_per_sqft_max,
                    COALESCE(AVG(price_per_sqft), 0)  AS price_per_sqft_avg,
                    COALESCE(SUM(total_price), 0)     AS total_plot_value,
                    COALESCE(MIN(total_price), 0)     AS min_plot_price,
                    COALESCE(MAX(total_price), 0)     AS max_plot_price
                 FROM plots WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );

            $totalInvestment = $landCost + $totalDev;
            $totalPlotValue  = (float) ($plotStats['total_plot_value'] ?? 0);
            $roiPct          = $totalInvestment > 0
                ? round((($totalPlotValue - $totalInvestment) / $totalInvestment) * 100, 2)
                : 0;

            $this->logger->info('Financial summary generated', [
                'colony_id'     => $colonyId,
                'total_investment' => $totalInvestment,
                'total_plot_value' => $totalPlotValue,
                'roi_pct'       => $roiPct,
            ]);

            return [
                'success'               => true,
                'colony_name'           => $colony['colony_name'] ?? '',
                'total_area_acres'      => (float) ($colony['total_area_acres'] ?? 0),
                'land_cost'             => round($landCost, 2),
                'development_costs'     => $devByType,
                'total_development'     => round($totalDev, 2),
                'total_investment'      => round($totalInvestment, 2),
                'total_saleable_area'   => round((float) ($plotStats['total_saleable_area'] ?? 0), 2),
                'price_per_sqft_min'    => round((float) ($plotStats['price_per_sqft_min'] ?? 0), 2),
                'price_per_sqft_max'    => round((float) ($plotStats['price_per_sqft_max'] ?? 0), 2),
                'price_per_sqft_avg'    => round((float) ($plotStats['price_per_sqft_avg'] ?? 0), 2),
                'min_plot_price'        => round((float) ($plotStats['min_plot_price'] ?? 0), 2),
                'max_plot_price'        => round((float) ($plotStats['max_plot_price'] ?? 0), 2),
                'total_plot_value'      => round($totalPlotValue, 2),
                'potential_revenue'     => round($totalPlotValue, 2),
                'roi_pct'               => $roiPct,
                'plots_total'           => (int) ($plotStats['plots_total'] ?? 0),
                'plots_priced'          => (int) ($plotStats['plots_priced'] ?? 0),
                'plots_available'       => (int) ($colony['available_plots'] ?? 0),
            ];
        } catch (Exception $e) {
            $this->logger->error('getColonyFinancialSummary failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Bulk-apply pricing to multiple colonies at once.
     *
     * @param array $colonyIds    List of colony IDs
     * @param array $overrides    Per-colony overrides: [colony_id => ['base_price' => X, 'premiums' => [...]]]
     * @return array  Per-colony results keyed by colony ID
     */
    public function bulkApplyPricing(array $colonyIds, array $overrides = []): array
    {
        $results = [];
        foreach ($colonyIds as $cid) {
            $cid = (int) $cid;
            $pricing = $this->calculateColonyPricing($cid);
            if (!$pricing['success']) {
                $results[$cid] = $pricing;
                continue;
            }

            $basePrice = $overrides[$cid]['base_price'] ?? $pricing['base_price_per_sqft'];
            $premiums  = $overrides[$cid]['premiums'] ?? [];

            $results[$cid] = $this->applyPricingToColony($cid, $basePrice, $premiums);
        }
        return $results;
    }

    /**
     * Get price history for a specific plot.
     *
     * @param int $plotId
     * @return array
     */
    public function getPlotPriceHistory(int $plotId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM price_history
                 WHERE entity_type = 'plot' AND entity_id = :pid
                 ORDER BY created_at DESC",
                ['pid' => $plotId]
            );
        } catch (Exception $e) {
            $this->logger->error('getPlotPriceHistory failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get price comparison across all colonies.
     *
     * @return array
     */
    public function getAllColoniesPricingComparison(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT
                    c.id,
                    c.colony_name,
                    c.total_plots,
                    c.available_plots,
                    c.starting_price,
                    COALESCE(SUM(p.area_sqft), 0)     AS total_saleable,
                    COALESCE(AVG(p.price_per_sqft), 0) AS avg_price_per_sqft,
                    COALESCE(SUM(p.total_price), 0)    AS total_project_value,
                    COUNT(p.id)                        AS plots_with_price
                 FROM colonies c
                 LEFT JOIN plots p ON p.colony_id = c.id AND p.price_per_sqft > 0
                 GROUP BY c.id, c.colony_name, c.total_plots, c.available_plots, c.starting_price
                 ORDER BY c.colony_name"
            );
        } catch (Exception $e) {
            $this->logger->error('getAllColoniesPricingComparison failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ================================================================
    //  PRIVATE — DATA LOOKUP
    // ================================================================

    /**
     * Retrieve the total land acquisition cost for a colony.
     *
     * Tries `land_acquisitions` first, then falls back to `acquisitions`.
     *
     * @param int $colonyId
     * @return float
     */
    private function getLandCost(int $colonyId): float
    {
        // Try land_acquisitions table first
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(acquisition_cost), 0) AS total
             FROM land_acquisitions WHERE colony_id = :cid AND status = 'registered'",
            ['cid' => $colonyId]
        );

        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Fallback: try the acquisitions table
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(acquisition_cost), 0) AS total
             FROM acquisitions WHERE colony_id = :cid",
            ['cid' => $colonyId]
        );

        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Second fallback: check if colony has a land_cost column directly
        $row = $this->tryFetch(
            "SELECT COALESCE(land_cost, 0) AS total FROM colonies WHERE id = :cid",
            ['cid' => $colonyId]
        );

        return $row ? (float) $row['total'] : 0.0;
    }

    /**
     * Retrieve all development cost line-items for a colony.
     *
     * @param int $colonyId
     * @return array
     */
    private function getDevelopmentCosts(int $colonyId): array
    {
        return $this->tryFetchAll(
            "SELECT cost_type, amount, description
             FROM colony_development_costs
             WHERE colony_id = :cid
             ORDER BY cost_type",
            ['cid' => $colonyId]
        ) ?? [];
    }

    /**
     * Retrieve total saleable area (sum of all plot areas) for a colony.
     *
     * @param int $colonyId
     * @return float
     */
    private function getTotalSaleableArea(int $colonyId): float
    {
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(area_sqft), 0) AS total
             FROM plots WHERE colony_id = :cid",
            ['cid' => $colonyId]
        );

        return $row ? (float) $row['total'] : 0.0;
    }

    // ================================================================
    //  PRIVATE — PRICE HISTORY
    // ================================================================

/**
     * Insert price history record for a plot
     *
     * @param int $plotId
     * @param int $colonyId
     * @param float $oldPricePerSqft
     * @param float $newPricePerSqft
     * @param float $oldTotalPrice
     * @param float $newTotalPrice
     * @param string $changeType
     * @param string $reason
     * @param int $changedBy
     */
    private function insertPriceHistory(
        int $plotId,
        int $colonyId,
        float $oldPricePerSqft,
        float $newPricePerSqft,
        float $oldTotalPrice,
        float $newTotalPrice,
        string $changeType = 'bulk_update',
        string $reason = '',
        int $changedBy = 0
    ): void {
        try {
            $this->db->insert('price_history', [
                'plot_id'           => $plotId,
                'colony_id'         => $colonyId,
                'old_price'         => $oldTotalPrice,
                'new_price'         => $newTotalPrice,
                'old_price_per_sqft'=> $oldPricePerSqft,
                'new_price_per_sqft'=> $newPricePerSqft,
                'change_type'       => $changeType,
                'reason'            => $reason,
                'changed_by'        => $changedBy,
                'reference_type'    => 'colony_pricing',
                'reference_id'      => $colonyId,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            // Price history failure is non-critical — log and continue
            $this->logger->warning('price_history insert failed', [
                'plot_id' => $plotId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ================================================================
    //  PRIVATE — SAFE DB HELPERS
    // ================================================================

    /**
     * Attempt a single-row fetch; return null on failure.
     */
    private function tryFetch(string $sql, array $params = []): ?array
    {
        try {
            return $this->db->fetch($sql, $params);
        } catch (Exception $e) {
            $this->logger->warning('tryFetch failed', [
                'error'  => $e->getMessage(),
                'sql'    => mb_substr($sql, 0, 120),
            ]);
            return null;
        }
    }

    /**
     * Attempt a multi-row fetch; return null on failure.
     */
    private function tryFetchAll(string $sql, array $params = []): ?array
    {
        try {
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->warning('tryFetchAll failed', [
                'error'  => $e->getMessage(),
                'sql'    => mb_substr($sql, 0, 120),
            ]);
            return null;
        }
    }

    /**
     * Rollback only if a transaction is active.
     */
    private function safeRollback(): void
    {
        try {
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->db->rollBack();
            }
        } catch (Exception $ignored) {
            // Swallow
        }
    }

    /**
     * Log via SystemLogger with safe fallback.
     */
    private function log(string $level, string $message, array $context = []): void
    {
        try {
            if ($this->logger) {
                $this->logger->log($level, $message, $context);
            }
        } catch (Exception $ignored) {
            // Never let logging break the caller
        }
    }
}
