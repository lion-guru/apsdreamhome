<?php

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

/**
 * ColonyLandCostingService — Full Land Acquisition → Plot Selling Price Engine
 * ─────────────────────────────────────────────────────────────────────────────
 * Handles:
 *   • Land cost input: purchase rate, total area, wastage percentages
 *   • Development cost inputs: roads, drainage, electricity, water, boundary
 *   • Overhead inputs: legal approvals, admin %, marketing/MLM commission %
 *   • Profit margin → Suggested & Final Selling Price
 *   • Generates line-item audit trail (colony_land_costing_items)
 *
 * Formula Engine:
 *   Step 1: Net Sellable SqFt = Total × (1 - Total Wastage%)
 *   Step 2: Raw Land Cost/Sellable SqFt = (Total SqFt × Purchase Rate + Registry Cost) / Net Sellable
 *   Step 3: Dev Cost/SqFt = Road + Drainage + Electricity + Water + Boundary + Other
 *   Step 4: Legal Cost/SqFt = Legal Flat Amount / Net Sellable SqFt
 *   Step 5: Pre-Overhead Cost = Raw Land + Dev + Legal
 *   Step 6: Admin Overhead = Pre-Overhead × Admin %
 *   Step 7: Landing Cost = Pre-Overhead + Admin Overhead
 *   Step 8: Suggested Price = Landing Cost / (1 - Marketing% - Profit%)
 */
class ColonyLandCostingService
{
    use ServiceTenantTrait;

    /** @var PDO */
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    /* ================================================================
       FORMULA ENGINE
       ================================================================ */

    /**
     * Calculate complete colony land costing from raw inputs.
     * Returns full breakdown — does NOT save to DB.
     *
     * @param array $inputs {
     *   total_land_sqft, land_purchase_rate, land_registry_cost,
     *   road_wastage_pct, drainage_wastage_pct, park_wastage_pct, other_wastage_pct,
     *   road_dev_cost_sqft, drainage_dev_cost_sqft, electricity_cost_sqft,
     *   water_pipeline_cost_sqft, boundary_wall_cost_sqft, other_dev_cost_sqft,
     *   legal_approval_cost, admin_overhead_pct,
     *   marketing_commission_pct, target_profit_pct
     * }
     * @return array Full calculation breakdown
     */
    public function calculate(array $inputs): array
    {
        // ── Input normalization ──────────────────────────────────────────
        $totalSqft       = (float)($inputs['total_land_sqft']         ?? 0);
        $purchaseRate    = (float)($inputs['land_purchase_rate']       ?? 0);
        $registryCost    = (float)($inputs['land_registry_cost']       ?? 0);

        $roadWaste       = (float)($inputs['road_wastage_pct']         ?? 15);
        $drainWaste      = (float)($inputs['drainage_wastage_pct']     ?? 5);
        $parkWaste       = (float)($inputs['park_wastage_pct']         ?? 5);
        $otherWaste      = (float)($inputs['other_wastage_pct']        ?? 0);

        $roadDevCost     = (float)($inputs['road_dev_cost_sqft']       ?? 0);
        $drainDevCost    = (float)($inputs['drainage_dev_cost_sqft']   ?? 0);
        $elecCost        = (float)($inputs['electricity_cost_sqft']    ?? 0);
        $waterCost       = (float)($inputs['water_pipeline_cost_sqft'] ?? 0);
        $boundaryCost    = (float)($inputs['boundary_wall_cost_sqft']  ?? 0);
        $otherDevCost    = (float)($inputs['other_dev_cost_sqft']      ?? 0);

        $legalCost       = (float)($inputs['legal_approval_cost']      ?? 0);
        $adminPct        = (float)($inputs['admin_overhead_pct']       ?? 5);
        $marketingPct    = (float)($inputs['marketing_commission_pct'] ?? 20);
        $profitPct       = (float)($inputs['target_profit_pct']        ?? 20);

        // ── STEP 1: Wastage & Net Sellable Area ──────────────────────────
        $totalWastePct   = $roadWaste + $drainWaste + $parkWaste + $otherWaste;
        $wastedSqft      = $totalSqft * ($totalWastePct / 100);
        $netSellableSqft = $totalSqft - $wastedSqft;

        if ($netSellableSqft <= 0) {
            return ['success' => false, 'error' => 'Net sellable area is zero or negative. Check wastage percentages.'];
        }

        // Wastage breakdown in SqFt
        $wastage = [
            'road'      => round($totalSqft * $roadWaste / 100, 2),
            'drainage'  => round($totalSqft * $drainWaste / 100, 2),
            'park'      => round($totalSqft * $parkWaste / 100, 2),
            'other'     => round($totalSqft * $otherWaste / 100, 2),
            'total_pct' => round($totalWastePct, 2),
            'total_sqft'=> round($wastedSqft, 2),
        ];

        // ── STEP 2: Raw Land Cost per Sellable SqFt ──────────────────────
        $totalLandAcquisitionCost = ($totalSqft * $purchaseRate) + $registryCost;
        $landCostPerSellableSqft  = $totalLandAcquisitionCost / $netSellableSqft;

        // ── STEP 3: Development Cost per SqFt ────────────────────────────
        $totalDevCostPerSqft = $roadDevCost + $drainDevCost + $elecCost
                             + $waterCost + $boundaryCost + $otherDevCost;

        // ── STEP 4: Legal Cost per Sellable SqFt ─────────────────────────
        $legalCostPerSqft = $netSellableSqft > 0 ? $legalCost / $netSellableSqft : 0;

        // ── STEP 5: Pre-Overhead Cost ─────────────────────────────────────
        $preOverheadCostPerSqft = $landCostPerSellableSqft + $totalDevCostPerSqft + $legalCostPerSqft;

        // ── STEP 6: Admin Overhead ────────────────────────────────────────
        $adminOverheadPerSqft = $preOverheadCostPerSqft * ($adminPct / 100);

        // ── STEP 7: Total Landing Cost per Sellable SqFt ─────────────────
        $landingCostPerSqft = $preOverheadCostPerSqft + $adminOverheadPerSqft;

        // ── STEP 8: Suggested Selling Price ──────────────────────────────
        // Marketing commission and profit are both % of final price, not cost.
        // So: FinalPrice × (1 - Marketing% - Profit%) = Landing Cost
        // → FinalPrice = Landing Cost / (1 - Marketing% - Profit%)
        $deductionFactor = 1 - ($marketingPct / 100) - ($profitPct / 100);
        if ($deductionFactor <= 0) {
            $suggestedPricePerSqft = 0;
        } else {
            $suggestedPricePerSqft = $landingCostPerSqft / $deductionFactor;
        }

        // ── Revenue projections ───────────────────────────────────────────
        $totalRevenue         = $suggestedPricePerSqft * $netSellableSqft;
        $totalMarketingBudget = $totalRevenue * $marketingPct / 100;
        $totalProfit          = $totalRevenue * $profitPct / 100;
        $totalLandingCost     = $landingCostPerSqft * $netSellableSqft;

        return [
            'success'           => true,

            // Input summary
            'total_land_sqft'             => round($totalSqft, 2),
            'land_purchase_rate'          => round($purchaseRate, 4),
            'land_registry_cost'          => round($registryCost, 2),

            // Wastage
            'wastage'                     => $wastage,
            'net_sellable_sqft'           => round($netSellableSqft, 2),
            'sellable_pct'                => round(100 - $totalWastePct, 2),

            // Cost breakdown per sellable SqFt
            'land_cost_per_sqft'          => round($landCostPerSellableSqft, 4),
            'dev_cost_per_sqft'           => round($totalDevCostPerSqft, 4),
            'legal_cost_per_sqft'         => round($legalCostPerSqft, 4),
            'pre_overhead_per_sqft'       => round($preOverheadCostPerSqft, 4),
            'admin_overhead_per_sqft'     => round($adminOverheadPerSqft, 4),
            'landing_cost_per_sqft'       => round($landingCostPerSqft, 4),

            // Selling price
            'marketing_commission_pct'    => round($marketingPct, 2),
            'target_profit_pct'           => round($profitPct, 2),
            'suggested_price_per_sqft'    => round($suggestedPricePerSqft, 2),

            // Project totals
            'total_land_acquisition_cost' => round($totalLandAcquisitionCost, 2),
            'total_landing_cost'          => round($totalLandingCost, 2),
            'total_revenue'               => round($totalRevenue, 2),
            'total_marketing_budget'      => round($totalMarketingBudget, 2),
            'total_profit'                => round($totalProfit, 2),
        ];
    }

    /* ================================================================
       CRUD — SAVE / FETCH COSTING
       ================================================================ */

    /**
     * Save or update colony costing record.
     * Also generates line-item breakdown in colony_land_costing_items.
     *
     * @param int   $colonyId
     * @param array $inputs     Same as calculate() inputs
     * @param float $finalPrice Admin-approved final price (may differ from suggested)
     * @param int   $userId     Who is saving this
     * @return array ['success'=>bool, 'id'=>int, 'calc'=>array]
     */
    public function saveCosting(int $colonyId, array $inputs, float $finalPrice, int $userId): array
    {
        $tid = (int)$this->tenantId();

        try {
            $calc = $this->calculate($inputs);
            if (!$calc['success']) {
                return ['success' => false, 'error' => $calc['error'] ?? 'Calculation failed', 'id' => null];
            }

            $this->pdo->beginTransaction();

            // Get next version for this colony
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(MAX(version), 0) + 1 AS next_ver
                FROM colony_land_costing
                WHERE colony_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$colonyId, $tid]);
            $nextVer = (int)$stmt->fetchColumn();

            // Insert costing record
            $ins = $this->pdo->prepare("
                INSERT INTO colony_land_costing (
                    colony_id, costing_label,
                    total_land_sqft, land_purchase_rate, land_registry_cost,
                    road_wastage_pct, drainage_wastage_pct, park_wastage_pct, other_wastage_pct,
                    road_dev_cost_sqft, drainage_dev_cost_sqft, electricity_cost_sqft,
                    water_pipeline_cost_sqft, boundary_wall_cost_sqft, other_dev_cost_sqft,
                    legal_approval_cost, admin_overhead_pct, marketing_commission_pct, target_profit_pct,
                    landing_cost_sqft, suggested_price_sqft, final_price_sqft,
                    is_approved, version, tenant_id, created_by, created_at, updated_at
                ) VALUES (
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    0, ?, ?, ?, NOW(), NOW()
                )
            ");

            $ins->execute([
                $colonyId,
                $inputs['costing_label'] ?? 'Initial Costing',
                $inputs['total_land_sqft'],
                $inputs['land_purchase_rate'],
                $inputs['land_registry_cost'] ?? 0,
                $inputs['road_wastage_pct'] ?? 15,
                $inputs['drainage_wastage_pct'] ?? 5,
                $inputs['park_wastage_pct'] ?? 5,
                $inputs['other_wastage_pct'] ?? 0,
                $inputs['road_dev_cost_sqft'] ?? 0,
                $inputs['drainage_dev_cost_sqft'] ?? 0,
                $inputs['electricity_cost_sqft'] ?? 0,
                $inputs['water_pipeline_cost_sqft'] ?? 0,
                $inputs['boundary_wall_cost_sqft'] ?? 0,
                $inputs['other_dev_cost_sqft'] ?? 0,
                $inputs['legal_approval_cost'] ?? 0,
                $inputs['admin_overhead_pct'] ?? 5,
                $inputs['marketing_commission_pct'] ?? 20,
                $inputs['target_profit_pct'] ?? 20,
                $calc['landing_cost_per_sqft'],
                $calc['suggested_price_per_sqft'],
                $finalPrice > 0 ? $finalPrice : $calc['suggested_price_per_sqft'],
                $nextVer,
                $tid,
                $userId,
            ]);

            $costingId = (int)$this->pdo->lastInsertId();

            // Insert line items
            $this->insertLineItems($costingId, $inputs, $calc, $tid);

            $this->pdo->commit();

            return ['success' => true, 'id' => $costingId, 'calc' => $calc];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[ColonyLandCostingService] saveCosting: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'id' => null];
        }
    }

    /**
     * Insert detailed line items for audit trail.
     */
    private function insertLineItems(int $costingId, array $inputs, array $calc, int $tid): void
    {
        $totalSqft       = (float)($inputs['total_land_sqft'] ?? 0);
        $netSellableSqft = $calc['net_sellable_sqft'];

        $items = [
            ['land_purchase',       'Land Purchase Cost',              $totalSqft,       'SqFt', (float)($inputs['land_purchase_rate'] ?? 0),       ($totalSqft * (float)($inputs['land_purchase_rate'] ?? 0)), 0],
            ['land_registry',       'Registry / Stamp Duty',           1,                'Flat', (float)($inputs['land_registry_cost'] ?? 0),        (float)($inputs['land_registry_cost'] ?? 0),               0],
            ['road_wastage',        'Road Area Wastage',               $calc['wastage']['road'],    'SqFt', (float)($inputs['road_wastage_pct'] ?? 15),  $calc['wastage']['road'],    1],
            ['drainage_wastage',    'Drainage / Nali Wastage',         $calc['wastage']['drainage'], 'SqFt', (float)($inputs['drainage_wastage_pct'] ?? 5), $calc['wastage']['drainage'], 1],
            ['park_wastage',        'Park / Green Area Wastage',       $calc['wastage']['park'],    'SqFt', (float)($inputs['park_wastage_pct'] ?? 5),   $calc['wastage']['park'],    1],
            ['other_wastage',       'Other Wastage',                   $calc['wastage']['other'],   'SqFt', (float)($inputs['other_wastage_pct'] ?? 0),  $calc['wastage']['other'],   1],
            ['road_development',    'Road Construction',               $netSellableSqft, 'SqFt', (float)($inputs['road_dev_cost_sqft'] ?? 0),        ($netSellableSqft * (float)($inputs['road_dev_cost_sqft'] ?? 0)), 0],
            ['drainage_development','Drainage / Nali Construction',    $netSellableSqft, 'SqFt', (float)($inputs['drainage_dev_cost_sqft'] ?? 0),    ($netSellableSqft * (float)($inputs['drainage_dev_cost_sqft'] ?? 0)), 0],
            ['electricity',         'Electrical Infrastructure',       $netSellableSqft, 'SqFt', (float)($inputs['electricity_cost_sqft'] ?? 0),     ($netSellableSqft * (float)($inputs['electricity_cost_sqft'] ?? 0)), 0],
            ['water_pipeline',      'Water Pipeline',                  $netSellableSqft, 'SqFt', (float)($inputs['water_pipeline_cost_sqft'] ?? 0),  ($netSellableSqft * (float)($inputs['water_pipeline_cost_sqft'] ?? 0)), 0],
            ['boundary_wall',       'Boundary Wall / Security',        $netSellableSqft, 'SqFt', (float)($inputs['boundary_wall_cost_sqft'] ?? 0),   ($netSellableSqft * (float)($inputs['boundary_wall_cost_sqft'] ?? 0)), 0],
            ['other_development',   'Other Development',               $netSellableSqft, 'SqFt', (float)($inputs['other_dev_cost_sqft'] ?? 0),       ($netSellableSqft * (float)($inputs['other_dev_cost_sqft'] ?? 0)), 0],
            ['legal_approval',      'Legal / RERA / NOC Approvals',    1,                'Flat', (float)($inputs['legal_approval_cost'] ?? 0),       (float)($inputs['legal_approval_cost'] ?? 0), 0],
            ['admin_overhead',      'Admin & Management Overhead (' . ($inputs['admin_overhead_pct'] ?? 5) . '%)', $netSellableSqft, 'SqFt', $calc['admin_overhead_per_sqft'], ($calc['admin_overhead_per_sqft'] * $netSellableSqft), 0],
            ['marketing_commission','MLM/Sales Commission Budget (' . ($inputs['marketing_commission_pct'] ?? 20) . '%)', $netSellableSqft, 'SqFt', ($calc['suggested_price_per_sqft'] * (float)($inputs['marketing_commission_pct'] ?? 20) / 100), ($calc['total_marketing_budget'] ?? 0), 0],
            ['profit_margin',       'Company Profit Margin (' . ($inputs['target_profit_pct'] ?? 20) . '%)',          $netSellableSqft, 'SqFt', ($calc['suggested_price_per_sqft'] * (float)($inputs['target_profit_pct'] ?? 20) / 100), ($calc['total_profit'] ?? 0), 0],
        ];

        $itemStmt = $this->pdo->prepare("
            INSERT INTO colony_land_costing_items
                (costing_id, item_type, item_label, quantity, unit, rate, amount, is_deduction, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        foreach ($items as $item) {
            $itemStmt->execute([
                $costingId,
                $item[0], $item[1],
                round($item[2], 4), $item[3],
                round($item[4], 4),
                round($item[5], 2),
                $item[6],
                $tid,
            ]);
        }
    }

    /**
     * Get the latest approved costing for a colony.
     */
    public function getColonyCosting(int $colonyId): ?array
    {
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, col.name AS colony_name, u.name AS created_by_name
                FROM colony_land_costing c
                LEFT JOIN colonies col ON col.id = c.colony_id
                LEFT JOIN users u ON u.id = c.created_by
                WHERE c.colony_id = ? AND c.tenant_id = ?
                ORDER BY c.version DESC
                LIMIT 1
            ");
            $stmt->execute([$colonyId, $tid]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[ColonyLandCostingService] getColonyCosting: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all costing versions for a colony (history).
     */
    public function getCostingHistory(int $colonyId): array
    {
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.name AS created_by_name
                FROM colony_land_costing c
                LEFT JOIN users u ON u.id = c.created_by
                WHERE c.colony_id = ? AND c.tenant_id = ?
                ORDER BY c.version DESC
            ");
            $stmt->execute([$colonyId, $tid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[ColonyLandCostingService] getCostingHistory: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get line items for a specific costing record.
     */
    public function getCostingLineItems(int $costingId): array
    {
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM colony_land_costing_items
                WHERE costing_id = ? AND tenant_id = ?
                ORDER BY id ASC
            ");
            $stmt->execute([$costingId, $tid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[ColonyLandCostingService] getCostingLineItems: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Approve a costing record and set final selling price.
     */
    public function approveCosting(int $costingId, float $finalPrice, int $approvedByUserId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE colony_land_costing
                SET is_approved = 1, final_price_sqft = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$finalPrice, $approvedByUserId, $costingId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log('[ColonyLandCostingService] approveCosting: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all colonies with their latest costing status (for dashboard list).
     */
    public function getAllColoniesWithCostingStatus(): array
    {
        $tid = (int)$this->tenantId();
        try {
            $tWhere = $tid > 1 ? 'AND col.tenant_id = ?' : '';
            $params = $tid > 1 ? [$tid] : [];

            $stmt = $this->pdo->prepare("
                SELECT
                    col.id AS colony_id,
                    col.name AS colony_name,
                    col.district_id,
                    lc.id AS costing_id,
                    lc.version,
                    lc.landing_cost_sqft,
                    lc.suggested_price_sqft,
                    lc.final_price_sqft,
                    lc.is_approved,
                    lc.net_sellable_sqft,
                    lc.created_at AS costing_date
                FROM colonies col
                LEFT JOIN colony_land_costing lc ON lc.colony_id = col.id
                    AND lc.id = (
                        SELECT id FROM colony_land_costing
                        WHERE colony_id = col.id
                        ORDER BY version DESC LIMIT 1
                    )
                WHERE 1=1 {$tWhere}
                ORDER BY col.name ASC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[ColonyLandCostingService] getAllColoniesWithCostingStatus: ' . $e->getMessage());
            return [];
        }
    }
}
