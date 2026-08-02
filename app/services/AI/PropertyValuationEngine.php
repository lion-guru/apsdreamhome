<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Security;
use App\Traits\ServiceTenantTrait;

/**
 * APS Dream Home - AI Property Valuation Engine
 * Market Differentiator Feature - Phase 1 Priority 1
 */
class PropertyValuationEngine
{
    use ServiceTenantTrait;

    private $database;
    private $marketData;
    private $propertyTypeMultipliers;
    
    public function __construct()
    {
        $this->database = \App\Core\Database\Database::getInstance();
        $this->propertyTypeMultipliers = [
            'plot' => 1.0, 'house' => 1.25, 'flat' => 1.15, 'shop' => 1.45, 'farmhouse' => 1.35, 'commercial' => 1.5, 'residential' => 1.1
        ];
        $this->initializeMarketData();
    }
    
    /**
     * Calculate property valuation using AI algorithms
     */
    public function calculateValuation($propertyData)
    {
        // Normalise required keys so downstream accessors never hit undefined-key warnings.
        $propertyData = is_array($propertyData) ? $propertyData : [];
        $propertyData['location'] = $propertyData['location'] ?? ($propertyData['city'] ?? ($propertyData['city_name'] ?? 'default'));
        $propertyData['type'] = $propertyData['type'] ?? ($propertyData['property_type'] ?? 'plot');
        $propertyData['condition'] = $propertyData['condition'] ?? 'good';
        $propertyData['amenities'] = $propertyData['amenities'] ?? [];

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
            
        } catch (\Throwable $e) {
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
            'gorakhpur' => ['apartment' => 3000000, 'house' => 5500000, 'villa' => 9000000],
            'lucknow' => ['apartment' => 4500000, 'house' => 7500000, 'villa' => 12000000],
            'kushinagar' => ['apartment' => 1800000, 'house' => 3200000, 'villa' => 5000000],
            'varanasi' => ['apartment' => 4000000, 'house' => 7000000, 'villa' => 11000000]
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        return $basePrices[$loc][$type] ?? 2500000;
    }
    
    /**
     * Calculate location multiplier based on market data
     */
    private function getLocationMultiplier($location)
    {
        $locationScores = [
            'lucknow' => 1.35,
            'varanasi' => 1.25,
            'gorakhpur' => 1.15,
            'kushinagar' => 0.95
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        return $locationScores[$loc] ?? 1.0;
    }
    
    /**
     * Get market trend adjustment
     */
    private function getMarketTrendAdjustment($location)
    {
        $marketTrends = [
            'lucknow' => 1.07,
            'varanasi' => 1.05,
            'gorakhpur' => 1.06,
            'kushinagar' => 1.02
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        return $marketTrends[$loc] ?? 1.0;
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
            'lucknow' => ['apartment' => 1.15, 'house' => 1.20, 'villa' => 1.10],
            'varanasi' => ['apartment' => 1.10, 'house' => 1.15, 'villa' => 1.08],
            'gorakhpur' => ['apartment' => 1.18, 'house' => 1.25, 'villa' => 1.12],
            'kushinagar' => ['apartment' => 0.95, 'house' => 1.05, 'villa' => 0.98]
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        return $demandMatrix[$loc][$type] ?? 1.0;
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
                WHERE property_id = ?" . $this->tenantSql() . "
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $params = [$propertyId, $limit];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
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
            'lucknow' => 3500,
            'varanasi' => 3000,
            'gorakhpur' => 2800,
            'kushinagar' => 1800
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        return $pricesPerSqft[$loc] ?? 2200;
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

    /**
     * Income-Approach Valuation
     * Professional real estate appraisal: rental yield × 12 as property value component
     * Combined with 5-factor weighted formula: location 25%, condition 20%, comparables 30%, income 15%, market 10%
     */
    public function calculateIncomeValuation($propertyData): array
    {
        $monthlyRent = $propertyData['expected_rent'] ?? 0;
        $propertyAge = $propertyData['age'] ?? 0;
        $size = $propertyData['size'] ?? 1000;
        $location = $propertyData['location'] ?? '';

        // Rental yield calculation: annual rent / property value
        // In India, typical rental yield is 2-4% for residential, 6-9% for commercial
        $isCommercial = in_array(strtolower($propertyData['type'] ?? ''), ['commercial', 'shop', 'office']);

        // Expected rental yield by location (annual %)
        $rentalYields = [
            'lucknow'    => $isCommercial ? 0.075 : 0.030,
            'varanasi'   => $isCommercial ? 0.068 : 0.028,
            'gorakhpur'  => $isCommercial ? 0.072 : 0.032,
            'kushinagar' => $isCommercial ? 0.065 : 0.025,
        ];
        $loc = strtolower(trim(explode(',', $location)[0]));
        $expectedYield = $rentalYields[$loc] ?? ($isCommercial ? 0.07 : 0.028);

        // Income-based value: if monthly rent = X, what property price gives expected yield?
        $annualRent = $monthlyRent * 12;
        $incomeBasedValue = $expectedYield > 0 ? $annualRent / $expectedYield : 0;

        // Age depreciation: 1.5% per year, max 30% reduction
        $depreciationRate = min(0.30, $propertyAge * 0.015);
        $conditionMultiplier = 1.0 - $depreciationRate;

        // Size-based valuation (area × location rate)
        $pricePerSqft = $this->getPricePerSqft($location);
        $sizeBasedValue = $size * $pricePerSqft;

        // 5-factor weighted formula
        $locationScore = $this->getLocationMultiplier($location);
        $conditionScore = $conditionMultiplier;
        $comparableScore = $this->getComparableAnalysis($propertyData);
        $incomeScore = $incomeBasedValue > 0 ? 1.0 : 0.0;
        $marketScore = $this->getMarketTrendAdjustment($location);

        // Weighted factors: location 25%, condition 20%, comparables 30%, income 15%, market 10%
        $weightedScore = ($locationScore * 0.25)
            + ($conditionScore * 0.20)
            + ($comparableScore * 0.30)
            + ($incomeScore * 0.15)
            + ($marketScore * 0.10);

        // Final valuation: blend income-based and size-based
        $baseValuation = $incomeBasedValue > 0
            ? ($incomeBasedValue * 0.6) + ($sizeBasedValue * 0.4)
            : $sizeBasedValue;

        $finalValuation = $baseValuation * $weightedScore;

        // Confidence score
        $confidence = 0.5;
        if ($monthlyRent > 0) $confidence += 0.2;
        if ($propertyAge > 0) $confidence += 0.1;
        if (count($this->getComparableProperties($propertyData)) > 0) $confidence += 0.2;

        // Monthly rental income estimate
        $estimatedMonthlyRent = $monthlyRent > 0
            ? $monthlyRent
            : round($finalValuation * $expectedYield / 12);

        return [
            'method' => 'income_approach',
            'estimated_price' => round($finalValuation),
            'income_based_value' => round($incomeBasedValue),
            'size_based_value' => round($sizeBasedValue),
            'expected_rental_yield' => round($expectedYield * 100, 2) . '%',
            'estimated_monthly_rent' => $estimatedMonthlyRent,
            'annual_rental_income' => round($estimatedMonthlyRent * 12),
            'condition_score' => round($conditionScore, 2),
            'age_depreciation' => round($depreciationRate * 100, 1) . '%',
            'weighted_factors' => [
                'location'   => round($locationScore, 3) . ' (25%)',
                'condition'  => round($conditionScore, 3) . ' (20%)',
                'comparables'=> round($comparableScore, 3) . ' (30%)',
                'income'     => round($incomeScore, 3) . ' (15%)',
                'market'     => round($marketScore, 3) . ' (10%)',
            ],
            'confidence' => round(min(1.0, $confidence), 2),
            'is_commercial' => $isCommercial,
        ];
    }

    /**
     * Get 5-factor valuation breakdown (for API/dashboard display)
     */
    public function getValuationBreakdown($propertyData): array
    {
        $income = $this->calculateIncomeValuation($propertyData);
        $traditional = $this->calculateValuation($propertyData);

        return [
            'income_approach' => $income,
            'traditional_approach' => [
                'estimated_price' => $traditional['estimated_price'],
                'confidence_score' => $traditional['confidence_score'],
            ],
            'recommended_price' => round(
                ($income['estimated_price'] * 0.5) + ($traditional['estimated_price'] * 0.5)
            ),
            'price_range' => [
                'low' => round($income['estimated_price'] * 0.85),
                'high' => round($income['estimated_price'] * 1.15),
            ],
        ];
    }
}
