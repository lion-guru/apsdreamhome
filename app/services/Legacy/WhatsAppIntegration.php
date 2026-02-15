<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - WhatsApp Integration System
 * Handles WhatsApp messaging through multiple providers
 */

// Prevent direct access
if (!defined('BASE_URL') && !defined('SITE_URL')) {
    exit('Direct access not allowed');
}

require_once __DIR__ . '/permission_manager.php';

class WhatsAppIntegration {
    private $config;
    private $provider;

    public function __construct() {
        global $config;
        $this->config = $config['whatsapp'] ?? [];

        if (!$this->config['enabled']) {
            throw new Exception('WhatsApp integration is disabled');
        }

        $this->provider = $this->config['api_provider'] ?? 'whatsapp_business_api';
    }

    /**
     * Send WhatsApp message using template
     */
    public function sendTemplateMessage($phone_number, $template_name, $variables = []) {
        // Check permission for sending messages
        check_permission('send_whatsapp', true);

        // Include template system
        $templates_file = __DIR__ . '/whatsapp_templates.php';
        if (!file_exists($templates_file)) {
            return ['success' => false, 'error' => 'Template system file missing'];
        }
        require_once $templates_file;

        $result = sendWhatsAppTemplateMessage($phone_number, $template_name, $variables);

        if ($result['success']) {
            $this->logWhatsAppActivity('TEMPLATE_SENT', $phone_number, "Template: {$template_name}");
            audit_log('WhatsApp Template Sent', "To: $phone_number, Template: $template_name");
        } else {
            $this->logWhatsAppActivity('TEMPLATE_FAILED', $phone_number, "Template: {$template_name}", $result['error']);
            audit_log('WhatsApp Template Failed', "To: $phone_number, Error: " . $result['error']);
        }

        return $result;
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage($phone_number, $message, $message_type = 'text', $media_url = null) {
        // Check permission for sending messages
        check_permission('send_whatsapp', true);

        // Format phone number
        $formatted_number = $this->formatPhoneNumber($phone_number);

        // Check if number is valid
        if (!$this->isValidPhoneNumber($formatted_number)) {
            return ['success' => false, 'error' => 'Invalid phone number format'];
        }

        // Send based on provider
        $result = ['success' => false, 'error' => 'Unsupported WhatsApp provider'];
        switch ($this->provider) {
            case 'whatsapp_business_api':
                $result = $this->sendViaWhatsAppBusinessAPI($formatted_number, $message, $message_type, $media_url);
                break;
            case 'twilio':
                $result = $this->sendViaTwilio($formatted_number, $message, $media_url);
                break;
            case 'whatsapp_web':
                $result = $this->sendViaWhatsAppWeb($formatted_number, $message);
                break;
        }

        if ($result['success']) {
            audit_log('WhatsApp Message Sent', "To: $formatted_number");
        } else {
            audit_log('WhatsApp Message Failed', "To: $formatted_number, Error: " . ($result['error'] ?? 'Unknown error'));
        }

        return $result;
    }

    /**
     * Send welcome message
     */
    public function sendWelcomeMessage($phone_number, $customer_name) {
        if (!$this->config['notification_types']['welcome_message']) {
            return ['success' => false, 'error' => 'Welcome messages disabled'];
        }

        $message = "🏠 Welcome to APS Dream Home, {$customer_name}!\n\n";
        $message .= "Thank you for choosing us for your real estate needs. We're here to help you find your perfect property!\n\n";
        $message .= "📞 Contact us anytime: " . $this->config['phone_number'] . "\n";
        $message .= "🌐 Visit our website for latest listings";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send property inquiry notification
     */
    public function sendPropertyInquiryNotification($phone_number, $property_data, $customer_data) {
        if (!$this->config['notification_types']['property_inquiry']) {
            return ['success' => false, 'error' => 'Property inquiry notifications disabled'];
        }

        $message = "🏠 New Property Inquiry Received!\n\n";
        $message .= "📋 Property: {$property_data['title']}\n";
        $message .= "📍 Location: {$property_data['location']}\n";
        $message .= "💰 Price: ₹" . number_format($property_data['price']) . "\n\n";
        $message .= "👤 Customer: {$customer_data['name']}\n";
        $message .= "📞 Phone: {$customer_data['phone']}\n";
        $message .= "💬 Message: {$customer_data['message']}\n\n";
        $message .= "Please contact the customer to discuss this property.";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send booking confirmation
     */
    public function sendBookingConfirmation($phone_number, $booking_data) {
        if (!$this->config['notification_types']['booking_confirmation']) {
            return ['success' => false, 'error' => 'Booking confirmations disabled'];
        }

        $message = "✅ Booking Confirmed!\n\n";
        $message .= "🏠 Property: {$booking_data['property_title']}\n";
        $message .= "📍 Location: {$booking_data['property_location']}\n";
        $message .= "📅 Booking Date: {$booking_data['booking_date']}\n";
        $message .= "🆔 Booking ID: {$booking_data['booking_id']}\n";
        $message .= "💰 Amount: ₹" . number_format($booking_data['amount']) . "\n\n";
        $message .= "Our team will contact you shortly with next steps.\n\n";
        $message .= "Thank you for choosing APS Dream Home! 🏠✨";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send commission notification
     */
    public function sendCommissionNotification($phone_number, $commission_data) {
        if (!$this->config['notification_types']['commission_alert']) {
            return ['success' => false, 'error' => 'Commission notifications disabled'];
        }

        $message = "💰 Commission Earned!\n\n";
        $message .= "Congratulations! You've earned a commission of ₹" . number_format($commission_data['amount']) . "\n\n";
        $message .= "📋 Transaction ID: {$commission_data['transaction_id']}\n";
        $message .= "🏠 Property: {$commission_data['property_title']}\n";
        $message .= "👤 Customer: {$commission_data['customer_name']}\n";
        $message .= "📅 Date: {$commission_data['date']}\n\n";
        $message .= "Your commission will be processed within 7-10 business days.\n\n";
        $message .= "Keep up the great work! 🚀";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder($phone_number, $payment_data) {
        if (!$this->config['notification_types']['payment_reminder']) {
            return ['success' => false, 'error' => 'Payment reminders disabled'];
        }

        $message = "💳 Payment Reminder\n\n";
        $message .= "Dear Customer,\n\n";
        $message .= "This is a reminder for your pending payment:\n\n";
        $message .= "🏠 Property: {$payment_data['property_title']}\n";
        $message .= "💰 Amount: ₹" . number_format($payment_data['amount']) . "\n";
        $message .= "📅 Due Date: {$payment_data['due_date']}\n";
        $message .= "🆔 Payment ID: {$payment_data['payment_id']}\n\n";
        $message .= "Please make the payment to avoid any inconvenience.\n\n";
        $message .= "📞 Contact us: " . $this->config['phone_number'];

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send appointment reminder
     */
    public function sendAppointmentReminder($phone_number, $appointment_data) {
        if (!$this->config['notification_types']['appointment_reminder']) {
            return ['success' => false, 'error' => 'Appointment reminders disabled'];
        }

        $message = "📅 Appointment Reminder\n\n";
        $message .= "Hi {$appointment_data['customer_name']},\n\n";
        $message .= "This is a reminder for your appointment:\n\n";
        $message .= "🏠 Property Visit: {$appointment_data['property_title']}\n";
        $message .= "📅 Date & Time: {$appointment_data['appointment_date']} at {$appointment_data['appointment_time']}\n";
        $message .= "👨‍💼 Agent: {$appointment_data['agent_name']}\n";
        $message .= "📞 Contact: {$appointment_data['agent_phone']}\n\n";
        $message .= "Please arrive 10 minutes early. See you there! 🏠";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Send system alert
     */
    public function sendSystemAlert($phone_number, $alert_data) {
        // Restricted to Admin/Manager only
        check_role('admin', true);

        if (!$this->config['notification_types']['system_alerts']) {
            return ['success' => false, 'error' => 'System alerts disabled'];
        }

        $message = "🚨 System Alert\n\n";
        $message .= "Alert Type: {$alert_data['alert_type']}\n";
        $message .= "Message: {$alert_data['message']}\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Please check the system immediately.";

        return $this->sendMessage($phone_number, $message);
    }

    /**
     * Handle incoming WhatsApp webhook data (Legacy)
     */
    public function handleIncomingWebhook($data) {
        // Basic logging
        $this->logWhatsAppActivity('WEBHOOK_RECEIVED', 'SYSTEM', 'Payload: ' . json_encode($data));

        // In a real implementation, you would parse $data to identify:
        // 1. Sender phone number
        // 2. Message content
        // 3. Message type
        // 4. Update lead/customer records

        return ['success' => true];
    }

    /**
     * Send via WhatsApp Business API
     */
    private function sendViaWhatsAppBusinessAPI($phone_number, $message, $message_type, $media_url = null) {
        // WhatsApp Business API implementation
        $url = "https://graph.facebook.com/v17.0/" . ($this->config['business_account_id'] ?? '') . "/messages";

        if ($media_url && in_array($message_type, ['image', 'document', 'video', 'audio'])) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phone_number,
                'type' => $message_type,
                $message_type => [
                    'link' => $media_url,
                    'caption' => $message
                ]
            ];
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phone_number,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message
                ]
            ];
        }

        $headers = [
            'Authorization: Bearer ' . ($this->config['access_token'] ?? ''),
            'Content-Type: application/json'
        ];

        // Use curl for the request (since file_get_contents might not work with custom headers)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code === 200) {
            $this->logWhatsAppActivity('SENT', $phone_number, $message);
            return ['success' => true, 'message' => 'WhatsApp message sent successfully'];
        } else {
            $error = json_decode($response, true);
            $this->logWhatsAppActivity('FAILED', $phone_number, $message, $error['error']['message'] ?? 'Unknown error');
            return ['success' => false, 'error' => 'WhatsApp API error: ' . ($error['error']['message'] ?? 'Unknown error')];
        }
    }

    /**
     * Send via Twilio WhatsApp API
     */
    private function sendViaTwilio($phone_number, $message, $media_url = null) {
        // Twilio WhatsApp API implementation
        // Note: This requires a Twilio account with WhatsApp enabled

        $twilio_sid = getenv('TWILIO_ACCOUNT_SID');
        $twilio_token = getenv('TWILIO_AUTH_TOKEN');
        $twilio_number = getenv('TWILIO_PHONE_NUMBER');

        if (!$twilio_sid || !$twilio_token || !$twilio_number) {
            return ['success' => false, 'error' => 'Twilio credentials not configured'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilio_sid}/Messages.json";

        $payload = [
            'From' => 'whatsapp:' . $twilio_number,
            'To' => 'whatsapp:' . $phone_number,
            'Body' => $message
        ];

        if ($media_url) {
            $payload['MediaUrl'] = $media_url;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_USERPWD, "{$twilio_sid}:{$twilio_token}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close is deprecated in PHP 8.0+ for CurlHandle objects

        if ($http_code === 201) {
            $this->logWhatsAppActivity('SENT', $phone_number, $message);
            return ['success' => true, 'message' => 'WhatsApp message sent successfully via Twilio'];
        } else {
            $error = json_decode($response, true);
            $this->logWhatsAppActivity('FAILED', $phone_number, $message, $error['message'] ?? 'Unknown error');
            return ['success' => false, 'error' => 'Twilio API error: ' . ($error['message'] ?? 'Unknown error')];
        }
    }

    /**
     * Send via WhatsApp Web (alternative method)
     */
    private function sendViaWhatsAppWeb($phone_number, $message) {
        // This is a fallback method that opens WhatsApp Web
        // Not recommended for production use

        $whatsapp_url = "https://wa.me/{$phone_number}?text=" . urlencode($message);

        // Log the attempt
        $this->logWhatsAppActivity('WEB_REDIRECT', $phone_number, $message);

        return [
            'success' => true,
            'message' => 'WhatsApp Web URL generated',
            'whatsapp_url' => $whatsapp_url
        ];
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber($phone_number) {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone_number);

        // Add country code if not present
        if (strlen($phone) === 10) {
            $phone = ($this->config['country_code'] ?? '91') . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber($phone_number) {
        // Basic validation - should be 10-15 digits
        return preg_match('/^\d{10,15}$/', $phone_number);
    }

    /**
     * Log WhatsApp activity
     */
    private function logWhatsAppActivity($status, $recipient, $message, $error = null) {
        $log_file = __DIR__ . '/../logs/whatsapp.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => $status,
            'recipient' => $recipient,
            'message_length' => strlen($message),
            'provider' => $this->provider,
            'error' => $error,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'system'
        ];

        file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Handle incoming WhatsApp webhooks
     */
    public function handleWebhook() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            return ['error' => 'Invalid webhook data'];
        }

        // Verify webhook token
        $token = $_GET['token'] ?? '';
        if ($token !== $this->config['webhook_verify_token']) {
            return ['error' => 'Invalid webhook token'];
        }

        // Process incoming message
        if (isset($input['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message_data = $input['entry'][0]['changes'][0]['value']['messages'][0];
            $sender = $input['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'];

            return $this->processIncomingMessage($sender, $message_data);
        }

        return ['success' => true, 'message' => 'Webhook processed'];
    }

    /**
     * Process incoming WhatsApp message
     */
    private function processIncomingMessage($sender_phone, $message_data) {
        // Store incoming message in database for processing
        $this->storeIncomingMessage($sender_phone, $message_data);

        // Generate auto-response if needed
        $auto_response = $this->generateAutoResponse($message_data);

        if ($auto_response) {
            return $this->sendMessage($sender_phone, $auto_response);
        }

        return ['success' => true, 'message' => 'Message processed'];
    }

    /**
     * Store incoming message for processing
     */
    private function storeIncomingMessage($sender_phone, $message_data) {
        // Store in database for admin review and processing
        // This could be used to create leads, respond to inquiries, etc.
        $log_file = __DIR__ . '/../logs/whatsapp_incoming.log';
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'sender_phone' => $sender_phone,
            'message_type' => $message_data['type'] ?? 'text',
            'message_content' => $message_data['text']['body'] ?? '',
            'message_id' => $message_data['id'] ?? ''
        ];

        file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Generate auto-response based on message content
     */
    private function generateAutoResponse($message_data) {
        $message_text = strtolower($message_data['text']['body'] ?? '');

        // Check business hours
        $current_hour = date('H');
        $business_hours = explode('-', $this->config['auto_responses']['greeting_hours']);

        if ($current_hour < (int)$business_hours[0] || $current_hour > (int)$business_hours[1]) {
            return $this->config['auto_responses']['away_message'];
        }

        // Keyword-based responses
        if (strpos($message_text, 'hello') !== false || strpos($message_text, 'hi') !== false) {
            return $this->config['auto_responses']['welcome_message'];
        }

        if (strpos($message_text, 'property') !== false || strpos($message_text, 'house') !== false) {
            return "🏠 I can help you find properties! Please visit our website or contact us at " . $this->config['phone_number'] . " for the latest listings.";
        }

        if (strpos($message_text, 'price') !== false || strpos($message_text, 'cost') !== false) {
            return "💰 For property prices and valuations, please contact our team at " . $this->config['phone_number'] . " or visit our website.";
        }

        // Default response
        return "Thank you for your message! Our team will get back to you soon. For urgent inquiries, call us at " . $this->config['phone_number'] . ".";
    }

    /**
     * Get WhatsApp statistics
     */
    public function getWhatsAppStats() {
        $log_file = __DIR__ . '/../logs/whatsapp.log';

        if (!file_exists($log_file)) {
            return [
                'total_sent' => 0,
                'total_failed' => 0,
                'success_rate' => 0,
                'provider' => $this->provider
            ];
        }

        $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total_sent = 0;
        $total_failed = 0;

        foreach ($logs as $log) {
            $data = json_decode($log, true);
            if ($data && $data['status'] === 'SENT') {
                $total_sent++;
            } elseif ($data && $data['status'] === 'FAILED') {
                $total_failed++;
            }
        }

        $total = $total_sent + $total_failed;
        $success_rate = $total > 0 ? round(($total_sent / $total) * 100, 2) : 0;

        return [
            'total_sent' => $total_sent,
            'total_failed' => $total_failed,
            'success_rate' => $success_rate,
            'provider' => $this->provider
        ];
    }
}

// Utility functions for easy WhatsApp integration

/**
 * Send WhatsApp welcome message
 */
function sendWhatsAppWelcome($phone_number, $customer_name) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendWelcomeMessage($phone_number, $customer_name);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp property inquiry notification
 */
function sendWhatsAppPropertyInquiry($phone_number, $property_data, $customer_data) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendPropertyInquiryNotification($phone_number, $property_data, $customer_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp commission notification
 */
function sendWhatsAppCommissionNotification($phone_number, $commission_data) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendCommissionNotification($phone_number, $commission_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp payment reminder
 */
function sendWhatsAppPaymentReminder($phone_number, $payment_data) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendPaymentReminder($phone_number, $payment_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp appointment reminder
 */
function sendWhatsAppAppointmentReminder($phone_number, $appointment_data) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendAppointmentReminder($phone_number, $appointment_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp system alert
 */
function sendWhatsAppSystemAlert($phone_number, $alert_data) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendSystemAlert($phone_number, $alert_data);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send WhatsApp template message
 */
function sendWhatsAppTemplateMessage($phone_number, $template_name, $variables = []) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendTemplateMessage($phone_number, $template_name, $variables);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get WhatsApp statistics
 */
function getWhatsAppStats() {
    // Restricted to Admin only
    check_role('admin', true);

    try {
        $db = \App\Core\App::database();

        // Get total messages sent
        $total_sent_row = $db->fetch("SELECT COUNT(*) as count FROM whatsapp_logs WHERE action = 'SENT'");
        $total_sent = $total_sent_row['count'] ?? 0;

        // Get total messages delivered
        $total_delivered_row = $db->fetch("SELECT COUNT(*) as count FROM whatsapp_logs WHERE action = 'DELIVERED'");
        $total_delivered = $total_delivered_row['count'] ?? 0;

        // Get total messages failed
        $total_failed_row = $db->fetch("SELECT COUNT(*) as count FROM whatsapp_logs WHERE action = 'FAILED'");
        $total_failed = $total_failed_row['count'] ?? 0;

        // Get success rate
        $success_rate = $total_sent > 0 ? round(($total_delivered / $total_sent) * 100, 2) : 0;

        // Get recent activity (last 7 days)
        $recent_query = "SELECT
            COUNT(CASE WHEN action = 'SENT' THEN 1 END) as sent_7days,
            COUNT(CASE WHEN action = 'DELIVERED' THEN 1 END) as delivered_7days,
            COUNT(CASE WHEN action = 'FAILED' THEN 1 END) as failed_7days
        FROM whatsapp_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

        $recent_result = $db->fetch($recent_query);

        return [
            'total_sent' => (int)$total_sent,
            'total_delivered' => (int)$total_delivered,
            'total_failed' => (int)$total_failed,
            'success_rate' => (float)$success_rate,
            'recent_activity' => [
                'sent_7days' => (int)($recent_result['sent_7days'] ?? 0),
                'delivered_7days' => (int)($recent_result['delivered_7days'] ?? 0),
                'failed_7days' => (int)($recent_result['failed_7days'] ?? 0)
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];

    } catch (Exception $e) {
        error_log('Error getting WhatsApp stats: ' . $e->getMessage());
        return [
            'total_sent' => 0,
            'total_delivered' => 0,
            'total_failed' => 0,
            'success_rate' => 0.0,
            'recent_activity' => [
                'sent_7days' => 0,
                'delivered_7days' => 0,
                'failed_7days' => 0
            ],
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Initialize WhatsApp integration (if not already done)
 */
function initializeWhatsAppIntegration() {
    static $initialized = false;

    if ($initialized) {
        return true;
    }

    try {
        // Check if WhatsApp is enabled
        global $config;
        if (!isset($config['whatsapp']['enabled']) || !$config['whatsapp']['enabled']) {
            return false;
        }

        // Create logs table if it doesn't exist
        $db = \App\Core\App::database();
        $db->execute("CREATE TABLE IF NOT EXISTS whatsapp_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(20) NOT NULL,
            message TEXT,
            action ENUM('SENT', 'DELIVERED', 'FAILED', 'TEMPLATE_USED') NOT NULL,
            provider VARCHAR(50),
            message_id VARCHAR(255),
            error_message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone (phone_number),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        )");

        $initialized = true;
        return true;

    } catch (Exception $e) {
        error_log('WhatsApp initialization failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log WhatsApp activity
 */
function logWhatsAppActivity($action, $phone_number, $message = '', $error = '') {
    try {
        $db = \App\Core\App::database();
        $sql = "INSERT INTO whatsapp_logs (phone_number, message, action, error_message) VALUES (?, ?, ?, ?)";
        return $db->execute($sql, [$phone_number, $message, $action, $error]);
    } catch (Exception $e) {
        error_log('WhatsApp logging failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log WhatsApp activity
 */
function sendWhatsAppBookingConfirmation($phone_number, $booking_details) {
    global $config;

    if (!$config['whatsapp']['enabled']) {
        return ['success' => false, 'error' => 'WhatsApp integration disabled'];
    }

    $variables = [
        'customer_name' => $booking_details['customer_name'] ?? 'Valued Customer',
        'booking_id' => $booking_details['booking_id'] ?? '',
        'property_name' => $booking_details['property_name'] ?? '',
        'booking_date' => $booking_details['booking_date'] ?? date('Y-m-d'),
        'total_amount' => $booking_details['total_amount'] ?? '',
        'agent_name' => $booking_details['agent_name'] ?? 'Our Team'
    ];

    return sendWhatsAppTemplateMessage($phone_number, 'booking_confirmation', $variables);
}
