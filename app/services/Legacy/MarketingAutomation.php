<?php

namespace App\Services\Legacy;
<?php
/**
 * Marketing Automation Tools - APS Dream Homes
 * Lead generation, email marketing, and automation
 */

class MarketingAutomation {
    private $db;
    private \EmailService $emailService;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();

        if ($this->db) {
            $this->emailService = new \EmailService();
            $this->initMarketingSystem();
        }
    }

    /**
     * Initialize marketing system
     */
    private function initMarketingSystem() {
        // Create marketing tables
        $this->createMarketingTables();

        // Initialize email service
        $this->initEmailService();
    }

    /**
     * Create marketing database tables
     */
    private function createMarketingTables() {
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
            $this->db->query($sql);
        }
    }

    /**
     * Initialize email service
     */
    private function initEmailService() {
        // Initialize email marketing service
        $this->emailService = new \EmailService();
    }

    /**
     * Capture lead from form
     */
    public function captureLead($name, $email, $phone, $source = 'website', $campaign = '') {
        $sql = "INSERT INTO marketing_leads (name, email, phone, source, campaign)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                source = VALUES(source),
                campaign = VALUES(campaign),
                updated_at = NOW()";

        $this->db->execute($sql, [$name, $email, $phone, $source, $campaign]);
        $leadId = $this->db->lastInsertId() ?: $this->getLeadIdByEmail($email);

        // Trigger automation workflows
        $this->triggerAutomation('lead_signup', $leadId);

        // Calculate lead score
        $this->calculateLeadScore($leadId);

        return $leadId;
    }

    /**
     * Get lead ID by email
     */
    private function getLeadIdByEmail($email) {
        $sql = "SELECT id FROM marketing_leads WHERE email = ?";
        $row = $this->db->fetch($sql, [$email]);
        return $row['id'] ?? 0;
    }

    /**
     * Calculate lead score
     */
    private function calculateLeadScore($leadId) {
        $sql = "SELECT * FROM marketing_leads WHERE id = ?";
        $lead = $this->db->fetch($sql, [$leadId]);

        if ($lead) {
            $score = 0;

            // Score based on source
            $sourceScores = [
                'website' => 10,
                'google' => 20,
                'facebook' => 15,
                'referral' => 25,
                'direct' => 30
            ];

            $score += $sourceScores[$lead['source']] ?? 5;

            // Score based on phone availability
            if (!empty($lead['phone'])) {
                $score += 10;
            }

            // Update lead score
            $sql = "UPDATE marketing_leads SET score = ? WHERE id = ?";
            $this->db->execute($sql, [$score, $leadId]);
        }
    }

    /**
     * Create email campaign
     */
    public function createEmailCampaign($name, $subject, $content, $targetAudience, $scheduleAt = null) {
        $sql = "INSERT INTO marketing_campaigns (name, type, subject, content, target_audience, schedule_at, status)
                VALUES (?, 'email', ?, ?, ?, ?, 'draft')";

        $audienceJson = json_encode($targetAudience);
        $this->db->execute($sql, [$name, $subject, $content, $audienceJson, $scheduleAt]);

        return $this->db->lastInsertId();
    }

    /**
     * Trigger automation workflow
     */
    private function triggerAutomation($triggerType, $leadId) {
        $sql = "SELECT * FROM marketing_automations
                WHERE trigger_type = ? AND is_active = 1";
        $results = $this->db->fetchAll($sql, [$triggerType]);

        foreach ($results as $automation) {
            $this->executeAutomation($automation, $leadId);
        }
    }

    /**
     * Execute automation
     */
    private function executeAutomation($automation, $leadId) {
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
            }
        }
    }

    /**
     * Send automated email
     */
    private function sendAutomatedEmail($leadId, $templateId) {
        $sql = "SELECT * FROM marketing_leads WHERE id = ?";
        $lead = $this->db->fetch($sql, [$leadId]);

        if ($lead) {
            // Send email using email service
            $this->emailService->send($lead['email'], $templateId, $lead);

            // Update last contacted
            $sql = "UPDATE marketing_leads SET last_contacted = NOW() WHERE id = ?";
            $this->db->execute($sql, [$leadId]);
        }
    }

    /**
     * Assign lead score
     */
    private function assignLeadScore($leadId, $score) {
        $sql = "UPDATE marketing_leads SET score = score + ? WHERE id = ?";
        $this->db->execute($sql, [$score, $leadId]);
    }

    /**
     * Update lead status
     */
    private function updateLeadStatus($leadId, $status) {
        $sql = "UPDATE marketing_leads SET status = ? WHERE id = ?";
        $this->db->execute($sql, [$status, $leadId]);
    }

    /**
     * Notify sales team
     */
    private function notifySalesTeam($leadId, $message) {
        // Send notification to sales team
    }

    /**
     * Get marketing dashboard data
     */
    public function getDashboardData() {
        $data = [
            'total_leads' => $this->getTotalLeads(),
            'new_leads_today' => $this->getNewLeadsToday(),
            'conversion_rate' => $this->getConversionRate(),
            'top_campaigns' => $this->getTopCampaigns(),
            'lead_sources' => $this->getLeadSources(),
            'pipeline_stages' => $this->getPipelineStages()
        ];

        return $data;
    }

    /**
     * Get total leads
     */
    private function getTotalLeads() {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads";
        $row = $this->db->fetch($sql);
        return $row['count'] ?? 0;
    }

    /**
     * Get new leads today
     */
    private function getNewLeadsToday() {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE DATE(created_at) = CURDATE()";
        $row = $this->db->fetch($sql);
        return $row['count'] ?? 0;
    }

    /**
     * Get conversion rate
     */
    private function getConversionRate() {
        $sql = "SELECT
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as rate
                FROM marketing_leads";
        $row = $this->db->fetch($sql);
        return round($row['rate'] ?? 0, 2);
    }

    /**
     * Get top campaigns
     */
    private function getTopCampaigns() {
        $sql = "SELECT name, sent_count, opened_count, clicked_count, converted_count
                FROM marketing_campaigns
                WHERE status = 'completed'
                ORDER BY converted_count DESC
                LIMIT 5";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get lead sources
     */
    private function getLeadSources() {
        $sql = "SELECT source, COUNT(*) as count
                FROM marketing_leads
                GROUP BY source
                ORDER BY count DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get pipeline stages
     */
    private function getPipelineStages() {
        $sql = "SELECT status, COUNT(*) as count
                FROM marketing_leads
                GROUP BY status";
        return $this->db->fetchAll($sql);
    }
}

// Initialize marketing automation if needed
if (!isset($marketing)) {
    $marketing = new MarketingAutomation();
}
?>
