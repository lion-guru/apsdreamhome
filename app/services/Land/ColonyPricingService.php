<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\SystemLogger;
use Exception;
use \App\Traits\ServiceTenantTrait;

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
    use \App\Traits\ServiceTenantTrait;

    /** @var Database */
    private $db;

    /** @var SystemLogger */
    private $logger;

    /** @var \PDO|null */
    private $pdo;

    // ── Default premium percentages (loaded from config or fallback) ─────────────────────────────
    private float $cornerPremium      = 0.10;
    private float $parkFacingPremium  = 0.15;
    private float $wideRoadPremium    = 0.08;
    private float $wideRoadThreshold  = 40.0;

    public function __construct()
    {
        try {
            $this->db     = Database::getInstance();
            $this->pdo    = $this->db->getPdo();
            $this->logger = new SystemLogger();
            
            // Load premiums from config if available
            $this->loadPremiumsFromConfig();
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('ColonyPricingService init failed', ['error' => $e->getMessage()]);
            }
            $this->db     = null;
            $this->pdo    = null;
            $this->logger = null;
        }
    }

    private function loadPremiumsFromConfig(): void
    {
        try {
            $cfg = $this->db->fetchAll("SELECT config_key, config_value FROM service_configs WHERE config_group = 'pricing_premiums' AND is_active = 1");
            foreach ($cfg as $row) {
                $key = $row['config_key'];
                $val = (float)$row['config_value'];
                if ($key === 'corner_premium_pct') $this->cornerPremium = $val / 100;
                elseif ($key === 'park_facing_premium_pct') $this->parkFacingPremium = $val / 100;
                elseif ($key === 'wide_road_premium_pct') $this->wideRoadPremium = $val / 100;
                elseif ($key === 'wide_road_threshold_ft') $this->wideRoadThreshold = $val;
            }
        } catch (Exception $e) {
            // Use defaults if config not available
        }
    }

    public function getPremiumConfig(): array
    {
        return [
            'corner_premium_pct' => $this->cornerPremium * 100,
            'park_facing_premium_pct' => $this->parkFacingPremium * 100,
            'wide_road_premium_pct' => $this->wideRoadPremium * 100,
            'wide_road_threshold_ft' => $this->wideRoadThreshold,
        ];
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

            // Store min_price_per_sqft on colony (cost floor)
        $this->storeMinPrice($colonyId, $landCost, $totalDev, $saleableArea, $rawCostPerSqft);

        $this->logger->info('Colony pricing calculated', [
                'colony_id'         => $colonyId,
                'land_cost'         => $landCost,
                'total_development' => $totalDev,
                'saleable_area'     => $saleableArea,
                'raw_cost_per_sqft' => round($rawCostPerSqft, 2),
                'markup_factor'     => round($markupFactor, 4),
                'base_price_per_sqft' => $basePrice,
                'min_price_per_sqft' => round($rawCostPerSqft, 2),
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
                'min_price_per_sqft'     => round($rawCostPerSqft, 2),
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
     * Enforces minimum rate guard: if colony.min_price_per_sqft > 0,
     * base price cannot be below it (unless approval flow is used).
     *
     * @param int   $colonyId
     * @param float $basePricePerSqft   The calculated base price
     * @param array $premiums           Optional overrides, e.g. ['corner_plot' => 0.12, 'block' => ['A'=>5, 'B'=>3]]
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

            // ── Minimum rate guard ─────────────────────────────
            $tenantId = TenantContext::getId();
            $colony = $this->db->fetch(
                "SELECT id, name, min_price_per_sqft FROM colonies WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $minPpsf = (float) ($colony['min_price_per_sqft'] ?? 0);
            if ($minPpsf > 0 && $basePricePerSqft < $minPpsf) {
                return [
                    'success' => false,
                    'error'   => "Cannot price below minimum rate of ₹{$minPpsf}/sqft. Use discount approval for lower pricing.",
                ];
            }

            // Merge caller premiums with defaults
            $cornerPct      = $premiums['corner_plot']      ?? $this->cornerPremium;
            $parkPct        = $premiums['park_facing']      ?? $this->parkFacingPremium;
            $wideRoadPct    = $premiums['road_width_ft']    ?? $this->wideRoadPremium;
            $wideRoadThresh = $premiums['wide_road_threshold'] ?? $this->wideRoadThreshold;
            $blockPremiums  = $premiums['block']            ?? [];
            $phasePremiums  = $premiums['phase']            ?? [];

            // Fetch all plots for this colony
            $plots = $this->db->fetchAll(
                "SELECT id, area_sqft, corner_plot, park_facing, road_width_ft, block, phase
                 FROM plots WHERE colony_id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
                WHERE id = :id" . ($tenantId > 1 ? " AND tenant_id = :tid" : "");

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

                // Per-block premium
                $block = $plot['block'] ?? '';
                if (!empty($block) && isset($blockPremiums[$block])) {
                    $premiumMultiplier += (float) $blockPremiums[$block] / 100;
                }

                // Per-phase premium
                $phase = $plot['phase'] ?? '';
                if (!empty($phase) && isset($phasePremiums[$phase])) {
                    $premiumMultiplier += (float) $phasePremiums[$phase] / 100;
                }

                $pricePerSqft = round($basePricePerSqft * $premiumMultiplier, 2);
                $totalPrice   = round($pricePerSqft * (float) $plot['area_sqft'], 2);

                // Get old prices before update
                $oldPlot = $this->db->fetch(
                    "SELECT price_per_sqft, total_price FROM plots WHERE id = ?" . ($tenantId > 1 ? " AND tenant_id = ?" : ""),
                    $tenantId > 1 ? [$plot['id'], $tenantId] : [$plot['id']]
                );
                $oldPricePerSqft = (float) ($oldPlot['price_per_sqft'] ?? 0);
                $oldTotalPrice   = (float) ($oldPlot['total_price'] ?? 0);

                $stmt->execute(array_merge([
                    ':base'  => $basePricePerSqft,
                    ':price' => $pricePerSqft,
                    ':total' => $totalPrice,
                    ':id'    => $plot['id'],
                ], $tenantId > 1 ? [':tid' => $tenantId] : []));

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
                "UPDATE colonies SET starting_price = :sp WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['sp' => $minPrice, 'cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
            $tenantId = TenantContext::getId();
            $colony = $this->db->fetch(
                "SELECT id, name AS colony_name, total_area_acres, total_plots, available_plots, starting_price
                 FROM colonies WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
                 FROM plots WHERE colony_id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
            $tenantId = TenantContext::getId();
            return $this->db->fetchAll(
                "SELECT * FROM price_history
                 WHERE entity_type = 'plot' AND entity_id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : "") . "
                 ORDER BY created_at DESC",
                array_merge(['pid' => $plotId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
            $tenantId = TenantContext::getId();
            $whereClause = $tenantId > 1 ? " WHERE c.tenant_id = :tid" : "";
            return $this->db->fetchAll(
                "SELECT
                     c.id,
                     c.name AS colony_name,
                     c.total_plots,
                     c.available_plots,
                     c.starting_price,
                     COALESCE(SUM(p.area_sqft), 0)     AS total_saleable,
                     COALESCE(AVG(p.price_per_sqft), 0) AS avg_price_per_sqft,
                     COALESCE(SUM(p.total_price), 0)    AS total_project_value,
                     COUNT(p.id)                        AS plots_with_price
                  FROM colonies c
                  LEFT JOIN plots p ON p.colony_id = c.id AND p.price_per_sqft > 0" .
                  ($tenantId > 1 ? " AND p.tenant_id = :tid" : "") . "
                  {$whereClause}
                  GROUP BY c.id, c.name, c.total_plots, c.available_plots, c.starting_price
                  ORDER BY c.name",
                $tenantId > 1 ? ['tid' => $tenantId] : []
            );
        } catch (Exception $e) {
            $this->logger->error('getAllColoniesPricingComparison failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ================================================================
    //  PUBLIC — PRICE OVERRIDE & DISCOUNT APPROVAL
    // ================================================================

    /**
     * Update a single plot's price (individual override).
     *
     * @param int   $plotId
     * @param float $newPricePerSqft
     * @param string $reason
     * @return array
     */
    public function updatePlotPrice(int $plotId, float $newPricePerSqft, string $reason = ''): array
    {
        try {
            $tenantId = TenantContext::getId();
            $plot = $this->tryFetch(
                "SELECT id, colony_id, area_sqft, price_per_sqft, total_price
                 FROM plots WHERE id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['pid' => $plotId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            if (!$plot) {
                return ['success' => false, 'error' => 'Plot not found'];
            }

            $colonyId = (int) $plot['colony_id'];
            $areaSqft = (float) ($plot['area_sqft'] ?? 0);
            $oldPpsf  = (float) ($plot['price_per_sqft'] ?? 0);
            $oldTotal = (float) ($plot['total_price'] ?? 0);
            $newTotal = round($newPricePerSqft * $areaSqft, 2);

            // Minimum rate guard
            $colony = $this->tryFetch(
                "SELECT min_price_per_sqft FROM colonies WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            $minPpsf = (float) ($colony['min_price_per_sqft'] ?? 0);
            if ($minPpsf > 0 && $newPricePerSqft < $minPpsf) {
                return [
                    'success' => false,
                    'error'   => "Cannot set price ₹{$newPricePerSqft}/sqft below minimum ₹{$minPpsf}/sqft. Use discount approval.",
                ];
            }

            $this->db->execute(
                "UPDATE plots SET
                    price_per_sqft = :pps, total_price = :tot,
                    negotiated_price = :tot, price_override_reason = :reason,
                    price_overridden_by = :by, price_overridden_at = NOW(),
                    updated_at = NOW()
                 WHERE id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge([
                    'pps'    => $newPricePerSqft,
                    'tot'    => $newTotal,
                    'reason' => $reason,
                    'by'     => $_SESSION['user_id'] ?? 0,
                    'pid'    => $plotId,
                ], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );

            $this->insertPriceHistory(
                $plotId, $colonyId,
                $oldPpsf, $newPricePerSqft,
                $oldTotal, $newTotal,
                'override', $reason,
                (int) ($_SESSION['user_id'] ?? 0)
            );

            $this->logger->info('Plot price overridden', [
                'plot_id' => $plotId, 'old' => $oldPpsf, 'new' => $newPricePerSqft,
            ]);

            return [
                'success'          => true,
                'old_price_per_sqft' => $oldPpsf,
                'new_price_per_sqft' => $newPricePerSqft,
                'old_total'        => $oldTotal,
                'new_total'        => $newTotal,
            ];
        } catch (Exception $e) {
            $this->logger->error('updatePlotPrice failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Record a discount request (below minimum rate).
     *
     * @param int    $plotId
     * @param float  $requestedPricePerSqft
     * @param string $reason
     * @return array
     */
    public function requestDiscount(int $plotId, float $requestedPricePerSqft, string $reason = ''): array
    {
        try {
            $tenantId = TenantContext::getId();
            $plot = $this->tryFetch(
                "SELECT id, colony_id, area_sqft, price_per_sqft, total_price
                 FROM plots WHERE id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['pid' => $plotId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            if (!$plot) {
                return ['success' => false, 'error' => 'Plot not found'];
            }

            $colonyId = (int) $plot['colony_id'];
            $currentPpsf = (float) ($plot['price_per_sqft'] ?? 0);

            // Check if it's actually below min price
            $colony = $this->tryFetch(
                "SELECT min_price_per_sqft FROM colonies WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            $minPpsf = (float) ($colony['min_price_per_sqft'] ?? 0);
            if ($minPpsf <= 0 || $requestedPricePerSqft >= $minPpsf) {
                return [
                    'success' => false,
                    'error'   => 'Discount request not needed — price is above minimum. Use direct price update.',
                ];
            }

            $existingApproval = $this->tryFetch(
                "SELECT id, status FROM pricing_approvals
                 WHERE plot_id = :pid AND status = 'pending'" . ($tenantId > 1 ? " AND tenant_id = :tid" : "") . "
                 ORDER BY id DESC LIMIT 1",
                array_merge(['pid' => $plotId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            if ($existingApproval) {
                return [
                    'success' => false,
                    'error'   => 'A pending discount request already exists for this plot.',
                ];
            }

            $approvalData = [
                'plot_id'               => $plotId,
                'colony_id'             => $colonyId,
                'request_type'          => 'discount',
                'requested_price'       => $requestedPricePerSqft * (float) ($plot['area_sqft'] ?? 0),
                'requested_price_per_sqft' => $requestedPricePerSqft,
                'current_price'         => $currentPpsf * (float) ($plot['area_sqft'] ?? 0),
                'current_price_per_sqft'   => $currentPpsf,
                'reason'                => $reason,
                'requested_by'          => $_SESSION['user_id'] ?? 0,
                'requested_at'          => date('Y-m-d H:i:s'),
                'status'                => 'pending',
                'created_at'            => date('Y-m-d H:i:s'),
            ];
            if ($tenantId > 1) {
                $approvalData['tenant_id'] = $tenantId;
            }
            $this->db->insert('pricing_approvals', $approvalData);

            $this->logger->info('Discount request created', [
                'plot_id' => $plotId, 'requested' => $requestedPricePerSqft,
            ]);

            return ['success' => true, 'message' => 'Discount request submitted for approval.'];
        } catch (Exception $e) {
            $this->logger->error('requestDiscount failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Approve a pending discount/override request.
     *
     * @param int    $approvalId
     * @param string $notes
     * @return array
     */
    public function approveDiscount(int $approvalId, string $notes = ''): array
    {
        return $this->processApproval($approvalId, 'approved', $notes);
    }

    /**
     * Reject a pending discount/override request.
     *
     * @param int    $approvalId
     * @param string $notes
     * @return array
     */
    public function rejectDiscount(int $approvalId, string $notes = ''): array
    {
        return $this->processApproval($approvalId, 'rejected', $notes);
    }

    /**
     * Get pending pricing approvals, optionally filtered by colony.
     *
     * @param int|null $colonyId
     * @return array
     */
    public function getPendingApprovals(?int $colonyId = null): array
    {
        try {
            $tenantId = TenantContext::getId();
            $sql = "SELECT pa.*, p.plot_number, p.block, p.area_sqft,
                           c.name AS colony_name,
                           u1.name AS requested_by_name,
                           u2.name AS approved_by_name
                     FROM pricing_approvals pa
                     LEFT JOIN plots p ON pa.plot_id = p.id
                     LEFT JOIN colonies c ON pa.colony_id = c.id
                     LEFT JOIN users u1 ON pa.requested_by = u1.id
                     LEFT JOIN users u2 ON pa.approved_by = u2.id
                     WHERE pa.status = 'pending'";
            $params = [];
            if ($tenantId > 1) {
                $sql .= " AND pa.tenant_id = :tid";
                $params['tid'] = $tenantId;
            }
            if ($colonyId) {
                $sql .= " AND pa.colony_id = :cid";
                $params['cid'] = $colonyId;
            }
            $sql .= " ORDER BY pa.requested_at DESC LIMIT 100";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->error('getPendingApprovals failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get all approvals (with filter by status), optionally by colony.
     *
     * @param string|null $status
     * @param int|null $colonyId
     * @return array
     */
    public function getAllApprovals(?string $status = null, ?int $colonyId = null): array
    {
        try {
            $tenantId = TenantContext::getId();
            $sql = "SELECT pa.*, p.plot_number, p.block, p.area_sqft,
                           c.name AS colony_name,
                           u1.name AS requested_by_name,
                           u2.name AS approved_by_name
                     FROM pricing_approvals pa
                     LEFT JOIN plots p ON pa.plot_id = p.id
                     LEFT JOIN colonies c ON pa.colony_id = c.id
                     LEFT JOIN users u1 ON pa.requested_by = u1.id
                     LEFT JOIN users u2 ON pa.approved_by = u2.id
                     WHERE 1=1";
            $params = [];
            if ($tenantId > 1) {
                $sql .= " AND pa.tenant_id = :tid";
                $params['tid'] = $tenantId;
            }
            if ($status) {
                $sql .= " AND pa.status = :status";
                $params['status'] = $status;
            }
            if ($colonyId) {
                $sql .= " AND pa.colony_id = :cid";
                $params['cid'] = $colonyId;
            }
            $sql .= " ORDER BY pa.requested_at DESC LIMIT 200";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->error('getAllApprovals failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ================================================================
    //  PRIVATE — APPROVAL PROCESSING
    // ================================================================

    /**
     * Process approval (approve or reject).
     */
    private function processApproval(int $approvalId, string $action, string $notes = ''): array
    {
        try {
            $tenantId = TenantContext::getId();
            $approval = $this->tryFetch(
                "SELECT * FROM pricing_approvals WHERE id = :aid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge(['aid' => $approvalId], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
            if (!$approval) {
                return ['success' => false, 'error' => 'Approval request not found'];
            }
            if ($approval['status'] !== 'pending') {
                return [
                    'success' => false,
                    'error'   => "Request already {$approval['status']}.",
                ];
            }

            $now = date('Y-m-d H:i:s');
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            $this->db->execute(
                "UPDATE pricing_approvals SET
                    status = :status, approved_by = :by,
                    approved_at = :at, admin_notes = :notes, updated_at = NOW()
                 WHERE id = :aid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge([
                    'status' => $action,
                    'by'     => $userId,
                    'at'     => $now,
                    'notes'  => $notes,
                    'aid'    => $approvalId,
                ], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );

            // If approved, update the plot price
            if ($action === 'approved') {
                $plotId = (int) $approval['plot_id'];
                $newPpsf = (float) ($approval['requested_price_per_sqft'] ?? 0);
                $areaSqft = (float) ($this->tryFetch(
                    "SELECT area_sqft FROM plots WHERE id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                    array_merge(['pid' => $plotId], $tenantId > 1 ? ['tid' => $tenantId] : [])
                )['area_sqft'] ?? 0);
                $newTotal = round($newPpsf * $areaSqft, 2);

                $this->db->execute(
                    "UPDATE plots SET
                        negotiated_price = :tot, negotiated_price_approved = 1,
                        negotiated_price_approved_by = :by,
                        negotiated_price_approved_at = :at,
                        price_override_reason = CONCAT(COALESCE(price_override_reason,''), ' | Discount approved #', :aid),
                        updated_at = NOW()
                     WHERE id = :pid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                    array_merge([
                        'tot'  => $newTotal,
                        'by'   => $userId,
                        'at'   => $now,
                        'aid'  => $approvalId,
                        'pid'  => $plotId,
                    ], $tenantId > 1 ? ['tid' => $tenantId] : [])
                );
            }

            $this->logger->info("Discount request {$action}", [
                'approval_id' => $approvalId, 'plot_id' => $approval['plot_id'],
            ]);

            return [
                'success' => true,
                'message' => "Discount request #{$approvalId} {$action}.",
                'action'  => $action,
            ];
        } catch (Exception $e) {
            $this->logger->error("processApproval failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PRIVATE — DATA LOOKUP
    // ================================================================

    /**
     * Store the minimum price (breakeven cost) on the colonies table.
     * Called automatically during calculateColonyPricing().
     */
    private function storeMinPrice(int $colonyId, float $landCost, float $totalDev, float $saleableArea, float $rawCostPerSqft): void
    {
        try {
            $tenantId = TenantContext::getId();
            $this->db->execute(
                "UPDATE colonies SET land_cost = :lc, min_price_per_sqft = :mp WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
                array_merge([
                    'lc'  => round($landCost + $totalDev, 2),
                    'mp'  => round($rawCostPerSqft, 2),
                    'cid' => $colonyId,
                ], $tenantId > 1 ? ['tid' => $tenantId] : [])
            );
        } catch (Exception $e) {
            $this->logger->warning('storeMinPrice failed', ['error' => $e->getMessage()]);
        }
    }

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
        $tenantId = TenantContext::getId();
        // Try land_acquisitions table first
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(acquisition_cost), 0) AS total
             FROM land_acquisitions WHERE colony_id = :cid AND status = 'registered'" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
            array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
        );

        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Fallback: try the acquisitions table
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(acquisition_cost), 0) AS total
             FROM acquisitions WHERE colony_id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
            array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
        );

        if ($row && (float) $row['total'] > 0) {
            return (float) $row['total'];
        }

        // Second fallback: check if colony has a land_cost column directly
        $row = $this->tryFetch(
            "SELECT COALESCE(land_cost, 0) AS total FROM colonies WHERE id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
            array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
        $tenantId = TenantContext::getId();
        return $this->tryFetchAll(
            "SELECT cost_type, amount, description
             FROM colony_development_costs
             WHERE colony_id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : "") . "
             ORDER BY cost_type",
            array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
        $tenantId = TenantContext::getId();
        $row = $this->tryFetch(
            "SELECT COALESCE(SUM(area_sqft), 0) AS total
             FROM plots WHERE colony_id = :cid" . ($tenantId > 1 ? " AND tenant_id = :tid" : ""),
            array_merge(['cid' => $colonyId], $tenantId > 1 ? ['tid' => $tenantId] : [])
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
            $tenantId = TenantContext::getId();
            $historyData = [
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
            ];
            if ($tenantId > 1) {
                $historyData['tenant_id'] = $tenantId;
            }
            $this->db->insert('price_history', $historyData);
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
        error_log($ignored->getMessage());
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
            error_log($ignored->getMessage());
        }
    }

    // ================================================================
    //  PRICING VERSIONING
    // ================================================================

    /**
     * Save a new pricing plan version for a colony.
     *
     * @param int $colonyId
     * @param float $basePricePerSqft
     * @param array $premiums
     * @param array $config
     * @param string $name
     * @param string|null $description
     * @param int|null $parentVersionId
     * @param int $createdBy
     * @return array{success: bool, id?: int, version?: int, error?: string}
     */
    public function savePricingPlan(int $colonyId, float $basePricePerSqft, array $premiums, array $config, string $name, ?string $description = null, ?int $parentVersionId = null, int $createdBy = 0): array
    {
        try {
            $tid = $this->tenantId();
            
            // Get next version number
            $maxVersion = $this->db->fetchOne(
                "SELECT MAX(version) as max_v FROM pricing_plans WHERE colony_id = ? AND tenant_id = ?",
                [$colonyId, $tid]
            );
            $nextVersion = (int)($maxVersion['max_v'] ?? 0) + 1;

            $premiumsJson = json_encode($premiums);
            $configJson = json_encode($config);

            $this->db->beginTransaction();
            try {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO pricing_plans (colony_id, name, description, base_price_per_sqft, premiums, config, version, parent_version_id, is_active, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)"
                );
                $stmt->execute([$colonyId, $name, $description, $basePricePerSqft, $premiumsJson, $configJson, $nextVersion, $parentVersionId, $createdBy, $tid]);
                $planId = (int)$this->pdo->lastInsertId();
                $this->db->commit();
                
                $this->log('info', 'Pricing plan created', [
                    'plan_id' => $planId,
                    'colony_id' => $colonyId,
                    'version' => $nextVersion,
                    'created_by' => $createdBy
                ]);

                return ['success' => true, 'id' => $planId, 'version' => $nextVersion];
            } catch (Exception $e) {
                $this->safeRollback();
                throw $e;
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Activate a specific pricing plan version for a colony.
     * Deactivates all other versions for this colony.
     *
     * @param int $planId
     * @return array{success: bool, error?: string}
     */
    public function activatePricingPlan(int $planId): array
    {
        try {
            $tid = $this->tenantId();
            
            // Get the plan
            $plan = $this->db->fetchOne(
                "SELECT * FROM pricing_plans WHERE id = ? AND tenant_id = ?",
                [(int)$planId, $this->tenantId()]
            );
            if (!$plan) {
                return ['success' => false, 'error' => 'Pricing plan not found'];
            }

            $this->db->beginTransaction();
            try {
                // Deactivate all other versions for this colony
                $this->pdo->prepare("UPDATE pricing_plans SET is_active = 0 WHERE colony_id = ? AND tenant_id = ?")
                    ->execute([$plan['colony_id'], $this->tenantId()]);
                
                // Activate this version
                $this->pdo->prepare("UPDATE pricing_plans SET is_active = 1 WHERE id = ?")
                    ->execute([$planId]);
                
                $this->db->commit();
                
                $this->log('info', 'Pricing plan activated', [
                    'plan_id' => $planId,
                    'colony_id' => $plan['colony_id'],
                    'version' => $plan['version']
                ]);

                return ['success' => true];
            } catch (Exception $e) {
                $this->safeRollback();
                throw $e;
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all pricing plan versions for a colony.
     *
     * @param int $colonyId
     * @return array{success: bool, plans?: array, error?: string}
     */
    public function getPricingPlanHistory(int $colonyId): array
    {
        try {
            $tid = $this->tenantId();
            $plans = $this->db->fetchAll(
                "SELECT * FROM pricing_plans WHERE colony_id = ? AND tenant_id = ? ORDER BY version DESC",
                [$colonyId, $tid]
            );
            return ['success' => true, 'plans' => $plans];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Apply a specific pricing plan version to the colony.
     * Creates an application record and updates plot prices.
     *
     * @param int $planId
     * @param int $appliedBy
     * @return array{success: bool, error?: string, plots_updated?: int, total_value?: float}
     */
    public function applyPricingPlan(int $planId, int $appliedBy = 0): array
    {
        try {
            $tid = $this->tenantId();
            
            $plan = $this->db->fetchOne(
                "SELECT * FROM pricing_plans WHERE id = ? AND tenant_id = ?",
                [(int)$planId, $tid]
            );
            if (!$plan) {
                return ['success' => false, 'error' => 'Pricing plan not found'];
            }

            if (!$plan['is_active']) {
                return ['success' => false, 'error' => 'Pricing plan is not active'];
            }

            $premiums = json_decode($plan['premiums'], true);
            $config = json_decode($plan['config'], true);
            
            $this->db->beginTransaction();
            try {
                // Apply pricing to colony
                $result = $this->applyPricingToColony($plan['colony_id'], $plan['base_price_per_sqft'], $premiums, $config);
                
                if ($result['success']) {
                    // Record application
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO pricing_plan_applications (pricing_plan_id, colony_id, applied_by, plots_updated, total_value) VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $planId,
                        $plan['colony_id'],
                        $appliedBy,
                        $result['plots_updated'] ?? 0,
                        $result['total_value'] ?? 0
                    ]);
                    
                    $this->db->commit();
                    
                    $this->log('info', 'Pricing plan applied', [
                        'plan_id' => $planId,
                        'colony_id' => $plan['colony_id'],
                        'plots_updated' => $result['plots_updated'] ?? 0,
                        'total_value' => $result['total_value'] ?? 0,
                        'applied_by' => $appliedBy
                    ]);

                    return ['success' => true, 'plots_updated' => $result['plots_updated'] ?? 0, 'total_value' => $result['total_value'] ?? 0];
                } else {
                    $this->safeRollback();
                    return ['success' => false, 'error' => $result['error'] ?? 'Failed to apply pricing'];
                }
            } catch (Exception $e) {
                $this->safeRollback();
                throw $e;
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get application history for a colony.
     *
     * @param int $colonyId
     * @return array{success: bool, applications?: array, error?: string}
     */
    public function getApplicationHistory(int $colonyId): array
    {
        try {
            $tid = $this->tenantId();
            $applications = $this->db->fetchAll(
                "SELECT pa.*, pp.name as plan_name, pp.version as plan_version, pp.base_price_per_sqft
                 FROM pricing_plan_applications pa
                 JOIN pricing_plans pp ON pa.pricing_plan_id = pp.id
                 WHERE pa.colony_id = ? AND pa.tenant_id = ?
                 ORDER BY pa.applied_at DESC",
                [$colonyId, $tid]
            );
            return ['success' => true, 'applications' => $applications];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get a specific pricing plan by ID.
     *
     * @param int $planId
     * @return array{success: bool, plan?: array, error?: string}
     */
    public function getPricingPlan(int $planId): array
    {
        try {
            $tid = $this->tenantId();
            $plan = $this->db->fetchOne(
                "SELECT * FROM pricing_plans WHERE id = ? AND tenant_id = ?",
                [(int)$planId, $tid]
            );
            if (!$plan) {
                return ['success' => false, 'error' => 'Pricing plan not found'];
            }
            $plan['premiums'] = json_decode($plan['premiums'], true);
            $plan['config'] = json_decode($plan['config'], true);
            return ['success' => true, 'plan' => $plan];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
