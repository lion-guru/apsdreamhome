<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Services\AI\PropertyValuationEngine;

/**
 * AI Property Valuation Controller
 * Advanced AI-powered property valuation and market analysis
 */
class AIValuationController extends BaseController
{
    private $valuationEngine;

    public function __construct()
    {
        $this->valuationEngine = new PropertyValuationEngine();
    }

    /**
     * AI Valuation Dashboard
     */
    public function index()
    {
        // $this->requireLogin(); // Temporarily removed for testing

        $this->render('pages/ai-valuation', [
            'page_title' => 'AI Property Valuation - APS Dream Home',
            'page_description' => 'Advanced AI-powered property valuation and market analysis'
        ]);
    }

    /**
     * Calculate property valuation
     */
    public function calculateValuation()
    {
        header('Content-Type: application/json');

        try {
            $propertyData = [
                'location' => $this->sanitize($_POST['location']) ?? '',
                'type' => $this->sanitize($_POST['type']) ?? '',
                'size' => $this->sanitize($_POST['size']) ?? 1000,
                'age' => $this->sanitize($_POST['age']) ?? 0,
                'condition' => $this->sanitize($_POST['condition']) ?? 'average',
                'amenities' => $this->sanitize($_POST['amenities']) ?? []
            ];

            $valuation = $this->valuationEngine->calculateValuation($propertyData);

            echo json_encode([
                'success' => true,
                'valuation' => $valuation
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to calculate valuation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get market trends
     */
    public function getMarketTrends()
    {
        header('Content-Type: application/json');

        try {
            $location = $_GET['location'] ?? '';

            $trends = [
                'mumbai' => [
                    'avg_price_per_sqft' => 15000,
                    'price_change_6months' => '+5.2%',
                    'price_change_1year' => '+8.7%',
                    'inventory_level' => 'Low',
                    'days_on_market' => 42,
                    'market_sentiment' => 'Strong'
                ],
                'delhi' => [
                    'avg_price_per_sqft' => 8000,
                    'price_change_6months' => '+3.1%',
                    'price_change_1year' => '+5.4%',
                    'inventory_level' => 'Medium',
                    'days_on_market' => 58,
                    'market_sentiment' => 'Positive'
                ],
                'bangalore' => [
                    'avg_price_per_sqft' => 6000,
                    'price_change_6months' => '+6.8%',
                    'price_change_1year' => '+9.2%',
                    'inventory_level' => 'Low',
                    'days_on_market' => 38,
                    'market_sentiment' => 'Very Strong'
                ]
            ];

            echo json_encode([
                'success' => true,
                'trends' => $trends[$location] ?? $trends['bangalore']
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch market trends'
            ]);
        }
    }

    /**
     * Get investment analysis
     */
    public function getInvestmentAnalysis()
    {
        header('Content-Type: application/json');

        try {
            $propertyData = json_decode($this->sanitize($_POST['property_data']), true) ?? [];

            $analysis = [
                'investment_score' => $this->calculateInvestmentScore($propertyData),
                'roi_projection' => $this->calculateROIProjection($propertyData),
                'risk_assessment' => $this->assessInvestmentRisk($propertyData),
                'recommendation' => $this->getInvestmentRecommendation($propertyData)
            ];

            echo json_encode([
                'success' => true,
                'analysis' => $analysis
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to analyze investment'
            ]);
        }
    }

    /**
     * Calculate investment score
     */
    private function calculateInvestmentScore($propertyData)
    {
        $score = 75; // Base score

        // Location factor
        $locationScores = ['mumbai' => 90, 'delhi' => 80, 'bangalore' => 85, 'pune' => 75, 'hyderabad' => 70];
        $score = $locationScores[$propertyData['location']] ?? 70;

        // Property type factor
        if ($propertyData['type'] === 'apartment') $score += 5;
        elseif ($propertyData['type'] === 'house') $score += 3;

        // Condition factor
        $conditionScores = ['excellent' => 10, 'good' => 5, 'average' => 0, 'fair' => -5, 'poor' => -10];
        $score += $conditionScores[$propertyData['condition']] ?? 0;

        return min(100, max(0, $score));
    }

    /**
     * Calculate ROI projection
     */
    private function calculateROIProjection($propertyData)
    {
        $baseROI = 8.5; // Base ROI percentage

        // Location adjustment
        $locationROI = ['mumbai' => 12.5, 'delhi' => 10.2, 'bangalore' => 11.8, 'pune' => 9.5, 'hyderabad' => 8.9];
        $roi = $locationROI[$propertyData['location']] ?? $baseROI;

        // Condition adjustment
        if ($propertyData['condition'] === 'excellent') $roi += 2;
        elseif ($propertyData['condition'] === 'good') $roi += 1;
        elseif ($propertyData['condition'] === 'poor') $roi -= 2;

        return [
            'annual_roi' => round($roi, 2),
            '5_year_projection' => round($roi * 5, 2),
            '10_year_projection' => round($roi * 10, 2)
        ];
    }

    /**
     * Assess investment risk
     */
    private function assessInvestmentRisk($propertyData)
    {
        $riskFactors = [
            'market_risk' => $this->getMarketRisk($propertyData['location']),
            'property_risk' => $this->getPropertyRisk($propertyData),
            'location_risk' => $this->getLocationRisk($propertyData['location']),
            'overall_risk' => 'Medium'
        ];

        // Calculate overall risk
        $avgRisk = ($riskFactors['market_risk'] + $riskFactors['property_risk'] + $riskFactors['location_risk']) / 3;

        if ($avgRisk <= 2) $riskFactors['overall_risk'] = 'Low';
        elseif ($avgRisk <= 3.5) $riskFactors['overall_risk'] = 'Medium';
        else $riskFactors['overall_risk'] = 'High';

        return $riskFactors;
    }

    /**
     * Get market risk level
     */
    private function getMarketRisk($location)
    {
        $marketRisk = ['mumbai' => 2, 'delhi' => 3, 'bangalore' => 2, 'pune' => 3, 'hyderabad' => 3];
        return $marketRisk[$location] ?? 3;
    }

    /**
     * Get property risk level
     */
    private function getPropertyRisk($propertyData)
    {
        $risk = 3; // Base risk

        if ($propertyData['condition'] === 'excellent') $risk -= 1;
        elseif ($propertyData['condition'] === 'good') $risk -= 0.5;
        elseif ($propertyData['condition'] === 'poor') $risk += 1;

        if ($propertyData['age'] > 20) $risk += 1;
        elseif ($propertyData['age'] < 5) $risk -= 0.5;

        return max(1, min(5, $risk));
    }

    /**
     * Get location risk level
     */
    private function getLocationRisk($location)
    {
        $locationRisk = ['mumbai' => 2, 'delhi' => 2, 'bangalore' => 2, 'pune' => 3, 'hyderabad' => 3];
        return $locationRisk[$location] ?? 3;
    }

    /**
     * Get investment recommendation
     */
    private function getInvestmentRecommendation($propertyData)
    {
        $score = $this->calculateInvestmentScore($propertyData);
        $risk = $this->assessInvestmentRisk($propertyData);

        if ($score >= 85 && $risk['overall_risk'] === 'Low') {
            return 'Excellent investment opportunity - Strong buy recommendation';
        } elseif ($score >= 75 && $risk['overall_risk'] !== 'High') {
            return 'Good investment potential - Consider buying';
        } elseif ($score >= 65) {
            return 'Moderate investment - Evaluate carefully';
        } else {
            return 'High risk investment - Not recommended';
        }
    }
}
