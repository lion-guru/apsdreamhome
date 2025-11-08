<?php
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

class CRMManager {
    private $conn;
    private $logger;
    private $emailManager;
    private $propertyManager;
    private $plottingManager;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->emailManager = new EmailTemplateManager($conn, $logger);
        $this->propertyManager = new PropertyManager($conn, $logger);
        $this->plottingManager = new PlottingManager($conn, $logger);
        $this->createCRMTables();
    }

    /**
     * Create CRM system tables
     */
    private function createCRMTables() {
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

        $this->conn->query($sql);

        // Leads table (Enhanced)
        $sql = "CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lead_number VARCHAR(50) NOT NULL UNIQUE,
            lead_source_id INT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100),
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
            assigned_to INT,
            next_follow_up_date DATETIME,
            last_contact_date DATETIME,
            conversion_probability DECIMAL(5,2) DEFAULT 0,
            expected_deal_value DECIMAL(15,2),
            expected_closure_date DATE,
            lost_reason VARCHAR(255),
            competitor_info TEXT,
            tags JSON,
            custom_fields JSON,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lead_source_id) REFERENCES lead_sources(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

        // Customer profiles table
        $sql = "CREATE TABLE IF NOT EXISTS customer_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT,
            lead_id INT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100),
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
            credit_score ENUM('excellent','good','fair','poor') DEFAULT 'fair',
            address TEXT,
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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

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

        $this->conn->query($sql);

        // Insert default data
        $this->insertDefaultCRMData();
    }

    /**
     * Insert default CRM data
     */
    private function insertDefaultCRMData() {
        // Check if default data already exists
        $checkSql = "SELECT COUNT(*) as count FROM lead_sources";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
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
                       VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssdss", $source[0], $source[1], $source[2], $source[3], $source[4], $source[5]);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Insert default pipeline stages
        $checkPipeline = "SELECT COUNT(*) as count FROM sales_pipeline_stages";
        $result = $this->conn->query($checkPipeline);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
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
                       VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sdsdis", $stage[0], $stage[1], $stage[2], $stage[3], $stage[4], $stage[5]);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Insert default communication templates
        $checkTemplates = "SELECT COUNT(*) as count FROM communication_templates";
        $result = $this->conn->query($checkTemplates);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $templates = [
                [
                    'welcome_lead',
                    'email',
                    'lead_nurturing',
                    'Welcome to APS Dream Home!',
                    'Dear {{first_name}},

Thank you for your interest in APS Dream Home. We have received your inquiry and our team will contact you shortly.

In the meantime, you can explore our available properties at: {{website_url}}

If you have any immediate questions, please call us at: {{contact_number}}

Best regards,
APS Dream Home Team',
                    '["first_name", "website_url", "contact_number"]'
                ],
                [
                    'follow_up_call',
                    'call_script',
                    'follow_up',
                    'Lead Follow-up Call Script',
                    'Hi {{first_name}},

This is {{agent_name}} from APS Dream Home calling regarding your inquiry about {{property_interest}}.

I wanted to check if you had any specific requirements or questions about our properties.

Are you available for a quick discussion about your requirements?

[Listen to customer needs]

Thank you for your time. I will send you more details via email/WhatsApp.

Best regards,
{{agent_name}}',
                    '["first_name", "agent_name", "property_interest"]'
                ]
            ];

            foreach ($templates as $template) {
                $sql = "INSERT INTO communication_templates (template_name, template_type, category, subject, message_body, variables)
                       VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssssss", $template[0], $template[1], $template[2], $template[3], $template[4], $template[5]);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Add new lead
     */
    public function addLead($leadData) {
        $sql = "INSERT INTO leads (
            lead_number, lead_source_id, first_name, last_name, email, phone, alternate_phone,
            date_of_birth, gender, marital_status, occupation, company, designation, annual_income,
            address, city, state, pincode, property_interest, budget_min, budget_max,
            preferred_location, property_type, requirement_details, lead_score, assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisssssssssdsssssdsis",
            $leadData['lead_number'],
            $leadData['lead_source_id'],
            $leadData['first_name'],
            $leadData['last_name'],
            $leadData['email'],
            $leadData['phone'],
            $leadData['alternate_phone'],
            $leadData['date_of_birth'],
            $leadData['gender'],
            $leadData['marital_status'],
            $leadData['occupation'],
            $leadData['company'],
            $leadData['designation'],
            $leadData['annual_income'],
            $leadData['address'],
            $leadData['city'],
            $leadData['state'],
            $leadData['pincode'],
            $leadData['property_interest'],
            $leadData['budget_min'],
            $leadData['budget_max'],
            $leadData['preferred_location'],
            $leadData['property_type'],
            $leadData['requirement_details'],
            $leadData['lead_score'],
            $leadData['assigned_to'],
            $leadData['created_by']
        );

        $result = $stmt->execute();
        $leadId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Calculate lead score
            $this->calculateLeadScore($leadId);

            // Send welcome email
            $this->sendLeadWelcomeEmail($leadId);

            if ($this->logger) {
                $this->logger->log("Lead created: {$leadData['first_name']} {$leadData['last_name']} ({$leadData['lead_number']})", 'info', 'crm');
            }
        }

        return $result ? $leadId : false;
    }

    /**
     * Calculate lead score
     */
    private function calculateLeadScore($leadId) {
        $sql = "SELECT * FROM leads WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lead = $result->fetch_assoc();
        $stmt->close();

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
        $updateSql = "UPDATE leads SET lead_score = ? WHERE id = ?";
        $stmt = $this->conn->prepare($updateSql);
        $stmt->bind_param("ii", $score, $leadId);
        $stmt->execute();
        $stmt->close();

        // Update conversion probability based on score
        $probability = min($score * 2, 100); // Max 100%
        $probabilitySql = "UPDATE leads SET conversion_probability = ? WHERE id = ?";
        $stmt = $this->conn->prepare($probabilitySql);
        $stmt->bind_param("di", $probability, $leadId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Send welcome email to lead
     */
    private function sendLeadWelcomeEmail($leadId) {
        $sql = "SELECT first_name, email FROM leads WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lead = $result->fetch_assoc();
        $stmt->close();

        if ($lead && $lead['email']) {
            $emailData = [
                'first_name' => $lead['first_name'],
                'website_url' => 'https://' . $_SERVER['HTTP_HOST'],
                'contact_number' => '1800-XXX-XXXX' // Replace with actual number
            ];

            $this->emailManager->sendTemplateEmail('welcome_lead', $emailData, $lead['email'], $lead['first_name']);
        }
    }

    /**
     * Get leads with filtering and pagination
     */
    public function getLeads($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT l.*, ls.source_name, u.full_name as assigned_to_name
                FROM leads l
                LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['lead_status'])) {
            $sql .= " AND l.lead_status = ?";
            $params[] = $filters['lead_status'];
            $types .= "s";
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
            $types .= "i";
        }

        if (!empty($filters['lead_source_id'])) {
            $sql .= " AND l.lead_source_id = ?";
            $params[] = $filters['lead_source_id'];
            $types .= "i";
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (l.first_name LIKE ? OR l.last_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $sql .= " ORDER BY l.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $leads = [];
        while ($row = $result->fetch_assoc()) {
            $row['tags'] = json_decode($row['tags'] ?? '[]', true);
            $leads[] = $row;
        }
        $stmt->close();

        return $leads;
    }

    /**
     * Add lead activity
     */
    public function addLeadActivity($activityData) {
        $sql = "INSERT INTO lead_activities (
            lead_id, activity_type, activity_date, subject, description, duration,
            outcome, next_action, next_action_date, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssissis",
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
        );

        $result = $stmt->execute();
        $activityId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update last contact date
            $updateSql = "UPDATE leads SET last_contact_date = ? WHERE id = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("si", $activityData['activity_date'], $activityData['lead_id']);
            $stmt->execute();
            $stmt->close();

            // Schedule next follow-up if specified
            if (!empty($activityData['next_action_date'])) {
                $this->scheduleFollowUp($activityData['lead_id'], $activityData['next_action_date'], $activityData['next_action']);
            }

            if ($this->logger) {
                $this->logger->log("Lead activity added: Lead ID {$activityData['lead_id']}, Type: {$activityData['activity_type']}", 'info', 'crm');
            }
        }

        return $result ? $activityId : false;
    }

    /**
     * Schedule follow-up
     */
    private function scheduleFollowUp($leadId, $followUpDate, $nextAction) {
        $sql = "UPDATE leads SET next_follow_up_date = ?, lead_status = 'nurturing' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $followUpDate, $leadId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Create opportunity from lead
     */
    public function createOpportunity($opportunityData) {
        $sql = "INSERT INTO opportunities (
            opportunity_number, lead_id, opportunity_title, opportunity_type, pipeline_stage_id,
            property_id, plot_id, expected_value, probability_percentage, expected_closure_date,
            assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdiiiddsii",
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
        );

        $result = $stmt->execute();
        $opportunityId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update lead status
            $updateSql = "UPDATE leads SET lead_status = 'proposal_sent' WHERE id = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("i", $opportunityData['lead_id']);
            $stmt->execute();
            $stmt->close();

            if ($this->logger) {
                $this->logger->log("Opportunity created: {$opportunityData['opportunity_title']} ({$opportunityData['opportunity_number']})", 'info', 'crm');
            }
        }

        return $result ? $opportunityId : false;
    }

    /**
     * Get opportunities with filtering
     */
    public function getOpportunities($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT o.*, l.first_name, l.last_name, l.phone, l.email,
                       s.stage_name, s.probability_percentage as stage_probability,
                       u.full_name as assigned_to_name, p.title as property_title,
                       pl.plot_number, pl.colony_name
                FROM opportunities o
                LEFT JOIN leads l ON o.lead_id = l.id
                LEFT JOIN sales_pipeline_stages s ON o.pipeline_stage_id = s.id
                LEFT JOIN users u ON o.assigned_to = u.id
                LEFT JOIN properties p ON o.property_id = p.id
                LEFT JOIN plots pl ON o.plot_id = pl.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND o.assigned_to = ?";
            $params[] = $filters['assigned_to'];
            $types .= "i";
        }

        if (!empty($filters['pipeline_stage_id'])) {
            $sql .= " AND o.pipeline_stage_id = ?";
            $params[] = $filters['pipeline_stage_id'];
            $types .= "i";
        }

        if (!empty($filters['opportunity_type'])) {
            $sql .= " AND o.opportunity_type = ?";
            $params[] = $filters['opportunity_type'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (o.opportunity_title LIKE ? OR l.first_name LIKE ? OR l.last_name LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $sql .= " ORDER BY o.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $opportunities = [];
        while ($row = $result->fetch_assoc()) {
            $opportunities[] = $row;
        }
        $stmt->close();

        return $opportunities;
    }

    /**
     * Convert lead to customer
     */
    public function convertLeadToCustomer($leadId, $customerData = []) {
        // Get lead details
        $sql = "SELECT * FROM leads WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lead = $result->fetch_assoc();
        $stmt->close();

        if (!$lead) return false;

        // Check if customer already exists
        if (!empty($lead['email'])) {
            $checkSql = "SELECT id FROM customer_profiles WHERE email = ? OR phone = ?";
            $stmt = $this->conn->prepare($checkSql);
            $stmt->bind_param("ss", $lead['email'], $lead['phone']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $existingCustomer = $result->fetch_assoc();
                return $existingCustomer['id'];
            }
            $stmt->close();
        }

        // Create customer profile
        $customerSql = "INSERT INTO customer_profiles (
            customer_number, first_name, last_name, email, phone, alternate_phone,
            date_of_birth, gender, marital_status, occupation, company, designation,
            annual_income, address, city, state, pincode, customer_type, customer_status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $customerNumber = 'CUST' . date('Y') . str_pad($leadId, 4, '0', STR_PAD_LEFT);
        $stmt = $this->conn->prepare($customerSql);
        $stmt->bind_param("sssssssssssdssssss",
            $customerNumber,
            $lead['first_name'],
            $lead['last_name'],
            $lead['email'],
            $lead['phone'],
            $lead['alternate_phone'],
            $lead['date_of_birth'],
            $lead['gender'],
            $lead['marital_status'],
            $lead['occupation'],
            $lead['company'],
            $lead['designation'],
            $lead['annual_income'],
            $lead['address'],
            $lead['city'],
            $lead['state'],
            $lead['pincode'],
            $customerData['customer_type'] ?? 'individual',
            'active'
        );

        $result = $stmt->execute();
        $customerId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Update lead status
            $updateSql = "UPDATE leads SET lead_status = 'won' WHERE id = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("i", $leadId);
            $stmt->execute();
            $stmt->close();

            if ($this->logger) {
                $this->logger->log("Lead converted to customer: Lead ID $leadId, Customer ID $customerId", 'info', 'crm');
            }
        }

        return $result ? $customerId : false;
    }

    /**
     * Create support ticket
     */
    public function createSupportTicket($ticketData) {
        $sql = "INSERT INTO support_tickets (
            ticket_number, customer_id, ticket_type, priority, subject, description,
            status, assigned_to, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sissssiis",
            $ticketData['ticket_number'],
            $ticketData['customer_id'],
            $ticketData['ticket_type'],
            $ticketData['priority'],
            $ticketData['subject'],
            $ticketData['description'],
            $ticketData['status'],
            $ticketData['assigned_to'],
            $ticketData['created_by']
        );

        $result = $stmt->execute();
        $ticketId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Send acknowledgment email
            $this->sendTicketAcknowledgment($ticketId);

            if ($this->logger) {
                $this->logger->log("Support ticket created: {$ticketData['ticket_number']}", 'info', 'crm');
            }
        }

        return $result ? $ticketId : false;
    }

    /**
     * Send ticket acknowledgment email
     */
    private function sendTicketAcknowledgment($ticketId) {
        $sql = "SELECT st.*, cp.first_name, cp.email
                FROM support_tickets st
                JOIN customer_profiles cp ON st.customer_id = cp.id
                WHERE st.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();
        $ticket = $result->fetch_assoc();
        $stmt->close();

        if ($ticket && $ticket['email']) {
            $emailData = [
                'first_name' => $ticket['first_name'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $ticket['subject'],
                'priority' => $ticket['priority'],
                'created_date' => date('Y-m-d H:i:s')
            ];

            $this->emailManager->sendTemplateEmail('support_ticket_acknowledgment', $emailData, $ticket['email'], $ticket['first_name']);
        }
    }

    /**
     * Get CRM dashboard data
     */
    public function getCRMDashboard() {
        $dashboard = [];

        // Lead statistics
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN lead_status = 'new' THEN 1 ELSE 0 END) as new_leads,
            SUM(CASE WHEN lead_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as converted_leads,
            AVG(lead_score) as avg_lead_score
            FROM leads";
        $result = $this->conn->query($sql);
        $dashboard['lead_stats'] = $result->fetch_assoc();

        // Opportunity statistics
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 6 THEN 1 ELSE 0 END) as lost_opportunities,
            SUM(expected_value) as total_pipeline_value,
            AVG(probability_percentage) as avg_probability
            FROM opportunities";
        $result = $this->conn->query($sql);
        $dashboard['opportunity_stats'] = $result->fetch_assoc();

        // Customer statistics
        $sql = "SELECT
            COUNT(*) as total_customers,
            SUM(CASE WHEN customer_status = 'active' THEN 1 ELSE 0 END) as active_customers,
            SUM(CASE WHEN customer_status = 'vip' THEN 1 ELSE 0 END) as vip_customers,
            AVG(total_purchase_value) as avg_customer_value
            FROM customer_profiles";
        $result = $this->conn->query($sql);
        $dashboard['customer_stats'] = $result->fetch_assoc();

        // Support ticket statistics
        $sql = "SELECT
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets,
            AVG(satisfaction_rating) as avg_satisfaction
            FROM support_tickets";
        $result = $this->conn->query($sql);
        $dashboard['support_stats'] = $result->fetch_assoc();

        // Recent activities
        $sql = "SELECT la.*, l.first_name, l.last_name
                FROM lead_activities la
                LEFT JOIN leads l ON la.lead_id = l.id
                ORDER BY la.created_at DESC
                LIMIT 10";
        $result = $this->conn->query($sql);
        $dashboard['recent_activities'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['recent_activities'][] = $row;
        }

        // Sales pipeline
        $sql = "SELECT s.stage_name, COUNT(o.id) as opportunity_count,
                       SUM(o.expected_value) as total_value, AVG(o.probability_percentage) as avg_probability
                FROM sales_pipeline_stages s
                LEFT JOIN opportunities o ON s.id = o.pipeline_stage_id
                GROUP BY s.id, s.stage_name
                ORDER BY s.stage_order";
        $result = $this->conn->query($sql);
        $dashboard['sales_pipeline'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['sales_pipeline'][] = $row;
        }

        // Lead sources performance
        $sql = "SELECT ls.source_name, COUNT(l.id) as lead_count,
                       SUM(CASE WHEN l.lead_status = 'won' THEN 1 ELSE 0 END) as converted_count
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.lead_source_id
                GROUP BY ls.id, ls.source_name
                ORDER BY lead_count DESC";
        $result = $this->conn->query($sql);
        $dashboard['lead_sources'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['lead_sources'][] = $row;
        }

        return $dashboard;
    }

    /**
     * Get lead sources
     */
    public function getLeadSources() {
        $sql = "SELECT * FROM lead_sources WHERE status = 'active' ORDER BY source_name";
        $result = $this->conn->query($sql);

        $sources = [];
        while ($row = $result->fetch_assoc()) {
            $sources[] = $row;
        }

        return $sources;
    }

    /**
     * Get single lead by ID
     */
    public function getLead($leadId) {
        $sql = "SELECT l.*, ls.source_name, u.full_name as assigned_to_name
                FROM leads l
                LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id
                LEFT JOIN users u ON l.assigned_to = u.id
                WHERE l.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $lead = $result->fetch_assoc();
            $lead['tags'] = json_decode($lead['tags'] ?? '[]', true);
            $stmt->close();
            return $lead;
        }

        $stmt->close();
        return null;
    }

    /**
     * Get pipeline stages
     */
    public function getPipelineStages() {
        $sql = "SELECT * FROM sales_pipeline_stages WHERE is_active = TRUE ORDER BY stage_order";
        $result = $this->conn->query($sql);

        $stages = [];
        while ($row = $result->fetch_assoc()) {
            $stages[] = $row;
        }

        return $stages;
    }

    /**
     * Update opportunity stage
     */
    public function updateOpportunityStage($opportunityId, $stageId, $notes = '') {
        $sql = "UPDATE opportunities SET pipeline_stage_id = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $stageId, $opportunityId);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
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
        }

        return $result;
    }

    /**
     * Get opportunity lead ID
     */
    private function getOpportunityLeadId($opportunityId) {
        $sql = "SELECT lead_id FROM opportunities WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $opportunityId);
        $stmt->execute();
        $result = $stmt->get_result();
        $leadId = $result->fetch_assoc()['lead_id'] ?? 0;
        $stmt->close();

        return $leadId;
    }

    /**
     * Generate lead number
     */
    public function generateLeadNumber() {
        $prefix = 'LEAD';
        $year = date('Y');
        $month = date('m');

        $sql = "SELECT MAX(CAST(SUBSTRING(lead_number, 9) AS UNSIGNED)) as max_num
                FROM leads WHERE lead_number LIKE ?";
        $pattern = $prefix . $year . $month . '%';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $maxNum = $result->fetch_assoc()['max_num'] ?? 0;
        $stmt->close();

        $nextNum = $maxNum + 1;
        return $prefix . $year . $month . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate opportunity number
     */
    public function generateOpportunityNumber() {
        $prefix = 'OPP';
        $year = date('Y');
        $month = date('m');

        $sql = "SELECT MAX(CAST(SUBSTRING(opportunity_number, 8) AS UNSIGNED)) as max_num
                FROM opportunities WHERE opportunity_number LIKE ?";
        $pattern = $prefix . $year . $month . '%';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $maxNum = $result->fetch_assoc()['max_num'] ?? 0;
        $stmt->close();

        $nextNum = $maxNum + 1;
        return $prefix . $year . $month . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
?>
