<?php

namespace App\Services\Legacy;

/**
 * APS Dream Home - SelForce Style CRM System
 * Comprehensive Customer Relationship Management for Real Estate & Colonizer Business
 *
 * Features:
 * - Lead Management with scoring and qualification
 * - Sales Pipeline and Opportunity Tracking
 * - Customer Communication and Follow-up
 * - Support Ticket System
 * - Customer Analytics and Reporting
 * - Integration with Property, Plotting, and Farmer systems
 */

class CRMManager
{
    private $db;
    private $logger;
    private $emailManager;
    private $propertyManager;
    private $plottingManager;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->emailManager = new EmailService();
        $this->propertyManager = new PropertyManager($this->db, $logger);
        $this->plottingManager = new PlottingManager($this->db, $logger);
        $this->createCRMTables();
    }

    /**
     * Create CRM system tables
     */
    private function createCRMTables()
    {
        // Lead sources table
        $sql = "CREATE TABLE IF NOT EXISTS lead_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_name VARCHAR(100) NOT NULL UNIQUE,
            source_type ENUM('online','offline','referral','advertisement','social_media','other') DEFAULT 'other',
            description TEXT,
            cost_per_lead DECIMAL(10,2) DEFAULT 0,
            conversion_rate DECIMAL(5,2) DEFAULT 0,
            status ENUM('active','inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $this->db->execute($sql);

        // Leads table (Enhanced)
        $sql = "CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_number VARCHAR(50) NOT NULL UNIQUE,
            lead_source_id INT,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(150),
            phone VARCHAR(15) NOT NULL,
            alternate_phone VARCHAR(15),
            date_of_birth DATE,
            gender ENUM('male','female','other') DEFAULT NULL,
            marital_status ENUM('single','married','divorced','widowed') DEFAULT NULL,
            occupation VARCHAR(100),
            company VARCHAR(150),
            designation VARCHAR(100),
            annual_income DECIMAL(15,2),
            address TEXT,
            city VARCHAR(100),
            state VARCHAR(100),
            pincode VARCHAR(10),
            property_interest VARCHAR(255),
            budget_min DECIMAL(15,2),
            budget_max DECIMAL(15,2),
            preferred_location VARCHAR(255),
            property_type VARCHAR(100),
            requirement_details TEXT,
            lead_score INT DEFAULT 0,
            lead_status ENUM('new','contacted','qualified','proposal_sent','negotiation','won','lost','nurturing') DEFAULT 'new',
            assigned_to BIGINT(20) UNSIGNED,
            next_follow_up_date DATETIME,
            last_contact_date DATETIME,
            conversion_probability DECIMAL(5,2) DEFAULT 0,
            expected_deal_value DECIMAL(15,2),
            expected_closure_date DATE,
            lost_reason VARCHAR(255),
            competitor_info TEXT,
            tags JSON,
            custom_fields JSON,
            created_by BIGINT(20) UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lead_source_id) REFERENCES lead_sources(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Lead activities table
        $sql = "CREATE TABLE IF NOT EXISTS lead_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_id INT NOT NULL,
            activity_type ENUM('call','email','meeting','site_visit','follow_up','note','demo','quote_sent','negotiation','closed') NOT NULL,
            activity_date DATETIME NOT NULL,
            subject VARCHAR(255),
            description TEXT,
            duration INT DEFAULT 0, -- in minutes
            outcome ENUM('positive','neutral','negative','no_response') DEFAULT 'neutral',
            next_action VARCHAR(255),
            next_action_date DATETIME,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Sales pipeline stages table
        $sql = "CREATE TABLE IF NOT EXISTS sales_pipeline_stages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stage_name VARCHAR(100) NOT NULL UNIQUE,
            stage_order INT NOT NULL,
            description TEXT,
            probability_percentage DECIMAL(5,2) NOT NULL,
            expected_duration_days INT DEFAULT 7,
            is_active BOOLEAN DEFAULT TRUE,
            color_code VARCHAR(7) DEFAULT '#007bff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $this->db->execute($sql);

        // Opportunities table
        $sql = "CREATE TABLE IF NOT EXISTS opportunities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            opportunity_number VARCHAR(50) NOT NULL UNIQUE,
            lead_id INT NOT NULL,
            opportunity_title VARCHAR(255) NOT NULL,
            opportunity_type ENUM('property_sale','plot_sale','land_acquisition','consultation','other') DEFAULT 'property_sale',
            pipeline_stage_id INT,
            property_id INT,
            plot_id INT,
            expected_value DECIMAL(15,2) NOT NULL,
            probability_percentage DECIMAL(5,2) DEFAULT 0,
            expected_closure_date DATE,
            actual_closure_date DATE,
            won_lost_reason TEXT,
            competitor_info TEXT,
            assigned_to INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
            FOREIGN KEY (pipeline_stage_id) REFERENCES sales_pipeline_stages(id) ON DELETE SET NULL,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
            FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Customer profiles table
        $sql = "CREATE TABLE IF NOT EXISTS customer_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT,
            lead_id INT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150),
            phone VARCHAR(15) NOT NULL,
            alternate_phone VARCHAR(15),
            date_of_birth DATE,
            gender ENUM('male','female','other') DEFAULT NULL,
            marital_status ENUM('single','married','divorced','widowed') DEFAULT NULL,
            occupation VARCHAR(100),
            company VARCHAR(150),
            designation VARCHAR(100),
            annual_income DECIMAL(15,2),
            credit_score ENUM('excellent','good','fair','poor') DEFAULT 'fair',            address TEXT,
            city VARCHAR(100),
            state VARCHAR(100),
            pincode VARCHAR(10),
            customer_type ENUM('individual','business','investor','developer') DEFAULT 'individual',
            customer_status ENUM('active','inactive','vip','blacklisted') DEFAULT 'active',
            total_purchases DECIMAL(15,2) DEFAULT 0,
            total_purchase_value DECIMAL(15,2) DEFAULT 0,
            last_purchase_date DATE,
            preferred_communication ENUM('email','phone','sms','whatsapp') DEFAULT 'email',
            tags JSON,
            custom_fields JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Customer interactions table
        $sql = "CREATE TABLE IF NOT EXISTS customer_interactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            interaction_type ENUM('call','email','meeting','site_visit','support_ticket','complaint','feedback','payment','other') NOT NULL,
            interaction_date DATETIME NOT NULL,
            subject VARCHAR(255),
            description TEXT,
            interaction_outcome ENUM('resolved','pending','escalated','satisfied','dissatisfied') DEFAULT 'pending',
            satisfaction_rating INT DEFAULT 0, -- 1-5 scale
            follow_up_required BOOLEAN DEFAULT FALSE,
            follow_up_date DATETIME,
            assigned_to INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Support tickets table
        $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_number VARCHAR(50) NOT NULL UNIQUE,
            customer_id INT NOT NULL,
            ticket_type ENUM('technical','billing','property','plot','booking','complaint','feature_request','other') NOT NULL,
            priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('open','in_progress','waiting_for_customer','resolved','closed','escalated') DEFAULT 'open',
            assigned_to INT,
            resolution TEXT,
            resolution_date DATETIME,
            satisfaction_rating INT DEFAULT 0,
            internal_notes TEXT,
            attachments JSON,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customer_profiles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Communication templates table
        $sql = "CREATE TABLE IF NOT EXISTS communication_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_name VARCHAR(100) NOT NULL UNIQUE,
            template_type ENUM('email','sms','whatsapp','call_script') NOT NULL,
            category ENUM('lead_nurturing','follow_up','promotion','support','feedback','other') DEFAULT 'other',
            subject VARCHAR(255),
            message_body TEXT NOT NULL,
            variables JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Campaign table
        $sql = "CREATE TABLE IF NOT EXISTS campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_name VARCHAR(150) NOT NULL UNIQUE,
            campaign_type ENUM('email','sms','whatsapp','telecalling','social_media','advertisement') NOT NULL,
            target_audience JSON,
            campaign_goal TEXT,
            budget DECIMAL(15,2),
            start_date DATE NOT NULL,
            end_date DATE,
            status ENUM('draft','scheduled','active','paused','completed','cancelled') DEFAULT 'draft',
            total_sent INT DEFAULT 0,
            total_opened INT DEFAULT 0,
            total_clicked INT DEFAULT 0,
            total_converted INT DEFAULT 0,
            conversion_rate DECIMAL(5,2) DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->db->execute($sql);

        // Insert default data
        $this->insertDefaultCRMData();
    }

    /**
     * Insert default CRM data
     */
    private function insertDefaultCRMData()
    {
        // Check if default data already exists
        $checkSql = "SELECT COUNT(*) as count FROM lead_sources";
        $row = $this->db->fetch($checkSql);

        if ($row && $row['count'] == 0) {
            // Insert default lead sources
            $sources = [
                ['Website', 'online', 'Leads from company website', 0, 0, 'active'],
                ['Google Ads', 'advertisement', 'Google advertisement campaigns', 50, 0, 'active'],
                ['Facebook', 'social_media', 'Facebook marketing leads', 25, 0, 'active'],
                ['Referral', 'referral', 'Customer referrals', 0, 0, 'active'],
                ['Walk-in', 'offline', 'Direct walk-in customers', 0, 0, 'active'],
                ['Associate', 'referral', 'Associate network referrals', 0, 0, 'active'],
                ['Property Exhibition', 'offline', 'Property exhibition leads', 100, 0, 'active'],
                ['Newspaper Ads', 'advertisement', 'Newspaper advertisements', 30, 0, 'active']
            ];

            foreach ($sources as $source) {
                $sql = "INSERT INTO lead_sources (source_name, source_type, description, cost_per_lead, conversion_rate, status) 
                        VALUES (:source_name, :source_type, :description, :cost_per_lead, :conversion_rate, :status)";
                $params = [
                    ':source_name' => $source[0],
                    ':source_type' => $source[1],
                    ':description' => $source[2],
                    ':cost_per_lead' => $source[3],
                    ':conversion_rate' => $source[4],
                    ':status' => $source[5]
                ];
                $this->db->execute($sql, $params);
            }
        }

        // Insert default pipeline stages
        $checkPipeline = "SELECT COUNT(*) as count FROM sales_pipeline_stages";
        $row = $this->db->fetch($checkPipeline);

        if ($row && $row['count'] == 0) {
            $stages = [
                ['Lead Generation', 1, 'New leads are generated', 10, 3, '#6c757d'],
                ['Lead Qualification', 2, 'Qualify and score leads', 25, 5, '#007bff'],
                ['Proposal Sent', 3, 'Proposal or quote sent to customer', 50, 7, '#ffc107'],
                ['Negotiation', 4, 'Price and terms negotiation', 75, 10, '#fd7e14'],
                ['Closed Won', 5, 'Deal successfully closed', 100, 1, '#28a745'],
                ['Closed Lost', 6, 'Deal lost to competition or other reasons', 0, 1, '#dc3545']
            ];

            foreach ($stages as $stage) {
                $sql = "INSERT INTO sales_pipeline_stages (stage_name, stage_order, description, probability_percentage, expected_duration_days, color_code) 
                        VALUES (:stage_name, :stage_order, :description, :probability_percentage, :expected_duration_days, :color_code)";
                $params = [
                    ':stage_name' => $stage[0],
                    ':stage_order' => $stage[1],
                    ':description' => $stage[2],
                    ':probability_percentage' => $stage[3],
                    ':expected_duration_days' => $stage[4],
                    ':color_code' => $stage[5]
                ];
                $this->db->execute($sql, $params);
            }
        }

        // Insert default communication templates
        $checkTemplates = "SELECT COUNT(*) as count FROM communication_templates";
        $row = $this->db->fetch($checkTemplates);

        if ($row && $row['count'] == 0) {
            $templates = [
                [
                    'welcome_lead',
                    'email',
                    'lead_nurturing',
                    'Welcome to APS Dream Home!',
                    'Dear {{first_name}},\n\nThank you for your interest in APS Dream Home. We have received your inquiry and our team will contact you shortly.\n\nIn the meantime, you can explore our available properties at: {{website_url}}\n\nIf you have any immediate questions, please call us at: {{contact_number}}\n\nBest regards,\nAPS Dream Home Team',
                    '["first_name", "website_url", "contact_number"]'
                ],
                [
                    'follow_up_call',
                    'call_script',
                    'follow_up',
                    'Lead Follow-up Call Script',
                    'Hi {{first_name}},\n\nThis is {{agent_name}} from APS Dream Home calling regarding your inquiry about {{property_interest}}.\n\nI wanted to check if you had any specific requirements or questions about our properties.\n\nAre you available for a quick discussion about your requirements?\n\n[Listen to customer needs]\n\nThank you for your time. I will send you more details via email/WhatsApp.\n\nBest regards,\n{{agent_name}}',
                    '["first_name", "agent_name", "property_interest"]'
                ]
            ];

            foreach ($templates as $template) {
                $sql = "INSERT INTO communication_templates (template_name, template_type, category, subject, message_body, variables) 
                        VALUES (:template_name, :template_type, :category, :subject, :message_body, :variables)";
                $params = [
                    ':template_name' => $template[0],
                    ':template_type' => $template[1],
                    ':category' => $template[2],
                    ':subject' => $template[3],
                    ':message_body' => $template[4],
                    ':variables' => $template[5]
                ];
                $this->db->execute($sql, $params);
            }
        }
    }

    /**
     * Add new lead
     */
    public function addLead($leadData)
    {
        $sql = "INSERT INTO leads (
            lead_number, lead_source_id, name, email, phone, alternate_phone,
            date_of_birth, gender, marital_status, occupation, company, designation,
            annual_income, address, city, state, pincode, property_interest,
            budget_min, budget_max, preferred_location, property_type, requirement_details,
            lead_score, assigned_to, created_by
        ) VALUES (
            :lead_number, :lead_source_id, :name, :email, :phone, :alternate_phone,
            :date_of_birth, :gender, :marital_status, :occupation, :company, :designation,
            :annual_income, :address, :city, :state, :pincode, :property_interest,
            :budget_min, :budget_max, :preferred_location, :property_type, :requirement_details,
            :lead_score, :assigned_to, :created_by
        )";

        $params = [
            ':lead_number' => $leadData['lead_number'],
            ':lead_source_id' => $leadData['lead_source_id'],
            ':name' => $leadData['name'],
            ':email' => $leadData['email'],
            ':phone' => $leadData['phone'],
            ':alternate_phone' => $leadData['alternate_phone'],
            ':date_of_birth' => $leadData['date_of_birth'],
            ':gender' => $leadData['gender'],
            ':marital_status' => $leadData['marital_status'],
            ':occupation' => $leadData['occupation'],
            ':company' => $leadData['company'],
            ':designation' => $leadData['designation'],
            ':annual_income' => $leadData['annual_income'],
            ':address' => $leadData['address'],
            ':city' => $leadData['city'],
            ':state' => $leadData['state'],
            ':pincode' => $leadData['pincode'],
            ':property_interest' => $leadData['property_interest'],
            ':budget_min' => $leadData['budget_min'],
            ':budget_max' => $leadData['budget_max'],
            ':preferred_location' => $leadData['preferred_location'],
            ':property_type' => $leadData['property_type'],
            ':requirement_details' => $leadData['requirement_details'],
            ':lead_score' => $leadData['lead_score'],
            ':assigned_to' => $leadData['assigned_to'],
            ':created_by' => $leadData['created_by']
        ];

        try {
            if ($this->db->execute($sql, $params)) {
                $leadId = $this->db->lastInsertId();

                if ($leadId) {
                    // Calculate lead score
                    $this->calculateLeadScore($leadId);
                    // Send welcome email
                    $this->sendLeadWelcomeEmail($leadId);

                    // --- External Integrations ---
                    $helpersPath = __DIR__ . '/../admin/includes/integration_helpers.php';
                    if (file_exists($helpersPath)) {
                        require_once $helpersPath;

                        // 1. Export to Google Sheets
                        if (\function_exists('export_to_google_sheets')) {
                            $sheet_row = [
                                $leadData['name'],
                                $leadData['email'],
                                $leadData['phone'],
                                'New', // Default status
                                \date('Y-m-d H:i:s'),
                                $leadData['lead_number']
                            ];
                            export_to_google_sheets($sheet_row);
                        }

                        // 2. Send Slack/Telegram Notification to Admin
                        $adminMsg = "🚀 *New Lead Created!*\n" .
                            "*Name:* {$leadData['name']}\n" .
                            "*Phone:* {$leadData['phone']}\n" .
                            "*Interest:* {$leadData['property_interest']}\n" .
                            "*Source:* {$leadData['lead_source_id']}";

                        if (\function_exists('send_slack_notification')) {
                            send_slack_notification($adminMsg);
                        }
                        if (\function_exists('send_telegram_notification')) {
                            send_telegram_notification($adminMsg);
                        }

                        // 3. Trigger Zapier/Make Webhook
                        if (\function_exists('trigger_external_webhook')) {
                            trigger_external_webhook('lead_created', $leadData);
                        }
                    }
                    // --- End External Integrations ---

                    if ($this->logger) {
                        $this->logger->log("Lead created: {$leadData['name']} ({$leadData['lead_number']})", 'info', 'crm');
                    }
                    return $leadId;
                }
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error adding lead: " . $e->getMessage(), 'error', 'crm');
            }
        }
        return false;
    }

    /**
     * Calculate lead score
     */
    private function calculateLeadScore($leadId)
    {
        $sql = "SELECT budget_min, email, alternate_phone, property_interest, preferred_location, annual_income FROM leads WHERE id = :id";
        try {
            $lead = $this->db->fetch($sql, [':id' => $leadId]);

            if (!$lead) return;

            $score = 0;

            // Budget scoring
            if ($lead['budget_min'] >= 1000000) $score += 20;
            elseif ($lead['budget_min'] >= 500000) $score += 15;
            elseif ($lead['budget_min'] >= 250000) $score += 10;

            // Contact information completeness
            if (!empty($lead['email'])) $score += 10;
            if (!empty($lead['alternate_phone'])) $score += 5;

            // Property interest specificity
            if (!empty($lead['property_interest'])) $score += 15;
            if (!empty($lead['preferred_location'])) $score += 10;

            // Income level
            if ($lead['annual_income'] >= 2000000) $score += 20;
            elseif ($lead['annual_income'] >= 1000000) $score += 15;
            elseif ($lead['annual_income'] >= 500000) $score += 10;

            // Update lead score
            $updateSql = "UPDATE leads SET lead_score = :score WHERE id = :id";
            $this->db->execute($updateSql, [':score' => $score, ':id' => $leadId]);

            // Update conversion probability based on score
            $probability = min($score * 2, 100); // Max 100%
            $probabilitySql = "UPDATE leads SET conversion_probability = :probability WHERE id = :id";
            $this->db->execute($probabilitySql, [':probability' => $probability, ':id' => $leadId]);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error calculating lead score: " . $e->getMessage(), 'error', 'crm');
            }
        }
    }

    /**
     * Send welcome email to lead
     */
    private function sendLeadWelcomeEmail($leadId)
    {
        $sql = "SELECT name, email FROM leads WHERE id = :id";
        try {
            $lead = $this->db->fetch($sql, [':id' => $leadId]);

            if ($lead && $lead['email']) {
                $this->emailManager->send($lead['email'], 'lead_welcome_email', ['name' => $lead['name']]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error sending welcome email: " . $e->getMessage(), 'error', 'crm');
            }
        }
    }

    /**
     * Get leads with filtering and pagination
     */
    public function getLeads($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT l.*, ls.source_name, u.name as assigned_to_name
                FROM leads l
                LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['lead_status'])) {
            $sql .= " AND l.lead_status = :lead_status";
            $params[':lead_status'] = $filters['lead_status'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = :assigned_to";
            $params[':assigned_to'] = $filters['assigned_to'];
        }

        if (!empty($filters['lead_source_id'])) {
            $sql .= " AND l.lead_source_id = :lead_source_id";
            $params[':lead_source_id'] = $filters['lead_source_id'];
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (l.name LIKE :search_name OR l.email LIKE :search_email OR l.phone LIKE :search_phone)";
            $params[':search_name'] = $search;
            $params[':search_email'] = $search;
            $params[':search_phone'] = $search;
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY l.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$limit;
        }

        if ($offset > 0) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$offset;
        }

        try {
            $rows = $this->db->fetchAll($sql, $params);
            $leads = [];
            foreach ($rows as $row) {
                $row['tags'] = json_decode($row['tags'] ?? '[]', true);
                $leads[] = $row;
            }
            return $leads;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching leads: " . $e->getMessage(), 'error', 'crm');
            }
            return [];
        }
    }

    /**
     * Add lead activity
     */
    public function addLeadActivity($activityData)
    {
        $sql = "INSERT INTO lead_activities (
            lead_id, activity_type, activity_date, subject, description, duration,
            outcome, next_action, next_action_date, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $params = [
                $activityData['lead_id'],
                $activityData['activity_type'],
                $activityData['activity_date'],
                $activityData['subject'],
                $activityData['description'],
                $activityData['duration'],
                $activityData['outcome'],
                $activityData['next_action'],
                $activityData['next_action_date'],
                $activityData['created_by']
            ];

            if ($this->db->execute($sql, $params)) {
                $activityId = $this->db->lastInsertId();

                if ($activityId) {
                    // Update last contact date
                    $updateSql = "UPDATE leads SET last_contact_date = ? WHERE id = ?";
                    $this->db->execute($updateSql, [$activityData['activity_date'], $activityData['lead_id']]);

                    // Schedule next follow-up if specified
                    if (!empty($activityData['next_action_date'])) {
                        $this->scheduleFollowUp($activityData['lead_id'], $activityData['next_action_date'], $activityData['next_action']);
                    }

                    if ($this->logger) {
                        $this->logger->log("Lead activity added: Lead ID {$activityData['lead_id']}, Type: {$activityData['activity_type']}", 'info', 'crm');
                    }
                    return $activityId;
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error adding lead activity: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return false;
    }

    /**
     * Schedule follow-up
     */
    private function scheduleFollowUp($leadId, $followUpDate, $nextAction)
    {
        $sql = "UPDATE leads SET next_follow_up_date = ?, lead_status = 'nurturing' WHERE id = ?";
        try {
            $this->db->execute($sql, [$followUpDate, $leadId]);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error scheduling follow-up: " . $e->getMessage(), 'error', 'crm');
            }
        }
    }

    /**
     * Create opportunity from lead
     */
    public function createOpportunity($opportunityData)
    {
        $sql = "INSERT INTO opportunities (
            opportunity_number, lead_id, opportunity_title, opportunity_type, pipeline_stage_id,
            property_id, plot_id, expected_value, probability_percentage, expected_closure_date,
            assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $params = [
                $opportunityData['opportunity_number'],
                $opportunityData['lead_id'],
                $opportunityData['opportunity_title'],
                $opportunityData['opportunity_type'],
                $opportunityData['pipeline_stage_id'],
                $opportunityData['property_id'],
                $opportunityData['plot_id'],
                $opportunityData['expected_value'],
                $opportunityData['probability_percentage'],
                $opportunityData['expected_closure_date'],
                $opportunityData['assigned_to'],
                $opportunityData['created_by']
            ];

            if ($this->db->execute($sql, $params)) {
                $opportunityId = $this->db->lastInsertId();

                if ($opportunityId) {
                    // Update lead status
                    $updateSql = "UPDATE leads SET lead_status = 'proposal_sent' WHERE id = ?";
                    $this->db->execute($updateSql, [$opportunityData['lead_id']]);

                    if ($this->logger) {
                        $this->logger->log("Opportunity created: {$opportunityData['opportunity_title']} ({$opportunityData['opportunity_number']})", 'info', 'crm');
                    }
                    return $opportunityId;
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating opportunity: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return false;
    }

    /**
     * Get opportunities with filtering
     */
    public function getOpportunities($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT o.*, l.name, l.phone, l.email, 
                       s.stage_name, s.probability_percentage as stage_probability,
                       u.name as assigned_to_name, p.title as property_title,
                       pl.plot_number, pl.colony_name
                FROM opportunities o 
                LEFT JOIN leads l ON o.lead_id = l.id 
                LEFT JOIN sales_pipeline_stages s ON o.pipeline_stage_id = s.id 
                LEFT JOIN users u ON o.assigned_to = u.id 
                LEFT JOIN properties p ON o.property_id = p.id 
                LEFT JOIN plots pl ON o.plot_id = pl.id 
                WHERE 1=1";

        $params = [];

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND o.assigned_to = :assigned_to";
            $params[':assigned_to'] = $filters['assigned_to'];
        }

        if (!empty($filters['pipeline_stage_id'])) {
            $sql .= " AND o.pipeline_stage_id = :pipeline_stage_id";
            $params[':pipeline_stage_id'] = $filters['pipeline_stage_id'];
        }

        if (!empty($filters['opportunity_type'])) {
            $sql .= " AND o.opportunity_type = :opportunity_type";
            $params[':opportunity_type'] = $filters['opportunity_type'];
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (o.opportunity_title LIKE :search_title OR l.name LIKE :search_name)";
            $params[':search_title'] = $search;
            $params[':search_name'] = $search;
        }

        $sql .= " ORDER BY o.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$limit;
        }

        if ($offset > 0) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$offset;
        }

        try {
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching opportunities: " . $e->getMessage(), 'error', 'crm');
            }
            return [];
        }
    }

    /**
     * Convert lead to customer
     */
    public function convertLeadToCustomer($leadId, $customerData = [])
    {
        try {
            // Get lead details
            $sql = "SELECT * FROM leads WHERE id = :id";
            $lead = $this->db->fetch($sql, [':id' => $leadId]);

            if (!$lead) return false;

            // Check if customer already exists
            if (!empty($lead['email'])) {
                $checkSql = "SELECT id FROM customer_profiles WHERE email = :email OR phone = :phone";
                $existingCustomer = $this->db->fetch($checkSql, [':email' => $lead['email'], ':phone' => $lead['phone']]);
                if ($existingCustomer) {
                    return $existingCustomer['id'];
                }
            }

            // Create customer profile
            $customerSql = "INSERT INTO customer_profiles (
                customer_number, name, email, phone, alternate_phone,
                date_of_birth, gender, marital_status, occupation, company, designation, annual_income,
                address, city, state, pincode, user_id, lead_id
            ) VALUES (
                :customer_number, :name, :email, :phone, :alternate_phone,
                :date_of_birth, :gender, :marital_status, :occupation, :company, :designation, :annual_income,
                :address, :city, :state, :pincode, :user_id, :lead_id
            )";

            $customerNumber = 'CUST' . date('Y') . str_pad($leadId, 4, '0', STR_PAD_LEFT);
            $params = [
                ':customer_number' => $customerNumber,
                ':name' => $lead['name'],
                ':email' => $lead['email'],
                ':phone' => $lead['phone'],
                ':alternate_phone' => $lead['alternate_phone'],
                ':date_of_birth' => $lead['date_of_birth'],
                ':gender' => $lead['gender'],
                ':marital_status' => $lead['marital_status'],
                ':occupation' => $lead['occupation'],
                ':company' => $lead['company'],
                ':designation' => $lead['designation'],
                ':annual_income' => $lead['annual_income'],
                ':address' => $lead['address'],
                ':city' => $lead['city'],
                ':state' => $lead['state'],
                ':pincode' => $lead['pincode'],
                ':user_id' => $lead['assigned_to'],
                ':lead_id' => $leadId
            ];

            if ($this->db->execute($customerSql, $params)) {
                $customerId = $this->db->lastInsertId();

                if ($customerId) {
                    // Update lead status
                    $updateSql = "UPDATE leads SET lead_status = 'won' WHERE id = :id";
                    $this->db->execute($updateSql, [':id' => $leadId]);

                    if ($this->logger) {
                        $this->logger->log("Lead converted to customer: Lead ID $leadId, Customer ID $customerId", 'info', 'crm');
                    }
                    return $customerId;
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error converting lead to customer: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return false;
    }

    /**
     * Create support ticket
     */
    public function createSupportTicket($ticketData)
    {
        $sql = "INSERT INTO support_tickets (
            ticket_number, customer_id, ticket_type, priority, subject, description,
            status, assigned_to, created_by
        ) VALUES (
            :ticket_number, :customer_id, :ticket_type, :priority, :subject, :description,
            :status, :assigned_to, :created_by
        )";

        try {
            $params = [
                ':ticket_number' => $ticketData['ticket_number'],
                ':customer_id' => $ticketData['customer_id'],
                ':ticket_type' => $ticketData['ticket_type'],
                ':priority' => $ticketData['priority'],
                ':subject' => $ticketData['subject'],
                ':description' => $ticketData['description'],
                ':status' => $ticketData['status'],
                ':assigned_to' => $ticketData['assigned_to'],
                ':created_by' => $ticketData['created_by']
            ];

            if ($this->db->execute($sql, $params)) {
                $ticketId = $this->db->lastInsertId();

                if ($ticketId) {
                    // Send acknowledgment email
                    $this->sendTicketAcknowledgment($ticketId);

                    if ($this->logger) {
                        $this->logger->log("Support ticket created: {$ticketData['ticket_number']}", 'info', 'crm');
                    }
                    return $ticketId;
                }
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error creating support ticket: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return false;
    }

    /**
     * Send ticket acknowledgment email
     */
    private function sendTicketAcknowledgment($ticketId)
    {
        $sql = "SELECT st.*, cp.name, cp.email
                FROM support_tickets st
                JOIN customer_profiles cp ON st.customer_id = cp.id
                WHERE st.id = :id";
        try {
            $ticket = $this->db->fetch($sql, [':id' => $ticketId]);

            if ($ticket && $ticket['email']) {
                $this->emailManager->send($ticket['email'], 'support_ticket_acknowledgment', ['ticket_number' => $ticket['ticket_number'], 'name' => $ticket['name']]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error sending ticket acknowledgment: " . $e->getMessage(), 'error', 'crm');
            }
        }
    }

    /**
     * Get CRM dashboard data
     */
    public function getCRMDashboard()
    {
        $dashboard = [];

        try {
            // Lead statistics
            $sql = "SELECT
                COUNT(*) as total_leads,
                SUM(CASE WHEN lead_status = 'new' THEN 1 ELSE 0 END) as new_leads,
                SUM(CASE WHEN lead_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
                SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as converted_leads,
                AVG(lead_score) as avg_lead_score
                FROM leads";
            $dashboard['lead_stats'] = $this->db->fetch($sql);

            // Opportunity statistics
            $sql = "SELECT
                COUNT(*) as total_opportunities,
                SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_opportunities,
                SUM(CASE WHEN pipeline_stage_id = 6 THEN 1 ELSE 0 END) as lost_opportunities,
                SUM(expected_value) as total_pipeline_value,
                AVG(probability_percentage) as avg_probability
                FROM opportunities";
            $dashboard['opportunity_stats'] = $this->db->fetch($sql);

            // Customer statistics
            $sql = "SELECT
                COUNT(*) as total_customers,
                SUM(CASE WHEN customer_status = 'active' THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN customer_status = 'vip' THEN 1 ELSE 0 END) as vip_customers,
                AVG(total_purchase_value) as avg_customer_value
                FROM customer_profiles";
            $dashboard['customer_stats'] = $this->db->fetch($sql);

            // Support ticket statistics
            $sql = "SELECT
                COUNT(*) as total_tickets,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets,
                AVG(satisfaction_rating) as avg_satisfaction
                FROM support_tickets";
            $dashboard['support_stats'] = $this->db->fetch($sql);

            // Recent activities
            $sql = "SELECT la.*, l.name
                    FROM lead_activities la
                    LEFT JOIN leads l ON la.lead_id = l.id
                    ORDER BY la.created_at DESC
                    LIMIT 10";
            $dashboard['recent_activities'] = $this->db->fetchAll($sql);

            // Sales pipeline
            $sql = "SELECT s.stage_name, COUNT(o.id) as opportunity_count,
                           SUM(o.expected_value) as total_value, AVG(o.probability_percentage) as avg_probability
                    FROM sales_pipeline_stages s
                    LEFT JOIN opportunities o ON s.id = o.pipeline_stage_id
                    GROUP BY s.id, s.stage_name
                    ORDER BY s.stage_order";
            $dashboard['sales_pipeline'] = $this->db->fetchAll($sql);

            // Lead sources performance
            $sql = "SELECT ls.source_name, COUNT(l.id) as lead_count,
                           SUM(CASE WHEN l.lead_status = 'won' THEN 1 ELSE 0 END) as converted_count
                    FROM lead_sources ls
                    LEFT JOIN leads l ON ls.id = l.lead_source_id
                    GROUP BY ls.id, ls.source_name
                    ORDER BY lead_count DESC";
            $dashboard['lead_sources'] = $this->db->fetchAll($sql);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error getting CRM dashboard: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return $dashboard;
    }

    /**
     * Get lead sources
     */
    public function getLeadSources()
    {
        $sql = "SELECT * FROM lead_sources WHERE status = 'active' ORDER BY source_name";
        try {
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching lead sources: " . $e->getMessage(), 'error', 'crm');
            }
            return [];
        }
    }

    /**
     * Get single lead by ID
     */
    public function getLead($leadId)
    {
        $sql = "SELECT l.*, ls.source_name, u.name as assigned_to_name
                FROM leads l
                LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.id = ?";

        try {
            $lead = $this->db->fetch($sql, [$leadId]);

            if ($lead) {
                $lead['tags'] = json_decode($lead['tags'] ?? '[]', true);
                return $lead;
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching lead: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return null;
    }

    /**
     * Get pipeline stages
     */
    public function getPipelineStages()
    {
        $sql = "SELECT * FROM sales_pipeline_stages WHERE is_active = TRUE ORDER BY stage_order";
        try {
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error fetching pipeline stages: " . $e->getMessage(), 'error', 'crm');
            }
            return [];
        }
    }

    /**
     * Update opportunity stage
     */
    public function updateOpportunityStage($opportunityId, $stageId, $notes = '')
    {
        $sql = "UPDATE opportunities SET pipeline_stage_id = ?, updated_at = NOW() WHERE id = ?";
        try {
            $this->db->execute($sql, [$stageId, $opportunityId]);

            // Add stage change activity
            $activityData = [
                'lead_id' => $this->getOpportunityLeadId($opportunityId),
                'activity_type' => 'negotiation',
                'activity_date' => date('Y-m-d H:i:s'),
                'subject' => 'Opportunity Stage Updated',
                'description' => "Opportunity moved to new stage. Notes: $notes",
                'created_by' => $_SESSION['user_id'] ?? 1
            ];

            $this->addLeadActivity($activityData);

            if ($this->logger) {
                $this->logger->log("Opportunity stage updated: ID $opportunityId to stage $stageId", 'info', 'crm');
            }
            return true;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating opportunity stage: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return false;
    }

    /**
     * Get opportunity lead ID
     */
    private function getOpportunityLeadId($opportunityId)
    {
        $sql = "SELECT lead_id FROM opportunities WHERE id = ?";
        try {
            $row = $this->db->fetch($sql, [$opportunityId]);
            return $row['lead_id'] ?? 0;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error getting opportunity lead ID: " . $e->getMessage(), 'error', 'crm');
            }
        }

        return 0;
    }

    /**
     * Generate lead number
     */
    public function generateLeadNumber()
    {
        $prefix = 'LEAD';
        $year = date('Y');
        $month = date('m');

        $sql = "SELECT MAX(CAST(SUBSTRING(lead_number, 9) AS UNSIGNED)) as max_num
                FROM leads WHERE lead_number LIKE ?";
        $pattern = $prefix . $year . $month . '%';
        try {
            $row = $this->db->fetch($sql, [$pattern]);
            $maxNum = $row['max_num'] ?? 0;

            $nextNum = $maxNum + 1;
            return $prefix . $year . $month . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error generating lead number: " . $e->getMessage(), 'error', 'crm');
            }
            return $prefix . $year . $month . '0001'; // Fallback
        }
    }

    /**
     * Generate opportunity number
     */
    public function generateOpportunityNumber()
    {
        $prefix = 'OPP';
        $year = date('Y');
        $month = date('m');

        $sql = "SELECT MAX(CAST(SUBSTRING(opportunity_number, 8) AS UNSIGNED)) as max_num
                FROM opportunities WHERE opportunity_number LIKE ?";
        $pattern = $prefix . $year . $month . '%';
        try {
            $row = $this->db->fetch($sql, [$pattern]);
            $maxNum = $row['max_num'] ?? 0;

            $nextNum = $maxNum + 1;
            return $prefix . $year . $month . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error generating opportunity number: " . $e->getMessage(), 'error', 'crm');
            }
            return $prefix . $year . $month . '0001'; // Fallback
        }
    }
}
