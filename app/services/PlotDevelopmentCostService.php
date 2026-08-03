<?php
/**
 * Plot Development Cost Calculator
 * Calculates land development costs including roads, parks, drainage, etc.
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class PlotDevelopmentCostService
{
    use ServiceTenantTrait;

    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Calculate total development cost for a colony
     */
    public function calculateColonyCost($colonyId)
    {
        $colony = $this->getColony($colonyId);
        if (!$colony) return null;
        
        $costs = [
            'land_cost' => $this->getLandCost($colonyId),
            'development_cost' => $this->getDevelopmentCost($colonyId),
            'amenities_cost' => $this->getAmenitiesCost($colonyId),
            'legal_cost' => $this->getLegalCost($colonyId),
            'misc_cost' => $this->getMiscCost($colonyId)
        ];
        
        $costs['total_cost'] = array_sum($costs);
        $costs['total_area_sqft'] = floatval($colony['total_area_sqft'] ?? 0);
        $costs['plot_area_sqft'] = $this->getPlotArea($colonyId);
        $costs['cost_per_sqft'] = $costs['total_cost'] / max(1, $costs['plot_area_sqft']);
        
        return $costs;
    }
    
    /**
     * Calculate plot price based on costs and margin
     */
    public function calculatePlotPrice($plotId, $marginPercent = 25)
    {
        $plot = $this->getPlot($plotId);
        if (!$plot) return null;
        
        $colonyId = $plot['colony_id'];
        $colonyCost = $this->calculateColonyCost($colonyId);
        
        if (!$colonyCost) return null;
        
        // Calculate plot's share of total cost
        $plotArea = floatval($plot['area_sqft'] ?? 0);
        $sharePercent = ($plotArea / max(1, $colonyCost['plot_area_sqft'])) * 100;
        $plotShareOfCost = ($colonyCost['total_cost'] * $sharePercent) / 100;
        
        // Cost per sqft for this plot
        $costPerSqft = $plotShareOfCost / max(1, $plotArea);
        
        // Add margin
        $margin = $costPerSqft * ($marginPercent / 100);
        $finalPricePerSqft = $costPerSqft + $margin;
        $totalPrice = $finalPricePerSqft * $plotArea;
        
        return [
            'plot_id' => $plotId,
            'plot_area_sqft' => $plotArea,
            'share_of_land_cost' => ($colonyCost['land_cost'] * $sharePercent) / 100,
            'share_of_development_cost' => ($colonyCost['development_cost'] * $sharePercent) / 100,
            'share_of_amenities_cost' => ($colonyCost['amenities_cost'] * $sharePercent) / 100,
            'share_of_legal_cost' => ($colonyCost['legal_cost'] * $sharePercent) / 100,
            'share_of_misc_cost' => ($colonyCost['misc_cost'] * $sharePercent) / 100,
            'total_cost' => $plotShareOfCost,
            'cost_per_sqft' => $costPerSqft,
            'margin_percent' => $marginPercent,
            'margin_amount' => $margin * $plotArea,
            'final_price' => $totalPrice,
            'final_price_per_sqft' => $finalPricePerSqft
        ];
    }
    
    /**
     * Update plot price based on calculation
     */
    public function updatePlotPrice($plotId, $marginPercent = 25)
    {
        $pricing = $this->calculatePlotPrice($plotId, $marginPercent);
        if (!$pricing) return false;

        // Get old prices before update
        $oldPlot = $this->db->fetch(
            "SELECT price_per_sqft, total_price, colony_id FROM plots WHERE id = ?" . $this->tenantSql(),
            [$plotId]
        );
        $oldPricePerSqft = (float) ($oldPlot['price_per_sqft'] ?? 0);
        $oldTotalPrice   = (float) ($oldPlot['total_price'] ?? 0);
        $colonyId        = (int) ($oldPlot['colony_id'] ?? 0);

        $this->db->execute(
            "UPDATE plots SET 
                price_per_sqft = ?,
                total_price = ?,
                updated_at = NOW()
              WHERE id = ?" . $this->tenantSql(),
            [$pricing['final_price_per_sqft'], $pricing['final_price'], $plotId]
        );

        // Log price change to price_history
        $this->logPriceHistory(
            $plotId,
            $colonyId,
            $oldPricePerSqft,
            $pricing['final_price_per_sqft'],
            $oldTotalPrice,
            $pricing['final_price'],
            'margin_update',
            "Margin update: {$marginPercent}% margin applied",
            (int) ($_SESSION['user_id'] ?? 0)
        );

        return $pricing;
    }

    /**
     * Log price history
     */
    private function logPriceHistory($plotId, $colonyId, $oldPricePerSqft, $newPricePerSqft, $oldTotalPrice, $newTotalPrice, $changeType, $reason, $changedBy)
    {
        try {
            $insertData = $this->tenantInsertData();
            $extraCols = !empty($insertData) ? ", " . implode(', ', array_keys($insertData)) : "";
            $extraVals = !empty($insertData) ? ", " . str_repeat('?,', count($insertData) - 1) . '?' : "";
            $extraParams = !empty($insertData) ? array_values($insertData) : [];
            $this->db->insert('price_history', array_merge([
                'plot_id'           => $plotId,
                'colony_id'         => $colonyId,
                'old_price'         => $oldTotalPrice,
                'new_price'         => $newTotalPrice,
                'old_price_per_sqft'=> $oldPricePerSqft,
                'new_price_per_sqft'=> $newPricePerSqft,
                'change_type'       => $changeType,
                'reason'            => $reason,
                'changed_by'        => $changedBy,
                'reference_type'    => 'cost_calculation',
                'reference_id'      => $colonyId,
                'created_at'        => date('Y-m-d H:i:s'),
            ], $insertData));
        } catch (\Exception $e) {
            error_log("Price history insert failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get land cost from plot_master (Gata based)
     */
    private function getLandCost($colonyId)
    {
        try {
            $result = $this->db->fetch(
                "SELECT SUM(plot_price * available_area) as total_land_cost 
                 FROM plot_master WHERE site_id = ?" . $this->tenantSql(),
                [$colonyId]
            );
            return floatval($result['total_land_cost'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
    
    /**
     * Get development cost (roads, drainage, etc.)
     */
    private function getDevelopmentCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total 
             FROM colony_development_costs 
             WHERE colony_id = ?" . $this->tenantSql() . " AND cost_type IN ('road','electricity','water','sewerage','drainage','street_light')",
            [$colonyId]
        );
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Get amenities cost (park, club, etc.)
     */
    private function getAmenitiesCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total 
             FROM colony_development_costs 
             WHERE colony_id = ?" . $this->tenantSql() . " AND cost_type IN ('landscaping','compound_wall','gate','security')",
            [$colonyId]
        );
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Get legal cost (registry, agreement, etc.)
     */
    private function getLegalCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total 
             FROM colony_development_costs 
             WHERE colony_id = ?" . $this->tenantSql() . " AND cost_type IN ('legal','approval_fee')",
            [$colonyId]
        );
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Get miscellaneous cost
     */
    private function getMiscCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total 
             FROM colony_development_costs 
             WHERE colony_id = ?" . $this->tenantSql() . " AND cost_type IN ('brokerage','marketing','office_setup','staff','other')",
            [$colonyId]
        );
        return floatval($result['total'] ?? 0);
    }
    
    /**
     * Get total plot area in sqft
     */
    private function getPlotArea($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(area_sqft) as total_area FROM plots WHERE colony_id = ?" . $this->tenantSql(),
            [$colonyId]
        );
        return floatval($result['total_area'] ?? 0);
    }
    
    /**
     * Get colony details
     */
    private function getColony($colonyId)
    {
        return $this->db->fetch("SELECT * FROM colonies WHERE id = ?" . $this->tenantSql(), [$colonyId]);
    }
    
    /**
     * Get plot details
     */
    private function getPlot($plotId)
    {
        return $this->db->fetch("SELECT * FROM plots WHERE id = ?" . $this->tenantSql(), [$plotId]);
    }
    
    /**
     * Add development cost entry
     */
    public function addCost($colonyId, $costType, $description, $amount, $perSqftRate = null, $totalArea = null)
    {
        $insertData = $this->tenantInsertData();
        $cols = "colony_id, cost_type, work_description, amount";
        $vals = "?, ?, ?, ?";
        $params = [$colonyId, $costType, $description, $amount];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $vals .= ", " . str_repeat('?,', count($insertData) - 1) . '?';
            $params = array_merge($params, array_values($insertData));
        }

        $this->db->execute(
            "INSERT INTO colony_development_costs ($cols, created_at) VALUES ($vals, NOW())",
            array_merge($params, [date('Y-m-d H:i:s')])
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get cost breakdown for a colony
     */
    public function getCostBreakdown($colonyId)
    {
        $costs = $this->db->fetchAll(
            "SELECT cost_type, SUM(amount) as total_amount, COUNT(*) as entries
             FROM colony_development_costs
             WHERE colony_id = ?" . $this->tenantSql() . "
             GROUP BY cost_type",
            [$colonyId]
        );
        
        $breakdown = [
            'land_acquisition' => 0,
            'road' => 0,
            'electricity' => 0,
            'water' => 0,
            'sewerage' => 0,
            'street_light' => 0,
            'drainage' => 0,
            'compound_wall' => 0,
            'gate' => 0,
            'security' => 0,
            'landscaping' => 0,
            'approval_fee' => 0,
            'legal' => 0,
            'brokerage' => 0,
            'marketing' => 0,
            'office_setup' => 0,
            'staff' => 0,
            'other' => 0,
            'total' => 0
        ];
        
        foreach ($costs as $cost) {
            $type = $cost['cost_type'];
            $breakdown[$type] = floatval($cost['total_amount']);
            $breakdown['total'] += floatval($cost['total_amount']);
        }
        
        return $breakdown;
    }
    
    /**
     * Generate development cost report
     * OPTIMIZED: Calculates colony cost ONCE, then computes per-plot pricing in PHP (no N+1)
     */
    public function generateCostReport($colonyId)
    {
        $colony = $this->getColony($colonyId);
        if (!$colony) return null;
        
        $breakdown = $this->getCostBreakdown($colonyId);
        $colonyCost = $this->calculateColonyCost($colonyId);
        
        if (!$colonyCost) return null;

        $plotAreaSqft = $colonyCost['plot_area_sqft'] ?? 0;
        $totalCost = $colonyCost['total_cost'] ?? 0;
        
        // Single query to get all plots
        $plots = $this->db->fetchAll(
            "SELECT p.*, (p.total_price / NULLIF(p.area_sqft, 0)) as price_per_sqft_calc
             FROM plots p
             WHERE p.colony_id = ?" . $this->tenantSql(),
            [$colonyId]
        );
        
        // Compute per-plot pricing in PHP (no more DB queries)
        $plotPricing = [];
        foreach ($plots as $plot) {
            $plotArea = floatval($plot['area_sqft'] ?? 0);
            $sharePercent = ($plotArea / max(1, $plotAreaSqft)) * 100;
            $plotShareOfCost = ($totalCost * $sharePercent) / 100;
            $costPerSqft = $plotShareOfCost / max(1, $plotArea);
            $margin = $costPerSqft * 0.25;
            $finalPricePerSqft = $costPerSqft + $margin;
            $totalPrice = $finalPricePerSqft * $plotArea;
            
            $plotPricing[] = [
                'plot_id' => $plot['id'],
                'plot_number' => $plot['plot_number'] ?? '',
                'block' => $plot['block'] ?? '',
                'plot_area_sqft' => $plotArea,
                'share_of_land_cost' => ($colonyCost['land_cost'] * $sharePercent) / 100,
                'share_of_development_cost' => ($colonyCost['development_cost'] * $sharePercent) / 100,
                'share_of_amenities_cost' => ($colonyCost['amenities_cost'] * $sharePercent) / 100,
                'share_of_legal_cost' => ($colonyCost['legal_cost'] * $sharePercent) / 100,
                'share_of_misc_cost' => ($colonyCost['misc_cost'] * $sharePercent) / 100,
                'total_cost' => $plotShareOfCost,
                'cost_per_sqft' => $costPerSqft,
                'margin_percent' => 25,
                'margin_amount' => $margin * $plotArea,
                'final_price' => $totalPrice,
                'final_price_per_sqft' => $finalPricePerSqft
            ];
        }
        
        return [
            'colony' => $colony,
            'cost_breakdown' => $breakdown,
            'total_cost' => $totalCost,
            'plots' => $plotPricing,
            'total_plot_value' => array_sum(array_column($plotPricing, 'final_price')),
            'total_plot_area' => array_sum(array_column($plotPricing, 'plot_area_sqft')),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Update all plot prices for a colony
     */
    public function updateAllPlotPrices($colonyId, $marginPercent = 25)
    {
        $plots = $this->db->fetchAll(
            "SELECT id FROM plots WHERE colony_id = ?" . $this->tenantSql(),
            [$colonyId]
        );
        
        $updated = 0;
        foreach ($plots as $plot) {
            if ($this->updatePlotPrice($plot['id'], $marginPercent)) {
                $updated++;
            }
        }
        
        return $updated;
    }
}
