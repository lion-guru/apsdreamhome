<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

/**
 * Lead Scoring Controller
 * Handles lead scoring dashboard and management
 */
class LeadScoringController extends AdminController
{
    protected $db;
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Lead Scoring Dashboard
     */
    public function index()
    {
        try {
            // Get filter parameters
            $scoreMin = isset($_GET['score_min']) ? intval($_GET['score_min']) : 0;
            $scoreMax = isset($_GET['score_max']) ? intval($_GET['score_max']) : 100;
            $status = $_GET['status'] ?? '';
            $source = $_GET['source'] ?? '';
            $assignedTo = $_GET['assigned_to'] ?? '';

            // Get leads with scores
            $leads = $this->getLeadsWithScores($scoreMin, $scoreMax, $status, $source, $assignedTo);

            // Get score distribution
            $scoreDistribution = $this->getScoreDistribution();

            // Get agents for filter
            $agents = $this->getAgents();

            // Get scoring statistics
            $stats = $this->getScoringStats();

            $data = [
                'page_title' => 'Lead Scoring Dashboard - APS Dream Home',
                'leads' => $leads,
                'score_distribution' => $scoreDistribution,
                'agents' => $agents,
                'stats' => $stats,
                'filters' => [
                    'score_min' => $scoreMin,
                    'score_max' => $scoreMax,
                    'status' => $status,
                    'source' => $source,
                    'assigned_to' => $assignedTo
                ]
            ];

            $this->render('admin/leads/scoring', $data);
        } catch (\Exception $e) {
            error_log("LeadScoringController::index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load lead scoring dashboard');
            $this->redirect('/admin/leads');
        }
    }

    /**
     * Calculate scores for all leads
     */
    public function recalculateScores()
    {
        try {
            $sql = "SELECT l.*, 
                           (SELECT COUNT(*) FROM lead_engagement_metrics WHERE lead_id = l.id) as engagement_count,
                           (SELECT AVG(time_spent) FROM lead_engagement_metrics WHERE lead_id = l.id) as avg_time_spent
                    FROM leads l
                    WHERE l.status != 'converted' AND l.status != 'lost'";

            $stmt = $this->pdo->query($sql);
            $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $updated = 0;
            foreach ($leads as $lead) {
                $score = $this->calculateLeadScore($lead);
                $this->saveLeadScore($lead['id'], $score);
                $updated++;
            }

            $this->setFlash('success', "Recalculated scores for {$updated} leads");
            $this->redirect('/admin/leads/scoring');
        } catch (\Exception $e) {
            error_log("LeadScoringController::recalculateScores error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to recalculate scores');
            $this->redirect('/admin/leads/scoring');
        }
    }

    /**
     * Get single lead score details
     */
    public function getScoreDetails($leadId)
    {
        try {
            $lead = $this->getLeadById($leadId);

            if (!$lead) {
                return parent::jsonResponse(['success' => false, 'message' => 'Lead not found']);
            }

            $scoreBreakdown = $this->getScoreBreakdown($lead);
            $scoreHistory = $this->getScoreHistory($leadId);

            return $this->jsonResponse([
                'success' => true,
                'lead' => $lead,
                'score_breakdown' => $scoreBreakdown,
                'score_history' => $scoreHistory
            ]);
        } catch (\Exception $e) {
            error_log("LeadScoringController::getScoreDetails error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to get score details']);
        }
    }

    /**
     * Export leads by score
     */
    public function export()
    {
        try {
            $scoreMin = $_GET['score_min'] ?? 0;
            $scoreMax = $_GET['score_max'] ?? 100;

            $leads = $this->getLeadsWithScores($scoreMin, $scoreMax);

            // Generate CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="leads_score_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');

            // Headers
            fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Score', 'Status', 'Source', 'Assigned To', 'Last Activity']);

            // Data
            foreach ($leads as $lead) {
                fputcsv($output, [
                    $lead['id'],
                    $lead['name'],
                    $lead['email'],
                    $lead['phone'],
                    $lead['score'] ?? 0,
                    $lead['status'],
                    $lead['source'],
                    $lead['assigned_name'] ?? 'Unassigned',
                    $lead['last_activity_date'] ?? 'Never'
                ]);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log("LeadScoringController::export error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export leads');
            $this->redirect('/admin/leads/scoring');
        }
    }

    /**
     * Calculate lead score based on various factors
     */
    private function calculateLeadScore($lead)
    {
        $score = 0;
        $breakdown = [];

        // 1. Budget Match (30% weight)
        $budgetScore = $this->calculateBudgetScore($lead);
        $score += $budgetScore['score'];
        $breakdown['budget'] = $budgetScore;

        // 2. Location Preference (20% weight)
        $locationScore = $this->calculateLocationScore($lead);
        $score += $locationScore['score'];
        $breakdown['location'] = $locationScore;

        // 3. Property Type Match (20% weight)
        $propertyScore = $this->calculatePropertyScore($lead);
        $score += $propertyScore['score'];
        $breakdown['property'] = $propertyScore;

        // 4. Engagement Level (15% weight)
        $engagementScore = $this->calculateEngagementScore($lead);
        $score += $engagementScore['score'];
        $breakdown['engagement'] = $engagementScore;

        // 5. Source Quality (15% weight)
        $sourceScore = $this->calculateSourceScore($lead);
        $score += $sourceScore['score'];
        $breakdown['source'] = $sourceScore;

        return [
            'total' => min(100, round($score)),
            'breakdown' => $breakdown
        ];
    }

    /**
     * Calculate budget score
     */
    private function calculateBudgetScore($lead)
    {
        $maxScore = 30;
        $score = 0;

        if (!empty($lead['budget']) && $lead['budget'] > 0) {
            $score = $maxScore; // Has budget specified
        } elseif (!empty($lead['budget_range'])) {
            $score = $maxScore * 0.8; // Has budget range
        } else {
            $score = $maxScore * 0.3; // No budget info
        }

        return [
            'score' => $score,
            'max' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100),
            'factor' => 'Budget Match'
        ];
    }

    /**
     * Calculate location score
     */
    private function calculateLocationScore($lead)
    {
        $maxScore = 20;
        $score = 0;

        if (!empty($lead['location_preference'])) {
            $score = $maxScore; // Has location preference
        } elseif (!empty($lead['city'])) {
            $score = $maxScore * 0.7; // Has city
        } else {
            $score = $maxScore * 0.2; // No location info
        }

        return [
            'score' => $score,
            'max' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100),
            'factor' => 'Location Preference'
        ];
    }

    /**
     * Calculate property type score
     */
    private function calculatePropertyScore($lead)
    {
        $maxScore = 20;
        $score = 0;

        if (!empty($lead['property_interest'])) {
            $score = $maxScore; // Has property interest
        } else {
            $score = $maxScore * 0.4; // No property interest specified
        }

        return [
            'score' => $score,
            'max' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100),
            'factor' => 'Property Type Match'
        ];
    }

    /**
     * Calculate engagement score
     */
    private function calculateEngagementScore($lead)
    {
        $maxScore = 15;
        $score = 0;

        // Based on engagement metrics
        $engagementCount = $lead['engagement_count'] ?? 0;
        $avgTimeSpent = $lead['avg_time_spent'] ?? 0;

        if ($engagementCount > 5) {
            $score = $maxScore;
        } elseif ($engagementCount > 2) {
            $score = $maxScore * 0.7;
        } elseif ($engagementCount > 0) {
            $score = $maxScore * 0.4;
        } else {
            $score = $maxScore * 0.1;
        }

        // Bonus for time spent
        if ($avgTimeSpent > 300) { // More than 5 minutes
            $score += 2;
        }

        return [
            'score' => min($score, $maxScore),
            'max' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100),
            'factor' => 'Engagement Level',
            'engagement_count' => $engagementCount,
            'avg_time_spent' => round($avgTimeSpent / 60, 1) // Convert to minutes
        ];
    }

    /**
     * Calculate source quality score
     */
    private function calculateSourceScore($lead)
    {
        $maxScore = 15;
        $score = 0;

        $sourceQuality = [
            'referral' => 1.0,
            'website' => 0.9,
            'google' => 0.9,
            'facebook' => 0.7,
            'instagram' => 0.7,
            'direct' => 0.8,
            'walkin' => 1.0,
            'call' => 0.9,
            'other' => 0.5
        ];

        $source = strtolower($lead['source'] ?? 'other');
        $multiplier = $sourceQuality[$source] ?? 0.5;
        $score = $maxScore * $multiplier;

        return [
            'score' => $score,
            'max' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100),
            'factor' => 'Source Quality',
            'source' => $source
        ];
    }

    /**
     * Save lead score to database
     */
    private function saveLeadScore($leadId, $score)
    {
        try {
            // Check if score record exists
            $sql = "SELECT id FROM lead_scoring WHERE lead_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$leadId]);
            $existing = $stmt->fetch();

            $breakdownJson = json_encode($score['breakdown']);

            if ($existing) {
                // Update existing
                $sql = "UPDATE lead_scoring 
                        SET score = ?, breakdown_json = ?, calculated_at = NOW()
                        WHERE lead_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$score['total'], $breakdownJson, $leadId]);
            } else {
                // Insert new
                $sql = "INSERT INTO lead_scoring 
                        (lead_id, score, breakdown_json, calculated_at, created_at)
                        VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$leadId, $score['total'], $breakdownJson]);
            }

            // Also save to history
            $sql = "INSERT INTO lead_scoring_history 
                    (lead_id, score, calculated_at) 
                    VALUES (?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$leadId, $score['total']]);
        } catch (\Exception $e) {
            error_log("LeadScoringController::saveLeadScore error: " . $e->getMessage());
        }
    }

    /**
     * Get leads with scores
     */
    private function getLeadsWithScores($scoreMin = 0, $scoreMax = 100, $status = '', $source = '', $assignedTo = '')
    {
        $sql = "SELECT l.*, 
                       COALESCE(ls.score, 0) as score,
                       ls.breakdown_json,
                       ls.calculated_at,
                       u.name as assigned_name,
                       (SELECT COUNT(*) FROM lead_engagement_metrics WHERE lead_id = l.id) as engagement_count
                FROM leads l
                LEFT JOIN lead_scoring ls ON l.id = ls.lead_id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE 1=1";

        $params = [];

        if ($scoreMin > 0 || $scoreMax < 100) {
            $sql .= " AND COALESCE(ls.score, 0) BETWEEN ? AND ?";
            $params[] = $scoreMin;
            $params[] = $scoreMax;
        }

        if (!empty($status)) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }

        if (!empty($source)) {
            $sql .= " AND l.source = ?";
            $params[] = $source;
        }

        if (!empty($assignedTo)) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $sql .= " ORDER BY score DESC, l.created_at DESC
                  LIMIT 100";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get score distribution
     */
    private function getScoreDistribution()
    {
        $sql = "SELECT 
                    SUM(CASE WHEN score >= 70 THEN 1 ELSE 0 END) as hot_count,
                    SUM(CASE WHEN score >= 40 AND score < 70 THEN 1 ELSE 0 END) as warm_count,
                    SUM(CASE WHEN score < 40 THEN 1 ELSE 0 END) as cold_count,
                    COUNT(*) as total_count
                FROM lead_scoring
                WHERE calculated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get agents list
     */
    private function getAgents()
    {
        $sql = "SELECT id, name FROM users WHERE role IN ('agent', 'manager', 'admin') AND status = 'active' ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get scoring statistics
     */
    private function getScoringStats()
    {
        $sql = "SELECT 
                    AVG(score) as avg_score,
                    MAX(score) as max_score,
                    MIN(score) as min_score,
                    COUNT(*) as total_scored
                FROM lead_scoring
                WHERE calculated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get lead by ID
     */
    private function getLeadById($leadId)
    {
        $sql = "SELECT l.*, COALESCE(ls.score, 0) as score, ls.breakdown_json
                FROM leads l
                LEFT JOIN lead_scoring ls ON l.id = ls.lead_id
                WHERE l.id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$leadId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get score breakdown for lead
     */
    private function getScoreBreakdown($lead)
    {
        if (!empty($lead['breakdown_json'])) {
            return json_decode($lead['breakdown_json'], true);
        }

        // Calculate fresh
        $score = $this->calculateLeadScore($lead);
        return $score['breakdown'];
    }

    /**
     * Get score history
     */
    private function getScoreHistory($leadId)
    {
        $sql = "SELECT score, calculated_at 
                FROM lead_scoring_history 
                WHERE lead_id = ? 
                ORDER BY calculated_at DESC 
                LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$leadId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
