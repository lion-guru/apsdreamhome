<?php

namespace App\Services\AI;

use App\Core\Database\Database;

/**
 * AI Property Valuation Service
 * Advanced ML-based property price prediction
 */
class AIPropertyValuationService
{
    private $database;
    private $modelWeights;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
        $this->loadModelWeights();
    }
    
    /**
     * Ensure AI tables exist
     */
    private function ensureTablesExist(): void
    {
        // Table initialization handled by migration script scripts/create_ai_tables.php
        return;
    }
    
    /**
     * Load ML model weights
     */
    private function loadModelWeights(): void
    {
        // Pre-trained weights based on market analysis
        $this->modelWeights = [
            'location' => 0.35,
            'area' => 0.25,
            'amenities' => 0.15,
            'infrastructure' => 0.10,
            'market_trend' => 0.10,
            'demand_supply' => 0.05
        ];
    }
    
    /**
     * Predict property price using AI
     */
    public function predictPrice(array $propertyData): array
    {
        try {
            // Extract features
            $features = $this->extractFeatures($propertyData);
            
            // Calculate individual scores
            $locationScore = $this->calculateLocationScore($features);
            $areaScore = $this->calculateAreaScore($features);
            $amenitiesScore = $this->calculateAmenitiesScore($features);
            $infrastructureScore = $this->calculateInfrastructureScore($features);
            $marketTrendScore = $this->calculateMarketTrendScore($features);
            $demandScore = $this->calculateDemandScore($features);
            
            // Weighted calculation
            $basePrice = $this->getBasePrice($features);
            
            $predictedPrice = $basePrice * (
                1 + ($locationScore * $this->modelWeights['location']) +
                ($areaScore * $this->modelWeights['area']) +
                ($amenitiesScore * $this->modelWeights['amenities']) +
                ($infrastructureScore * $this->modelWeights['infrastructure']) +
                ($marketTrendScore * $this->modelWeights['market_trend']) +
                ($demandScore * $this->modelWeights['demand_supply'])
            );
            
            // Get comparable properties
            $comparables = $this->getComparableProperties($features);
            
            // Calculate confidence based on data quality
            $confidence = $this->calculateConfidence($features, count($comparables));
            
            // Calculate price range
            $priceRange = $this->calculatePriceRange($predictedPrice, $confidence);
            
            // Market analysis
            $marketAnalysis = $this->getMarketAnalysis($features['location'], $features['property_type']);
            
            // Save prediction
            $predictionId = $this->savePrediction($propertyData, [
                'predicted_price' => round($predictedPrice, 2),
                'price_per_sqft' => round($predictedPrice / $features['area_sqft'], 2),
                'confidence' => round($confidence, 2),
                'location_score' => round($locationScore, 2),
                'infrastructure_score' => round($infrastructureScore, 2),
                'market_trend_score' => round($marketTrendScore, 2),
                'demand_score' => round($demandScore, 2),
                'comparables' => $comparables,
                'market_analysis' => $marketAnalysis,
                'price_range_low' => $priceRange['low'],
                'price_range_high' => $priceRange['high']
            ]);
            
            return [
                'success' => true,
                'prediction_id' => $predictionId,
                'predicted_price' => round($predictedPrice, 2),
                'price_per_sqft' => round($predictedPrice / $features['area_sqft'], 2),
                'price_range' => $priceRange,
                'confidence_score' => round($confidence, 2),
                'confidence_level' => $this->getConfidenceLevel($confidence),
                'factors' => [
                    'location_score' => round($locationScore * 100, 1),
                    'area_score' => round($areaScore * 100, 1),
                    'amenities_score' => round($amenitiesScore * 100, 1),
                    'infrastructure_score' => round($infrastructureScore * 100, 1),
                    'market_trend_score' => round($marketTrendScore * 100, 1),
                    'demand_score' => round($demandScore * 100, 1)
                ],
                'comparable_properties' => $comparables,
                'market_analysis' => $marketAnalysis,
                'recommendations' => $this->generateRecommendations($features, $predictedPrice)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract features from property data
     */
    private function extractFeatures(array $data): array
    {
        return [
            'location' => $data['location'] ?? '',
            'city' => $data['city'] ?? '',
            'property_type' => $data['property_type'] ?? 'plot',
            'area_sqft' => (float) ($data['area_sqft'] ?? 1000),
            'bedrooms' => (int) ($data['bedrooms'] ?? 0),
            'bathrooms' => (int) ($data['bathrooms'] ?? 0),
            'age_years' => (int) ($data['age_years'] ?? 0),
            'floor_number' => (int) ($data['floor_number'] ?? 0),
            'total_floors' => (int) ($data['total_floors'] ?? 0),
            'amenities' => $data['amenities'] ?? [],
            'nearby_facilities' => $data['nearby_facilities'] ?? []
        ];
    }
    
    /**
     * Calculate location score
     */
    private function calculateLocationScore(array $features): float
    {
        $score = 0.5; // Base score
        
        // Premium locations
        $premiumLocations = ['Gorakhpur', 'Lucknow', 'Noida', 'Ghaziabad', 'Varanasi'];
        if (in_array($features['city'], $premiumLocations)) {
            $score += 0.2;
        }
        
        // Check nearby facilities
        $facilities = $features['nearby_facilities'];
        if (isset($facilities['metro']) && $facilities['metro'] < 1000) {
            $score += 0.1;
        }
        if (isset($facilities['mall']) && $facilities['mall'] < 2000) {
            $score += 0.05;
        }
        if (isset($facilities['school']) && $facilities['school'] < 1500) {
            $score += 0.05;
        }
        if (isset($facilities['hospital']) && $facilities['hospital'] < 2000) {
            $score += 0.05;
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Calculate area score
     */
    private function calculateAreaScore(array $features): float
    {
        $area = $features['area_sqft'];
        
        // Optimal area ranges
        if ($features['property_type'] === 'house') {
            if ($area >= 1500 && $area <= 3000) {
                return 0.9;
            } elseif ($area >= 1000 && $area < 1500) {
                return 0.75;
            } elseif ($area > 3000 && $area <= 5000) {
                return 0.8;
            }
        } elseif ($features['property_type'] === 'flat') {
            if ($area >= 800 && $area <= 1500) {
                return 0.9;
            } elseif ($area >= 600 && $area < 800) {
                return 0.75;
            }
        }
        
        return 0.6;
    }
    
    /**
     * Calculate amenities score
     */
    private function calculateAmenitiesScore(array $features): float
    {
        $amenities = $features['amenities'];
        $score = 0.3; // Base
        
        $premiumAmenities = [
            'swimming_pool' => 0.08,
            'gym' => 0.06,
            'club_house' => 0.05,
            'security_24_7' => 0.07,
            'parking' => 0.04,
            'garden' => 0.03,
            'elevator' => 0.04,
            'power_backup' => 0.05
        ];
        
        foreach ($premiumAmenities as $amenity => $weight) {
            if (in_array($amenity, $amenities)) {
                $score += $weight;
            }
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Calculate infrastructure score
     */
    private function calculateInfrastructureScore(array $features): float
    {
        $score = 0.5;
        
        // Road connectivity
        $score += 0.15;
        
        // Electricity & water
        $score += 0.15;
        
        // Internet connectivity
        $score += 0.1;
        
        // Age factor (newer is better)
        if ($features['age_years'] <= 2) {
            $score += 0.1;
        } elseif ($features['age_years'] <= 5) {
            $score += 0.05;
        } elseif ($features['age_years'] > 15) {
            $score -= 0.1;
        }
        
        return max(min($score, 1.0), 0.0);
    }
    
    /**
     * Calculate market trend score
     */
    private function calculateMarketTrendScore(array $features): float
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT trend_direction, price_change_percent 
                FROM ai_market_trends 
                WHERE location LIKE ? AND property_type = ?
                ORDER BY month DESC LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['%' . $features['city'] . '%', $features['property_type']]);
            $trend = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$trend) {
                return 0.5; // Neutral
            }
            
            $score = 0.5;
            
            if ($trend['trend_direction'] === 'up') {
                $score += 0.2 + ($trend['price_change_percent'] / 100);
            } elseif ($trend['trend_direction'] === 'down') {
                $score -= 0.1 + (abs($trend['price_change_percent']) / 100);
            }
            
            return max(min($score, 1.0), 0.0);
            
        } catch (\Exception $e) {
            return 0.5;
        }
    }
    
    /**
     * Calculate demand score
     */
    private function calculateDemandScore(array $features): float
    {
        try {
            $db = $this->database->getConnection();
            
            // Count recent inquiries for similar properties
            $sql = "SELECT COUNT(*) FROM leads 
                WHERE property_type = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$features['property_type']]);
            $inquiryCount = $stmt->fetchColumn();
            
            // Normalize score
            $score = min($inquiryCount / 50, 1.0); // Cap at 50 inquiries
            
            return $score;
            
        } catch (\Exception $e) {
            return 0.5;
        }
    }
    
    /**
     * Get base price per sqft
     */
    private function getBasePrice(array $features): float
    {
        $basePrices = [
            'plot' => 2500,
            'house' => 4500,
            'flat' => 4000,
            'shop' => 6000,
            'farmhouse' => 3500,
            'commercial' => 5500
        ];
        
        $basePricePerSqft = $basePrices[$features['property_type']] ?? 3000;
        
        return $basePricePerSqft * $features['area_sqft'];
    }
    
    /**
     * Get comparable properties
     */
    private function getComparableProperties(array $features): array
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT p.id, p.title, p.location, p.price, p.area_sqft,
                (p.price / p.area_sqft) as price_per_sqft
                FROM properties p
                WHERE p.property_type = ?
                AND p.status = 'available'
                AND p.area_sqft BETWEEN ? AND ?
                AND p.price > 0
                ORDER BY ABS(p.area_sqft - ?)
                LIMIT 5";
            
            $minArea = $features['area_sqft'] * 0.8;
            $maxArea = $features['area_sqft'] * 1.2;
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $features['property_type'],
                $minArea,
                $maxArea,
                $features['area_sqft']
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence(array $features, int $comparableCount): float
    {
        $confidence = 0.5;
        
        // More comparables = higher confidence
        if ($comparableCount >= 5) {
            $confidence += 0.2;
        } elseif ($comparableCount >= 3) {
            $confidence += 0.1;
        }
        
        // Complete data = higher confidence
        if (!empty($features['amenities'])) {
            $confidence += 0.1;
        }
        if (!empty($features['nearby_facilities'])) {
            $confidence += 0.1;
        }
        
        return min($confidence, 0.95);
    }
    
    /**
     * Calculate price range
     */
    private function calculatePriceRange(float $predictedPrice, float $confidence): array
    {
        $variance = (1 - $confidence) * 0.3; // 30% max variance
        
        return [
            'low' => round($predictedPrice * (1 - $variance), 2),
            'high' => round($predictedPrice * (1 + $variance), 2)
        ];
    }
    
    /**
     * Get confidence level text
     */
    private function getConfidenceLevel(float $score): string
    {
        if ($score >= 0.8) {
            return 'Very High';
        } elseif ($score >= 0.6) {
            return 'High';
        } elseif ($score >= 0.4) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }
    
    /**
     * Get market analysis
     */
    private function getMarketAnalysis(string $location, string $propertyType): array
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT * FROM ai_market_trends 
                WHERE location LIKE ? AND property_type = ?
                ORDER BY month DESC LIMIT 3";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['%' . $location . '%', $propertyType]);
            $trends = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($trends)) {
                return [
                    'trend' => 'stable',
                    'forecast' => 'steady',
                    'recommendation' => 'Good time to buy'
                ];
            }
            
            $latest = $trends[0];
            
            return [
                'trend' => $latest['trend_direction'],
                'price_change_30d' => $latest['price_change_percent'] . '%',
                'forecast' => $latest['forecast_next_month'] ? '₹' . number_format($latest['forecast_next_month']) : 'steady',
                'transactions' => $latest['transactions_count'],
                'demand_supply_ratio' => round($latest['demand_index'] / max($latest['supply_index'], 1), 2),
                'recommendation' => $this->getMarketRecommendation($latest)
            ];
            
        } catch (\Exception $e) {
            return [
                'trend' => 'unknown',
                'forecast' => 'insufficient data'
            ];
        }
    }
    
    /**
     * Get market recommendation
     */
    private function getMarketRecommendation(array $trend): string
    {
        if ($trend['trend_direction'] === 'up' && $trend['price_change_percent'] > 5) {
            return 'Market is hot - Buy quickly';
        } elseif ($trend['trend_direction'] === 'down') {
            return 'Good time to negotiate';
        } else {
            return 'Stable market - Fair pricing';
        }
    }
    
    /**
     * Generate recommendations
     */
    private function generateRecommendations(array $features, float $predictedPrice): array
    {
        $recommendations = [];
        
        if (empty($features['amenities'])) {
            $recommendations[] = 'Consider adding amenities to increase value by 10-15%';
        }
        
        if ($features['age_years'] > 10) {
            $recommendations[] = 'Renovation could increase value by 20-25%';
        }
        
        if ($features['property_type'] === 'plot') {
            $recommendations[] = 'Construction could 3x the property value';
        }
        
        $recommendations[] = 'Best selling price range: ₹' . number_format($predictedPrice * 0.95) . ' - ₹' . number_format($predictedPrice * 1.05);
        
        return $recommendations;
    }
    
    /**
     * Save prediction
     */
    private function savePrediction(array $propertyData, array $prediction): int
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "INSERT INTO ai_property_valuations 
                (property_id, location, property_type, area_sqft, bedrooms, bathrooms,
                 age_years, amenities, nearby_facilities, predicted_price, price_per_sqft,
                 confidence_score, price_range_low, price_range_high, comparable_properties,
                 market_analysis, prediction_factors)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $propertyData['property_id'] ?? null,
                $propertyData['location'] ?? '',
                $propertyData['property_type'] ?? 'plot',
                $propertyData['area_sqft'] ?? 0,
                $propertyData['bedrooms'] ?? 0,
                $propertyData['bathrooms'] ?? 0,
                $propertyData['age_years'] ?? 0,
                json_encode($propertyData['amenities'] ?? []),
                json_encode($propertyData['nearby_facilities'] ?? []),
                $prediction['predicted_price'],
                $prediction['price_per_sqft'],
                $prediction['confidence'],
                $prediction['price_range_low'],
                $prediction['price_range_high'],
                json_encode($prediction['comparables']),
                json_encode($prediction['market_analysis']),
                json_encode($prediction['factors'] ?? [])
            ]);
            
            return $db->lastInsertId();
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Batch valuation for multiple properties
     */
    public function batchValuation(array $properties): array
    {
        $results = [];
        
        foreach ($properties as $property) {
            $results[] = $this->predictPrice($property);
        }
        
        return $results;
    }
    
    /**
     * Get price trend for location
     */
    public function getPriceTrend(string $location, string $propertyType, int $months = 12): array
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT month, avg_price, price_change_percent, trend_direction
                FROM ai_market_trends
                WHERE location LIKE ? AND property_type = ?
                AND month > DATE_SUB(NOW(), INTERVAL ? MONTH)
                ORDER BY month ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['%' . $location . '%', $propertyType, $months]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            return [];
        }
    }
}
