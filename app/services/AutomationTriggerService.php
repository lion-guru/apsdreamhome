<?php
/**
 * Automation Trigger Service
 * Handles automated actions based on events
 */

namespace App\Services;

use App\Core\Database\Database;

class AutomationTriggerService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Handle lead creation event
     */
    public function onLeadCreated($leadId)
    {
        $lead = $this->getLead($leadId);
        if (!$lead) return;
        
        // Trigger: Score calculation
        $scoringService = new LeadScoringService();
        $scores = $scoringService->calculateScore($leadId);
        if ($scores) {
            $scoringService->saveScore($leadId, $scores);
        }
        
        // Trigger: Auto-assign based on location
        $this->autoAssignByLocation($leadId, $lead);
        
        // Trigger: Auto-tag based on budget
        $this->autoTagByBudget($leadId, $lead);
        
        // Trigger: Notify manager for high-value leads
        if (floatval($lead['budget'] ?? 0) >= 10000000) {
            $this->notifyHighValueLead($leadId, $lead);
        }
        
        // Trigger: Add to campaign
        $this->addToCampaign($leadId, $lead);
        
        // Log activity
        $this->logActivity($leadId, 'lead_created', 'Automation: Lead created triggers processed');
    }
    
    /**
     * Handle property inquiry event
     */
    public function onPropertyInquiry($leadId, $propertyId)
    {
        // Log the inquiry
        $this->logActivity($leadId, 'property_inquiry', "Inquiry for property #$propertyId");
        
        // Update engagement metrics
        $this->updateEngagementMetric($leadId, 'inquiries', 1);
        
        // Calculate new score
        $scoringService = new LeadScoringService();
        $scores = $scoringService->calculateScore($leadId);
        if ($scores) {
            $scoringService->saveScore($leadId, $scores);
        }
        
        // Check for hot lead
        if ($scores['is_hot']) {
            $this->notifySalesTeam($leadId, "Hot lead inquiry for property #$propertyId");
        }
    }
    
    /**
     * Handle website visit event
     */
    public function onWebsiteVisit($leadId, $propertyId = null, $duration = 0)
    {
        // Update visit count
        $this->updateEngagementMetric($leadId, 'page_views', 1);
        
        if ($propertyId) {
            // Log property view
            $this->db->execute(
                "INSERT INTO lead_visits (lead_id, property_id, visit_type, visit_date, duration_seconds, source, created_at)
                 VALUES (?, ?, 'property_page', NOW(), ?, 'website', NOW())",
                [$leadId, $propertyId, $duration]
            );
            
            // Log activity
            $this->logActivity($leadId, 'property_view', "Viewed property #$propertyId for $duration seconds");
        }
        
        // Recalculate score
        $scoringService = new LeadScoringService();
        $scores = $scoringService->calculateScore($leadId);
        if ($scores) {
            $scoringService->saveScore($leadId, $scores);
        }
    }
    
    /**
     * Handle payment received event
     */
    public function onPaymentReceived($leadId, $amount, $paymentId)
    {
        // Log activity
        $this->logActivity($leadId, 'payment_received', "Payment of ₹" . number_format($amount) . " received");
        
        // Update lead status
        $this->db->execute(
            "UPDATE leads SET status = 'qualified' WHERE id = ? AND status = 'contacted'",
            [$leadId]
        );
        
        // Award points if MLM enabled
        $this->awardReferralPoints($leadId, $amount);
        
        // Notify team
        $this->notifySalesTeam($leadId, "Payment received: ₹" . number_format($amount));
    }
    
    /**
     * Handle lead status change
     */
    public function onLeadStatusChange($leadId, $oldStatus, $newStatus)
    {
        // Log status change
        $this->logActivity($leadId, 'status_change', "Status changed from $oldStatus to $newStatus");
        
        // Update status history
        $this->db->execute(
            "INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_at)
             VALUES (?, ?, ?, NOW())",
            [$leadId, $oldStatus, $newStatus]
        );
        
        // Auto-triggers based on status
        if ($newStatus === 'closed_won') {
            $this->onLeadWon($leadId);
        } elseif ($newStatus === 'closed_lost') {
            $this->onLeadLost($leadId);
        }
    }
    
    /**
     * Handle lead not contacted for X hours
     */
    public function checkUncontactedLeads($hours = 24)
    {
        $leads = $this->db->fetchAll(
            "SELECT l.*, u.name as assigned_to_name 
             FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.status = 'new' 
             AND l.assigned_to IS NOT NULL
             AND l.created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
             AND NOT EXISTS (
                 SELECT 1 FROM lead_activities 
                 WHERE lead_id = l.id 
                 AND activity_type IN ('call', 'email', 'meeting')
                 AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
             )",
            [$hours, $hours]
        );
        
        foreach ($leads as $lead) {
            // Send alert to assigned user
            $this->sendAlert(
                $lead['assigned_to'],
                "Uncontacted Lead Alert",
                "Lead '{$lead['name']}' has not been contacted in $hours hours."
            );
            
            // Log alert
            $this->logActivity($lead['id'], 'alert_sent', "Uncontacted alert sent after $hours hours");
        }
        
        return count($leads);
    }
    
    /**
     * Auto-assign lead based on location
     */
    private function autoAssignByLocation($leadId, $lead)
    {
        $location = strtolower($lead['location_preference'] ?? '');
        
        // Get users with expertise in that location
        $users = $this->db->fetchAll(
            "SELECT u.id FROM users u
             JOIN user_profiles up ON u.id = up.user_id
             WHERE u.status = 'active' 
             AND u.user_type IN ('agent', 'associate')
             AND (up.preferred_locations LIKE ? OR ? = '')
             LIMIT 1",
            ["%$location%", $location]
        );
        
        if (!empty($users)) {
            $this->db->execute(
                "UPDATE leads SET assigned_to = ? WHERE id = ?",
                [$users[0]['id'], $leadId]
            );
            
            $this->logActivity($leadId, 'auto_assigned', "Auto-assigned based on location preference");
        }
    }
    
    /**
     * Auto-tag lead based on budget
     */
    private function autoTagByBudget($leadId, $lead)
    {
        $budget = floatval($lead['budget'] ?? 0);
        
        if ($budget >= 50000000) {
            $this->addTag($leadId, 'Premium');
            $this->addTag($leadId, 'High-Value');
        } elseif ($budget >= 20000000) {
            $this->addTag($leadId, 'High-Value');
        } elseif ($budget <= 1000000) {
            $this->addTag($leadId, 'Budget');
        }
    }
    
    /**
     * Add tag to lead
     */
    private function addTag($leadId, $tagName)
    {
        // Get or create tag
        $tag = $this->db->fetch(
            "SELECT id FROM lead_tags WHERE name = ?",
            [$tagName]
        );
        
        if (!$tag) {
            $this->db->execute(
                "INSERT INTO lead_tags (name, color, is_system) VALUES (?, '#FF0000', 1)",
                [$tagName]
            );
            $tagId = $this->db->lastInsertId();
        } else {
            $tagId = $tag['id'];
        }
        
        // Map tag to lead
        $existing = $this->db->fetch(
            "SELECT 1 FROM lead_tag_mapping WHERE lead_id = ? AND tag_id = ?",
            [$leadId, $tagId]
        );
        
        if (!$existing) {
            $this->db->execute(
                "INSERT INTO lead_tag_mapping (lead_id, tag_id) VALUES (?, ?)",
                [$leadId, $tagId]
            );
        }
    }
    
    /**
     * Notify manager for high-value leads
     */
    private function notifyHighValueLead($leadId, $lead)
    {
        // Get managers
        $managers = $this->db->fetchAll(
            "SELECT id FROM users WHERE user_type IN ('admin', 'super_admin') AND status = 'active'"
        );
        
        foreach ($managers as $manager) {
            $this->sendAlert(
                $manager['id'],
                "High Value Lead Alert",
                "New lead with budget ₹" . number_format(floatval($lead['budget'])) . ": {$lead['name']} ({$lead['phone']})"
            );
        }
    }
    
    /**
     * Add lead to appropriate campaign
     */
    private function addToCampaign($leadId, $lead)
    {
        $budget = floatval($lead['budget'] ?? 0);
        
        // Find matching campaign
        $campaign = $this->db->fetch(
            "SELECT id FROM campaigns 
             WHERE is_active = 1 
             AND (target_budget_min IS NULL OR target_budget_min <= ?)
             AND (target_budget_max IS NULL OR target_budget_max >= ?)
             LIMIT 1",
            [$budget, $budget]
        );
        
        if ($campaign) {
            $this->db->execute(
                "INSERT INTO campaign_members (campaign_id, lead_id, added_at) VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE added_at = NOW()",
                [$campaign['id'], $leadId]
            );
        }
    }
    
    /**
     * Update engagement metric
     */
    private function updateEngagementMetric($leadId, $metricType, $value)
    {
        $today = date('Y-m-d');
        
        $existing = $this->db->fetch(
            "SELECT id, metric_value FROM lead_engagement_metrics 
             WHERE lead_id = ? AND metric_type = ? AND metric_date = ?",
            [$leadId, $metricType, $today]
        );
        
        if ($existing) {
            $this->db->execute(
                "UPDATE lead_engagement_metrics SET metric_value = metric_value + ? WHERE id = ?",
                [$value, $existing['id']]
            );
        } else {
            $this->db->execute(
                "INSERT INTO lead_engagement_metrics (lead_id, metric_type, metric_value, metric_date, source)
                 VALUES (?, ?, ?, ?, 'automated')",
                [$leadId, $metricType, $value, $today]
            );
        }
    }
    
    /**
     * Award referral points
     */
    private function awardReferralPoints($leadId, $amount)
    {
        $lead = $this->getLead($leadId);
        if (!$lead || !$lead['referred_by']) return;
        
        // Calculate points (1% of amount in rupees)
        $points = intval($amount / 100);
        
        // Add to referrer's points
        $this->db->execute(
            "UPDATE users SET mlm_points = COALESCE(mlm_points, 0) + ? WHERE id = ?",
            [$points, $lead['referred_by']]
        );
        
        // Log points transaction
        $this->db->execute(
            "INSERT INTO mlm_points_transactions (user_id, points, type, reference_id, description)
             VALUES (?, ?, 'credit', ?, 'Referral bonus for lead $leadId')",
            [$lead['referred_by'], $points, $leadId]
        );
    }
    
    /**
     * Handle lead won
     */
    private function onLeadWon($leadId)
    {
        // Award achievement
        $this->addTag($leadId, 'Won');
        
        // Log
        $this->logActivity($leadId, 'lead_won', 'Lead marked as won - Deal closed');
        
        // Notify team
        $this->notifySalesTeam($leadId, "Lead won! Deal closed successfully.");
    }
    
    /**
     * Handle lead lost
     */
    private function onLeadLost($leadId)
    {
        $this->addTag($leadId, 'Lost');
        $this->logActivity($leadId, 'lead_lost', 'Lead marked as lost');
    }
    
    /**
     * Send alert to user
     */
    private function sendAlert($userId, $title, $message)
    {
        $this->db->execute(
            "INSERT INTO notifications (user_id, title, message, type, created_at)
             VALUES (?, ?, ?, 'alert', NOW())",
            [$userId, $title, $message]
        );
    }
    
    /**
     * Log lead activity
     */
    private function logActivity($leadId, $type, $description)
    {
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, activity_type, description, created_at)
             VALUES (?, ?, ?, NOW())",
            [$leadId, $type, $description]
        );
    }
    
    /**
     * Notify sales team
     */
    private function notifySalesTeam($leadId, $message)
    {
        $team = $this->db->fetchAll(
            "SELECT id FROM users WHERE user_type IN ('admin', 'agent') AND status = 'active'"
        );
        
        foreach ($team as $member) {
            $this->sendAlert($member['id'], "Lead #$leadId Update", $message);
        }
    }
    
    /**
     * Get lead
     */
    private function getLead($leadId)
    {
        return $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
    }
}
