<?php

namespace App\Services\Marketing;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * Marketing Automation Service - APS Dream Home
 * Lead generation, email marketing, and automation
 * Custom MVC implementation without Laravel dependencies
 */
class MarketingAutomationService
{
    private $db;
    private $logger;
    private $emailService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new LoggingService();
        $this->initMarketingSystem();
    }

    /**
     * Initialize marketing system
     */
    private function initMarketingSystem()
    {
        // Create marketing tables
        $this->createMarketingTables();

        // Initialize email service
        $this->initEmailService();
    }

    /**
     * Create marketing database tables
     */
    private function createMarketingTables()
    {
        $tables = [
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
            try {
                $this->db->query($sql);
                $this->logger->info('Marketing table created', ['table' => $sql]);
            } catch (Exception $e) {
                $this->logger->error('Failed to create marketing table', [
                    'sql' => $sql,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Initialize email service
     */
    private function initEmailService()
    {
        // Initialize email marketing service
        $this->emailService = new \EmailService();
    }

    /**
     * Capture lead from form
     */
    public function captureLead($name, $email, $phone, $source = 'website', $campaign = '')
    {
        try {
            // Validate input
            if (empty($name) || empty($email)) {
                throw new InvalidArgumentException('Name and email are required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format');
            }

            $sql = "INSERT INTO marketing_leads (name, email, phone, source, campaign)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    source = VALUES(source),
                    campaign = VALUES(campaign),
                    updated_at = NOW()";

            $this->db->query($sql, [$name, $email, $phone, $source, $campaign]);
            $leadId = $this->db->lastInsertId() ?: $this->getLeadIdByEmail($email);

            // Log lead capture
            $this->logger->info('Lead captured', [
                'lead_id' => $leadId,
                'email' => $email,
                'source' => $source,
                'campaign' => $campaign
            ]);

            // Trigger automation workflows
            $this->triggerAutomation('lead_signup', $leadId);

            // Calculate lead score
            $this->calculateLeadScore($leadId);

            return $leadId;
        } catch (Exception $e) {
            $this->logger->error('Failed to capture lead', [
                'name' => $name,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get lead ID by email
     */
    private function getLeadIdByEmail($email)
    {
        $result = $this->db->fetchOne("SELECT id FROM marketing_leads WHERE email = ?", [$email]);
        return $result['id'] ?? 0;
    }

    /**
     * Calculate lead score
     */
    private function calculateLeadScore($leadId)
    {
        try {
            $result = $this->db->fetchOne("SELECT * FROM marketing_leads WHERE id = ?", [$leadId]);
            $lead = $result;

            if ($lead) {
                $score = 0;

                // Score based on source
                $sourceScores = [
                    'website' => 10,
                    'google' => 20,
                    'facebook' => 15,
                    'referral' => 25,
                    'direct' => 30,
                    'linkedin' => 35,
                    'instagram' => 12,
                    'twitter' => 8
                ];

                $score += $sourceScores[$lead['source']] ?? 5;

                // Score based on phone availability
                if (!empty($lead['phone'])) {
                    $score += 10;
                }

                // Score based on campaign
                if (!empty($lead['campaign'])) {
                    $score += 5;
                }

                // Update lead score
                $this->db->query("UPDATE marketing_leads SET score = ? WHERE id = ?", [$score, $leadId]);

                $this->logger->info('Lead score calculated', [
                    'lead_id' => $leadId,
                    'score' => $score,
                    'source' => $lead['source']
                ]);
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to calculate lead score', [
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create email campaign
     */
    public function createEmailCampaign($name, $subject, $content, $targetAudience, $scheduleAt = null)
    {
        try {
            if (empty($name) || empty($subject) || empty($content)) {
                throw new InvalidArgumentException('Name, subject, and content are required');
            }

            $sql = "INSERT INTO marketing_campaigns (name, type, subject, content, target_audience, schedule_at, status)
                    VALUES (?, 'email', ?, ?, ?, ?, 'draft')";

            $audienceJson = json_encode($targetAudience);
            $this->db->query($sql, [$name, $subject, $content, $audienceJson, $scheduleAt]);

            $campaignId = $this->db->lastInsertId();

            $this->logger->info('Email campaign created', [
                'campaign_id' => $campaignId,
                'name' => $name,
                'schedule_at' => $scheduleAt
            ]);

            return $campaignId;
        } catch (Exception $e) {
            $this->logger->error('Failed to create email campaign', [
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Trigger automation workflow
     */
    private function triggerAutomation($triggerType, $leadId)
    {
        try {
            $results = $this->db->fetchAll(
                "SELECT * FROM marketing_automations WHERE trigger_type = ? AND is_active = 1",
                [$triggerType]
            );

            foreach ($results as $automation) {
                $this->executeAutomation($automation, $leadId);
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to trigger automation', [
                'trigger_type' => $triggerType,
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
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
                        $this->sendAutomatedEmail($leadId, $action['template_id']);
                        break;
                    case 'assign_score':
                        $this->assignLeadScore($leadId, $action['score']);
                        break;
                    case 'update_status':
                        $this->updateLeadStatus($leadId, $action['status']);
                        break;
                    case 'notify_team':
                        $this->notifySalesTeam($leadId, $action['message']);
                        break;
                    case 'add_tag':
                        $this->addLeadTag($leadId, $action['tag']);
                        break;
                    case 'delay':
                        $this->scheduleDelayedAction($leadId, $action['delay'], $action['next_actions']);
                        break;
                }
            }

            $this->logger->info('Automation executed', [
                'automation_id' => $automation['id'],
                'lead_id' => $leadId
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to execute automation', [
                'automation_id' => $automation['id'],
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send automated email
     */
    private function sendAutomatedEmail($leadId, $templateId)
    {
        try {
            $result = $this->db->fetchOne("SELECT * FROM marketing_leads WHERE id = ?", [$leadId]);
            $lead = $result;

            if ($lead && $this->emailService) {
                // Send email using email service
                $sent = $this->emailService->send($lead['email'], $templateId, $lead);

                if ($sent) {
                    // Update last contacted
                    $this->db->query("UPDATE marketing_leads SET last_contacted = NOW() WHERE id = ?", [$leadId]);

                    // Log analytics
                    $this->logAnalytics($leadId, null, 'email_sent', ['template_id' => $templateId]);

                    $this->logger->info('Automated email sent', [
                        'lead_id' => $leadId,
                        'template_id' => $templateId,
                        'email' => $lead['email']
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to send automated email', [
                'lead_id' => $leadId,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Assign lead score
     */
    private function assignLeadScore($leadId, $score)
    {
        try {
            $this->db->query("UPDATE marketing_leads SET score = score + ? WHERE id = ?", [$score, $leadId]);

            $this->logger->info('Lead score assigned', [
                'lead_id' => $leadId,
                'score_added' => $score
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to assign lead score', [
                'lead_id' => $leadId,
                'score' => $score,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update lead status
     */
    private function updateLeadStatus($leadId, $status)
    {
        try {
            $validStatuses = ['new', 'contacted', 'interested', 'converted', 'lost'];
            if (!in_array($status, $validStatuses)) {
                throw new InvalidArgumentException('Invalid lead status');
            }

            $this->db->query("UPDATE marketing_leads SET status = ? WHERE id = ?", [$status, $leadId]);

            $this->logger->info('Lead status updated', [
                'lead_id' => $leadId,
                'status' => $status
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to update lead status', [
                'lead_id' => $leadId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify sales team
     */
    private function notifySalesTeam($leadId, $message)
    {
        try {
            // Get lead details
            $result = $this->db->fetchOne("SELECT * FROM marketing_leads WHERE id = ?", [$leadId]);
            $lead = $result;

            if ($lead) {
                // Send notification to sales team (implementation depends on notification system)
                $this->logger->info('Sales team notified', [
                    'lead_id' => $leadId,
                    'message' => $message,
                    'lead_email' => $lead['email']
                ]);
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to notify sales team', [
                'lead_id' => $leadId,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add lead tag
     */
    private function addLeadTag($leadId, $tag)
    {
        try {
            // Implementation for adding tags to leads
            $this->logger->info('Lead tag added', [
                'lead_id' => $leadId,
                'tag' => $tag
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to add lead tag', [
                'lead_id' => $leadId,
                'tag' => $tag,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule delayed action
     */
    private function scheduleDelayedAction($leadId, $delay, $nextActions)
    {
        try {
            // Implementation for scheduling delayed actions
            $this->logger->info('Delayed action scheduled', [
                'lead_id' => $leadId,
                'delay' => $delay,
                'next_actions' => $nextActions
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to schedule delayed action', [
                'lead_id' => $leadId,
                'delay' => $delay,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log marketing analytics
     */
    private function logAnalytics($leadId, $campaignId, $actionType, $actionData = [])
    {
        try {
            $sql = "INSERT INTO marketing_analytics (campaign_id, lead_id, action_type, action_data)
                    VALUES (?, ?, ?, ?)";

            $this->db->query($sql, [$campaignId, $leadId, $actionType, json_encode($actionData)]);
        } catch (Exception $e) {
            $this->logger->error('Failed to log analytics', [
                'lead_id' => $leadId,
                'campaign_id' => $campaignId,
                'action_type' => $actionType,
                'error' => $e->getMessage()
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
                'conversion_rate' => $this->getConversionRate(),
                'top_campaigns' => $this->getTopCampaigns(),
                'lead_sources' => $this->getLeadSources(),
                'pipeline_stages' => $this->getPipelineStages(),
                'recent_activities' => $this->getRecentActivities()
            ];

            return $data;
        } catch (Exception $e) {
            $this->logger->error('Failed to get dashboard data', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get total leads
     */
    private function getTotalLeads()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM marketing_leads");
        return $result['count'] ?? 0;
    }

    /**
     * Get new leads today
     */
    private function getNewLeadsToday()
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()"
        );
        return $result['count'] ?? 0;
    }

    /**
     * Get conversion rate
     */
    private function getConversionRate()
    {
        $result = $this->db->fetchOne(
            "SELECT SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate
             FROM marketing_leads"
        );
        return round($result['rate'] ?? 0, 2);
    }

    /**
     * Get top campaigns
     */
    private function getTopCampaigns()
    {
        return $this->db->fetchAll(
            "SELECT name, sent_count, opened_count, clicked_count, converted_count
             FROM marketing_campaigns
             WHERE status = 'completed'
             ORDER BY converted_count DESC
             LIMIT 5"
        );
    }

    /**
     * Get lead sources
     */
    private function getLeadSources()
    {
        return $this->db->fetchAll(
            "SELECT source, COUNT(*) as count
             FROM marketing_leads
             GROUP BY source
             ORDER BY count DESC"
        );
    }

    /**
     * Get pipeline stages
     */
    private function getPipelineStages()
    {
        return $this->db->fetchAll(
            "SELECT status, COUNT(*) as count
             FROM marketing_leads
             GROUP BY status"
        );
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        return $this->db->fetchAll(
            "SELECT ma.*, ml.name as lead_name, ml.email as lead_email
             FROM marketing_analytics ma
             LEFT JOIN marketing_leads ml ON ma.lead_id = ml.id
             ORDER BY ma.created_at DESC
             LIMIT 10"
        );
    }

    /**
     * Get leads by status
     */
    public function getLeadsByStatus($status = null)
    {
        try {
            $sql = "SELECT * FROM marketing_leads";
            $params = [];

            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY created_at DESC";

            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            $this->logger->error('Failed to get leads by status', [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update lead
     */
    public function updateLead($leadId, $data)
    {
        try {
            $allowedFields = ['name', 'email', 'phone', 'status', 'score'];
            $updates = [];
            $params = [];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = ?";
                    $params[] = $value;
                }
            }

            if (empty($updates)) {
                throw new InvalidArgumentException('No valid fields to update');
            }

            $params[] = $leadId;
            $sql = "UPDATE marketing_leads SET " . implode(', ', $updates) . " WHERE id = ?";

            $this->db->query($sql, $params);

            $this->logger->info('Lead updated', [
                'lead_id' => $leadId,
                'fields_updated' => array_keys($data)
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to update lead', [
                'lead_id' => $leadId,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create automation workflow
     */
    public function createAutomation($name, $triggerType, $triggerConditions, $actions)
    {
        try {
            $sql = "INSERT INTO marketing_automations (name, trigger_type, trigger_conditions, actions)
                    VALUES (?, ?, ?, ?)";

            $this->db->query($sql, [
                $name,
                $triggerType,
                json_encode($triggerConditions),
                json_encode($actions)
            ]);

            $automationId = $this->db->lastInsertId();

            $this->logger->info('Automation created', [
                'automation_id' => $automationId,
                'name' => $name,
                'trigger_type' => $triggerType
            ]);

            return $automationId;
        } catch (Exception $e) {
            $this->logger->error('Failed to create automation', [
                'name' => $name,
                'trigger_type' => $triggerType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
