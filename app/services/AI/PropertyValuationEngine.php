<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Security;

/**
 * APS Dream Home - AI Property Valuation Engine
 * Market Differentiator Feature - Phase 1 Priority 1
 */
class PropertyValuationEngine
{
    private $database;
    private $marketData;
    
    public function __construct()
    {
        $this->database = \App\Core\Database\Database::getInstance();
        $this->initializeMarketData();
    }
    
    /**
     * Calculate property valuation using AI algorithms
     */
    public function calculateValuation($propertyData)
    {
        // Base valuation factors
        $basePrice = $this->getBasePrice($propertyData['location'], $propertyData['type']);
        
        // AI-enhanced adjustments
        $locationMultiplier = $this->getLocationMultiplier($propertyData['location']);
        $marketTrendAdjustment = $this->getMarketTrendAdjustment($propertyData['location']);
        $propertyConditionScore = $this->getPropertyConditionScore($propertyData);
        $demandIndex = $this->getDemandIndex($propertyData['type'], $propertyData['location']);
        
        // Advanced AI calculation
        $aiScore = $this->calculateAIScore($propertyData);
        $comparableAnalysis = $this->getComparableAnalysis($propertyData);
        
        $valuation = $basePrice * $locationMultiplier * $marketTrendAdjustment * 
                   $propertyConditionScore * $demandIndex * $aiScore * $comparableAnalysis;
        
        return [
            'estimated_price' => round($valuation),
            'confidence_score' => $this->calculateConfidenceScore($propertyData),
            'market_analysis' => $this->getMarketAnalysis($propertyData),
            'recommendations' => $this->getRecommendations($propertyData, $valuation),
            'comparable_properties' => $this->getComparableProperties($propertyData)
        ];
    }
    
    /**
     * Generate comprehensive property valuation (for API compatibility)
     */
    public function generateValuation($propertyId)
    {
        try {
            // Get property data
            $property = $this->getPropertyData($propertyId);
            if (!$property) {
                return [
                    'success' => false,
                    'message' => 'Property not found'
                ];
            }
            
            // Use existing calculateValuation method
            $valuation = $this->calculateValuation($property);
            
            // Format for API response
            return [
                'success' => true,
                'data' => [
                    'property_id' => $propertyId,
                    'base_valuation' => round($valuation['estimated_price'] * 0.7, 2),
                    'location_multiplier' => $this->getLocationMultiplier($property['location']),
                    'type_multiplier' => $this->getPropertyTypeMultiplier($property),
                    'amenity_value' => round($valuation['estimated_price'] * 0.15, 2),
                    'market_adjustment' => round($this->getMarketTrendAdjustment($property['location']) * 100, 2) . '%',
                    'final_valuation' => round($valuation['estimated_price'], 2),
                    'confidence_score' => round($valuation['confidence_score'], 2),
                    'comparable_properties' => count($valuation['comparable_properties']),
                    'valuation_date' => date('Y-m-d H:i:s'),
                    'market_analysis' => $valuation['market_analysis'],
                    'recommendations' => $valuation['recommendations']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Valuation calculation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get base price for location and property type
     */
    private function getBasePrice($location, $type)
    {
        $basePrices = [
            'mumbai' => [
                'apartment' => 15000000,
                'house' => 25000000,
                'villa' => 50000000
            ],
            'delhi' => [
                'apartment' => 8000000,
                'house' => 15000000,
                'villa' => 30000000
            ],
            'bangalore' => [
                'apartment' => 6000000,
                'house' => 12000000,
                'villa' => 25000000
            ]
        ];
        
        return $basePrices[$location][$type] ?? 5000000;
    }
    
    /**
     * Calculate location multiplier based on market data
     */
    private function getLocationMultiplier($location)
    {
        $locationScores = [
            'mumbai' => 1.8,
            'delhi' => 1.4,
            'bangalore' => 1.3,
            'pune' => 1.1,
            'hyderabad' => 1.2
        ];
        
        return $locationScores[$location] ?? 1.0;
    }
    
    /**
     * Get market trend adjustment
     */
    private function getMarketTrendAdjustment($location)
    {
        // Simulated market trend data
        $marketTrends = [
            'mumbai' => 1.05,  // 5% growth
            'delhi' => 1.03,   // 3% growth
            'bangalore' => 1.07, // 7% growth
            'pune' => 1.04,    // 4% growth
            'hyderabad' => 1.06  // 6% growth
        ];
        
        return $marketTrends[$location] ?? 1.0;
    }
    
    /**
     * Calculate property condition score
     */
    private function getPropertyConditionScore($propertyData)
    {
        $conditionScores = [
            'excellent' => 1.15,
            'good' => 1.05,
            'average' => 1.0,
            'fair' => 0.95,
            'poor' => 0.85
        ];
        
        return $conditionScores[$propertyData['condition']] ?? 1.0;
    }
    
    /**
     * Get demand index for property type and location
     */
    private function getDemandIndex($type, $location)
    {
        $demandMatrix = [
            'mumbai' => ['apartment' => 1.2, 'house' => 1.1, 'villa' => 1.05],
            'delhi' => ['apartment' => 1.15, 'house' => 1.08, 'villa' => 1.03],
            'bangalore' => ['apartment' => 1.25, 'house' => 1.12, 'villa' => 1.08]
        ];
        
        return $demandMatrix[$location][$type] ?? 1.0;
    }
    
    /**
     * Advanced AI scoring algorithm
     */
    private function calculateAIScore($propertyData)
    {
        $score = 1.0;
        
        // Age factor
        $age = $propertyData['age'] ?? 0;
        if ($age < 5) $score *= 1.1;
        elseif ($age < 10) $score *= 1.05;
        elseif ($age < 20) $score *= 1.0;
        else $score *= 0.95;
        
        // Amenities factor
        $amenities = $propertyData['amenities'] ?? [];
        $amenityScore = min(1.2, 1.0 + (count($amenities) * 0.05));
        $score *= $amenityScore;
        
        // Size factor
        $size = $propertyData['size'] ?? 1000;
        if ($size > 2000) $score *= 1.1;
        elseif ($size > 1500) $score *= 1.05;
        elseif ($size > 1000) $score *= 1.0;
        else $score *= 0.95;
        
        return $score;
    }
    
    /**
     * Get comparable analysis
     */
    private function getComparableAnalysis($propertyData)
    {
        // Simulated comparable property analysis
        $comparableMultiplier = 1.0;
        
        // In real implementation, this would query actual database
        // For now, we simulate with market averages
        $comparableMultiplier += (rand(-5, 5) / 100);
        
        return max(0.9, min(1.1, $comparableMultiplier));
    }
    
    /**
     * Calculate confidence score for valuation
     */
    private function calculateConfidenceScore($propertyData)
    {
        $confidence = 0.85; // Base confidence
        
        // Increase confidence with more data
        if (!empty($propertyData['size'])) $confidence += 0.05;
        if (!empty($propertyData['age'])) $confidence += 0.03;
        if (!empty($propertyData['condition'])) $confidence += 0.04;
        if (!empty($propertyData['amenities'])) $confidence += 0.03;
        
        return min(0.98, $confidence);
    }
    
    /**
     * Get market analysis
     */
    private function getMarketAnalysis($propertyData)
    {
        return [
            'market_trend' => 'positive',
            'growth_rate' => '6.5%',
            'demand_level' => 'high',
            'inventory_level' => 'low',
            'average_days_on_market' => 45,
            'price_per_sqft' => $this->getPricePerSqft($propertyData['location'])
        ];
    }
    
    /**
     * Get investment recommendations
     */
    private function getRecommendations($propertyData, $valuation)
    {
        $recommendations = [];
        
        // Value assessment
        $marketPrice = $this->getMarketPrice($propertyData);
        if ($valuation > $marketPrice * 1.1) {
            $recommendations[] = 'Property appears overpriced - negotiate for better price';
        } elseif ($valuation < $marketPrice * 0.9) {
            $recommendations[] = 'Good investment opportunity - below market value';
        } else {
            $recommendations[] = 'Property priced fairly - good market value';
        }
        
        // Improvement recommendations
        if ($propertyData['condition'] === 'average') {
            $recommendations[] = 'Consider renovations to increase property value';
        }
        
        if (empty($propertyData['amenities']) || count($propertyData['amenities']) < 3) {
            $recommendations[] = 'Adding amenities can significantly increase value';
        }
        
        return $recommendations;
    }
    
    /**
     * Get comparable properties
     */
    private function getComparableProperties($propertyData)
    {
        // In real implementation, this would query database
        // For now, return simulated comparable properties
        return [
            [
                'id' => 1,
                'location' => $propertyData['location'],
                'type' => $propertyData['type'],
                'size' => $propertyData['size'] ?? 1000,
                'price' => $this->getBasePrice($propertyData['location'], $propertyData['type']) * 0.95,
                'condition' => 'good'
            ],
            [
                'id' => 2,
                'location' => $propertyData['location'],
                'type' => $propertyData['type'],
                'size' => $propertyData['size'] ?? 1000,
                'price' => $this->getBasePrice($propertyData['location'], $propertyData['type']) * 1.05,
                'condition' => 'excellent'
            ]
        ];
    }
    
    /**
     * Get valuation history for a property
     */
    public function getValuationHistory($propertyId, $limit = 10)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT * FROM property_valuations 
                WHERE property_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$propertyId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Batch valuation for multiple properties
     */
    public function batchValuation($propertyIds)
    {
        $results = [];
        foreach ($propertyIds as $propertyId) {
            $results[] = $this->generateValuation($propertyId);
        }
        return $results;
    }
    
    /**
     * Get property type multiplier
     */
    private function getPropertyTypeMultiplier($property)
    {
        $type = strtolower($property['property_type_name'] ?? 'residential');
        
        return $this->propertyTypeMultipliers[$type] ?? 1.0;
    }
    
    /**
     * Get property data from database
     */
    private function getPropertyData($propertyId)
    {
        $stmt = $this->database->prepare("
            SELECT p.*, pi.image_url, pt.type_name as property_type_name
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            LEFT JOIN property_types pt ON p.type = pt.id
            WHERE p.id = ?
        ");
        $stmt->execute([$propertyId]);
        return $stmt->fetch();
    }
    
    /**
     * Get price per square foot
     */
    private function getPricePerSqft($location)
    {
        $pricesPerSqft = [
            'mumbai' => 15000,
            'delhi' => 8000,
            'bangalore' => 6000,
            'pune' => 5500,
            'hyderabad' => 5000
        ];
        
        return $pricesPerSqft[$location] ?? 4000;
    }
    
    /**
     * Get market price for property
     */
    private function getMarketPrice($propertyData)
    {
        return $this->getBasePrice($propertyData['location'], $propertyData['type']);
    }
    
    /**
     * Initialize market data
     */
    private function initializeMarketData()
    {
        // In real implementation, this would fetch from external APIs
        $this->marketData = [
            'last_updated' => date('Y-m-d'),
            'sources' => ['magicbricks', '99acres', 'housing.com']
        ];
    }
}
