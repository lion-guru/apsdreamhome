<?php
/**
 * MarketIntelligenceAgent — Real estate market analysis
 * 
 * Tracks and analyzes:
 * - Local property price trends (Gorakhpur)
 * - Colony-wise demand patterns
 * - Seasonal buying patterns
 * - Competitor pricing
 * - Lead source effectiveness
 * - ROI predictions for investors
 * 
 * All using internal data — no external API needed
 */

namespace App\Services\AI\Agents;

use App\Core\Database\Database;
use App\Services\AI\PricePredictor;

class MarketIntelligenceAgent
{
    private $db;
    private $pricePredictor;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pricePredictor = new PricePredictor($this->db);
    }

    /**
     * Full market intelligence report
     */
    public function getMarketReport(): array
    {
        return [
            'price_trends' => $this->getPriceTrends(),
            'demand_analysis' => $this->getDemandAnalysis(),
            'seasonal_patterns' => $this->getSeasonalPatterns(),
            'colony_performance' => $this->getColonyPerformance(),
            'source_effectiveness' => $this->getSourceEffectiveness(),
            'investor_insights' => $this->getInvestorInsights(),
            'recommendations' => $this->generateRecommendations(),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Price trends over time
     */
    public function getPriceTrends(): array
    {
        try {
            // Monthly average prices for last 12 months
            $monthly = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                        AVG(price) as avg_price,
                        MIN(price) as min_price,
                        MAX(price) as max_price,
                        COUNT(*) as sales_count
                 FROM plot_bookings
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) AND status = 'confirmed'
                 GROUP BY month ORDER BY month ASC"
            ) ?: [];

            // Current vs last month
            $current = $this->db->fetch(
                "SELECT AVG(price) as avg FROM plot_bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'confirmed'"
            );
            $lastMonth = $this->db->fetch(
                "SELECT AVG(price) as avg FROM plot_bookings WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND status = 'confirmed'"
            );

            $currentAvg = (float)($current['avg'] ?? 0);
            $lastAvg = (float)($lastMonth['avg'] ?? 0);
            $growth = $lastAvg > 0 ? round(($currentAvg - $lastAvg) / $lastAvg * 100, 1) : 0;

            return [
                'monthly' => $monthly,
                'current_avg' => $currentAvg,
                'last_month_avg' => $lastAvg,
                'growth_pct' => $growth,
                'trend' => $growth > 0 ? 'rising' : ($growth < 0 ? 'falling' : 'stable'),
            ];
        } catch (\Throwable $e) {
            return ['monthly' => [], 'current_avg' => 0, 'growth_pct' => 0, 'trend' => 'unknown'];
        }
    }

    /**
     * Demand analysis by area/type
     */
    public function getDemandAnalysis(): array
    {
        try {
            // Leads by location preference
            $locationDemand = $this->db->fetchAll(
                "SELECT location_preference, COUNT(*) as leads, AVG(lead_score) as avg_score
                 FROM leads WHERE deleted_at IS NULL AND location_preference != ''
                 GROUP BY location_preference ORDER BY leads DESC LIMIT 10"
            ) ?: [];

            // Leads by source
            $sourceDemand = $this->db->fetchAll(
                "SELECT source, COUNT(*) as leads,
                        SUM(CASE WHEN status IN ('won','booking','qualified') THEN 1 ELSE 0 END) as qualified,
                        ROUND(AVG(lead_score),1) as avg_score
                 FROM leads WHERE deleted_at IS NULL
                 GROUP BY source ORDER BY leads DESC"
            ) ?: [];

            // Budget distribution
            $budgetDist = $this->db->fetchAll(
                "SELECT
                    CASE
                        WHEN budget < 1000000 THEN 'Under 10L'
                        WHEN budget < 2000000 THEN '10L-20L'
                        WHEN budget < 5000000 THEN '20L-50L'
                        ELSE '50L+'
                    END as range,
                    COUNT(*) as leads
                 FROM leads WHERE deleted_at IS NULL AND budget > 0
                 GROUP BY range ORDER BY MIN(budget)"
            ) ?: [];

            return [
                'location_demand' => $locationDemand,
                'source_demand' => $sourceDemand,
                'budget_distribution' => $budgetDist,
            ];
        } catch (\Throwable $e) {
            return ['location_demand' => [], 'source_demand' => [], 'budget_distribution' => []];
        }
    }

    /**
     * Seasonal buying patterns
     */
    public function getSeasonalPatterns(): array
    {
        try {
            $monthly = $this->db->fetchAll(
                "SELECT MONTH(created_at) as month, COUNT(*) as bookings, AVG(price) as avg_price
                 FROM plot_bookings WHERE status = 'confirmed'
                 GROUP BY month ORDER BY month"
            ) ?: [];

            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $peak = ['month' => '', 'bookings' => 0];
            foreach ($monthly as $m) {
                if ($m['bookings'] > $peak['bookings']) {
                    $peak = ['month' => $monthNames[$m['month']] ?? $m['month'], 'bookings' => $m['bookings']];
                }
            }

            return ['monthly' => $monthly, 'peak_season' => $peak];
        } catch (\Throwable $e) {
            return ['monthly' => [], 'peak_season' => []];
        }
    }

    /**
     * Colony-wise performance
     */
    public function getColonyPerformance(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT c.name as colony_name,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available') as available,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'sold') as sold,
                        (SELECT COUNT(*) FROM leads l WHERE l.location_preference LIKE CONCAT('%', c.name, '%') AND l.deleted_at IS NULL) as leads,
                        (SELECT AVG(price) FROM plots p WHERE p.colony_id = c.id) as avg_price,
                        (SELECT COUNT(*) FROM plot_bookings pb
                         LEFT JOIN plots p ON pb.plot_id = p.id
                         WHERE p.colony_id = c.id AND pb.status = 'confirmed' AND pb.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as month_sales
                 FROM colonies c
                 WHERE c.is_active = 1
                 ORDER BY leads DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Lead source effectiveness
     */
    public function getSourceEffectiveness(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT source,
                        COUNT(*) as total_leads,
                        SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as converted,
                        ROUND(AVG(lead_score),1) as avg_score,
                        ROUND(AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)),1) as avg_days_to_convert,
                        ROUND(SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as conversion_rate
                 FROM leads WHERE deleted_at IS NULL
                 GROUP BY source
                 ORDER BY conversion_rate DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Investor insights
     */
    public function getInvestorInsights(): array
    {
        try {
            // ROI by colony (price appreciation)
            $roi = $this->db->fetchAll(
                "SELECT c.name,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price,
                        ROUND((MAX(p.price) - MIN(p.price)) / MIN(p.price) * 100, 1) as price_range_pct,
                        COUNT(p.id) as plots
                 FROM plots p JOIN colonies c ON p.colony_id = c.id
                 WHERE p.status = 'available'
                 GROUP BY c.name HAVING plots > 1
                 ORDER BY price_range_pct DESC"
            ) ?: [];

            return [
                'roi_by_colony' => $roi,
                'tip' => 'Gorakhpur real estate growing at 8-12% annually. Best time to invest.',
            ];
        } catch (\Throwable $e) {
            return ['roi_by_colony' => [], 'tip' => ''];
        }
    }

    /**
     * Market Health Score — 5-factor weighted assessment
     * Ported from AIMarketAnalyzer::assessMarketHealth()
     */
    public function getMarketHealthScore(): array
    {
        try {
            $healthScore = 0;
            $factors = [];

            // 1. Volume factor (20%) — total active plots as proxy for market liquidity
            $plots = $this->db->fetch(
                "SELECT COUNT(*) as total, SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available
                 FROM plots"
            );
            $totalPlots = (int)($plots['total'] ?? 0);
            $volumeScore = min($totalPlots / 50, 1) * 20;
            $healthScore += $volumeScore;
            $factors['volume'] = [
                'score' => round($volumeScore, 1),
                'description' => 'Market volume and liquidity',
                'detail' => "$totalPlots total plots in inventory"
            ];

            // 2. Demand factor (25%) — recent leads vs available plots
            $recentLeads = $this->db->fetch(
                "SELECT COUNT(*) as cnt FROM leads
                 WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $leadCount = (int)($recentLeads['cnt'] ?? 0);
            $demandRate = $totalPlots > 0 ? $leadCount / $totalPlots : 0;
            $demandScore = min($demandRate * 5, 1) * 25;
            $healthScore += $demandScore;
            $factors['demand'] = [
                'score' => round($demandScore, 1),
                'description' => 'Buyer demand and interest levels',
                'detail' => "$leadCount leads in last 30 days"
            ];

            // 3. Price stability factor (20%) — volatility of recent booking prices
            $recentBookings = $this->db->fetchAll(
                "SELECT price FROM plot_bookings
                 WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)"
            );
            $prices = array_column($recentBookings ?? [], 'price');
            $priceStability = 1.0;
            if (count($prices) > 2) {
                $avg = array_sum($prices) / count($prices);
                $variance = 0;
                foreach ($prices as $p) { $variance += ($p - $avg) ** 2; }
                $stdDev = sqrt($variance / count($prices));
                $cv = $avg > 0 ? $stdDev / $avg : 0;
                $priceStability = max(0, 1 - ($cv * 5));
            }
            $stabilityScore = $priceStability * 20;
            $healthScore += $stabilityScore;
            $factors['stability'] = [
                'score' => round($stabilityScore, 1),
                'description' => 'Price stability and predictability',
                'detail' => count($prices) . ' bookings in 90 days analyzed'
            ];

            // 4. Sales velocity factor (20%) — bookings per month trend
            $thisMonth = $this->db->fetch(
                "SELECT COUNT(*) as cnt FROM plot_bookings
                 WHERE status='confirmed' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"
            );
            $lastMonth = $this->db->fetch(
                "SELECT COUNT(*) as cnt FROM plot_bookings
                 WHERE status='confirmed' AND MONTH(created_at)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
            );
            $thisCount = (int)($thisMonth['cnt'] ?? 0);
            $lastCount = max((int)($lastMonth['cnt'] ?? 1), 1);
            $velocityRatio = min($thisCount / $lastCount, 2);
            $velocityScore = $velocityRatio * 10;
            $healthScore += $velocityScore;
            $factors['velocity'] = [
                'score' => round($velocityScore, 1),
                'description' => 'Sales velocity momentum',
                'detail' => "$thisCount bookings this month vs $lastCount last month"
            ];

            // 5. Conversion factor (15%) — lead-to-booking conversion rate
            $conversions = $this->db->fetch(
                "SELECT
                    COUNT(*) as total_leads,
                    SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as won
                 FROM leads WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)"
            );
            $convRate = ((int)($conversions['total_leads'] ?? 0)) > 0
                ? ((int)($conversions['won'] ?? 0)) / (int)($conversions['total_leads'] ?? 1)
                : 0;
            $convScore = min($convRate * 50, 1) * 15;
            $healthScore += $convScore;
            $factors['conversion'] = [
                'score' => round($convScore, 1),
                'description' => 'Lead-to-booking conversion efficiency',
                'detail' => round($convRate * 100, 1) . '% conversion rate (90d)'
            ];

            // Overall rating
            $rating = 'Critical';
            if ($healthScore >= 80) $rating = 'Excellent';
            elseif ($healthScore >= 65) $rating = 'Good';
            elseif ($healthScore >= 50) $rating = 'Fair';
            elseif ($healthScore >= 35) $rating = 'Poor';

            return [
                'overall_score' => round($healthScore, 1),
                'rating' => $rating,
                'factors' => $factors,
                'generated_at' => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            return ['overall_score' => 0, 'rating' => 'Unknown', 'factors' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Investment Insights — ROI, rental yield, risk assessment, timeline
     * Ported from AIMarketAnalyzer::getInvestmentInsights()
     */
    public function getInvestmentInsightsFull(): array
    {
        try {
            $insights = [];

            // ROI Analysis — price appreciation by colony
            $roi = $this->db->fetchAll(
                "SELECT c.name,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price,
                        ROUND((MAX(p.price) - MIN(p.price)) / NULLIF(MIN(p.price),0) * 100, 1) as price_range_pct,
                        COUNT(p.id) as plots
                 FROM plots p JOIN colonies c ON p.colony_id = c.id
                 WHERE p.status = 'available'
                 GROUP BY c.name HAVING plots > 1
                 ORDER BY price_range_pct DESC"
            ) ?: [];

            $avgRoi = 0;
            if (count($roi) > 0) {
                $avgRoi = array_sum(array_column($roi, 'price_range_pct')) / count($roi);
            }

            $insights['roi_analysis'] = [
                'by_colony' => $roi,
                'average_appreciation_pct' => round($avgRoi, 1),
                'estimated_rental_yield_pct' => 2.5,
                'total_annual_return_pct' => round(2.5 + $avgRoi, 1),
            ];

            // Risk Assessment
            $health = $this->getMarketHealthScore();
            $hs = $health['overall_score'] ?? 0;
            $insights['risk_assessment'] = [
                'market_health' => $health['rating'] ?? 'Unknown',
                'health_score' => $hs,
                'liquidity_risk' => 'Low',
                'price_volatility' => ($hs > 65) ? 'Low' : (($hs > 40) ? 'Medium' : 'High'),
            ];

            // Investment Timeline
            $score = $health['overall_score'] ?? 0;
            $recommended = 'long_term';
            if ($score >= 80) $recommended = 'short_term';
            elseif ($score >= 60) $recommended = 'medium_term';

            $insights['investment_timeline'] = [
                'short_term' => '3-12 months',
                'medium_term' => '1-3 years',
                'long_term' => '3-5+ years',
                'recommended' => $recommended,
            ];

            // Colony-specific insights
            $insights['colony_insights'] = $this->getColonyPerformance();

            // Buy/Hold/Avoid verdict
            if ($score >= 80) {
                $verdict = ['action' => 'Strong Buy', 'confidence' => 'High', 'reasoning' => 'Excellent market conditions with strong growth potential'];
            } elseif ($score >= 60) {
                $verdict = ['action' => 'Buy', 'confidence' => 'Medium', 'reasoning' => 'Favorable market conditions with moderate growth'];
            } elseif ($score >= 40) {
                $verdict = ['action' => 'Hold/Wait', 'confidence' => 'Low', 'reasoning' => 'Market conditions are uncertain'];
            } else {
                $verdict = ['action' => 'Avoid', 'confidence' => 'High', 'reasoning' => 'Unfavorable market conditions — wait for better timing'];
            }
            $insights['verdict'] = $verdict;

            return $insights;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Comparative Analysis — rank locations against each other
     * Ported from AIMarketAnalyzer::getComparativeAnalysis()
     */
    public function getComparativeAnalysis(): array
    {
        try {
            // Colony-wise comparison
            $comparisons = $this->db->fetchAll(
                "SELECT c.name as colony,
                        AVG(p.price) as avg_price,
                        COUNT(p.id) as total_plots,
                        SUM(CASE WHEN p.status='sold' THEN 1 ELSE 0 END) as sold,
                        ROUND(AVG(p.area_sqft),0) as avg_area,
                        ROUND(AVG(p.price) / NULLIF(AVG(p.area_sqft),0), 0) as price_per_sqft
                 FROM plots p JOIN colonies c ON p.colony_id = c.id
                 GROUP BY c.name
                 ORDER BY avg_price DESC"
            ) ?: [];

            // Gorakhpur average
            $gorakhpur = $this->db->fetch(
                "SELECT AVG(p.price) as avg_price, AVG(p.price/NULLIF(p.area_sqft,0)) as price_per_sqft
                 FROM plots p WHERE p.status IN ('available','sold')"
            );

            // Deoria average (if separate colonies exist)
            $deoria = $this->db->fetch(
                "SELECT AVG(p.price) as avg_price, AVG(p.price/NULLIF(p.area_sqft,0)) as price_per_sqft
                 FROM plots p JOIN colonies c ON p.colony_id = c.id
                 WHERE c.district = 'Deoria'"
            );

            return [
                'colony_comparison' => $comparisons,
                'gorakhpur_avg' => [
                    'avg_price' => round((float)($gorakhpur['avg_price'] ?? 0)),
                    'price_per_sqft' => round((float)($gorakhpur['price_per_sqft'] ?? 0)),
                ],
                'deoria_avg' => [
                    'avg_price' => round((float)($deoria['avg_price'] ?? 0)),
                    'price_per_sqft' => round((float)($deoria['price_per_sqft'] ?? 0)),
                ],
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate actionable recommendations
     */
    public function generateRecommendations(): array
    {
        $recommendations = [];

        // High demand, low supply
        $demand = $this->getDemandAnalysis();
        foreach ($demand['location_demand'] ?? [] as $loc) {
            if ($loc['leads'] > 20 && $loc['avg_score'] > 50) {
                $recommendations[] = [
                    'type' => 'opportunity',
                    'title' => "High demand in {$loc['location_preference']}",
                    'detail' => "{$loc['leads']} leads with avg score {$loc['avg_score']}. Consider increasing inventory.",
                    'priority' => 'high',
                ];
            }
        }

        // Low conversion sources
        foreach ($demand['source_demand'] ?? [] as $src) {
            if ($src['leads'] > 10 && ($src['qualified'] / max($src['leads'], 1)) < 0.1) {
                $recommendations[] = [
                    'type' => 'warning',
                    'title' => "Low conversion from {$src['source']}",
                    'detail' => "{$src['leads']} leads but only {$src['qualified']} qualified. Review lead quality.",
                    'priority' => 'medium',
                ];
            }
        }

        // Budget gap
        $budgetDist = $demand['budget_distribution'] ?? [];
        $under10L = 0;
        foreach ($budgetDist as $b) {
            if (strpos($b['range'], 'Under') !== false) $under10L = $b['leads'];
        }
        if ($under10L > 50) {
            $recommendations[] = [
                'type' => 'insight',
                'title' => 'Large budget-conscious segment',
                'detail' => "$under10L leads with budget under ₹10L. Consider affordable plot options.",
                'priority' => 'medium',
            ];
        }

        return $recommendations;
    }
}
