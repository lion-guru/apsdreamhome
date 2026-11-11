<?php
/**
 * PropertyValuationEngine — AI-powered property valuation
 * Estimates market value based on location, amenities, demand, and historical data
 */

namespace App\Services\AI;

use App\Core\Database\Database;

class PropertyValuationEngine
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Estimate property value
     */
    public function estimateValue(array $params): array
    {
        $plotId = $params['plot_id'] ?? null;
        $colonyId = $params['colony_id'] ?? null;
        $area = $params['area'] ?? 0;
        $location = $params['location'] ?? '';
        $facing = $params['facing'] ?? '';
        $roadWidth = $params['road_width'] ?? 0;
        $plcFlags = $params['plc_flags'] ?? [];

        // Get base data
        $basePrice = 0;
        $colony = null;
        
        if ($plotId) {
            $plot = $this->db->fetch("
                SELECT p.*, c.name as colony_name, c.city, c.state
                FROM plots p
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE p.id = ?
            ", [$plotId]);
            
            if ($plot) {
                $basePrice = $plot['base_price_per_sqft'] ?? $plot['price_per_sqft'] ?? 0;
                $colonyId = $plot['colony_id'];
                $area = $plot['plot_size'] ?? $area;
                $facing = $plot['facing'] ?? $facing;
                $roadWidth = $plot['road_width_ft'] ?? $roadWidth;
                $colony = ['name' => $plot['colony_name'], 'city' => $plot['city']];
            }
        }

        if (!$colony && $colonyId) {
            $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if ($colony) {
                $basePrice = $colony['min_price_per_sqft'] ?? 0;
                // "0.00" is a truthy string — treat zero as missing (lesson _706 family)
                if ((float) $basePrice <= 0) {
                    $basePrice = $colony['starting_price'] ?? 0;
                }
            }
        }

        if ((float) $basePrice <= 0) {
            $basePrice = $this->getAveragePricePerSqft($colonyId);
        }

        // Calculate valuation factors
        $factors = $this->calculateFactors($colonyId, $area, $facing, $roadWidth, $plcFlags, $colony);

        // Apply factors to base price
        $estimatedPricePerSqft = $basePrice;
        foreach ($factors as $factor) {
            $estimatedPricePerSqft *= (1 + $factor['impact']);
        }

        $estimatedTotal = round($estimatedPricePerSqft * $area);
        $confidence = $this->calculateConfidence($colonyId, $area, $factors);

        // Get market comparison
        $comparison = $this->getMarketComparison($colonyId, $estimatedPricePerSqft);

        // Get price range (low/high)
        $priceRange = $this->getPriceRange($estimatedTotal, $confidence);

        return [
            'success' => true,
            'estimated_value' => $estimatedTotal,
            'price_per_sqft' => round($estimatedPricePerSqft, 2),
            'base_price_per_sqft' => $basePrice,
            'area' => $area,
            'confidence' => $confidence,
            'confidence_level' => $this->getConfidenceLevel($confidence),
            'factors' => $factors,
            'market_comparison' => $comparison,
            'price_range' => $priceRange,
            'recommendation' => $this->getRecommendation($estimatedTotal, $colony, $factors),
        ];
    }

    /**
     * Calculate valuation factors
     */
    private function calculateFactors($colonyId, $area, $facing, $roadWidth, $plcFlags, $colony): array
    {
        $factors = [];

        // 1. Location demand (based on colony sales velocity)
        $demandScore = $this->getColonyDemandScore($colonyId);
        $factors[] = [
            'name' => 'Location Demand',
            'impact' => ($demandScore - 50) / 100 * 0.15,
            'value' => $demandScore,
            'description' => "Colony demand score: {$demandScore}/100",
        ];

        // 2. Facing premium
        $facingPremium = $this->getFacingPremium($facing);
        $factors[] = [
            'name' => 'Facing Premium',
            'impact' => $facingPremium,
            'value' => round($facingPremium * 100, 1),
            'description' => "Facing: " . ($facing ?: 'Not specified'),
        ];

        // 3. Road width premium
        $roadPremium = $this->getRoadWidthPremium($roadWidth);
        $factors[] = [
            'name' => 'Road Width',
            'impact' => $roadPremium,
            'value' => round($roadPremium * 100, 1),
            'description' => "Road width: {$roadWidth}ft",
        ];

        // 4. PLC flags impact
        $plcImpact = $this->getPLCFlagsImpact($plcFlags);
        $factors[] = [
            'name' => 'PLC Features',
            'impact' => $plcImpact,
            'value' => round($plcImpact * 100, 1),
            'description' => "PLC flags: " . implode(', ', $plcFlags ?: ['None']),
        ];

        // 5. Size factor (larger plots may have bulk discount)
        $sizeFactor = $this->getSizeFactor($area);
        $factors[] = [
            'name' => 'Size Factor',
            'impact' => $sizeFactor,
            'value' => round($sizeFactor * 100, 1),
            'description' => "Plot area: {$area} sqft",
        ];

        // 6. Market trend (price change over last 6 months)
        $trendFactor = $this->getMarketTrendFactor($colonyId);
        $factors[] = [
            'name' => 'Market Trend',
            'impact' => $trendFactor,
            'value' => round($trendFactor * 100, 1),
            'description' => '6-month price trend',
        ];

        return $factors;
    }

    /**
     * Get colony demand score (0-100)
     */
    private function getColonyDemandScore($colonyId): float
    {
        if (!$colonyId) return 50;

        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_plots,
                SUM(CASE WHEN status IN ('booked', 'sold', 'registration_done') THEN 1 ELSE 0 END) as sold_count,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_count
            FROM plots
            WHERE colony_id = ?
        ", [$colonyId]);

        if (!$stats || $stats['total_plots'] == 0) return 50;

        $soldRatio = $stats['sold_count'] / $stats['total_plots'];
        $availabilityRatio = $stats['available_count'] / $stats['total_plots'];

        // High demand = high sold ratio + low availability
        $demandScore = ($soldRatio * 60) + ((1 - $availabilityRatio) * 40);

        return min(100, max(0, $demandScore));
    }

    /**
     * Get facing premium
     */
    private function getFacingPremium($facing): float
    {
        $premiums = [
            'north' => 0.05,
            'east' => 0.08,
            'south' => 0.03,
            'west' => 0.02,
            'north-east' => 0.10,
            'north-west' => 0.07,
            'south-east' => 0.06,
            'south-west' => 0.04,
            'corner' => 0.12,
            'park_facing' => 0.10,
            'main_road' => 0.08,
        ];

        $facingLower = strtolower($facing ?? '');
        return $premiums[$facingLower] ?? 0;
    }

    /**
     * Get road width premium
     */
    private function getRoadWidthPremium($roadWidth): float
    {
        $roadWidth = (int)$roadWidth;
        if ($roadWidth >= 60) return 0.10;
        if ($roadWidth >= 40) return 0.07;
        if ($roadWidth >= 30) return 0.05;
        if ($roadWidth >= 20) return 0.02;
        return 0;
    }

    /**
     * Get PLC flags impact
     */
    private function getPLCFlagsImpact($plcFlags): float
    {
        if (!is_array($plcFlags)) return 0;

        $impacts = [
            'corner' => 0.12,
            'park_facing' => 0.10,
            'main_road' => 0.08,
            'corner_plot' => 0.12,
            'park_facing' => 0.10,
            'wide_road' => 0.07,
            'east_facing' => 0.08,
            'corner_park' => 0.15,
            'commercial' => 0.20,
        ];

        $total = 0;
        foreach ($plcFlags as $flag) {
            $flagLower = strtolower(str_replace(' ', '_', $flag));
            $total += $impacts[$flagLower] ?? 0;
        }

        return min(0.25, $total); // Cap at 25%
    }

    /**
     * Get size factor
     */
    private function getSizeFactor($area): float
    {
        $area = (int)$area;
        if ($area >= 5000) return -0.05; // Large plots: slight discount
        if ($area >= 2000) return -0.02;
        if ($area >= 1000) return 0;
        return 0.03; // Small plots: slight premium
    }

    /**
     * Get market trend factor (6-month price change)
     */
    private function getMarketTrendFactor($colonyId): float
    {
        if (!$colonyId) return 0;

        $trend = $this->db->fetch("
            SELECT 
                COALESCE(AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN base_price_per_sqft END), 0) as recent_avg,
                COALESCE(AVG(CASE WHEN created_at BETWEEN DATE_SUB(NOW(), INTERVAL 6 MONTH) AND DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN base_price_per_sqft END), 0) as older_avg
            FROM plots
            WHERE colony_id = ? AND base_price_per_sqft > 0
        ", [$colonyId]);

        if (!$trend || !$trend['recent_avg'] || !$trend['older_avg']) return 0;

        $change = ($trend['recent_avg'] - $trend['older_avg']) / $trend['older_avg'];
        return max(-0.10, min(0.10, $change)); // Cap at ±10%
    }

    /**
     * Calculate confidence score (0-100)
     */
    private function calculateConfidence($colonyId, $area, $factors): float
    {
        $confidence = 50; // base

        // More data = higher confidence
        $plotCount = $this->db->fetchColumn("SELECT COUNT(*) FROM plots WHERE colony_id = ?", [$colonyId]) ?? 0;
        if ($plotCount >= 100) $confidence += 20;
        elseif ($plotCount >= 50) $confidence += 15;
        elseif ($plotCount >= 20) $confidence += 10;
        elseif ($plotCount >= 10) $confidence += 5;

        // Historical data availability
        $historyCount = $this->db->fetchColumn("SELECT COUNT(*) FROM price_history WHERE colony_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)", [$colonyId]) ?? 0;
        if ($historyCount >= 50) $confidence += 15;
        elseif ($historyCount >= 20) $confidence += 10;
        elseif ($historyCount >= 5) $confidence += 5;

        // Factor consistency (if all factors agree direction)
        $impacts = array_column($factors, 'impact');
        $positive = count(array_filter($impacts, fn($i) => $i > 0));
        $negative = count(array_filter($impacts, fn($i) => $i < 0));
        if ($positive === count($impacts) || $negative === count($impacts)) {
            $confidence += 10; // All factors agree
        }

        return min(95, max(30, $confidence));
    }

    /**
     * Get confidence level text
     */
    private function getConfidenceLevel(float $confidence): string
    {
        if ($confidence >= 85) return 'Very High';
        if ($confidence >= 70) return 'High';
        if ($confidence >= 55) return 'Medium';
        if ($confidence >= 40) return 'Low';
        return 'Very Low';
    }

    /**
     * Get market comparison
     */
    private function getMarketComparison($colonyId, $estimatedPrice): array
    {
        if (!$colonyId) return ['note' => 'No colony data available'];

        $stats = $this->db->fetch("
            SELECT 
                MIN(base_price_per_sqft) as min_price,
                MAX(base_price_per_sqft) as max_price,
                AVG(base_price_per_sqft) as avg_price,
                COUNT(*) as total_plots
            FROM plots
            WHERE colony_id = ? AND base_price_per_sqft > 0 AND status IN ('available', 'booked', 'sold')
        ", [$colonyId]);

        if (!$stats || $stats['total_plots'] == 0) {
            return ['note' => 'No market data available'];
        }

        $comparison = 'average';
        if ($estimatedPrice > $stats['avg_price'] * 1.1) $comparison = 'above average';
        elseif ($estimatedPrice < $stats['avg_price'] * 0.9) $comparison = 'below average';

        return [
            'min_price' => (int)$stats['min_price'],
            'max_price' => (int)$stats['max_price'],
            'avg_price' => (int)$stats['avg_price'],
            'total_plots' => (int)$stats['total_plots'],
            'comparison' => $comparison,
            'difference_from_avg' => round(($estimatedPrice - $stats['avg_price']) / $stats['avg_price'] * 100, 1),
        ];
    }

    /**
     * Get price range (low/high)
     */
    private function getPriceRange($estimatedTotal, $confidence): array
    {
        $variance = (100 - $confidence) / 100 * 0.15; // Higher confidence = narrower range
        $variance = max(0.05, min(0.25, $variance)); // 5-25% variance

        return [
            'low' => round($estimatedTotal * (1 - $variance)),
            'high' => round($estimatedTotal * (1 + $variance)),
            'variance_pct' => round($variance * 100, 1),
        ];
    }

    /**
     * Get recommendation text
     */
    private function getRecommendation($estimatedTotal, $colony, $factors): string
    {
        $colonyName = $colony['name'] ?? 'this colony';
        
        $positiveFactors = array_filter($factors, fn($f) => $f['impact'] > 0);
        $negativeFactors = array_filter($factors, fn($f) => $f['impact'] < 0);
        
        $parts = [];
        $parts[] = "Estimated value for plot in {$colonyName}: ₹" . number_format($estimatedTotal);
        
        if (!empty($positiveFactors)) {
            $parts[] = "Positive factors: " . implode(', ', array_map(fn($f) => $f['name'], $positiveFactors));
        }
        
        if (!empty($negativeFactors)) {
            $parts[] = "Consider: " . implode(', ', array_map(fn($f) => $f['name'], $negativeFactors));
        }
        
        return implode('. ', $parts) . '.';
    }

    /**
     * Get average price per sqft for colony
     */
    private function getAveragePricePerSqft($colonyId): float
    {
        if (!$colonyId) return 1000; // default
        
        $avg = $this->db->fetchColumn("
            SELECT AVG(base_price_per_sqft) FROM plots 
            WHERE colony_id = ? AND base_price_per_sqft > 0 AND status IN ('available', 'booked', 'sold')
        ", [$colonyId]);
        
        return $avg ?: 1000;
    }

    /**
     * Bulk valuation
     */
    public function bulkValuate(array $plotIds): array
    {
        $results = [];
        foreach ($plotIds as $plotId) {
            $result = $this->estimateValue(['plot_id' => $plotId]);
            if ($result['success']) {
                $results[] = [
                    'plot_id' => $plotId,
                    'estimated_value' => $result['estimated_value'],
                    'price_per_sqft' => $result['price_per_sqft'],
                    'confidence' => $result['confidence'],
                ];
            } else {
                $results[] = ['plot_id' => $plotId, 'error' => $result['error']];
            }
        }
        return ['success' => true, 'results' => $results];
    }

    /**
     * Get valuation history for a plot
     */
    public function getValuationHistory(int $plotId, int $limit = 10): array
    {
        $history = $this->db->fetchAll("
            SELECT * FROM property_valuations 
            WHERE property_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$plotId, $limit]);

        return ['success' => true, 'history' => $history];
    }

    /**
     * Dashboard stats across generated valuation reports
     */
    public function getValuationStats(): array
    {
        try {
            $statsRow = $this->db->fetchOne("
                SELECT COUNT(*) as total,
                       COALESCE(AVG(final_valuation), 0) as avg_value,
                       COALESCE(MAX(final_valuation), 0) as max_value,
                       COALESCE(AVG(confidence_score), 0) as avg_confidence,
                       COUNT(DISTINCT property_id) as unique_props,
                       COALESCE(SUM(base_valuation), 0) as total_base
                FROM property_valuation_reports
            ");
            return [
                'total' => (int) ($statsRow['total'] ?? 0),
                'avg_value' => (float) ($statsRow['avg_value'] ?? 0),
                'max_value' => (float) ($statsRow['max_value'] ?? 0),
                'avg_confidence' => (float) ($statsRow['avg_confidence'] ?? 0),
                'total_valuations' => (int) ($statsRow['total'] ?? 0),
                'avg_valuation' => (float) ($statsRow['avg_value'] ?? 0),
                'unique_properties' => (int) ($statsRow['unique_props'] ?? 0),
                'total_base_valuation' => (float) ($statsRow['total_base'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'avg_value' => 0, 'max_value' => 0, 'avg_confidence' => 0];
        }
    }

    /**
     * Generated valuation reports with optional filters
     */
    public function getReports(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['date_from'])) {
            $where[] = 'generated_at >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'generated_at <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['min_value'])) {
            $where[] = 'final_valuation >= ?';
            $params[] = $filters['min_value'];
        }
        if (!empty($filters['max_value'])) {
            $where[] = 'final_valuation <= ?';
            $params[] = $filters['max_value'];
        }
        $sqlWhere = implode(' AND ', array_map(fn($w) => $w === '1=1' ? '1=1' : 'r.' . $w, $where));
        try {
            return $this->db->fetchAll(
                "SELECT r.*, u.name AS generated_by_name FROM property_valuation_reports r
                 LEFT JOIN users u ON u.id = r.generated_by
                 WHERE " . $sqlWhere .
                " ORDER BY r.generated_at DESC LIMIT 100",
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function generateValuation($propertyId): array
    {
        return $this->estimateValue(['plot_id' => (int)$propertyId]);
    }

    public function storeValuation($propertyId, array $result, int $userId = 0): int
    {
        $area = (float)($result['area'] ?? 0);
        $basePps = (float)($result['base_price_per_sqft'] ?? 0);
        $conf = (float)($result['confidence'] ?? 0);
        return (int)$this->db->insert('property_valuation_reports', [
            'tenant_id' => 1,
            'property_id' => (int)$propertyId,
            'property_type' => 'plot',
            'area_sqft' => $area,
            'base_valuation' => round($basePps * $area, 2),
            'final_valuation' => (float)($result['estimated_value'] ?? 0),
            'confidence_score' => $conf > 1 ? $conf / 100 : $conf,
            'ai_analysis' => json_encode($result['factors'] ?? []),
            'market_analysis' => json_encode($result['market_comparison'] ?? []),
            'recommendations' => (string)($result['recommendation'] ?? ''),
            'comparable_properties' => json_encode($result['price_range'] ?? []),
            'generated_by' => $userId ?: null,
            'generated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getReport(int $id): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT r.*, u.name AS generated_by_name FROM property_valuation_reports r
             LEFT JOIN users u ON u.id = r.generated_by WHERE r.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function batchValuation(array $ids): array
    {
        $out = $this->bulkValuate($ids);
        return $out['results'] ?? [];
    }
}