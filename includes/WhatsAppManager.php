<?php
/**
 * APS Dream Home - WhatsApp Integration Manager
 * Complete WhatsApp Business API integration for CRM and customer communication
 */

class WhatsAppManager {
    private $conn;
    private $logger;

    // WhatsApp API Configuration
    private $apiUrl = 'https://graph.facebook.com/v18.0/';
    private $accessToken = '';
    private $phoneNumberId = '';
    private $businessAccountId = '';

    // WhatsApp Templates
    private $templates = [
        'welcome_message' => [
            'name' => 'welcome_aps_dream_home',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => 'Welcome to APS Dream Home! 🏠'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, thank you for your interest in APS Dream Home! We have received your inquiry and will contact you shortly. You can explore our properties at {{2}} or call us at {{3}}.'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Your trusted real estate partner'
                ]
            ]
        ],
        'property_recommendation' => [
            'name' => 'property_recommendation_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '🏠 Property Recommendation'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, based on your requirements, we recommend {{2}} at {{3}}. Price: {{4}}. Contact us for more details: {{5}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home'
                ]
            ]
        ],
        'plot_booking_confirmation' => [
            'name' => 'plot_booking_confirmation_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '✅ Plot Booking Confirmed!'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, your plot {{2}} in {{3}} has been booked! Booking Amount: ₹{{4}}, Total: ₹{{5}}. Booking Number: {{6}}. Contact us for next steps.'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Colonizer Services'
                ]
            ]
        ],
        'follow_up_reminder' => [
            'name' => 'follow_up_reminder_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '📞 Follow-up Reminder'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, this is {{2}} from APS Dream Home following up on your {{3}} inquiry. Are you available for a quick discussion? Please let us know your preferred time.'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'Call us: 1800-XXX-XXXX'
                ]
            ]
        ],
        'appointment_reminder' => [
            'name' => 'appointment_reminder_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '📅 Appointment Reminder'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, reminder: You have an appointment scheduled for {{2}} at {{3}} regarding {{4}}. Please arrive 10 minutes early. Contact: {{5}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home'
                ]
            ]
        ],
        'payment_reminder' => [
            'name' => 'payment_reminder_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '💰 Payment Reminder'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, this is a friendly reminder for your pending payment of ₹{{2}} for {{3}}. Due date: {{4}}. Pay now to avoid late fees. UPI: {{5}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Accounts'
                ]
            ]
        ],
        'support_ticket_update' => [
            'name' => 'support_ticket_update_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '🎧 Support Update'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, your support ticket #{{2}} for {{3}} has been {{4}}. {{5}} Contact us if you need further assistance: {{6}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home Support'
                ]
            ]
        ],
        'farmer_communication' => [
            'name' => 'farmer_communication_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '🌾 Farmer Communication'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Namaste {{1}} ji, APS Dream Home se {{2}} bol raha hu. Aapke {{3}} ke baare mein baat karni thi. Samay nikaal sakte hai? {{4}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Kisan Sahayak'
                ]
            ]
        ],
        'mlm_commission_update' => [
            'name' => 'mlm_commission_update_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '💰 Commission Update'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Congratulations {{1}}! You have earned ₹{{2}} commission from {{3}} sale. Total earnings this month: ₹{{4}}. Keep up the great work! 🎉'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Associate Program'
                ]
            ]
        ],
        'property_alert' => [
            'name' => 'property_alert_aps',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => '🏠 New Property Alert!'
                ],
                [
                    'type' => 'BODY',
                    'text' => 'Hi {{1}}, a new {{2}} is now available in {{3}} for ₹{{4}}. This matches your saved preferences. View now: {{5}}'
                ],
                [
                    'type' => 'FOOTER',
                    'text' => 'APS Dream Home - Property Alerts'
                ]
            ]
        ]
    ];

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createWhatsAppTables();
        $this->setupWhatsAppConfiguration();
    }

    /**
     * Create WhatsApp related tables
     */
    private function createWhatsAppTables() {
        // WhatsApp messages table
        $sql = "CREATE TABLE IF NOT EXISTS whatsapp_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id VARCHAR(100) NOT NULL UNIQUE,
            recipient_phone VARCHAR(20) NOT NULL,
            recipient_name VARCHAR(100),
            message_type ENUM('template','text','image','document','audio','video','location') DEFAULT 'text',
            template_name VARCHAR(100),
            message_content TEXT,
            media_url VARCHAR(500),
            media_caption TEXT,
            status ENUM('sent','delivered','read','failed','pending') DEFAULT 'pending',
            sent_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            read_at TIMESTAMP NULL,
            error_message TEXT,
            response_data JSON,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // WhatsApp templates table
        $sql = "CREATE TABLE IF NOT EXISTS whatsapp_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_name VARCHAR(100) NOT NULL UNIQUE,
            template_id VARCHAR(100),
            category ENUM('MARKETING','UTILITY','AUTHENTICATION') DEFAULT 'UTILITY',
            language VARCHAR(10) DEFAULT 'en',
            status ENUM('APPROVED','PENDING','REJECTED','PAUSED') DEFAULT 'PENDING',
            components JSON,
            variables JSON,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // WhatsApp conversations table
        $sql = "CREATE TABLE IF NOT EXISTS whatsapp_conversations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_phone VARCHAR(20) NOT NULL,
            customer_name VARCHAR(100),
            conversation_status ENUM('active','inactive','archived') DEFAULT 'active',
            last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            message_count INT DEFAULT 0,
            unread_count INT DEFAULT 0,
            tags JSON,
            custom_fields JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $this->conn->query($sql);

        // WhatsApp campaigns table
        $sql = "CREATE TABLE IF NOT EXISTS whatsapp_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campaign_name VARCHAR(150) NOT NULL UNIQUE,
            campaign_type ENUM('promotional','informational','transactional','support') DEFAULT 'informational',
            target_audience JSON,
            template_name VARCHAR(100),
            message_content TEXT,
            media_url VARCHAR(500),
            status ENUM('draft','scheduled','active','paused','completed','cancelled') DEFAULT 'draft',
            total_recipients INT DEFAULT 0,
            sent_count INT DEFAULT 0,
            delivered_count INT DEFAULT 0,
            read_count INT DEFAULT 0,
            scheduled_at TIMESTAMP NULL,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        $this->conn->query($sql);

        // Insert default WhatsApp templates
        $this->insertDefaultWhatsAppTemplates();
    }

    /**
     * Insert default WhatsApp templates
     */
    private function insertDefaultWhatsAppTemplates() {
        $checkSql = "SELECT COUNT(*) as count FROM whatsapp_templates";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            foreach ($this->templates as $templateName => $template) {
                $sql = "INSERT INTO whatsapp_templates (template_name, category, language, components, variables, status)
                       VALUES (?, 'UTILITY', ?, ?, ?, 'APPROVED')";
                $stmt = $this->conn->prepare($sql);
                $componentsJson = json_encode($template['components']);
                $variablesJson = json_encode(['name', 'location', 'price', 'contact']);
                $stmt->bind_param("sss", $templateName, $template['language'], $componentsJson, $variablesJson);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Setup WhatsApp configuration
     */
    private function setupWhatsAppConfiguration() {
        // Load WhatsApp configuration from database or config file
        $configSql = "SELECT * FROM site_settings WHERE setting_name LIKE 'whatsapp_%'";
        $result = $this->conn->query($configSql);

        while ($row = $result->fetch_assoc()) {
            $settingName = str_replace('whatsapp_', '', $row['setting_name']);
            $this->$settingName = $row['setting_value'];
        }

        // If no configuration found, use defaults or environment variables
        if (empty($this->accessToken)) {
            $this->accessToken = getenv('WHATSAPP_ACCESS_TOKEN') ?: 'YOUR_WHATSAPP_ACCESS_TOKEN';
        }
        if (empty($this->phoneNumberId)) {
            $this->phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID') ?: 'YOUR_PHONE_NUMBER_ID';
        }
        if (empty($this->businessAccountId)) {
            $this->businessAccountId = getenv('WHATSAPP_BUSINESS_ACCOUNT_ID') ?: 'YOUR_BUSINESS_ACCOUNT_ID';
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendWhatsAppMessage($recipientPhone, $messageData, $messageType = 'text') {
        $messageId = 'MSG' . time() . rand(1000, 9999);

        // Clean phone number
        $cleanPhone = $this->cleanPhoneNumber($recipientPhone);

        // Log message
        $logSql = "INSERT INTO whatsapp_messages
                   (message_id, recipient_phone, recipient_name, message_type, message_content, status, created_by)
                   VALUES (?, ?, ?, ?, ?, 'pending', ?)";
        $stmt = $this->conn->prepare($logSql);
        $stmt->bind_param("ssssi", $messageId, $cleanPhone,
                         $messageData['name'] ?? '', $messageType,
                         $messageData['content'] ?? '', $_SESSION['user_id'] ?? 1);
        $stmt->execute();
        $stmt->close();

        // Send to WhatsApp API
        $result = $this->sendToWhatsAppAPI($cleanPhone, $messageData, $messageType);

        // Update message status
        $statusUpdateSql = "UPDATE whatsapp_messages SET status = ?, sent_at = NOW(),
                           response_data = ?, error_message = ? WHERE message_id = ?";
        $stmt = $this->conn->prepare($statusUpdateSql);
        $stmt->bind_param("ss", $result['status'], $result['response_data'] ?? '', $result['error'] ?? '', $messageId);
        $stmt->execute();
        $stmt->close();

        if ($this->logger) {
            $this->logger->log("WhatsApp message sent: $messageId to $cleanPhone", 'info', 'whatsapp');
        }

        return ['message_id' => $messageId, 'status' => $result['status']];
    }

    /**
     * Send WhatsApp template message
     */
    public function sendWhatsAppTemplate($recipientPhone, $templateName, $templateData = []) {
        $template = $this->getWhatsAppTemplate($templateName);
        if (!$template) {
            return ['error' => 'Template not found'];
        }

        // Prepare template components
        $components = [];
        foreach ($template['components'] as $component) {
            if ($component['type'] === 'BODY') {
                $text = $component['text'];
                foreach ($templateData as $key => $value) {
                    $text = str_replace('{{' . ($key + 1) . '}}', $value, $text);
                }
                $components[] = [
                    'type' => 'body',
                    'parameters' => array_map(function($value) {
                        return ['type' => 'text', 'text' => $value];
                    }, array_values($templateData))
                ];
            } else {
                $components[] = $component;
            }
        }

        $messageData = [
            'name' => $template['template_name'],
            'language' => ['code' => $template['language']],
            'components' => $components
        ];

        return $this->sendWhatsAppMessage($recipientPhone, $messageData, 'template');
    }

    /**
     * Send WhatsApp message to lead
     */
    public function sendWhatsAppToLead($leadId, $messageType, $customMessage = '') {
        $lead = $this->getLead($leadId);
        if (!$lead || empty($lead['phone'])) {
            return ['error' => 'Lead phone number not found'];
        }

        switch ($messageType) {
            case 'welcome':
                return $this->sendWhatsAppTemplate($lead['phone'], 'welcome_message', [
                    $lead['first_name'],
                    'https://apsdreamhome.com',
                    '1800-XXX-XXXX'
                ]);

            case 'property_recommendation':
                return $this->sendWhatsAppTemplate($lead['phone'], 'property_recommendation', [
                    $lead['first_name'],
                    $lead['property_interest'] ?: 'property',
                    $lead['preferred_location'] ?: 'your preferred location',
                    '₹' . number_format($lead['budget_min'] ?: 5000000),
                    '1800-XXX-XXXX'
                ]);

            case 'follow_up':
                $agentName = $this->getCurrentUserName();
                return $this->sendWhatsAppTemplate($lead['phone'], 'follow_up_reminder', [
                    $lead['first_name'],
                    $agentName,
                    $lead['property_interest'] ?: 'property inquiry'
                ]);

            case 'appointment_reminder':
                return $this->sendWhatsAppTemplate($lead['phone'], 'appointment_reminder', [
                    $lead['first_name'],
                    'tomorrow at 10:00 AM',
                    'APS Dream Home Office',
                    $lead['property_interest'] ?: 'property viewing',
                    '1800-XXX-XXXX'
                ]);

            case 'custom':
                return $this->sendWhatsAppMessage($lead['phone'], [
                    'content' => $customMessage,
                    'name' => $lead['first_name']
                ]);

            default:
                return ['error' => 'Invalid message type'];
        }
    }

    /**
     * Send WhatsApp message to customer
     */
    public function sendWhatsAppToCustomer($customerId, $messageType, $additionalData = []) {
        $customer = $this->getCustomer($customerId);
        if (!$customer || empty($customer['phone'])) {
            return ['error' => 'Customer phone number not found'];
        }

        switch ($messageType) {
            case 'plot_booking_confirmation':
                return $this->sendWhatsAppTemplate($customer['phone'], 'plot_booking_confirmation', [
                    $customer['first_name'],
                    $additionalData['plot_number'] ?: 'A-001',
                    $additionalData['colony_name'] ?: 'APS Dream City',
                    $additionalData['booking_amount'] ?: '50,000',
                    $additionalData['total_amount'] ?: '5,00,000',
                    $additionalData['booking_number'] ?: 'BK001'
                ]);

            case 'payment_reminder':
                return $this->sendWhatsAppTemplate($customer['phone'], 'payment_reminder', [
                    $customer['first_name'],
                    $additionalData['amount'] ?: '25,000',
                    $additionalData['description'] ?: 'plot installment',
                    $additionalData['due_date'] ?: date('d/m/Y', strtotime('+7 days')),
                    $additionalData['upi_id'] ?: 'apsdreamhome@paytm'
                ]);

            case 'support_update':
                return $this->sendWhatsAppTemplate($customer['phone'], 'support_ticket_update', [
                    $customer['first_name'],
                    $additionalData['ticket_number'] ?: 'TICKET001',
                    $additionalData['issue'] ?: 'your query',
                    $additionalData['status'] ?: 'resolved',
                    $additionalData['message'] ?: 'Issue has been resolved successfully.',
                    '1800-XXX-XXXX'
                ]);

            case 'property_alert':
                return $this->sendWhatsAppTemplate($customer['phone'], 'property_alert', [
                    $customer['first_name'],
                    $additionalData['property_type'] ?: 'property',
                    $additionalData['location'] ?: 'your preferred area',
                    $additionalData['price'] ?: '₹50,00,000',
                    'https://apsdreamhome.com/properties'
                ]);

            default:
                return ['error' => 'Invalid message type'];
        }
    }

    /**
     * Send WhatsApp to farmer
     */
    public function sendWhatsAppToFarmer($farmerId, $messageType, $additionalData = []) {
        $farmer = $this->getFarmer($farmerId);
        if (!$farmer || empty($farmer['phone'])) {
            return ['error' => 'Farmer phone number not found'];
        }

        switch ($messageType) {
            case 'land_acquisition':
                return $this->sendWhatsAppTemplate($farmer['phone'], 'farmer_communication', [
                    $farmer['full_name'],
                    $this->getCurrentUserName(),
                    $additionalData['land_details'] ?: 'your land',
                    '1800-XXX-XXXX'
                ]);

            case 'payment_update':
                return $this->sendWhatsAppMessage($farmer['phone'], [
                    'content' => "Namaste {$farmer['full_name']} ji, APS Dream Home se payment update: ₹{$additionalData['amount']} transferred to your account. Reference: {$additionalData['reference']}. Thank you for your partnership.",
                    'name' => $farmer['full_name']
                ]);

            case 'appointment':
                return $this->sendWhatsAppMessage($farmer['phone'], [
                    'content' => "Namaste {$farmer['full_name']} ji, APS Dream Home meeting scheduled for {$additionalData['date']} at {$additionalData['time']} regarding {$additionalData['purpose']}. Please confirm your availability.",
                    'name' => $farmer['full_name']
                ]);

            default:
                return ['error' => 'Invalid message type'];
        }
    }

    /**
     * Send WhatsApp to associate
     */
    public function sendWhatsAppToAssociate($associateId, $messageType, $additionalData = []) {
        $associate = $this->getAssociate($associateId);
        if (!$associate || empty($associate['phone'])) {
            return ['error' => 'Associate phone number not found'];
        }

        switch ($messageType) {
            case 'commission_update':
                return $this->sendWhatsAppTemplate($associate['phone'], 'mlm_commission_update', [
                    $associate['name'],
                    $additionalData['commission_amount'] ?: '25,000',
                    $additionalData['sale_type'] ?: 'property sale',
                    $additionalData['total_earnings'] ?: '1,50,000'
                ]);

            case 'team_update':
                return $this->sendWhatsAppMessage($associate['phone'], [
                    'content' => "Hi {$associate['name']}, great news! Your team has grown to {$additionalData['team_size']} members this month. Your total commission: ₹{$additionalData['total_commission']}. Keep up the excellent work! 🎉",
                    'name' => $associate['name']
                ]);

            case 'training_invitation':
                return $this->sendWhatsAppMessage($associate['phone'], [
                    'content' => "Hi {$associate['name']}, you're invited to APS Dream Home's training session on {$additionalData['date']} at {$additionalData['time']}. Topic: {$additionalData['topic']}. Please confirm your attendance.",
                    'name' => $associate['name']
                ]);

            default:
                return ['error' => 'Invalid message type'];
        }
    }

    /**
     * Send WhatsApp campaign
     */
    public function sendWhatsAppCampaign($campaignId, $phoneNumbers) {
        $campaign = $this->getWhatsAppCampaign($campaignId);
        if (!$campaign) {
            return ['error' => 'Campaign not found'];
        }

        $results = [];
        foreach ($phoneNumbers as $phone) {
            $result = $this->sendWhatsAppMessage($phone, [
                'content' => $campaign['message_content'],
                'name' => 'Campaign Recipient'
            ]);

            $results[] = [
                'phone' => $phone,
                'message_id' => $result['message_id'] ?? null,
                'status' => $result['status'] ?? 'failed'
            ];
        }

        // Update campaign statistics
        $this->updateCampaignStats($campaignId, $results);

        return $results;
    }

    /**
     * Get WhatsApp conversation history
     */
    public function getWhatsAppConversation($customerPhone) {
        $sql = "SELECT * FROM whatsapp_messages
                WHERE recipient_phone = ?
                ORDER BY created_at DESC
                LIMIT 50";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $customerPhone);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();

        return $messages;
    }

    /**
     * Get WhatsApp templates
     */
    public function getWhatsAppTemplates() {
        $sql = "SELECT * FROM whatsapp_templates WHERE status = 'APPROVED' ORDER BY template_name";
        $result = $this->conn->query($sql);

        $templates = [];
        while ($row = $result->fetch_assoc()) {
            $row['components'] = json_decode($row['components'] ?? '[]', true);
            $templates[] = $row;
        }

        return $templates;
    }

    /**
     * Get WhatsApp template
     */
    private function getWhatsAppTemplate($templateName) {
        $sql = "SELECT * FROM whatsapp_templates WHERE template_name = ? AND status = 'APPROVED'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $templateName);
        $stmt->execute();
        $result = $stmt->get_result();
        $template = $result->fetch_assoc();
        $stmt->close();

        if ($template) {
            $template['components'] = json_decode($template['components'] ?? '[]', true);
            $template['variables'] = json_decode($template['variables'] ?? '[]', true);
        }

        return $template;
    }

    /**
     * Create WhatsApp campaign
     */
    public function createWhatsAppCampaign($campaignData) {
        $sql = "INSERT INTO whatsapp_campaigns
                (campaign_name, campaign_type, template_name, message_content, media_url,
                 status, total_recipients, created_by)
                VALUES (?, ?, ?, ?, ?, 'draft', ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssii",
                         $campaignData['campaign_name'],
                         $campaignData['campaign_type'],
                         $campaignData['template_name'] ?? '',
                         $campaignData['message_content'],
                         $campaignData['media_url'] ?? '',
                         $campaignData['total_recipients'] ?? 0,
                         $campaignData['created_by']);

        $result = $stmt->execute();
        $campaignId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("WhatsApp campaign created: {$campaignData['campaign_name']}", 'info', 'whatsapp');
        }

        return $result ? $campaignId : false;
    }

    /**
     * Get WhatsApp campaigns
     */
    public function getWhatsAppCampaigns($filters = []) {
        $sql = "SELECT * FROM whatsapp_campaigns WHERE 1=1";

        if (!empty($filters['status'])) {
            $sql .= " AND status = '" . $this->conn->real_escape_string($filters['status']) . "'";
        }

        if (!empty($filters['campaign_type'])) {
            $sql .= " AND campaign_type = '" . $this->conn->real_escape_string($filters['campaign_type']) . "'";
        }

        $sql .= " ORDER BY created_at DESC";

        $result = $this->conn->query($sql);
        $campaigns = [];
        while ($row = $result->fetch_assoc()) {
            $campaigns[] = $row;
        }

        return $campaigns;
    }

    /**
     * Get WhatsApp campaign
     */
    private function getWhatsAppCampaign($campaignId) {
        $sql = "SELECT * FROM whatsapp_campaigns WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $campaignId);
        $stmt->execute();
        $result = $stmt->get_result();
        $campaign = $result->fetch_assoc();
        $stmt->close();

        return $campaign;
    }

    /**
     * Update campaign statistics
     */
    private function updateCampaignStats($campaignId, $results) {
        $sentCount = count(array_filter($results, fn($r) => $r['status'] === 'sent'));
        $deliveredCount = count(array_filter($results, fn($r) => $r['status'] === 'delivered'));
        $readCount = count(array_filter($results, fn($r) => $r['status'] === 'read'));

        $sql = "UPDATE whatsapp_campaigns SET
                sent_count = sent_count + ?,
                delivered_count = delivered_count + ?,
                read_count = read_count + ?,
                status = 'completed',
                completed_at = NOW()
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $sentCount, $deliveredCount, $readCount, $campaignId);
        $stmt->execute();
        $stmt->close();
    }

    // Helper methods
    private function cleanPhoneNumber($phone) {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Add country code if not present
        if (strpos($phone, '+91') !== 0 && strlen($phone) === 10) {
            $phone = '+91' . $phone;
        }

        return $phone;
    }

    private function sendToWhatsAppAPI($phone, $messageData, $messageType) {
        $url = $this->apiUrl . $this->phoneNumberId . '/messages';

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone
        ];

        if ($messageType === 'template') {
            $data['type'] = 'template';
            $data['template'] = $messageData;
        } else {
            $data['type'] = 'text';
            $data['text'] = ['body' => $messageData['content']];
        }

        // In a real implementation, you would use cURL to send to WhatsApp API
        // For demo purposes, we'll simulate the API call
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['status' => 'sent', 'response_data' => $response];
        } else {
            return ['status' => 'failed', 'error' => $response];
        }
    }

    private function getLead($leadId) {
        $sql = "SELECT * FROM leads WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lead = $result->fetch_assoc();
        $stmt->close();
        return $lead;
    }

    private function getCustomer($customerId) {
        $sql = "SELECT * FROM customer_profiles WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();
        return $customer;
    }

    private function getFarmer($farmerId) {
        $sql = "SELECT * FROM farmer_profiles WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $farmerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $farmer = $result->fetch_assoc();
        $stmt->close();
        return $farmer;
    }

    private function getAssociate($associateId) {
        $sql = "SELECT a.*, u.full_name as name, u.phone
                FROM associates a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $associateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $associate = $result->fetch_assoc();
        $stmt->close();
        return $associate;
    }

    private function getCurrentUserName() {
        return $_SESSION['user_name'] ?? 'APS Team';
    }

    /**
     * Get WhatsApp dashboard statistics
     */
    public function getWhatsAppDashboard() {
        $dashboard = [];

        // Message statistics
        $sql = "SELECT
            COUNT(*) as total_messages,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_messages,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_messages,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_messages,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_messages
            FROM whatsapp_messages";
        $result = $this->conn->query($sql);
        $dashboard['message_stats'] = $result->fetch_assoc();

        // Campaign statistics
        $sql = "SELECT
            COUNT(*) as total_campaigns,
            SUM(sent_count) as total_sent,
            SUM(delivered_count) as total_delivered,
            SUM(read_count) as total_read,
            AVG(CASE WHEN sent_count > 0 THEN (read_count / sent_count) * 100 END) as avg_read_rate
            FROM whatsapp_campaigns";
        $result = $this->conn->query($sql);
        $dashboard['campaign_stats'] = $result->fetch_assoc();

        // Recent messages
        $sql = "SELECT * FROM whatsapp_messages
                ORDER BY created_at DESC
                LIMIT 10";
        $result = $this->conn->query($sql);
        $dashboard['recent_messages'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['recent_messages'][] = $row;
        }

        // Active conversations
        $sql = "SELECT
            customer_phone,
            customer_name,
            message_count,
            unread_count,
            last_message_at
            FROM whatsapp_conversations
            WHERE conversation_status = 'active'
            ORDER BY last_message_at DESC
            LIMIT 20";
        $result = $this->conn->query($sql);
        $dashboard['active_conversations'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['active_conversations'][] = $row;
        }

        return $dashboard;
    }
}
?>
