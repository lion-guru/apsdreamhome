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
        parent::__construct();
        $this->valuationEngine = new PropertyValuationEngine();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    /**
     * AI Valuation Dashboard
     */
    public function index()
    {
        $this->requireLogin();

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
            // Accept form-encoded ($_POST) or JSON bodies
            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }

            $colonyId = isset($input['colony_id']) ? (int) $input['colony_id'] : null;
            $area = isset($input['area']) ? (float) $input['area']
                : (isset($input['size']) ? (float) $input['size'] : 1000);
            $location = $input['location'] ?? '';
            $facing = $input['facing'] ?? '';
            $roadWidth = isset($input['road_width']) ? (float) $input['road_width'] : 0;
            $plcFlags = $input['plc_flags'] ?? [];
            if (!is_array($plcFlags)) {
                $plcFlags = [];
            }
            // Flags from admin plot form checkboxes
            if (!empty($input['corner_plot'])) {
                $plcFlags[] = 'corner';
            }
            if (!empty($input['park_facing'])) {
                $plcFlags[] = 'park';
            }

            $valuation = $this->valuationEngine->estimateValue([
                'colony_id' => $colonyId,
                'area' => $area,
                'location' => $location,
                'facing' => $facing,
                'road_width' => $roadWidth,
                'plc_flags' => $plcFlags,
            ]);

            echo json_encode([
                'success' => true,
                'valuation' => $valuation,
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to calculate valuation: ' . $e->getMessage(),
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
            $db = \App\Core\Database\Database::getInstance();
            $colonyId = isset($_GET['colony_id']) ? (int) $_GET['colony_id'] : 0;

            // Real trends from plots table: avg pps + 6-month change per colony
            $where = $colonyId > 0 ? "WHERE p.colony_id = ? AND p.base_price_per_sqft > 0" : "WHERE p.base_price_per_sqft > 0";
            $params = $colonyId > 0 ? [$colonyId] : [];

            $trend = $db->fetch("
                SELECT
                    COALESCE(AVG(p.base_price_per_sqft), 0) as avg_pps,
                    COALESCE(AVG(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN p.base_price_per_sqft END), 0) as recent_avg,
                    COALESCE(AVG(CASE WHEN p.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 6 MONTH) AND DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN p.base_price_per_sqft END), 0) as older_avg,
                    COUNT(*) as total_plots
                FROM plots p
                $where
            ", $params);

            $avg = (float) ($trend['avg_pps'] ?? 0);
            $recent = (float) ($trend['recent_avg'] ?? 0);
            $older = (float) ($trend['older_avg'] ?? 0);
            $changePct = $older > 0 ? round(($recent - $older) / $older * 100, 1) : 0.0;

            $colonyNames = $db->fetchAll("SELECT id, name FROM colonies WHERE (status IS NULL OR status != 'inactive') ORDER BY name LIMIT 20");

            echo json_encode([
                'success' => true,
                'trends' => [
                    'avg_price_per_sqft' => round($avg, 2),
                    'price_change_6months' => ($changePct >= 0 ? '+' : '') . $changePct . '%',
                    'total_plots' => (int) ($trend['total_plots'] ?? 0),
                    'colonies' => $colonyNames ?: [],
                ],
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch market trends',
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
            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $input = $decoded;
                }
            }
            $propertyData = $input['property_data'] ?? $input;
            if (is_string($propertyData)) {
                $propertyData = json_decode($propertyData, true) ?? [];
            }
            if (!is_array($propertyData)) {
                $propertyData = [];
            }
            // Normalize: colony_id may arrive top-level
            if (empty($propertyData['colony_id']) && isset($input['colony_id'])) {
                $propertyData['colony_id'] = (int) $input['colony_id'];
            }

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
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to analyze investment'
            ]);
        }
    }

    /**
     * Real colony stats (plots count, available ratio, avg pps) for scoring.
     */
    private function getColonyStats($colonyId): array
    {
        $colonyId = (int) ($colonyId ?? 0);
        if ($colonyId <= 0) {
            return ['total' => 0, 'available' => 0, 'avg_pps' => 0, 'avail_ratio' => 0];
        }
        try {
            $db = \App\Core\Database\Database::getInstance();
            $row = $db->fetch("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    COALESCE(AVG(base_price_per_sqft), 0) as avg_pps
                FROM plots
                WHERE colony_id = ? AND base_price_per_sqft > 0
            ", [$colonyId]);
            $total = (int) ($row['total'] ?? 0);
            $avail = (int) ($row['available'] ?? 0);
            return [
                'total' => $total,
                'available' => $avail,
                'avg_pps' => (float) ($row['avg_pps'] ?? 0),
                'avail_ratio' => $total > 0 ? $avail / $total : 0,
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'available' => 0, 'avg_pps' => 0, 'avail_ratio' => 0];
        }
    }

    /**
     * Calculate investment score (colony-driven, not city-mock)
     */
    private function calculateInvestmentScore($propertyData)
    {
        $stats = $this->getColonyStats($propertyData['colony_id'] ?? 0);

        // Base from colony depth: 50 (empty/unknown) → 85 (deep colony, 100+ plots)
        $depth = min(35, $stats['total'] / 4);
        $score = 50 + $depth;
        // Availability bonus: healthy inventory (20-60%) = best for buyers
        if ($stats['avail_ratio'] >= 0.2 && $stats['avail_ratio'] <= 0.6) {
            $score += 5;
        }

        // Condition factor
        $conditionScores = ['excellent' => 10, 'good' => 5, 'average' => 0, 'fair' => -5, 'poor' => -10];
        $score += $conditionScores[$propertyData['condition'] ?? ''] ?? 0;

        return min(100, max(0, (int) round($score)));
    }

    /**
     * Calculate ROI projection
     */
    private function calculateROIProjection($propertyData)
    {
        // Real trend: recent-3mo vs older-3mo avg pps change (capped ±10, engine convention)
        $baseROI = 8.0; // platform base when no trend data
        $colonyId = (int) ($propertyData['colony_id'] ?? 0);
        if ($colonyId > 0) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $t = $db->fetch("
                    SELECT
                        COALESCE(AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN base_price_per_sqft END), 0) as recent_avg,
                        COALESCE(AVG(CASE WHEN created_at BETWEEN DATE_SUB(NOW(), INTERVAL 6 MONTH) AND DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN base_price_per_sqft END), 0) as older_avg
                    FROM plots
                    WHERE colony_id = ? AND base_price_per_sqft > 0
                ", [$colonyId]);
                $recent = (float) ($t['recent_avg'] ?? 0);
                $older = (float) ($t['older_avg'] ?? 0);
                if ($older > 0 && $recent > 0) {
                    $change = ($recent - $older) / $older * 100;
                    $baseROI = max(0, min(20, 8.0 + max(-10, min(10, $change))));
                }
            } catch (\Throwable $e) {
                // keep platform base
            }
        }

        $roi = $baseROI;

        // Condition adjustment
        if (($propertyData['condition'] ?? '') === 'excellent') $roi += 1;
        elseif (($propertyData['condition'] ?? '') === 'good') $roi += 0.5;
        elseif (($propertyData['condition'] ?? '') === 'poor') $roi -= 1;

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
        $colonyId = (int) ($propertyData['colony_id'] ?? 0);
        $riskFactors = [
            'market_risk' => $this->getMarketRisk($colonyId),
            'property_risk' => $this->getPropertyRisk($propertyData),
            'location_risk' => $this->getLocationRisk($colonyId),
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
     * Get market risk level (colony inventory-based, not city-mock)
     */
    private function getMarketRisk($location)
    {
        // Accept colony_id directly when passed; $location kept for compat
        if (is_numeric($location) && (int) $location > 0) {
            $stats = $this->getColonyStats((int) $location);
            return $this->riskFromStats($stats);
        }
        // Unknown location → neutral
        return 3;
    }

    /**
     * Map colony inventory stats to 1-5 risk (low availability = higher risk)
     */
    private function riskFromStats(array $stats): int
    {
        if ($stats['total'] <= 0) {
            return 3; // unknown → neutral
        }
        $ratio = $stats['avail_ratio'];
        if ($ratio < 0.05) return 5; // nearly sold out
        if ($ratio < 0.15) return 4;
        if ($ratio < 0.4) return 3;
        if ($ratio < 0.65) return 2;
        return 1; // abundant choice, lowest market risk
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
     * Get location risk level (colony status-based, not city-mock)
     */
    private function getLocationRisk($location)
    {
        $colonyId = is_numeric($location) ? (int) $location : 0;
        if ($colonyId <= 0) {
            return 3; // unknown → neutral
        }
        try {
            $db = \App\Core\Database\Database::getInstance();
            $c = $db->fetch("SELECT status FROM colonies WHERE id = ?", [$colonyId]);
            $status = $c['status'] ?? 'planning';
            // active colony = proven (low), planning = mid, inactive = high
            if ($status === 'active') return 2;
            if ($status === 'planning') return 3;
            return 5; // inactive/unknown
        } catch (\Throwable $e) {
            return 3;
        }
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
        } elseif ($risk['overall_risk'] === 'High') {
            return 'High risk investment - Not recommended';
        } else {
            return 'Low investment score - Evaluate carefully before buying';
        }
    }
}
