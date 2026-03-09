<?php

namespace App\Services\Marketing;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Marketing Automation Service
 * Handles lead generation, email marketing, and automation workflows
 */
class AutomationService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $campaigns = [];
    private array $workflows = [];

    // Campaign types
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_WEBINAR = 'webinar';

    // Lead statuses
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_INTERESTED = 'interested';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_LOST = 'lost';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'auto_respond' => true,
            'lead_scoring' => true,
            'campaign_tracking' => true,
            'email_provider' => 'smtp',
            'max_emails_per_hour' => 100,
            'workflow_timeout' => 3600, // 1 hour
            'lead_retention_days' => 365
        ], $config);
        
        $this->initializeMarketingTables();
        $this->loadDefaultWorkflows();
    }

    /**
     * Create marketing campaign
     */
    public function createCampaign(string $name, string $type, array $config, array $segments = []): array
    {
        try {
            $campaignId = $this->createCampaignRecord($name, $type, $config, $segments);
            
            // Initialize campaign tracking
            $this->initializeCampaignTracking($campaignId);

            $this->logger->info("Marketing campaign created", [
                'campaign_id' => $campaignId,
                'name' => $name,
                'type' => $type
            ]);

            return [
                'success' => true,
                'message' => 'Campaign created successfully',
                'campaign_id' => $campaignId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to create campaign", [
                'name' => $name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add lead to marketing system
     */
    public function addLead(array $leadData, array $tags = []): array
    {
        try {
            // Validate lead data
            $validation = $this->validateLeadData($leadData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Lead validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Check for duplicate leads
            if ($this->isDuplicateLead($leadData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Lead already exists'
                ];
            }

            // Create lead record
            $leadId = $this->createLeadRecord($leadData, $tags);

            // Calculate lead score
            if ($this->config['lead_scoring']) {
                $score = $this->calculateLeadScore($leadData, $tags);
                $this->updateLeadScore($leadId, $score);
            }

            // Add to relevant campaigns
            $this->addLeadToCampaigns($leadId, $tags);

            // Send auto-responder if enabled
            if ($this->config['auto_respond']) {
                $this->sendAutoResponder($leadId, $leadData);
            }

            $this->logger->info("Lead added to marketing system", [
                'lead_id' => $leadId,
                'email' => $leadData['email'],
                'score' => $score ?? 0
            ]);

            return [
                'success' => true,
                'message' => 'Lead added successfully',
                'lead_id' => $leadId,
                'score' => $score ?? 0
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to add lead", [
                'email' => $leadData['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add lead: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute marketing campaign
     */
    public function executeCampaign(int $campaignId): array
    {
        try {
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign) {
                return [
                    'success' => false,
                    'message' => 'Campaign not found'
                ];
            }

            $results = [
                'sent' => 0,
                'delivered' => 0,
                'opened' => 0,
                'clicked' => 0,
                'converted' => 0,
                'errors' => []
            ];

            // Get campaign leads
            $leads = $this->getCampaignLeads($campaignId);

            foreach ($leads as $lead) {
                try {
                    $result = $this->executeCampaignForLead($campaign, $lead);
                    
                    if ($result['success']) {
                        $results['sent']++;
                        if ($result['delivered']) $results['delivered']++;
                        if ($result['opened']) $results['opened']++;
                        if ($result['clicked']) $results['clicked']++;
                        if ($result['converted']) $results['converted']++;
                    } else {
                        $results['errors'][] = "Lead {$lead['id']}: {$result['message']}";
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Lead {$lead['id']}: {$e->getMessage()}";
                }
            }

            // Update campaign status
            $this->updateCampaignStatus($campaignId, 'executed', $results);

            $this->logger->info("Campaign executed", [
                'campaign_id' => $campaignId,
                'results' => $results
            ]);

            return [
                'success' => true,
                'message' => 'Campaign executed successfully',
                'results' => $results
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to execute campaign", [
                'campaign_id' => $campaignId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to execute campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process marketing automation workflows
     */
    public function processWorkflows(): array
    {
        try {
            $processed = 0;
            $triggered = 0;
            $errors = [];

            // Get active workflows
            $workflows = $this->getActiveWorkflows();

            foreach ($workflows as $workflow) {
                try {
                    $result = $this->processWorkflow($workflow);
                    
                    if ($result['triggered']) {
                        $triggered++;
                    }
                    
                    $processed++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Workflow {$workflow['id']}: {$e->getMessage()}";
                    $processed++;
                }
            }

            $this->logger->info("Workflows processed", [
                'processed' => $processed,
                'triggered' => $triggered,
                'errors' => count($errors)
            ]);

            return [
                'success' => true,
                'message' => "Processed {$processed} workflows",
                'processed' => $processed,
                'triggered' => $triggered,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to process workflows", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to process workflows: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get marketing analytics
     */
    public function getAnalytics(array $filters = []): array
    {
        try {
            $analytics = [];

            // Campaign statistics
            $analytics['campaigns'] = $this->getCampaignStats($filters);

            // Lead statistics
            $analytics['leads'] = $this->getLeadStats($filters);

            // Conversion statistics
            $analytics['conversions'] = $this->getConversionStats($filters);

            // ROI statistics
            $analytics['roi'] = $this->getROIStats($filters);

            // Recent activity
            $analytics['recent_activity'] = $this->getRecentActivity($filters);

            return $analytics;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get analytics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get lead by ID
     */
    public function getLead(int $id): ?array
    {
        try {
            $sql = "SELECT l.*, 
                           (SELECT COUNT(*) FROM campaign_leads cl WHERE cl.lead_id = l.id) as campaign_count,
                           (SELECT COUNT(*) FROM lead_activities la WHERE la.lead_id = l.id) as activity_count
                    FROM marketing_leads l 
                    WHERE l.id = ?";
            
            $lead = $this->db->fetchOne($sql, [$id]);
            
            if ($lead) {
                $lead['tags'] = $this->getLeadTags($id);
                $lead['activities'] = $this->getLeadActivities($id);
                $lead['campaigns'] = $this->getLeadCampaigns($id);
            }
            
            return $lead;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get lead", ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus(int $id, string $status, string $reason = ''): array
    {
        try {
            // Validate status
            if (!in_array($status, $this->getValidStatuses())) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }

            // Get current lead
            $lead = $this->getLead($id);
            if (!$lead) {
                return [
                    'success' => false,
                    'message' => 'Lead not found'
                ];
            }

            // Update status
            $sql = "UPDATE marketing_leads 
                    SET status = ?, status_reason = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [$status, $reason, $id]);

            // Log status change
            $this->logLeadActivity($id, 'status_change', "Status changed to {$status}", $reason);

            // Trigger workflows
            $this->triggerWorkflows($id, 'status_change', ['status' => $status]);

            $this->logger->info("Lead status updated", [
                'lead_id' => $id,
                'old_status' => $lead['status'],
                'new_status' => $status,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Lead status updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to update lead status", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeMarketingTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS marketing_campaigns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('email', 'sms', 'social', 'webinar') NOT NULL,
                config JSON,
                status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_status (status)
            )",
            
            "CREATE TABLE IF NOT EXISTS marketing_leads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(50),
                company VARCHAR(255),
                position VARCHAR(255),
                source VARCHAR(100),
                status ENUM('new', 'contacted', 'interested', 'qualified', 'converted', 'lost') DEFAULT 'new',
                status_reason TEXT,
                score INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_score (score),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS campaign_leads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                campaign_id INT NOT NULL,
                lead_id INT NOT NULL,
                status ENUM('pending', 'sent', 'delivered', 'opened', 'clicked', 'converted', 'bounced', 'unsubscribed') DEFAULT 'pending',
                sent_at TIMESTAMP NULL,
                delivered_at TIMESTAMP NULL,
                opened_at TIMESTAMP NULL,
                clicked_at TIMESTAMP NULL,
                converted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE CASCADE,
                FOREIGN KEY (lead_id) REFERENCES marketing_leads(id) ON DELETE CASCADE,
                INDEX idx_campaign_id (campaign_id),
                INDEX idx_lead_id (lead_id),
                INDEX idx_status (status)
            )",
            
            "CREATE TABLE IF NOT EXISTS lead_activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT NOT NULL,
                activity_type VARCHAR(100) NOT NULL,
                description TEXT,
                data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (lead_id) REFERENCES marketing_leads(id) ON DELETE CASCADE,
                INDEX idx_lead_id (lead_id),
                INDEX idx_activity_type (activity_type),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS lead_tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT NOT NULL,
                tag VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (lead_id) REFERENCES marketing_leads(id) ON DELETE CASCADE,
                INDEX idx_lead_id (lead_id),
                INDEX idx_tag (tag)
            )",
            
            "CREATE TABLE IF NOT EXISTS marketing_workflows (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                trigger_type VARCHAR(100) NOT NULL,
                trigger_config JSON,
                actions JSON,
                enabled BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_trigger_type (trigger_type),
                INDEX idx_enabled (enabled)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function loadDefaultWorkflows(): void
    {
        $this->workflows = [
            'new_lead_welcome' => [
                'trigger_type' => 'lead_created',
                'trigger_config' => [],
                'actions' => [
                    ['type' => 'send_email', 'template' => 'welcome_email', 'delay' => 0],
                    ['type' => 'add_tag', 'tag' => 'new_lead', 'delay' => 0],
                    ['type' => 'assign_to_campaign', 'campaign_id' => 1, 'delay' => 300] // 5 minutes
                ]
            ],
            'lead_nurturing' => [
                'trigger_type' => 'lead_activity',
                'trigger_config' => ['activity_type' => 'email_opened'],
                'actions' => [
                    ['type' => 'update_score', 'points' => 5, 'delay' => 0],
                    ['type' => 'send_followup', 'template' => 'followup_1', 'delay' => 86400] // 24 hours
                ]
            ],
            'conversion_followup' => [
                'trigger_type' => 'status_change',
                'trigger_config' => ['status' => 'qualified'],
                'actions' => [
                    ['type' => 'send_email', 'template' => 'qualified_followup', 'delay' => 0],
                    ['type' => 'notify_sales', 'delay' => 0],
                    ['type' => 'assign_to_campaign', 'campaign_id' => 2, 'delay' => 3600] // 1 hour
                ]
            ]
        ];
    }

    private function createCampaignRecord(string $name, string $type, array $config, array $segments): string
    {
        $sql = "INSERT INTO marketing_campaigns 
                (name, type, config, status, created_at) 
                VALUES (?, ?, ?, 'draft', NOW())";
        
        $this->db->execute($sql, [
            $name,
            $type,
            json_encode([
                'config' => $config,
                'segments' => $segments
            ])
        ]);
        
        return $this->db->lastInsertId();
    }

    private function initializeCampaignTracking(int $campaignId): void
    {
        $sql = "INSERT INTO campaign_analytics 
                (campaign_id, sent, delivered, opened, clicked, converted, created_at) 
                VALUES (?, 0, 0, 0, 0, 0, NOW())";
        
        $this->db->execute($sql, [$campaignId]);
    }

    private function validateLeadData(array $data): array
    {
        $errors = [];

        if (empty($data['first_name']) || strlen($data['first_name']) < 2) {
            $errors[] = 'First name is required and must be at least 2 characters';
        }

        if (empty($data['last_name']) || strlen($data['last_name']) < 2) {
            $errors[] = 'Last name is required and must be at least 2 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function isDuplicateLead(string $email): bool
    {
        $sql = "SELECT COUNT(*) as count FROM marketing_leads WHERE email = ?";
        $count = $this->db->fetchOne($sql, [$email]) ?? 0;
        return $count > 0;
    }

    private function createLeadRecord(array $leadData, array $tags): string
    {
        $sql = "INSERT INTO marketing_leads 
                (first_name, last_name, email, phone, company, position, source, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW())";
        
        $this->db->execute($sql, [
            $leadData['first_name'],
            $leadData['last_name'],
            $leadData['email'],
            $leadData['phone'] ?? null,
            $leadData['company'] ?? null,
            $leadData['position'] ?? null,
            $leadData['source'] ?? 'manual'
        ]);
        
        $leadId = $this->db->lastInsertId();

        // Add tags
        foreach ($tags as $tag) {
            $this->addLeadTag($leadId, $tag);
        }

        return $leadId;
    }

    private function calculateLeadScore(array $leadData, array $tags): int
    {
        $score = 0;

        // Base score for having complete information
        if (!empty($leadData['company'])) $score += 10;
        if (!empty($leadData['position'])) $score += 10;
        if (!empty($leadData['phone'])) $score += 5;

        // Score based on tags
        $tagScores = [
            'high_value' => 20,
            'vip' => 25,
            'hot_lead' => 30,
            'qualified' => 15,
            'interested' => 10,
            'new' => 5
        ];

        foreach ($tags as $tag) {
            $score += $tagScores[$tag] ?? 0;
        }

        // Score based on email domain
        $domain = explode('@', $leadData['email'])[1] ?? '';
        $professionalDomains = ['gmail.com', 'yahoo.com', 'outlook.com'];
        if (!in_array($domain, $professionalDomains)) {
            $score += 15; // Business email
        }

        return min(100, $score);
    }

    private function updateLeadScore(int $leadId, int $score): void
    {
        $sql = "UPDATE marketing_leads SET score = ? WHERE id = ?";
        $this->db->execute($sql, [$score, $leadId]);
    }

    private function addLeadToCampaigns(int $leadId, array $tags): void
    {
        // Logic to add lead to relevant campaigns based on tags
        if (in_array('new', $tags)) {
            $this->addLeadToCampaign($leadId, 1); // Welcome campaign
        }
        
        if (in_array('qualified', $tags)) {
            $this->addLeadToCampaign($leadId, 2); // Nurturing campaign
        }
    }

    private function addLeadToCampaign(int $leadId, int $campaignId): void
    {
        $sql = "INSERT IGNORE INTO campaign_leads (campaign_id, lead_id, status, created_at) 
                VALUES (?, ?, 'pending', NOW())";
        $this->db->execute($sql, [$campaignId, $leadId]);
    }

    private function sendAutoResponder(int $leadId, array $leadData): void
    {
        // Mock email sending
        $this->logger->info("Auto-responder sent", [
            'lead_id' => $leadId,
            'email' => $leadData['email']
        ]);
    }

    private function getCampaign(int $id): ?array
    {
        $sql = "SELECT * FROM marketing_campaigns WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    private function getCampaignLeads(int $campaignId): array
    {
        $sql = "SELECT l.* FROM campaign_leads cl 
                JOIN marketing_leads l ON cl.lead_id = l.id 
                WHERE cl.campaign_id = ? AND cl.status = 'pending'";
        return $this->db->fetchAll($sql, [$campaignId]);
    }

    private function executeCampaignForLead(array $campaign, array $lead): array
    {
        // Mock campaign execution
        return [
            'success' => true,
            'delivered' => true,
            'opened' => rand(0, 1) === 1,
            'clicked' => rand(0, 1) === 1,
            'converted' => rand(0, 1) === 1
        ];
    }

    private function updateCampaignStatus(int $campaignId, string $status, array $results): void
    {
        $sql = "UPDATE marketing_campaigns SET status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$status, $campaignId]);
    }

    private function getActiveWorkflows(): array
    {
        $sql = "SELECT * FROM marketing_workflows WHERE enabled = 1";
        return $this->db->fetchAll($sql);
    }

    private function processWorkflow(array $workflow): array
    {
        // Mock workflow processing
        return [
            'triggered' => true,
            'actions_executed' => count(json_decode($workflow['actions'], true) ?? [])
        ];
    }

    private function getCampaignStats(array $filters): array
    {
        $sql = "SELECT type, COUNT(*) as count FROM marketing_campaigns";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " WHERE status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " GROUP BY type";
        
        return $this->db->fetchAll($sql, $params);
    }

    private function getLeadStats(array $filters): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM marketing_leads";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " WHERE created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        $sql .= " GROUP BY status";
        
        return $this->db->fetchAll($sql, $params);
    }

    private function getConversionStats(array $filters): array
    {
        $sql = "SELECT COUNT(*) as total FROM campaign_leads WHERE status = 'converted'";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        $total = $this->db->fetchOne($sql, $params) ?? 0;
        
        return [
            'total_conversions' => $total,
            'conversion_rate' => $total > 0 ? ($total / 100) * 100 : 0 // Mock calculation
        ];
    }

    private function getROIStats(array $filters): array
    {
        // Mock ROI calculation
        return [
            'total_investment' => 10000,
            'total_revenue' => 50000,
            'roi_percentage' => 400
        ];
    }

    private function getRecentActivity(array $filters): array
    {
        $sql = "SELECT la.*, l.first_name, l.last_name, l.email 
                FROM lead_activities la 
                JOIN marketing_leads l ON la.lead_id = l.id 
                ORDER BY la.created_at DESC 
                LIMIT 20";
        
        return $this->db->fetchAll($sql);
    }

    private function getLeadTags(int $leadId): array
    {
        $sql = "SELECT tag FROM lead_tags WHERE lead_id = ?";
        $tags = $this->db->fetchAll($sql, [$leadId]);
        return array_column($tags, 'tag');
    }

    private function getLeadActivities(int $leadId): array
    {
        $sql = "SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$leadId]);
    }

    private function getLeadCampaigns(int $leadId): array
    {
        $sql = "SELECT c.*, cl.status as lead_status, cl.created_at as added_at 
                FROM campaign_leads cl 
                JOIN marketing_campaigns c ON cl.campaign_id = c.id 
                WHERE cl.lead_id = ?";
        return $this->db->fetchAll($sql, [$leadId]);
    }

    private function getValidStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_INTERESTED,
            self::STATUS_QUALIFIED,
            self::STATUS_CONVERTED,
            self::STATUS_LOST
        ];
    }

    private function logLeadActivity(int $leadId, string $type, string $description, string $data = ''): void
    {
        $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, data, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $leadId,
            $type,
            $description,
            $data ? json_encode(['data' => $data]) : null
        ]);
    }

    private function triggerWorkflows(int $leadId, string $triggerType, array $triggerData): void
    {
        // Mock workflow triggering
        $this->logger->info("Workflows triggered", [
            'lead_id' => $leadId,
            'trigger_type' => $triggerType,
            'trigger_data' => $triggerData
        ]);
    }

    private function addLeadTag(int $leadId, string $tag): void
    {
        $sql = "INSERT IGNORE INTO lead_tags (lead_id, tag, created_at) VALUES (?, ?, NOW())";
        $this->db->execute($sql, [$leadId, $tag]);
    }
}
