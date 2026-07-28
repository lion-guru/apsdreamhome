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
    use \App\Traits\TenantAwareTrait;

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
            // Check if required tables exist
            $tablesExist = $this->checkRequiredTables();

            // Get filter parameters
            $scoreMin = isset($_GET['score_min']) ? intval($_GET['score_min']) : 0;
            $scoreMax = isset($_GET['score_max']) ? intval($_GET['score_max']) : 100;
            $status = $_GET['status'] ?? '';
            $source = $_GET['source'] ?? '';
            $assignedTo = $_GET['assigned_to'] ?? '';

            // Get leads with scores (or empty if tables don't exist)
            $leads = $tablesExist ? $this->getLeadsWithScores($scoreMin, $scoreMax, $status, $source, $assignedTo) : [];

            // Get score distribution
            $scoreDistribution = $tablesExist ? $this->getScoreDistribution() : ['hot_count' => 0, 'warm_count' => 0, 'cold_count' => 0];

            // Get users for filter
            $users = $this->getAgents();

            // Get scoring statistics
            $stats = $tablesExist ? $this->getScoringStats() : ['avg_score' => 0, 'total_scored' => 0, 'pending_scoring' => 0];

            $data = [
                'page_title' => 'Lead Scoring Dashboard - APS Dream Home',
                'leads' => $leads,
                'score_distribution' => $scoreDistribution,
                'users' => $users,
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
            // Show dashboard with empty data instead of redirecting
            $data = [
                'page_title' => 'Lead Scoring Dashboard - APS Dream Home',
                'leads' => [],
                'score_distribution' => ['hot_count' => 0, 'warm_count' => 0, 'cold_count' => 0],
                'users' => [],
                'stats' => ['avg_score' => 0, 'total_scored' => 0, 'pending_scoring' => 0],
                'filters' => [
                    'score_min' => 0,
                    'score_max' => 100,
                    'status' => '',
                    'source' => '',
                    'assigned_to' => ''
                ]
            ];
            $this->render('admin/leads/scoring', $data);
        }
    }

    /**
     * Check if required tables exist
     */
    private function checkRequiredTables(): bool
    {
        try {
            $this->pdo->query("SELECT 1 FROM lead_scoring LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
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
            list($tSql, $tParams) = $this->tenantWhere();
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
                        WHERE lead_id = ? $tSql";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array_merge([$score['total'], $breakdownJson, $leadId], $tParams));
            } else {
                // Insert new
                $insertExtra = $this->tenantInsertData();
                $cols = "lead_id, score, breakdown_json, calculated_at, created_at";
                $vals = "?, ?, ?, NOW(), NOW()";
                $params = [$leadId, $score['total'], $breakdownJson];
                if (!empty($insertExtra)) {
                    $cols .= ", tenant_id";
                    $vals .= ", ?";
                    $params[] = $insertExtra['tenant_id'];
                }
                $sql = "INSERT INTO lead_scoring ($cols) VALUES ($vals)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // Lead score updates are already saved above in lead_scoring table
        } catch (\Exception $e) {
            error_log("LeadScoringController::saveLeadScore error: " . $e->getMessage());
        }
    }

    /**
     * Get leads with scores
     */
    private function getLeadsWithScores($scoreMin = 0, $scoreMax = 100, $status = '', $source = '', $assignedTo = '')
    {
        try {
            $sql = "SELECT l.*, 
                       COALESCE(ls.score, 0) as score,
                       ls.breakdown_json,
                       ls.calculated_at,
                       u.name as assigned_name,
                       0 as engagement_count
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
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get score distribution
     */
    private function getScoreDistribution()
    {
        try {
            $sql = "SELECT 
                    SUM(CASE WHEN score >= 70 THEN 1 ELSE 0 END) as hot_count,
                    SUM(CASE WHEN score >= 40 AND score < 70 THEN 1 ELSE 0 END) as warm_count,
                    SUM(CASE WHEN score < 40 THEN 1 ELSE 0 END) as cold_count,
                    COUNT(*) as total_scored
                FROM lead_scoring ls
                JOIN leads l ON ls.lead_id = l.id
                WHERE ls.calculated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return [
                'hot_count' => $result['hot_count'] ?? 0,
                'warm_count' => $result['warm_count'] ?? 0,
                'cold_count' => $result['cold_count'] ?? 0,
                'total_scored' => $result['total_scored'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'hot_count' => 0,
                'warm_count' => 0,
                'cold_count' => 0,
                'total_scored' => 0
            ];
        }
    }

    /**
     * Get users list
     */
    private function getAgents()
    {
        try {
            $sql = "SELECT id, name FROM users WHERE role IN ('agent', 'manager', 'admin') AND status = 'active' ORDER BY name";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get scoring statistics
     */
    private function getScoringStats()
    {
        try {
            $sql = "SELECT 
                    AVG(score) as avg_score,
                    MAX(score) as max_score,
                    MIN(score) as min_score,
                    COUNT(*) as total_scored
                FROM lead_scoring
                WHERE calculated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return [
                'avg_score' => round($result['avg_score'] ?? 0, 1),
                'max_score' => $result['max_score'] ?? 0,
                'min_score' => $result['min_score'] ?? 0,
                'total_scored' => $result['total_scored'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'avg_score' => 0,
                'max_score' => 0,
                'min_score' => 0,
                'total_scored' => 0
            ];
        }
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
        $sql = "SELECT score, created_at as calculated_at 
                FROM lead_scoring 
                WHERE lead_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$leadId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Auto-assign leads based on scores
     */
    public function autoAssign()
    {
        try {
            // Get unscored or high-scoring unassigned leads
            $sql = "SELECT l.id, l.name, COALESCE(ls.score, 0) as score
                    FROM leads l
                    LEFT JOIN lead_scoring ls ON l.id = ls.lead_id
                    WHERE (l.assigned_to IS NULL OR l.assigned_to = 0)
                    AND l.status NOT IN ('converted', 'lost')
                    ORDER BY score DESC
                    LIMIT 50";

            $stmt = $this->pdo->query($sql);
            $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($leads)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'No leads to assign',
                    'assigned' => 0
                ]);
            }

            // Get available users (least busy first)
            $users = $this->pdo->query(
                "SELECT u.id, u.name, COUNT(l.id) as lead_count
                 FROM users u
                 LEFT JOIN leads l ON u.id = l.assigned_to AND l.status NOT IN ('converted', 'lost')
                 WHERE u.role IN ('agent', 'manager')
                 GROUP BY u.id, u.name
                 ORDER BY lead_count ASC"
            )->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($users)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'No available users found'
                ]);
            }

            $assigned = 0;
            $agentIndex = 0;

            foreach ($leads as $lead) {
                $agent = $users[$agentIndex % count($users)];
                $stmt = $this->pdo->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$agent['id'], $lead['id']]);
                $assigned++;
                $agentIndex++;
            }

            $this->setFlash('success', "Auto-assigned {$assigned} leads to users");
            return $this->jsonResponse([
                'success' => true,
                'message' => "Auto-assigned {$assigned} leads",
                'assigned' => $assigned
            ]);
        } catch (\Exception $e) {
            error_log("LeadScoringController::autoAssign error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to auto-assign leads: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process all leads for scoring
     */
    public function processAll()
    {
        return $this->recalculateScores();
    }

    /**
     * Rescore a single lead
     */
    public function rescore($id)
    {
        try {
            $lead = $this->getLeadById($id);

            if (!$lead) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Lead not found'
                ]);
            }

            $score = $this->calculateLeadScore($lead);
            $this->saveLeadScore($id, $score);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lead rescored successfully',
                'score' => $score['total'],
                'breakdown' => $score['breakdown']
            ]);
        } catch (\Exception $e) {
            error_log("LeadScoringController::rescore error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to rescore lead: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show lead score detail
     */
    public function show($id)
    {
        try {
            $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
            $score = $lead ? $this->calculateLeadScore($lead) : null;
            $history = $id ? $this->getScoreHistory($id) : [];

            $data = [
                'page_title' => 'Lead Score Detail',
                'lead' => $lead,
                'score' => $score,
                'history' => $history
            ];

            $this->render('admin/leads/scoring_show', $data);
        } catch (\Exception $e) {
            error_log("LeadScoringController::show error: " . $e->getMessage());
            $this->render('admin/leads/scoring_show', [
                'page_title' => 'Lead Score Detail',
                'lead' => null,
                'score' => null,
                'history' => []
            ]);
        }
    }
}
