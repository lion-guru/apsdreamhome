<?php

namespace App\Services\Marketing;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;

/**
 * Marketing Automation Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class MarketingAutomationService
{
    private $database;
    private $logger;
    private $config;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();
        
        $this->initMarketingSystem();
    }
    
    /**
     * Initialize marketing system
     */
    private function initMarketingSystem()
    {
        $this->createMarketingTables();
    }
    
    /**
     * Create marketing database tables
     */
    private function createMarketingTables()
    {
        try {
            $tables = [
                // Marketing leads table
                "CREATE TABLE IF NOT EXISTS marketing_leads (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255),
                    email VARCHAR(255),
                    phone VARCHAR(20),
                    source VARCHAR(100),
                    campaign VARCHAR(100),
                    status ENUM('new', 'contacted', 'interested', 'converted', 'lost') DEFAULT 'new',
                    score INT DEFAULT 0,
                    last_contacted TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_email (email),
                    INDEX idx_status (status),
                    INDEX idx_score (score),
                    INDEX idx_source (source)
                )",
                
                // Marketing campaigns table
                "CREATE TABLE IF NOT EXISTS marketing_campaigns (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255),
                    type ENUM('email', 'sms', 'social', 'google', 'facebook'),
                    subject VARCHAR(500),
                    content TEXT,
                    target_audience JSON,
                    schedule_at TIMESTAMP NULL,
                    status ENUM('draft', 'scheduled', 'running', 'completed', 'paused') DEFAULT 'draft',
                    sent_count INT DEFAULT 0,
                    opened_count INT DEFAULT 0,
                    clicked_count INT DEFAULT 0,
                    converted_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_type (type),
                    INDEX idx_status (status),
                    INDEX idx_schedule (schedule_at)
                )",
                
                // Marketing automations table
                "CREATE TABLE IF NOT EXISTS marketing_automations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255),
                    trigger_type ENUM('lead_signup', 'property_view', 'inquiry', 'booking', 'payment'),
                    trigger_conditions JSON,
                    actions JSON,
                    is_active BOOLEAN DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_trigger (trigger_type),
                    INDEX idx_active (is_active)
                )",
                
                // Marketing analytics table
                "CREATE TABLE IF NOT EXISTS marketing_analytics (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    campaign_id INT,
                    lead_id INT,
                    action_type VARCHAR(100),
                    action_data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_campaign (campaign_id),
                    INDEX idx_lead (lead_id),
                    INDEX idx_action (action_type)
                )"
            ];
            
            foreach ($tables as $sql) {
                $this->database->query($sql);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Error creating marketing tables', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Capture lead from form
     */
    public function captureLead($name, $email, $phone, $source = 'website', $campaign = '')
    {
        try {
            // Check if lead already exists
            $existingLead = $this->getLeadByEmail($email);
            
            if ($existingLead) {
                // Update existing lead
                $sql = "UPDATE marketing_leads SET 
                        source = ?, 
                        campaign = ?, 
                        updated_at = NOW() 
                        WHERE id = ?";
                
                $this->database->query($sql, [$source, $campaign, $existingLead['id']]);
                $leadId = $existingLead['id'];
            } else {
                // Create new lead
                $sql = "INSERT INTO marketing_leads (name, email, phone, source, campaign)
                        VALUES (?, ?, ?, ?, ?)";
                
                $this->database->query($sql, [$name, $email, $phone, $source, $campaign]);
                $leadId = $this->database->lastInsertId();
            }
            
            // Trigger automation workflows
            $this->triggerAutomation('lead_signup', $leadId);
            
            // Calculate lead score
            $this->calculateLeadScore($leadId);
            
            $this->logger->info('Lead captured', [
                'lead_id' => $leadId,
                'email' => $email,
                'source' => $source,
                'campaign' => $campaign
            ]);
            
            return [
                'success' => true,
                'lead_id' => $leadId,
                'message' => 'Lead captured successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to capture lead', [
                'error' => $e->getMessage(),
                'email' => $email,
                'source' => $source
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to capture lead: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get lead by email
     */
    public function getLeadByEmail($email)
    {
        try {
            $sql = "SELECT * FROM marketing_leads WHERE email = ?";
            return $this->database->selectOne($sql, [$email]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get lead by email', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return null;
        }
    }
    
    /**
     * Get lead by ID
     */
    public function getLead($leadId)
    {
        try {
            $sql = "SELECT * FROM marketing_leads WHERE id = ?";
            $lead = $this->database->selectOne($sql, [$leadId]);
            
            if ($lead) {
                // Get lead analytics
                $analyticsSql = "SELECT * FROM marketing_analytics WHERE lead_id = ? ORDER BY created_at DESC LIMIT 10";
                $lead['analytics'] = $this->database->select($analyticsSql, [$leadId]);
            }
            
            return $lead;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get lead', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId
            ]);
            return null;
        }
    }
    
    /**
     * Get all leads with filters
     */
    public function getLeads($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM marketing_leads WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['source'])) {
                $sql .= " AND source = ?";
                $params[] = $filters['source'];
            }
            
            if (!empty($filters['campaign'])) {
                $sql .= " AND campaign = ?";
                $params[] = $filters['campaign'];
            }
            
            if (!empty($filters['score_min'])) {
                $sql .= " AND score >= ?";
                $params[] = $filters['score_min'];
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $leads = $this->database->select($sql, $params);
            
            return [
                'success' => true,
                'data' => $leads
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get leads', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve leads: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate lead score
     */
    public function calculateLeadScore($leadId)
    {
        try {
            $lead = $this->getLead($leadId);
            
            if (!$lead) {
                return false;
            }
            
            $score = 0;
            
            // Score based on source
            $sourceScores = [
                'website' => 10,
                'google' => 20,
                'facebook' => 15,
                'referral' => 25,
                'direct' => 30,
                'linkedin' => 22,
                'twitter' => 12,
                'instagram' => 18
            ];
            
            $score += $sourceScores[$lead['source']] ?? 5;
            
            // Score based on phone availability
            if (!empty($lead['phone'])) {
                $score += 10;
            }
            
            // Score based on engagement
            $engagementScore = $this->calculateEngagementScore($leadId);
            $score += $engagementScore;
            
            // Update lead score
            $sql = "UPDATE marketing_leads SET score = ? WHERE id = ?";
            $this->database->query($sql, [$score, $leadId]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate lead score', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId
            ]);
            return false;
        }
    }
    
    /**
     * Calculate engagement score
     */
    private function calculateEngagementScore($leadId)
    {
        $sql = "SELECT COUNT(*) as engagement_count FROM marketing_analytics WHERE lead_id = ?";
        $result = $this->database->selectOne($sql, [$leadId]);
        
        $count = $result['engagement_count'] ?? 0;
        
        // Score based on engagement count
        if ($count >= 10) return 20;
        if ($count >= 5) return 15;
        if ($count >= 2) return 10;
        if ($count >= 1) return 5;
        
        return 0;
    }
    
    /**
     * Create email campaign
     */
    public function createEmailCampaign($name, $subject, $content, $targetAudience, $scheduleAt = null)
    {
        try {
            $sql = "INSERT INTO marketing_campaigns (name, type, subject, content, target_audience, schedule_at, status)
                    VALUES (?, 'email', ?, ?, ?, ?, 'draft')";
            
            $audienceJson = json_encode($targetAudience);
            $this->database->query($sql, [$name, $subject, $content, $audienceJson, $scheduleAt]);
            $campaignId = $this->database->lastInsertId();
            
            $this->logger->info('Email campaign created', [
                'campaign_id' => $campaignId,
                'name' => $name,
                'subject' => $subject
            ]);
            
            return [
                'success' => true,
                'campaign_id' => $campaignId,
                'message' => 'Campaign created successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create email campaign', [
                'error' => $e->getMessage(),
                'name' => $name
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get campaigns
     */
    public function getCampaigns($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM marketing_campaigns WHERE 1=1";
            $params = [];
            
            if (!empty($filters['type'])) {
                $sql .= " AND type = ?";
                $params[] = $filters['type'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $campaigns = $this->database->select($sql, $params);
            
            // Decode target audience
            foreach ($campaigns as &$campaign) {
                if ($campaign['target_audience']) {
                    $campaign['target_audience'] = json_decode($campaign['target_audience'], true);
                }
            }
            
            return [
                'success' => true,
                'data' => $campaigns
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get campaigns', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve campaigns: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update lead status
     */
    public function updateLeadStatus($leadId, $status)
    {
        try {
            $validStatuses = ['new', 'contacted', 'interested', 'converted', 'lost'];
            
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }
            
            $sql = "UPDATE marketing_leads SET status = ?, updated_at = NOW() WHERE id = ?";
            $this->database->query($sql, [$status, $leadId]);
            
            // Log analytics
            $this->logAnalytics($leadId, 'status_update', ['new_status' => $status]);
            
            $this->logger->info('Lead status updated', [
                'lead_id' => $leadId,
                'status' => $status
            ]);
            
            return [
                'success' => true,
                'message' => 'Lead status updated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update lead status', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId,
                'status' => $status
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update lead status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Assign lead score
     */
    public function assignLeadScore($leadId, $score)
    {
        try {
            $sql = "UPDATE marketing_leads SET score = score + ?, updated_at = NOW() WHERE id = ?";
            $this->database->query($sql, [$score, $leadId]);
            
            // Log analytics
            $this->logAnalytics($leadId, 'score_update', ['score_added' => $score]);
            
            $this->logger->info('Lead score assigned', [
                'lead_id' => $leadId,
                'score_added' => $score
            ]);
            
            return [
                'success' => true,
                'message' => 'Lead score assigned successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to assign lead score', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId,
                'score' => $score
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to assign lead score: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Trigger automation workflow
     */
    public function triggerAutomation($triggerType, $leadId)
    {
        try {
            $sql = "SELECT * FROM marketing_automations
                    WHERE trigger_type = ? AND is_active = 1";
            $automations = $this->database->select($sql, [$triggerType]);
            
            foreach ($automations as $automation) {
                $this->executeAutomation($automation, $leadId);
            }
            
            return [
                'success' => true,
                'message' => 'Automation triggered successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger automation', [
                'error' => $e->getMessage(),
                'trigger_type' => $triggerType,
                'lead_id' => $leadId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to trigger automation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute automation
     */
    private function executeAutomation($automation, $leadId)
    {
        try {
            $actions = json_decode($automation['actions'], true);
            
            foreach ($actions as $action) {
                switch ($action['type']) {
                    case 'send_email':
                        $this->sendAutomatedEmail($leadId, $action['template_id'], $action['subject'] ?? '');
                        break;
                    case 'assign_score':
                        $this->assignLeadScore($leadId, $action['score']);
                        break;
                    case 'update_status':
                        $this->updateLeadStatus($leadId, $action['status']);
                        break;
                    case 'delay':
                        // Simulate delay (in real implementation, this would be handled by a queue)
                        sleep($action['seconds'] ?? 1);
                        break;
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to execute automation', [
                'error' => $e->getMessage(),
                'automation_id' => $automation['id'],
                'lead_id' => $leadId
            ]);
        }
    }
    
    /**
     * Send automated email
     */
    private function sendAutomatedEmail($leadId, $templateId, $subject = '')
    {
        try {
            $lead = $this->getLead($leadId);
            
            if (!$lead) {
                return false;
            }
            
            // In a real implementation, this would use the email service
            // For now, we'll just log the action
            $this->logger->info('Automated email sent', [
                'lead_id' => $leadId,
                'email' => $lead['email'],
                'template_id' => $templateId,
                'subject' => $subject
            ]);
            
            // Update last contacted
            $sql = "UPDATE marketing_leads SET last_contacted = NOW() WHERE id = ?";
            $this->database->query($sql, [$leadId]);
            
            // Log analytics
            $this->logAnalytics($leadId, 'email_sent', [
                'template_id' => $templateId,
                'subject' => $subject
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to send automated email', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId,
                'template_id' => $templateId
            ]);
            return false;
        }
    }
    
    /**
     * Log analytics event
     */
    private function logAnalytics($leadId, $actionType, $actionData = [])
    {
        try {
            $sql = "INSERT INTO marketing_analytics (lead_id, action_type, action_data)
                    VALUES (?, ?, ?)";
            
            $this->database->query($sql, [$leadId, $actionType, json_encode($actionData)]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to log analytics', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId,
                'action_type' => $actionType
            ]);
        }
    }
    
    /**
     * Get marketing dashboard data
     */
    public function getDashboardData()
    {
        try {
            $data = [
                'total_leads' => $this->getTotalLeads(),
                'new_leads_today' => $this->getNewLeadsToday(),
                'new_leads_this_week' => $this->getNewLeadsThisWeek(),
                'conversion_rate' => $this->getConversionRate(),
                'top_campaigns' => $this->getTopCampaigns(),
                'lead_sources' => $this->getLeadSources(),
                'pipeline_stages' => $this->getPipelineStages(),
                'recent_activity' => $this->getRecentActivity()
            ];
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get dashboard data', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get total leads
     */
    private function getTotalLeads()
    {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads";
        $result = $this->database->selectOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get new leads today
     */
    private function getNewLeadsToday()
    {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()";
        $result = $this->database->selectOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get new leads this week
     */
    private function getNewLeadsThisWeek()
    {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
        $result = $this->database->selectOne($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get conversion rate
     */
    private function getConversionRate()
    {
        $sql = "SELECT
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate
                FROM marketing_leads";
        $result = $this->database->selectOne($sql);
        return round($result['rate'] ?? 0, 2);
    }
    
    /**
     * Get top campaigns
     */
    private function getTopCampaigns()
    {
        $sql = "SELECT name, sent_count, opened_count, clicked_count, converted_count
                FROM marketing_campaigns
                WHERE status = 'completed'
                ORDER BY converted_count DESC
                LIMIT 5";
        return $this->database->select($sql);
    }
    
    /**
     * Get lead sources
     */
    private function getLeadSources()
    {
        $sql = "SELECT source, COUNT(*) as count
                FROM marketing_leads
                GROUP BY source
                ORDER BY count DESC";
        return $this->database->select($sql);
    }
    
    /**
     * Get pipeline stages
     */
    private function getPipelineStages()
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM marketing_leads
                GROUP BY status";
        return $this->database->select($sql);
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        $sql = "SELECT ma.*, ml.name, ml.email
                FROM marketing_analytics ma
                LEFT JOIN marketing_leads ml ON ma.lead_id = ml.id
                ORDER BY ma.created_at DESC
                LIMIT 10";
        return $this->database->select($sql);
    }
    
    /**
     * Get lead statistics
     */
    public function getLeadStats()
    {
        try {
            $stats = [];
            
            // Status distribution
            $stats['status_distribution'] = $this->getPipelineStages();
            
            // Source distribution
            $stats['source_distribution'] = $this->getLeadSources();
            
            // Score distribution
            $stats['score_distribution'] = $this->database->select("
                SELECT 
                    CASE 
                        WHEN score >= 80 THEN 'Hot'
                        WHEN score >= 50 THEN 'Warm'
                        WHEN score >= 20 THEN 'Cool'
                        ELSE 'Cold'
                    END as category,
                    COUNT(*) as count
                FROM marketing_leads
                GROUP BY category
                ORDER BY category DESC
            ");
            
            // Monthly trends
            $stats['monthly_trends'] = $this->database->select("
                SELECT 
                    YEAR(created_at) as year,
                    MONTH(created_at) as month,
                    COUNT(*) as leads,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as conversions
                FROM marketing_leads
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY YEAR(created_at), MONTH(created_at)
                ORDER BY year DESC, month DESC
            ");
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get lead statistics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve lead statistics: ' . $e->getMessage()
            ];
        }
    }
}