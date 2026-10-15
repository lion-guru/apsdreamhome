<?php
/**
 * PredictiveAnalyticsService - Price trends, lead scoring, conversion prediction
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class PredictiveAnalyticsService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Predict price trend for a colony over next N months
     */
    public function predictPriceTrend(int $colonyId, int $monthsAhead = 6): array
    {
        $tid = $this->tenantId();
        
        // Get historical price data from price_history
        $history = $this->db->fetchAll(
            "SELECT created_at as effective_date, new_price_per_sqft as price_per_sqft, 
                    COUNT(*) as transactions
             FROM price_history ph
             WHERE ph.colony_id = ? AND ph.created_at >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
             GROUP BY DATE(ph.created_at)
             ORDER BY ph.created_at",
            [$colonyId]
        ) ?: [];

        if (count($history) < 3) {
            return [
                'success' => false,
                'error' => 'Insufficient historical data for prediction',
                'trend' => 'unknown',
                'confidence' => 0
            ];
        }

        // Simple linear regression for trend
        $trend = $this->calculateTrend($history);
        
        // Project future prices
        $lastPrice = end($history)['price_per_sqft'] ?? 0;
        $predicted = [];
        for ($i = 1; $i <= $monthsAhead; $i++) {
            $predicted[] = [
                'month' => date('Y-m', strtotime("+{$i} months")),
                'predicted_price' => round($lastPrice * (1 + $trend['slope'] * $i), 2),
                'confidence' => max(0.5, 1 - ($i * 0.1))
            ];
        }

        return [
            'success' => true,
            'colony_id' => $colonyId,
            'trend' => $trend['direction'],
            'monthly_change_pct' => round($trend['slope'] * 100, 2),
            'confidence' => round($trend['r2'] * 100, 1),
            'current_price' => $lastPrice,
            'predictions' => $predicted
        ];
    }

    /**
     * Calculate simple linear regression trend
     */
    private function calculateTrend(array $history): array
    {
        $n = count($history);
        $x = range(1, $n);
        $y = array_column($history, 'avg_final');
        
        // Filter out zero/null values
        $validData = [];
        foreach ($y as $i => $val) {
            if ($val > 0) {
                $validData[] = ['x' => $x[$i], 'y' => $val];
            }
        }
        
        if (count($validData) < 3) {
            return ['direction' => 'stable', 'slope' => 0, 'r2' => 0];
        }

        $n = count($validData);
        $sumX = array_sum(array_column($validData, 'x'));
        $sumY = array_sum(array_column($validData, 'y'));
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;
        
        foreach ($validData as $point) {
            $sumXY += $point['x'] * $point['y'];
            $sumX2 += $point['x'] * $point['x'];
            $sumY2 += $point['y'] * $point['y'];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate R-squared
        $ssRes = 0;
        $ssTot = 0;
        $meanY = $sumY / $n;
        
        foreach ($validData as $point) {
            $predicted = $slope * $point['x'] + $intercept;
            $ssRes += pow($point['y'] - $predicted, 2);
            $ssTot += pow($point['y'] - $meanY, 2);
        }
        
        $r2 = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;
        
        $direction = $slope > 0.01 ? 'rising' : ($slope < -0.01 ? 'falling' : 'stable');

        return [
            'direction' => $direction,
            'slope' => $slope,
            'intercept' => $intercept,
            'r2' => $r2
        ];
    }

    /**
     * Score a lead for conversion probability
     */
    public function scoreLead(int $leadId): array
    {
        $tid = $this->tenantId();
        
        $lead = $this->db->fetch(
            "SELECT * FROM leads WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$leadId], $tid > 1 ? [$tid] : [])
        );

        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $score = 0;
        $factors = [];

        // Budget factor (0-30)
        $budget = (float)$lead['budget'];
        if ($budget >= 10000000) { $score += 30; $factors[] = 'High budget (≥1Cr)'; }
        elseif ($budget >= 5000000) { $score += 25; $factors[] = 'Good budget (50L-1Cr)'; }
        elseif ($budget >= 2000000) { $score += 15; $factors[] = 'Moderate budget (20-50L)'; }
        elseif ($budget > 0) { $score += 10; $factors[] = 'Budget specified'; }

        // Source quality (0-20)
        $highValueSources = ['website', 'referral', 'walk_in', 'whatsapp'];
        if (in_array($lead['source'], $highValueSources)) {
            $score += 20;
            $factors[] = 'High-value source: ' . $lead['source'];
        } else {
            $score += 10;
        }

        // Engagement signals (0-25)
        $activities = $this->db->fetchAll("SELECT * FROM lead_activities WHERE lead_id = ?", [$leadId]);
        if (count($activities) > 5) { $score += 25; $factors[] = 'High engagement (' . count($activities) . ' activities)'; }
        elseif (count($activities) > 2) { $score += 15; $factors[] = 'Moderate engagement'; }
        elseif (count($activities) > 0) { $score += 10; $factors[] = 'Some engagement'; }

        // Status progression (0-15)
        $progressionScores = [
            'new' => 0, 'contacted' => 5, 'qualified' => 10, 
            'proposal' => 15, 'negotiation' => 20, 'closed_won' => 25
        ];
        $score += $progressionScores[$lead['status']] ?? 0;
        $factors[] = "Current status: {$lead['status']}";

        // Recency (0-10)
        $daysSinceActivity = $lead['last_activity_date'] 
            ? (time() - strtotime($lead['last_activity_date'])) / 86400 
            : 999;
        if ($daysSinceActivity <= 3) { $score += 10; $factors[] = 'Very recent activity'; }
        elseif ($daysSinceActivity <= 7) { $score += 5; $factors[] = 'Recent activity'; }

        $score = min(100, max(0, $score));
        
        $grade = $score >= 80 ? 'A' : ($score >= 60 ? 'B' : ($score >= 40 ? 'C' : 'D'));
        
        return [
            'success' => true,
            'lead_id' => $leadId,
            'score' => $score,
            'grade' => $grade,
            'factors' => $factors,
            'recommendation' => $this->getRecommendation($score)
        ];
    }

    private function getRecommendation(int $score): string
    {
        if ($score >= 80) return 'Hot lead - Immediate follow-up, schedule site visit';
        if ($score >= 60) return 'Warm lead - Schedule call within 24 hours, send brochure';
        if ($score >= 40) return 'Cold lead - Add to nurture campaign, weekly follow-up';
        return 'Low priority - Add to long-term nurture, monthly check-in';
    }

    /**
     * Predict conversion probability for all leads
     */
    public function predictConversions(array $filters = []): array
    {
        $tid = $this->tenantId();
        $where = "1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['assigned_to'])) {
            $where .= " AND assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }

        $leads = $this->db->fetchAll(
            "SELECT id FROM leads WHERE $where LIMIT 500",
            $params
        );

        $results = [];
        foreach ($leads as $lead) {
            $result = $this->scoreLead($lead['id']);
            if ($result['success']) {
                $results[] = [
                    'lead_id' => $lead['id'],
                    'score' => $result['score'],
                    'grade' => $result['grade'],
                    'recommendation' => $result['recommendation']
                ];
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'success' => true,
            'total_analyzed' => count($results),
            'hot' => count(array_filter($results, fn($r) => $r['score'] >= 80)),
            'warm' => count(array_filter($results, fn($r) => $r['score'] >= 60 && $r['score'] < 80)),
            'cold' => count(array_filter($results, fn($r) => $r['score'] < 40)),
            'leads' => array_slice($results, 0, 50)
        ];
    }

    /**
     * Get price trend for all colonies
     */
    public function getAllColonyTrends(): array
    {
        $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE is_active = 1");
        $trends = [];

        foreach ($colonies as $colony) {
            $trend = $this->predictPriceTrend($colony['id'], 6);
            if ($trend['success']) {
                $trends[] = [
                    'colony_id' => $colony['id'],
                    'colony_name' => $colony['name'],
                    'trend' => $trend['trend'],
                    'monthly_change_pct' => $trend['monthly_change_pct'],
                    'confidence' => $trend['confidence'],
                    'current_price' => $trend['current_price']
                ];
            }
        }

        usort($trends, fn($a, $b) => $b['monthly_change_pct'] <=> $a['monthly_change_pct']);

        return [
            'success' => true,
            'trends' => $trends,
            'summary' => [
                'rising' => count(array_filter($trends, fn($t) => $t['trend'] === 'rising')),
                'falling' => count(array_filter($trends, fn($t) => $t['trend'] === 'falling')),
                'stable' => count(array_filter($trends, fn($t) => $t['trend'] === 'stable'))
            ]
        ];
    }
}