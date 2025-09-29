<?php
/**
 * AI-Powered Market Analysis and Prediction System
 * Advanced analytics for real estate market trends and price predictions
 */

class AIMarketAnalyzer {
    private $conn;
    private $config;
    private $historicalData = [];
    private $marketIndicators = [];

    // Market analysis periods
    private $analysisPeriods = [
        '1_month' => 30,
        '3_months' => 90,
        '6_months' => 180,
        '1_year' => 365,
        '2_years' => 730
    ];

    /**
     * Constructor
     */
    public function __construct($conn, $config = []) {
        $this->conn = $conn;
        $this->config = array_merge([
            'enable_predictions' => true,
            'prediction_horizon' => 12, // months
            'confidence_threshold' => 0.7,
            'data_points_required' => 10
        ], $config);

        $this->loadMarketIndicators();
    }

    /**
     * Get comprehensive market analysis for a location
     */
    public function getMarketAnalysis($location = null, $propertyType = null) {
        $analysis = [
            'location' => $location,
            'property_type' => $propertyType,
            'current_metrics' => $this->getCurrentMarketMetrics($location, $propertyType),
            'price_trends' => $this->analyzePriceTrends($location, $propertyType),
            'market_health' => $this->assessMarketHealth($location, $propertyType),
            'predictions' => $this->generatePricePredictions($location, $propertyType),
            'comparative_analysis' => $this->getComparativeAnalysis($location, $propertyType),
            'investment_insights' => $this->getInvestmentInsights($location, $propertyType)
        ];

        $analysis['overall_score'] = $this->calculateOverallMarketScore($analysis);
        $analysis['recommendation'] = $this->generateMarketRecommendation($analysis);

        return $analysis;
    }

    /**
     * Get current market metrics
     */
    private function getCurrentMarketMetrics($location, $propertyType) {
        try {
            $whereClause = "WHERE p.status = 'active'";
            $params = [];
            $types = "";

            if ($location) {
                $whereClause .= " AND (p.location LIKE ? OR p.city LIKE ?)";
                $params[] = "%$location%";
                $params[] = "%$location%";
                $types .= "ss";
            }

            if ($propertyType) {
                $whereClause .= " AND pt.name LIKE ?";
                $params[] = "%$propertyType%";
                $types .= "s";
            }

            $sql = "SELECT
                        COUNT(*) as total_properties,
                        AVG(p.price) as avg_price,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price,
                        AVG(p.bedrooms) as avg_bedrooms,
                        AVG(p.bathrooms) as avg_bathrooms,
                        AVG(p.area_sqft) as avg_area,
                        COUNT(CASE WHEN p.featured = 1 THEN 1 END) as featured_count,
                        (SELECT COUNT(*) FROM property_inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_inquiries,
                        (SELECT COUNT(*) FROM property_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_views
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    $whereClause";

            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $metrics = $result->fetch_assoc();

            // Calculate additional metrics
            $metrics['avg_price_per_sqft'] = $metrics['avg_area'] > 0 ?
                $metrics['avg_price'] / $metrics['avg_area'] : 0;

            $metrics['price_range'] = [
                'budget' => $metrics['avg_price'] * 0.7,
                'mid_range' => $metrics['avg_price'],
                'luxury' => $metrics['avg_price'] * 1.5
            ];

            $metrics['demand_indicators'] = [
                'inquiry_rate' => $metrics['recent_inquiries'] / max($metrics['total_properties'], 1),
                'view_rate' => $metrics['recent_views'] / max($metrics['total_properties'], 1),
                'featured_ratio' => $metrics['featured_count'] / max($metrics['total_properties'], 1)
            ];

            return $metrics;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Analyze price trends over different periods
     */
    private function analyzePriceTrends($location, $propertyType) {
        $trends = [];

        foreach ($this->analysisPeriods as $periodName => $days) {
            $trend = $this->calculatePriceTrend($location, $propertyType, $days);
            $trends[$periodName] = $trend;
        }

        return $trends;
    }

    /**
     * Calculate price trend for a specific period
     */
    private function calculatePriceTrend($location, $propertyType, $days) {
        try {
            $whereClause = "WHERE p.status = 'active' AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params = [$days];
            $types = "i";

            if ($location) {
                $whereClause .= " AND (p.location LIKE ? OR p.city LIKE ?)";
                $params[] = "%$location%";
                $params[] = "%$location%";
                $types .= "ss";
            }

            if ($propertyType) {
                $whereClause .= " AND pt.name LIKE ?";
                $params[] = "%$propertyType%";
                $types .= "s";
            }

            $sql = "SELECT
                        AVG(p.price) as avg_price,
                        COUNT(*) as property_count,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    $whereClause";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentData = $result->fetch_assoc();

            // Get data from previous period for comparison
            $prevDays = $days * 2;
            $prevWhereClause = str_replace(">= DATE_SUB(NOW(), INTERVAL ? DAY)", ">= DATE_SUB(NOW(), INTERVAL ? DAY)", $whereClause);
            $prevParams = [$prevDays];
            if ($location) {
                $prevParams[] = "%$location%";
                $prevParams[] = "%$location%";
            }
            if ($propertyType) {
                $prevParams[] = "%$propertyType%";
            }

            $prevSql = "SELECT AVG(p.price) as avg_price FROM properties p
                       LEFT JOIN property_types pt ON p.property_type_id = pt.id
                       $prevWhereClause AND p.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

            $prevStmt = $this->conn->prepare($prevSql);
            $prevStmt->bind_param($types, ...$prevParams);
            $prevStmt->execute();
            $prevResult = $prevStmt->get_result();
            $prevData = $prevResult->fetch_assoc();

            $currentPrice = $currentData['avg_price'] ?? 0;
            $prevPrice = $prevData['avg_price'] ?? $currentPrice;

            $change = $prevPrice > 0 ? (($currentPrice - $prevPrice) / $prevPrice) * 100 : 0;
            $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable');

            return [
                'period' => $periodName ?? "$days days",
                'current_avg_price' => $currentPrice,
                'previous_avg_price' => $prevPrice,
                'change_percentage' => round($change, 2),
                'direction' => $direction,
                'volume' => $currentData['property_count'] ?? 0,
                'confidence' => $this->calculateTrendConfidence($currentData, $prevData)
            ];

        } catch (Exception $e) {
            return [
                'period' => $periodName ?? "$days days",
                'error' => $e->getMessage(),
                'direction' => 'unknown',
                'confidence' => 0
            ];
        }
    }

    /**
     * Calculate confidence level for trend analysis
     */
    private function calculateTrendConfidence($currentData, $prevData) {
        $currentVolume = $currentData['property_count'] ?? 0;
        $prevVolume = $prevData['property_count'] ?? 0;

        if ($currentVolume < 5 || $prevVolume < 5) {
            return 0.3; // Low confidence with insufficient data
        }

        $volumeStability = 1 - abs($currentVolume - $prevVolume) / max($currentVolume, $prevVolume);
        $priceStability = $prevData['avg_price'] > 0 ? 1 - abs($currentData['avg_price'] - $prevData['avg_price']) / $prevData['avg_price'] : 0.5;

        return min(($volumeStability + $priceStability) / 2, 1.0);
    }

    /**
     * Assess overall market health
     */
    private function assessMarketHealth($location, $propertyType) {
        $metrics = $this->getCurrentMarketMetrics($location, $propertyType);

        if (isset($metrics['error'])) {
            return ['error' => $metrics['error']];
        }

        $healthScore = 0;
        $factors = [];

        // Volume factor (20%)
        $volumeScore = min($metrics['total_properties'] / 50, 1) * 20;
        $healthScore += $volumeScore;
        $factors['volume'] = [
            'score' => $volumeScore,
            'description' => 'Market volume and liquidity'
        ];

        // Demand factor (25%)
        $demandRate = ($metrics['demand_indicators']['inquiry_rate'] + $metrics['demand_indicators']['view_rate']) / 2;
        $demandScore = min($demandRate * 10, 1) * 25;
        $healthScore += $demandScore;
        $factors['demand'] = [
            'score' => $demandScore,
            'description' => 'Buyer demand and interest levels'
        ];

        // Price stability factor (20%)
        $priceStability = $this->calculatePriceStability($location, $propertyType);
        $stabilityScore = $priceStability * 20;
        $healthScore += $stabilityScore;
        $factors['stability'] = [
            'score' => $stabilityScore,
            'description' => 'Price stability and predictability'
        ];

        // Quality factor (20%)
        $qualityScore = min($metrics['demand_indicators']['featured_ratio'] * 20, 20);
        $healthScore += $qualityScore;
        $factors['quality'] = [
            'score' => $qualityScore,
            'description' => 'Quality of properties in market'
        ];

        // Growth factor (15%)
        $recentTrend = $this->calculatePriceTrend($location, $propertyType, 30);
        $growthScore = $recentTrend['direction'] === 'up' ? 15 : ($recentTrend['direction'] === 'stable' ? 10 : 5);
        $healthScore += $growthScore;
        $factors['growth'] = [
            'score' => $growthScore,
            'description' => 'Recent market growth trends'
        ];

        return [
            'overall_score' => round($healthScore, 1),
            'rating' => $this->getHealthRating($healthScore),
            'factors' => $factors,
            'recommendations' => $this->getHealthRecommendations($factors, $healthScore)
        ];
    }

    /**
     * Get health rating based on score
     */
    private function getHealthRating($score) {
        if ($score >= 80) return 'Excellent';
        if ($score >= 65) return 'Good';
        if ($score >= 50) return 'Fair';
        if ($score >= 35) return 'Poor';
        return 'Critical';
    }

    /**
     * Get health recommendations
     */
    private function getHealthRecommendations($factors, $overallScore) {
        $recommendations = [];

        foreach ($factors as $factor => $data) {
            if ($data['score'] < 10) {
                switch ($factor) {
                    case 'volume':
                        $recommendations[] = 'Consider expanding search to nearby areas for more options';
                        break;
                    case 'demand':
                        $recommendations[] = 'Market demand is low - consider waiting for better conditions';
                        break;
                    case 'stability':
                        $recommendations[] = 'High price volatility - exercise caution with investments';
                        break;
                    case 'quality':
                        $recommendations[] = 'Limited quality properties - consider professional inspection';
                        break;
                    case 'growth':
                        $recommendations[] = 'Market showing downward trend - timing may not be ideal';
                        break;
                }
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Market conditions are favorable for investment';
        }

        return $recommendations;
    }

    /**
     * Calculate price stability index
     */
    private function calculatePriceStability($location, $propertyType) {
        $trends = $this->analyzePriceTrends($location, $propertyType);

        $volatility = 0;
        $trendChanges = 0;

        foreach ($trends as $trend) {
            $volatility += abs($trend['change_percentage']);
            if ($trend['direction'] !== 'stable') {
                $trendChanges++;
            }
        }

        $avgVolatility = $volatility / count($trends);
        $stabilityIndex = max(0, 1 - ($avgVolatility / 20)); // Normalize to 0-1 scale

        return $stabilityIndex;
    }

    /**
     * Generate price predictions using AI
     */
    private function generatePricePredictions($location, $propertyType) {
        if (!$this->config['enable_predictions']) {
            return ['predictions_disabled' => true];
        }

        try {
            // Get historical price data
            $historicalPrices = $this->getHistoricalPriceData($location, $propertyType);

            if (count($historicalPrices) < $this->config['data_points_required']) {
                return [
                    'error' => 'Insufficient historical data for predictions',
                    'required_points' => $this->config['data_points_required'],
                    'available_points' => count($historicalPrices)
                ];
            }

            // Apply linear regression for trend analysis
            $predictions = $this->predictPriceTrend($historicalPrices);

            // Apply market indicators adjustment
            $adjustedPredictions = $this->adjustPredictionsForMarketConditions($predictions, $location);

            return [
                'predictions' => $adjustedPredictions,
                'confidence' => $this->calculatePredictionConfidence($historicalPrices),
                'methodology' => 'Linear regression with market indicators',
                'disclaimer' => 'Predictions are estimates based on historical data and market trends'
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get historical price data
     */
    private function getHistoricalPriceData($location, $propertyType, $months = 24) {
        try {
            $whereClause = "WHERE p.status = 'active'";
            $params = [];
            $types = "";

            if ($location) {
                $whereClause .= " AND (p.location LIKE ? OR p.city LIKE ?)";
                $params[] = "%$location%";
                $params[] = "%$location%";
                $types .= "ss";
            }

            if ($propertyType) {
                $whereClause .= " AND pt.name LIKE ?";
                $params[] = "%$propertyType%";
                $types .= "s";
            }

            $sql = "SELECT
                        DATE_FORMAT(p.created_at, '%Y-%m') as period,
                        AVG(p.price) as avg_price,
                        COUNT(*) as property_count
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    $whereClause
                    AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                    GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
                    ORDER BY period";

            $stmt = $this->conn->prepare($sql);
            $params[] = $months;
            $types .= "i";
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $historicalData = [];
            while ($row = $result->fetch_assoc()) {
                $historicalData[] = $row;
            }

            return $historicalData;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Predict price trends using linear regression
     */
    private function predictPriceTrend($historicalData) {
        $predictions = [];

        // Simple linear regression implementation
        $n = count($historicalData);
        $sumX = $sumY = $sumXY = $sumX2 = 0;

        foreach ($historicalData as $index => $data) {
            $x = $index;
            $y = $data['avg_price'];

            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $lastPrice = end($historicalData)['avg_price'];

        // Generate predictions for next 12 months
        for ($i = 1; $i <= $this->config['prediction_horizon']; $i++) {
            $predictedPrice = $intercept + $slope * ($n + $i - 1);
            $changeFromLast = (($predictedPrice - $lastPrice) / $lastPrice) * 100;

            $predictions[] = [
                'month' => date('M Y', strtotime("+$i months")),
                'predicted_price' => max($predictedPrice, $lastPrice * 0.8), // Don't predict below 80% of last price
                'change_percentage' => round($changeFromLast, 2),
                'confidence' => max(0.5, 1 - ($i * 0.05)) // Confidence decreases over time
            ];
        }

        return $predictions;
    }

    /**
     * Adjust predictions based on market conditions
     */
    private function adjustPredictionsForMarketConditions($predictions, $location) {
        $marketIndicators = $this->getMarketIndicators($location);

        foreach ($predictions as &$prediction) {
            $adjustment = 0;

            // Apply market indicator adjustments
            foreach ($marketIndicators as $indicator) {
                if ($indicator['impact'] === 'positive') {
                    $adjustment += $indicator['weight'] * 0.02; // 2% positive adjustment
                } else {
                    $adjustment -= $indicator['weight'] * 0.01; // 1% negative adjustment
                }
            }

            $prediction['predicted_price'] *= (1 + $adjustment);
            $prediction['adjustment'] = round($adjustment * 100, 2);
        }

        return $predictions;
    }

    /**
     * Get market indicators for a location
     */
    private function getMarketIndicators($location) {
        // Load market indicators from config or database
        $indicators = $this->marketIndicators;

        // Filter by location if specified
        if ($location) {
            $locationIndicators = array_filter($indicators, function($indicator) use ($location) {
                return stripos($indicator['location'], $location) !== false;
            });
            return $locationIndicators ?: $indicators;
        }

        return $indicators;
    }

    /**
     * Load market indicators
     */
    private function loadMarketIndicators() {
        // These would typically come from a database or external API
        $this->marketIndicators = [
            [
                'location' => 'mumbai',
                'indicator' => 'Infrastructure Development',
                'impact' => 'positive',
                'weight' => 0.8,
                'description' => 'Metro expansion and infrastructure projects'
            ],
            [
                'location' => 'bangalore',
                'indicator' => 'IT Sector Growth',
                'impact' => 'positive',
                'weight' => 0.9,
                'description' => 'Strong IT industry driving demand'
            ],
            [
                'location' => 'delhi',
                'indicator' => 'Government Projects',
                'impact' => 'positive',
                'weight' => 0.7,
                'description' => 'Government initiatives boosting market'
            ]
        ];
    }

    /**
     * Calculate prediction confidence
     */
    private function calculatePredictionConfidence($historicalData) {
        $dataPoints = count($historicalData);

        if ($dataPoints < 5) return 0.3;
        if ($dataPoints < 10) return 0.5;
        if ($dataPoints < 20) return 0.7;

        return 0.85; // High confidence with sufficient data
    }

    /**
     * Get comparative analysis with other locations
     */
    private function getComparativeAnalysis($location, $propertyType) {
        try {
            $sql = "SELECT
                        SUBSTRING_INDEX(p.location, ',', 1) as city,
                        AVG(p.price) as avg_price,
                        COUNT(*) as total_properties,
                        AVG(p.price / NULLIF(p.area_sqft, 0)) as price_per_sqft
                    FROM properties p
                    WHERE p.status = 'active'
                    AND p.location IS NOT NULL
                    GROUP BY SUBSTRING_INDEX(p.location, ',', 1)
                    ORDER BY avg_price DESC
                    LIMIT 10";

            $result = $this->conn->query($sql);
            $comparisons = [];

            while ($row = $result->fetch_assoc()) {
                $comparisons[] = $row;
            }

            return [
                'location_comparison' => $comparisons,
                'national_average' => $this->getNationalAverages(),
                'price_ranking' => $this->calculateLocationRanking($comparisons, $location)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get national averages for comparison
     */
    private function getNationalAverages() {
        try {
            $sql = "SELECT
                        AVG(price) as avg_price,
                        AVG(price / NULLIF(area_sqft, 0)) as avg_price_per_sqft,
                        AVG(bedrooms) as avg_bedrooms,
                        AVG(bathrooms) as avg_bathrooms
                    FROM properties
                    WHERE status = 'active'";

            $result = $this->conn->query($sql);
            return $result->fetch_assoc();

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calculate location ranking
     */
    private function calculateLocationRanking($comparisons, $targetLocation) {
        $targetPrice = null;

        foreach ($comparisons as $index => $comparison) {
            if (stripos($comparison['city'], $targetLocation) !== false) {
                $targetPrice = $comparison['avg_price'];
                break;
            }
        }

        if ($targetPrice === null) {
            return ['rank' => 'N/A', 'percentile' => 0];
        }

        $sortedPrices = array_column($comparisons, 'avg_price');
        rsort($sortedPrices);

        $rank = array_search($targetPrice, $sortedPrices) + 1;
        $percentile = (($rank - 1) / (count($sortedPrices) - 1)) * 100;

        return [
            'rank' => $rank,
            'total_locations' => count($sortedPrices),
            'percentile' => round($percentile, 1)
        ];
    }

    /**
     * Get investment insights
     */
    private function getInvestmentInsights($location, $propertyType) {
        $metrics = $this->getCurrentMarketMetrics($location, $propertyType);
        $health = $this->assessMarketHealth($location, $propertyType);
        $trends = $this->analyzePriceTrends($location, $propertyType);

        $insights = [];

        // ROI Analysis
        $avgPrice = $metrics['avg_price'] ?? 0;
        if ($avgPrice > 0) {
            $estimatedRentalYield = $this->estimateRentalYield($location, $avgPrice);
            $expectedAppreciation = $this->estimateAppreciation($trends);

            $insights['roi_analysis'] = [
                'estimated_rental_yield' => round($estimatedRentalYield, 2),
                'expected_appreciation' => round($expectedAppreciation, 2),
                'total_annual_return' => round($estimatedRentalYield + $expectedAppreciation, 2)
            ];
        }

        // Risk Assessment
        $insights['risk_assessment'] = [
            'market_volatility' => $this->assessMarketVolatility($trends),
            'liquidity_risk' => $metrics['total_properties'] < 20 ? 'High' : 'Low',
            'economic_factors' => $this->assessEconomicFactors($location)
        ];

        // Investment Timeline
        $insights['investment_timeline'] = [
            'short_term' => '3-12 months',
            'medium_term' => '1-3 years',
            'long_term' => '3-5+ years',
            'recommended' => $this->getRecommendedTimeline($health)
        ];

        return $insights;
    }

    /**
     * Estimate rental yield
     */
    private function estimateRentalYield($location, $propertyValue) {
        // This would use actual rental data if available
        // For now, return estimated yields based on location
        $baseYield = 2.5; // Base rental yield

        $locationMultipliers = [
            'mumbai' => 1.2,
            'bangalore' => 1.3,
            'delhi' => 1.1,
            'pune' => 1.1,
            'hyderabad' => 1.0
        ];

        $multiplier = 1.0;
        foreach ($locationMultipliers as $loc => $mult) {
            if (stripos($location, $loc) !== false) {
                $multiplier = $mult;
                break;
            }
        }

        return $baseYield * $multiplier;
    }

    /**
     * Estimate property appreciation
     */
    private function estimateAppreciation($trends) {
        $recentTrend = $trends['3_months'] ?? $trends['1_month'] ?? null;

        if ($recentTrend && isset($recentTrend['change_percentage'])) {
            $annualized = $recentTrend['change_percentage'] * 4; // Annualize quarterly trend
            return max(0, min($annualized, 15)); // Cap at 15% annual appreciation
        }

        return 5; // Default 5% annual appreciation
    }

    /**
     * Assess market volatility
     */
    private function assessMarketVolatility($trends) {
        $volatilitySum = 0;
        $count = 0;

        foreach ($trends as $trend) {
            if (isset($trend['change_percentage'])) {
                $volatilitySum += abs($trend['change_percentage']);
                $count++;
            }
        }

        if ($count === 0) return 'Unknown';

        $avgVolatility = $volatilitySum / $count;

        if ($avgVolatility > 10) return 'High';
        if ($avgVolatility > 5) return 'Medium';
        return 'Low';
    }

    /**
     * Assess economic factors
     */
    private function assessEconomicFactors($location) {
        // This would integrate with economic data APIs
        // For now, return general assessment
        return [
            'employment_growth' => 'Positive',
            'infrastructure_development' => 'Active',
            'population_growth' => 'Steady',
            'overall_outlook' => 'Favorable'
        ];
    }

    /**
     * Get recommended investment timeline
     */
    private function getRecommendedTimeline($health) {
        $score = $health['overall_score'] ?? 0;

        if ($score >= 80) return 'short_term';
        if ($score >= 60) return 'medium_term';
        return 'long_term';
    }

    /**
     * Calculate overall market score
     */
    private function calculateOverallMarketScore($analysis) {
        $weights = [
            'health' => 0.4,
            'trends' => 0.3,
            'predictions' => 0.3
        ];

        $healthScore = $analysis['market_health']['overall_score'] ?? 0;
        $trendScore = $this->calculateTrendScore($analysis['price_trends']);
        $predictionScore = $this->calculatePredictionScore($analysis['predictions']);

        return round(
            $healthScore * $weights['health'] +
            $trendScore * $weights['trends'] +
            $predictionScore * $weights['predictions']
        );
    }

    /**
     * Calculate trend score
     */
    private function calculateTrendScore($trends) {
        $positiveTrends = 0;
        $totalTrends = 0;

        foreach ($trends as $trend) {
            if (isset($trend['direction'])) {
                $totalTrends++;
                if ($trend['direction'] === 'up') {
                    $positiveTrends++;
                }
            }
        }

        return $totalTrends > 0 ? ($positiveTrends / $totalTrends) * 100 : 50;
    }

    /**
     * Calculate prediction score
     */
    private function calculatePredictionScore($predictions) {
        if (isset($predictions['error']) || isset($predictions['predictions_disabled'])) {
            return 50;
        }

        $avgConfidence = 0;
        $count = 0;

        foreach ($predictions['predictions'] ?? [] as $prediction) {
            if (isset($prediction['confidence'])) {
                $avgConfidence += $prediction['confidence'];
                $count++;
            }
        }

        return $count > 0 ? ($avgConfidence / $count) * 100 : 50;
    }

    /**
     * Generate market recommendation
     */
    private function generateMarketRecommendation($analysis) {
        $overallScore = $analysis['overall_score'] ?? 0;
        $health = $analysis['market_health'] ?? [];
        $trends = $analysis['price_trends'] ?? [];

        if ($overallScore >= 80) {
            return [
                'action' => 'Strong Buy',
                'confidence' => 'High',
                'reasoning' => 'Excellent market conditions with strong growth potential',
                'timeframe' => 'Short to Medium term'
            ];
        } elseif ($overallScore >= 60) {
            return [
                'action' => 'Buy',
                'confidence' => 'Medium',
                'reasoning' => 'Favorable market conditions with moderate growth',
                'timeframe' => 'Medium term'
            ];
        } elseif ($overallScore >= 40) {
            return [
                'action' => 'Hold/Wait',
                'confidence' => 'Low',
                'reasoning' => 'Market conditions are uncertain',
                'timeframe' => 'Long term'
            ];
        } else {
            return [
                'action' => 'Avoid',
                'confidence' => 'High',
                'reasoning' => 'Unfavorable market conditions',
                'timeframe' => 'Wait for better conditions'
            ];
        }
    }
}
?>
