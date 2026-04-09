<?php
/**
 * Plot Development Cost Calculator
 * Calculates land development costs including roads, parks, drainage, etc.
 */

namespace App\Services;

use App\Core\Database\Database;

class PlotDevelopmentCostService
{
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
        
        $this->db->execute(
            "UPDATE plots SET 
                price_per_sqft = ?,
                total_price = ?,
                updated_at = NOW()
             WHERE id = ?",
            [$pricing['final_price_per_sqft'], $pricing['final_price'], $plotId]
        );
        
        return $pricing;
    }
    
    /**
     * Get land cost from plot_master (Gata based)
     */
    private function getLandCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(plot_price * available_area) as total_land_cost 
             FROM plot_master WHERE site_id = ?",
            [$colonyId]
        );
        return floatval($result['total_land_cost'] ?? 0);
    }
    
    /**
     * Get development cost (roads, drainage, etc.)
     */
    private function getDevelopmentCost($colonyId)
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total 
             FROM plot_development_costs 
             WHERE colony_id = ? AND cost_type = 'development'",
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
             FROM plot_development_costs 
             WHERE colony_id = ? AND cost_type = 'amenities'",
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
             FROM plot_development_costs 
             WHERE colony_id = ? AND cost_type = 'legal'",
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
             FROM plot_development_costs 
             WHERE colony_id = ? AND cost_type = 'misc'",
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
            "SELECT SUM(area_sqft) as total_area FROM plots WHERE colony_id = ?",
            [$colonyId]
        );
        return floatval($result['total_area'] ?? 0);
    }
    
    /**
     * Get colony details
     */
    private function getColony($colonyId)
    {
        return $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
    }
    
    /**
     * Get plot details
     */
    private function getPlot($plotId)
    {
        return $this->db->fetch("SELECT * FROM plots WHERE id = ?", [$plotId]);
    }
    
    /**
     * Add development cost entry
     */
    public function addCost($colonyId, $costType, $description, $amount, $perSqftRate = null, $totalArea = null)
    {
        $this->db->execute(
            "INSERT INTO plot_development_costs 
             (colony_id, cost_type, description, amount, per_sqft_rate, total_area_sqft)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$colonyId, $costType, $description, $amount, $perSqftRate, $totalArea]
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
             FROM plot_development_costs
             WHERE colony_id = ?
             GROUP BY cost_type",
            [$colonyId]
        );
        
        $breakdown = [
            'land' => 0,
            'development' => 0,
            'amenities' => 0,
            'legal' => 0,
            'misc' => 0,
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
     */
    public function generateCostReport($colonyId)
    {
        $colony = $this->getColony($colonyId);
        if (!$colony) return null;
        
        $breakdown = $this->getCostBreakdown($colonyId);
        $colonyCost = $this->calculateColonyCost($colonyId);
        
        // Get all plots with their pricing
        $plots = $this->db->fetchAll(
            "SELECT p.*, 
                    (p.total_price / NULLIF(p.area_sqft, 0)) as price_per_sqft_calc
             FROM plots p
             WHERE p.colony_id = ?",
            [$colonyId]
        );
        
        $plotPricing = [];
        foreach ($plots as $plot) {
            $pricing = $this->calculatePlotPrice($plot['id']);
            if ($pricing) {
                $plotPricing[] = $pricing;
            }
        }
        
        return [
            'colony' => $colony,
            'cost_breakdown' => $breakdown,
            'total_cost' => $colonyCost['total_cost'] ?? 0,
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
            "SELECT id FROM plots WHERE colony_id = ?",
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
