<?php

namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * APS Dream Home - WhatsApp Integration Manager
 * Complete WhatsApp Business API integration for CRM and customer communication
 */

class WhatsAppManager
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $logger;

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    // WhatsApp API Configuration
    private $apiUrl = 'https://graph.facebook.com/v18.0/';
    private $accessToken = '';
    private $phoneNumberId = '';
    private $businessAccountId = '';

    // WhatsApp Templates
    private $templates = [
        // ... (keeping existing templates)
    ];

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->logger = $logger;
        $this->createWhatsAppTables();
        $this->setupWhatsAppConfiguration();
    }

    /**
     * Create WhatsApp related tables
     */
    private function createWhatsAppTables()
    {
        // WhatsApp messages table
        $sql = "";

        $this->db->execute($sql);

        // WhatsApp templates table
        $sql = "";

        $this->db->execute($sql);

        // WhatsApp conversations table
        $sql = "";

        $this->db->execute($sql);

        // WhatsApp campaigns table
        $sql = "";

        $this->db->execute($sql);

        // Insert default WhatsApp templates
        $this->insertDefaultWhatsAppTemplates();
    }

    /**
     * Insert default WhatsApp templates
     */
    private function insertDefaultWhatsAppTemplates()
    {
        $checkSql = "SELECT COUNT(*) as count FROM whatsapp_templates";
        $row = $this->db->fetch($checkSql);

        if ($row['count'] == 0) {
            foreach ($this->templates as $templateName => $template) {
                $sql = "INSERT INTO whatsapp_templates (template_name, category, language, template_content, variables, status)
                       VALUES (?, 'UTILITY', ?, ?, ?, 'APPROVED')";
                $componentsJson = json_encode($template['components']);
                $variablesJson = json_encode(['name', 'location', 'price', 'contact']);
                $this->db->execute($sql, [$templateName, $template['language'], $componentsJson, $variablesJson]);
            }
        }
    }

    /**
     * Setup WhatsApp configuration
     */
    private function setupWhatsAppConfiguration()
    {
        // Load WhatsApp configuration from database or config file
        $configSql = "SELECT * FROM site_settings WHERE setting_name LIKE 'whatsapp_%'";
        $results = $this->db->fetchAll($configSql);

        foreach ($results as $row) {
            $settingName = str_replace('whatsapp_', '', $row['setting_name']);
            if (property_exists($this, $settingName)) {
                $this->$settingName = $row['setting_value'];
            }
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
    public function sendWhatsAppMessage($recipientPhone, $messageData, $messageType = 'text')
    {
        $messageId = 'MSG' . time() . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);

        // Clean phone number
        $cleanPhone = $this->cleanPhoneNumber($recipientPhone);

        // Log message
        $logSql = "INSERT INTO whatsapp_messages
                   (whatsapp_message_id, phone_number, contact_name, message_type, message, status)
                   VALUES (?, ?, ?, ?, ?, 'pending')";
        $params = [
            $messageId,
            $cleanPhone,
            $messageData['name'] ?? '',
            $messageType,
            $messageData['content'] ?? ''
        ];
        $this->db->execute($logSql, $params);

        // Send to WhatsApp API
        $result = $this->sendToWhatsAppAPI($cleanPhone, $messageData, $messageType);

        // Update message status
        $statusUpdateSql = "UPDATE whatsapp_messages SET status = ? WHERE whatsapp_message_id = ?";
        $this->db->execute($statusUpdateSql, [
            $result['status'],
            $messageId
        ]);

        if ($this->logger) {
            $this->logger->log("WhatsApp message sent: $messageId to $cleanPhone", 'info', 'whatsapp');
        }

        return ['message_id' => $messageId, 'status' => $result['status']];
    }

    /**
     * Send WhatsApp template message
     */
    public function sendWhatsAppTemplate($recipientPhone, $templateName, $templateData = [])
    {
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
                    'parameters' => array_map(function ($value) {
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
    public function sendWhatsAppToLead($leadId, $messageType, $customMessage = '')
    {
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
    public function sendWhatsAppToCustomer($customerId, $messageType, $additionalData = [])
    {
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
    public function sendWhatsAppToFarmer($farmerId, $messageType, $additionalData = [])
    {
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
    public function sendWhatsAppToAssociate($associateId, $messageType, $additionalData = [])
    {
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
    public function sendWhatsAppCampaign($campaignId, $phoneNumbers)
    {
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
    public function getWhatsAppConversation($customerPhone)
    {
        $sql = "SELECT * FROM whatsapp_messages
                WHERE recipient_phone = ?
                ORDER BY created_at DESC
                LIMIT 50";
        return $this->db->fetchAll($sql, [$customerPhone]);
    }

    /**
     * Get WhatsApp templates
     */
    public function getWhatsAppTemplates()
    {
        $sql = "SELECT * FROM whatsapp_templates WHERE status = 'APPROVED' ORDER BY template_name";
        $templates = $this->db->fetchAll($sql);
        foreach ($templates as &$row) {
            $row['components'] = json_decode($row['components'] ?? '[]', true);
        }

        return $templates;
    }

    /**
     * Get WhatsApp template
     */
    private function getWhatsAppTemplate($templateName)
    {
        $sql = "SELECT * FROM whatsapp_templates WHERE template_name = ? AND status = 'APPROVED'";
        $template = $this->db->fetch($sql, [$templateName]);

        if ($template) {
            $template['components'] = json_decode($template['components'] ?? '[]', true);
            $template['variables'] = json_decode($template['variables'] ?? '[]', true);
        }

        return $template;
    }

    /**
     * Create WhatsApp campaign
     */
    public function createWhatsAppCampaign($campaignData)
    {
        try {
            $sql = "INSERT INTO whatsapp_campaigns
                    (campaign_name, campaign_type, template_name, message_content, media_url,
                     status, total_recipients, created_by)
                    VALUES (?, ?, ?, ?, ?, 'draft', ?, ?)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $params = [
            $campaignData['campaign_name'],
            $campaignData['campaign_type'],
            $campaignData['template_name'] ?? '',
            $campaignData['message_content'],
            $campaignData['media_url'] ?? '',
            $campaignData['total_recipients'] ?? 0,
            $campaignData['created_by']
        ];

        $this->db->execute($sql, $params);
        $campaignId = $this->db->lastInsertId();

        if ($campaignId && $this->logger) {
            $this->logger->log("WhatsApp campaign created: {$campaignData['campaign_name']}", 'info', 'whatsapp');
        }

        return $campaignId;
    }

    /**
     * Get WhatsApp campaigns
     */
    public function getWhatsAppCampaigns($filters = [])
    {
        try {
            $sql = "SELECT * FROM whatsapp_campaigns WHERE 1=1";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['campaign_type'])) {
            $sql .= " AND campaign_type = ?";
            $params[] = $filters['campaign_type'];
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get WhatsApp campaign
     */
    private function getWhatsAppCampaign($campaignId)
    {
        try {
            $sql = "SELECT * FROM whatsapp_campaigns WHERE id = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $this->db->fetch($sql, [$campaignId]);
    }

    /**
     * Update campaign statistics
     */
    private function updateCampaignStats($campaignId, $results)
    {
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

        $this->db->execute($sql, [$sentCount, $deliveredCount, $readCount, $campaignId]);
    }

    // Helper methods
    private function cleanPhoneNumber($phone)
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Add country code if not present
        if (strpos($phone, '+91') !== 0 && strlen($phone) === 10) {
            $phone = '+91' . $phone;
        }

        return $phone;
    }

    private function sendToWhatsAppAPI($phone, $messageData, $messageType)
    {
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

        if ($httpCode === 200) {
            return ['status' => 'sent', 'response_data' => $response];
        } else {
            return ['status' => 'failed', 'error' => $response];
        }
    }

    private function getLead($leadId)
    {
        $sql = "SELECT * FROM leads WHERE id = ?" . $this->tenantSql();
        $params = [$leadId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        return $this->db->fetch($sql, $params);
    }

    private function getCustomer($customerId)
    {
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM users WHERE id = ?{$tenantSql}";
        return $this->db->fetch($sql, $tid > 1 ? [$customerId, $tid] : [$customerId]);
    }

    private function getFarmer($farmerId)
    {
        $sql = "SELECT * FROM farmer_profiles WHERE id = ?";
        return $this->db->fetch($sql, [$farmerId]);
    }

    private function getAssociate($associateId)
    {
        $tid = $this->getTenantId();
        $tenantJoin = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $tenantSql = $tid > 1 ? " AND a.tenant_id = ?" : "";
        $sql = "SELECT a.*, u.name as name, u.phone as phone
                FROM users a
                {$tenantJoin}
                WHERE a.id = ?{$tenantSql}";
        return $this->db->fetch($sql, $tid > 1 ? [$tid, $associateId, $tid] : [$associateId]);
    }

    private function getCurrentUserName()
    {
        return $_SESSION['user_name'] ?? 'APS Team';
    }

    /**
     * Get WhatsApp dashboard statistics
     */
    public function getWhatsAppDashboard()
    {
        $dashboard = [];

        // Message statistics
        $sql = "SELECT
            COUNT(*) as total_messages,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_messages,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_messages,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_messages,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_messages
            FROM whatsapp_messages";
        $dashboard['message_stats'] = $this->db->fetch($sql);

        try {
            // Campaign statistics
            $sql = "SELECT
                COUNT(*) as total_campaigns,
                SUM(sent_count) as total_sent,
                SUM(delivered_count) as total_delivered,
                SUM(read_count) as total_read,
                AVG(CASE WHEN sent_count > 0 THEN (read_count / sent_count) * 100 END) as avg_read_rate
                FROM whatsapp_campaigns";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $dashboard['campaign_stats'] = $this->db->fetch($sql);

        // Recent messages
        $sql = "SELECT * FROM whatsapp_messages
                ORDER BY created_at DESC
                LIMIT 10";
        $dashboard['recent_messages'] = $this->db->fetchAll($sql);

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
        $dashboard['active_conversations'] = $this->db->fetchAll($sql);

        return $dashboard;
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 725 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//