<?php
/**
 * Lead Scoring Service
 * AI-powered lead qualification system
 */

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class LeadScoringService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Calculate lead score based on multiple factors
     */
    public function calculateScore($leadId)
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return null;
        
        $scores = [
            'demographics' => $this->calculateDemographicsScore($lead),
            'engagement' => $this->calculateEngagementScore($leadId),
            'behavior' => $this->calculateBehaviorScore($leadId),
            'ai_analysis' => $this->calculateAIScore($lead)
        ];
        
        $totalScore = array_sum($scores);
        $rank = $this->getRank($totalScore);
        
        return [
            'total' => $totalScore,
            'demographics' => $scores['demographics'],
            'engagement' => $scores['engagement'],
            'behavior' => $scores['behavior'],
            'ai_analysis' => $scores['ai_analysis'],
            'rank' => $rank,
            'is_hot' => $totalScore >= 80
        ];
    }
    
    /**
     * Demographics score based on budget, location, property type
     */
    private function calculateDemographicsScore($lead)
    {
        $score = 0;
        
        // Budget scoring
        $budget = floatval($lead['budget'] ?? 0);
        if ($budget >= 50000000) $score += 30; // 5 Cr+
        elseif ($budget >= 20000000) $score += 25; // 2 Cr+
        elseif ($budget >= 10000000) $score += 20; // 1 Cr+
        elseif ($budget >= 5000000) $score += 15;  // 50L+
        elseif ($budget >= 2000000) $score += 10;  // 20L+
        else $score += 5;
        
        // Property type scoring
        $propertyType = strtolower($lead['property_interest'] ?? '');
        if (strpos($propertyType, 'commercial') !== false) $score += 10;
        elseif (strpos($propertyType, 'villa') !== false) $score += 8;
        elseif (strpos($propertyType, 'house') !== false) $score += 6;
        elseif (strpos($propertyType, 'flat') !== false) $score += 5;
        elseif (strpos($propertyType, 'plot') !== false) $score += 4;
        
        // Location preference
        if (!empty($lead['location_preference'])) $score += 5;
        
        return min(40, $score); // Max 40 points
    }
    
    /**
     * Engagement score based on interactions
     */
    private function calculateEngagementScore($leadId)
    {
        $score = 0;
        
         // Activities count
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $activities = $this->db->fetch(
            "SELECT COUNT(*) FROM lead_activities WHERE lead_id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $activityCount = intval($activities['COUNT(*)'] ?? 0);
        $score += min(15, $activityCount * 3);
        
        // Visit count
        $visits = $this->db->fetch(
            "SELECT COUNT(*) FROM lead_visits WHERE lead_id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $visitCount = intval($visits['COUNT(*)'] ?? 0);
        $score += min(15, $visitCount * 5);
        
        // Notes count
        $notes = $this->db->fetch(
            "SELECT COUNT(*) FROM lead_notes WHERE lead_id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $noteCount = intval($notes['COUNT(*)'] ?? 0);
        $score += min(10, $noteCount * 2);
        
        return min(40, $score); // Max 40 points
    }
    
    /**
     * Behavior score based on recent activity
     */
    private function calculateBehaviorScore($leadId)
    {
        $score = 0;
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        
        // Recent engagement (last 7 days)
        $recentVisits = $this->db->fetch(
            "SELECT COUNT(*) FROM lead_visits 
             WHERE lead_id = ?" . $tidSql . " AND visit_date > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $recentCount = intval($recentVisits['COUNT(*)'] ?? 0);
        $score += min(20, $recentCount * 7);
        
        // Page views
        $pageViews = $this->db->fetch(
            "SELECT SUM(metric_value) FROM lead_engagement_metrics 
             WHERE lead_id = ?" . $tidSql . " AND metric_type = 'page_views'",
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $views = intval($pageViews['SUM(metric_value)'] ?? 0);
        $score += min(15, $views);
        
        // Time on site (duration)
        $duration = $this->db->fetch(
            "SELECT SUM(duration_seconds) FROM lead_visits WHERE lead_id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        $totalDuration = intval($duration['SUM(duration_seconds)'] ?? 0);
        if ($totalDuration > 600) $score += 10; // > 10 minutes
        elseif ($totalDuration > 180) $score += 5; // > 3 minutes
        
        return min(40, $score); // Max 40 points
    }
    
    /**
     * AI analysis score based on chat history and recommendations
     */
    private function calculateAIScore($lead)
    {
        $score = 0;
        
        // AI summary exists
        if (!empty($lead['ai_summary'])) $score += 10;
        
        // AI analysis exists
        if (!empty($lead['ai_analysis'])) $score += 10;
        
        try {
            // Has AI-generated recommendations
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $recommendations = $this->db->fetch(
                "SELECT COUNT(*) FROM ai_recommendations WHERE user_id = ?" . $tidSql,
                $tid > 1 ? [$lead['assigned_to'] ?? 0, $tid] : [$lead['assigned_to'] ?? 0]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $recCount = intval($recommendations['COUNT(*)'] ?? 0);
        $score += min(10, $recCount * 2);
        
        // Lead category
        $category = strtolower($lead['lead_category'] ?? '');
        if ($category === 'hot') $score += 10;
        elseif ($category === 'warm') $score += 5;
        
        return min(40, $score); // Max 40 points
    }
    
    /**
     * Get rank based on score
     */
    private function getRank($score)
    {
        if ($score >= 90) return 'hot_plus';
        if ($score >= 70) return 'hot';
        if ($score >= 50) return 'warm';
        return 'cold';
    }
    
    /**
     * Get lead details
     */
    private function getLead($leadId)
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        return $this->db->fetch(
            "SELECT * FROM leads WHERE id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
    }
    
    /**
     * Save score to database
     */
    public function saveScore($leadId, $scores)
    {
        // Check if score exists
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $existing = $this->db->fetch(
            "SELECT id FROM lead_scores WHERE lead_id = ?" . $tidSql,
            $tid > 1 ? [$leadId, $tid] : [$leadId]
        );
        
        $factors = json_encode([
            'demographics' => $scores['demographics'],
            'engagement' => $scores['engagement'],
            'behavior' => $scores['behavior'],
            'ai_analysis' => $scores['ai_analysis']
        ]);
        
        if ($existing) {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [
                $scores['total'],
                $scores['demographics'],
                $scores['engagement'],
                $scores['behavior'],
                $scores['ai_analysis'],
                $scores['rank'],
                $scores['is_hot'] ? 1 : 0,
                $leadId
            ];
            if ($tid > 1) $params[] = $tid;
            $this->db->execute(
                "UPDATE lead_scores SET 
                    total_score = ?,
                    demographics_score = ?,
                    engagement_score = ?,
                    behavior_score = ?,
                    ai_analysis_score = ?,
                    rank = ?,
                    is_hot_lead = ?,
                    calculated_at = NOW()
                WHERE lead_id = ?" . $tidSql,
                $params
            );
        } else {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [
                $leadId,
                $scores['total'],
                $scores['demographics'],
                $scores['engagement'],
                $scores['behavior'],
                $scores['ai_analysis'],
                $scores['rank'],
                $scores['is_hot'] ? 1 : 0
            ];
            if ($tid > 1) $params[] = $tid;
            $this->db->execute(
                "INSERT INTO lead_scores (
                    lead_id, total_score, demographics_score, 
                    engagement_score, behavior_score, ai_analysis_score,
                    rank, is_hot_lead, calculated_at" . ($tid > 1 ? ", tenant_id" : "") . "
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                $params
            );
        }
        
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$scores['total'], $leadId];
        if ($tid > 1) $params[] = $tid;
        $this->db->execute(
            "UPDATE leads SET lead_score = ?, last_scored_at = NOW() WHERE id = ?" . $tidSql,
            $params
        );
        
        return true;
    }
    
    /**
     * Process all leads and calculate scores
     */
    public function processAllLeads()
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? ' WHERE tenant_id = ?' : ' WHERE 1=1';
        $tidParam = $tid > 1 ? [$tid] : [];
        $leads = $this->db->fetchAll("SELECT id FROM leads" . $tidSql, $tidParam);
        $processed = 0;
        
        foreach ($leads as $lead) {
            $scores = $this->calculateScore($lead['id']);
            if ($scores) {
                $this->saveScore($lead['id'], $scores);
                $processed++;
            }
        }
        
        return $processed;
    }
    
    /**
     * Auto-assign hot leads
     */
    public function autoAssignHotLeads()
    {
        $tid = $this->tenantId();
        $tidWhere = $tid > 1 ? ' AND l.tenant_id = ?' : '';
        $hotParams = $tid > 1 ? [$tid] : [];
        $hotLeads = $this->db->fetchAll(
            "SELECT ls.*, l.name, l.phone, l.assigned_to 
             FROM lead_scores ls
             JOIN leads l ON ls.lead_id = l.id
             WHERE ls.is_hot_lead = 1 AND l.assigned_to IS NULL" . $tidWhere, $hotParams
        );
        
        // Get available users
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $users = $this->db->fetchAll(
            "SELECT id FROM users WHERE role = 'agent' AND status = 'active'" . $tidSql,
            $tid > 1 ? [$tid] : []
        );
        
        if (empty($users)) {
            // Fallback to users
            $users = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'associate' AND status = 'active'" . $tidSql,
                $tid > 1 ? [$tid] : []
            );
        }
        
        if (empty($users)) {
            // Fallback to admin
            $users = $this->db->fetchAll(
                "SELECT id FROM users WHERE role IN ('admin', 'super_admin') AND status = 'active'" . $tidSql,
                $tid > 1 ? [$tid] : []
            );
        }
        
        $assigned = 0;
        $agentIndex = 0;
        
        foreach ($hotLeads as $lead) {
            if (!empty($users)) {
                $assignTo = $users[$agentIndex % count($users)]['id'];
                
                $this->db->execute(
                    "UPDATE leads SET assigned_to = ?, priority = 'high' WHERE id = ?" . $tidSql,
                    array_merge([$assignTo, $lead['lead_id']], $tid > 1 ? [$tid] : [])
                );
                
                $this->db->execute(
                    "UPDATE lead_scores SET assigned_to = ?, auto_assign_at = NOW() WHERE lead_id = ?" . $tidSql,
                    array_merge([$assignTo, $lead['lead_id']], $tid > 1 ? [$tid] : [])
                );
                
                // Log assignment
                $logParams = [$lead['lead_id'], "Auto-assigned due to high score ({$lead['total_score']})", $assignTo];
                if ($tid > 1) $logParams[] = $tid;
                $this->db->execute(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by" . ($tid > 1 ? ", tenant_id" : "") . ") 
                     VALUES (?, 'auto_assigned', ?, ?" . ($tid > 1 ? ", ?" : "") . ")",
                    $logParams
                );
                
                $assigned++;
                $agentIndex++;
            }
        }
        
        return $assigned;
    }
}
