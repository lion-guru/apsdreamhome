<?php

namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\AI\AIChatbotService;
use App\Services\AI\AIGateway;
use Exception;
use \App\Traits\ServiceTenantTrait;

/**
 * Unified Communication Automation Service
 * Handles WhatsApp, Telegram, SMS, Email with AI-powered auto-reply and lead generation
 */
class CommunicationAutomationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $aiChatbot;
    private $aiGateway;
    private $whatsappService;
    private $smsService;
    private $emailService;

    // Channel configurations
    private $channels = [
        'whatsapp' => ['enabled' => true, 'provider' => 'meta'], // meta, twilio, whatsapp_web
        'telegram' => ['enabled' => false, 'provider' => 'bot_api'],
        'sms' => ['enabled' => true, 'provider' => 'msg91'],
        'email' => ['enabled' => true, 'provider' => 'smtp'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->aiChatbot = new \App\Services\AIChatbotService();
        $this->aiGateway = \App\Services\AI\AIGateway::getInstance();
        $this->initServices();
    }

    private function initServices(): void
    {
        try {
            $this->whatsappService = new \App\Services\Communication\WhatsAppService();
        } catch (Exception $e) {
            $this->channels['whatsapp']['enabled'] = false;
        }

        try {
            $this->smsService = new \App\Services\Communication\SMSService();
        } catch (Exception $e) {
            $this->channels['sms']['enabled'] = false;
        }

        try {
            $this->emailService = new \App\Services\EmailService();
        } catch (Exception $e) {
            $this->channels['email']['enabled'] = false;
        }
    }

    /**
     * Process incoming message from any channel
     * This is the MAIN entry point for all webhooks
     */
    public function processIncomingMessage(string $channel, array $payload): array
    {
        $result = [
            'success' => false,
            'channel' => $channel,
            'reply_sent' => false,
            'lead_created' => false,
            'lead_id' => null,
            'response' => null,
            'error' => null
        ];

        try {
            // Extract message data based on channel
            $messageData = $this->extractMessageData($channel, $payload);
            if (!$messageData) {
                $result['error'] = 'Could not extract message data';
                return $result;
            }

            $from = $messageData['from'];
            $text = $messageData['text'] ?? '';
            $messageId = $messageData['message_id'] ?? null;
            $timestamp = $messageData['timestamp'] ?? time();

            // Store incoming message
            $this->storeMessage($channel, $from, $text, $messageId, 'inbound', $timestamp);

            // Check if user exists or create lead
            $leadId = $this->findOrCreateLead($channel, $from, $text, $messageData);

            if ($leadId) {
                $result['lead_created'] = true;
                $result['lead_id'] = $leadId;
            }

            // Generate AI response
            $aiResponse = $this->generateAIResponse($channel, $from, $text, $leadId);
            
            if ($aiResponse) {
                // Send auto-reply
                $sendResult = $this->sendMessage($channel, $from, $aiResponse['response']);
                
                if ($sendResult['success']) {
                    $result['reply_sent'] = true;
                    $result['response'] = $aiResponse['response'];
                    
                    // Store bot response
                    $this->storeMessage($channel, $from, $aiResponse['response'], null, 'outbound', time());
                }
            }

            $result['success'] = true;

        } catch (Exception $e) {
            error_log("CommunicationAutomation: Error processing incoming message: " . $e->getMessage());
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Extract message data from webhook payload per channel
     */
    private function extractMessageData(string $channel, array $payload): ?array
    {
        switch ($channel) {
            case 'whatsapp':
                return $this->extractWhatsAppMessage($payload);
            case 'telegram':
                return $this->extractTelegramMessage($payload);
            case 'sms':
                return $this->extractSMSMessage($payload);
            default:
                return null;
        }
    }

    private function extractWhatsAppMessage(array $payload): ?array
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return null;

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) return null;

        $value = $changes['value'] ?? [];
        $messages = $value['messages'] ?? [];

        if (empty($messages)) return null;

        $msg = $messages[0];
        return [
            'from' => $msg['from'] ?? '',
            'text' => $msg['text']['body'] ?? '',
            'message_id' => $msg['id'] ?? null,
            'timestamp' => $msg['timestamp'] ?? time(),
            'type' => $msg['type'] ?? 'text',
            'raw' => $msg
        ];
    }

    private function extractTelegramMessage(array $payload): ?array
    {
        $message = $payload['message'] ?? $payload['edited_message'] ?? $payload['channel_post'] ?? null;
        if (!$message) return null;

        return [
            'from' => $message['from']['id'] ?? '',
            'text' => $message['text'] ?? '',
            'message_id' => $message['message_id'] ?? null,
            'timestamp' => $message['date'] ?? time(),
            'chat_id' => $message['chat']['id'] ?? '',
            'username' => $message['from']['username'] ?? '',
            'first_name' => $message['from']['first_name'] ?? '',
            'raw' => $message
        ];
    }

    private function extractSMSMessage(array $payload): ?array
    {
        // MSG91 incoming SMS webhook format
        return [
            'from' => $payload['mobile'] ?? $payload['from'] ?? '',
            'text' => $payload['text'] ?? $payload['message'] ?? '',
            'message_id' => $payload['request_id'] ?? $payload['id'] ?? null,
            'timestamp' => time(),
            'raw' => $payload
        ];
    }

    /**
     * Find existing lead or create new one from message
     */
    private function findOrCreateLead(string $channel, string $from, string $text, array $messageData): ?int
    {
        // Normalize phone number
        $phone = $this->normalizePhone($from);
        if (!$phone) return null;

        // Check if lead exists by phone
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $existing = $this->db->fetchOne(
            "SELECT id FROM leads WHERE phone = ? OR whatsapp_number = ?" . $tenantFilter . " LIMIT 1",
            array_merge([$phone, $phone], $tid > 1 ? [$tid] : [])
        );

        if ($existing) {
            // Update last activity
            $this->db->update('leads', [
                'last_activity_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
            
            // Update channel-specific fields
            $this->updateLeadChannel($existing['id'], $channel, $from, $messageData);
            
            return (int)$existing['id'];
        }

        // Create new lead
        $name = $this->extractName($text, $messageData);
        
        $leadId = $this->db->insert('leads', [
            
            'tenant_id' => TenantContext::getId(),
            'name' => $name,
            'phone' => $phone,
            'whatsapp_number' => ($channel === 'whatsapp') ? $phone : null,
            'telegram_id' => ($channel === 'telegram') ? ($messageData['chat_id'] ?? $from) : null,
            'source' => $channel . '_inbound',
            'status' => 'new',
            'budget_min' => 0,
            'budget_max' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
            'notes' => "Auto-created from $channel message: " . substr($text, 0, 200)
        ]);

        if ($leadId) {
            // Log lead creation activity
            $this->db->insert('lead_activities', [
                
                'tenant_id' => TenantContext::getId(),
                'lead_id' => $leadId,
                'type' => 'created',
                'description' => "Lead auto-created from $channel inbound message",
                'metadata' => json_encode(['channel' => $channel, 'message' => $text]),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $leadId ? (int)$leadId : null;
    }

    private function updateLeadChannel(int $leadId, string $channel, string $from, array $messageData): void
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $updates = ['last_activity_at' => date('Y-m-d H:i:s')];
        
        if ($channel === 'whatsapp' && empty($this->db->fetchOne("SELECT whatsapp_number FROM leads WHERE id = ?" . $tenantFilter, array_merge([$leadId], $tid > 1 ? [$tid] : []))['whatsapp_number'] ?? '')) {
            $updates['whatsapp_number'] = $this->normalizePhone($from);
        }
        
        if ($channel === 'telegram' && empty($this->db->fetchOne("SELECT telegram_id FROM leads WHERE id = ?" . $tenantFilter, array_merge([$leadId], $tid > 1 ? [$tid] : []))['telegram_id'] ?? '')) {
            $updates['telegram_id'] = $messageData['chat_id'] ?? $from;
        }

        if (!empty($updates)) {
            $this->db->update('leads', $updates, 'id = ?', [$leadId]);
        }
    }

    /**
     * Generate AI-powered response
     */
    private function generateAIResponse(string $channel, string $from, string $text, ?int $leadId): ?array
    {
        // First, try the specialized AIChatbotService for real estate queries
        $sessionId = $channel . '_' . $from;
        
        // Use AIChatbotService (rule-based + keyword detection)
        $botResponse = $this->aiChatbot->processMessage($sessionId, $text);
        
        if ($botResponse['success'] && !empty($botResponse['response'])) {
            // If confidence is high, use it
            if ($botResponse['confidence'] > 0.6) {
                return [
                    'response' => $botResponse['response'],
                    'intent' => $botResponse['intent'],
                    'confidence' => $botResponse['confidence'],
                    'source' => 'chatbot_service'
                ];
            }
        }

        // Fallback: Use AIGateway for more sophisticated responses
        try {
            $gatewayResponse = $this->aiGateway->process('chat', [
                'message' => $text,
                'channel' => $channel,
                'lead_id' => $leadId,
                'context' => 'real_estate_customer_support'
            ]);

            if (!empty($gatewayResponse['result'])) {
                return [
                    'response' => $gatewayResponse['result'],
                    'intent' => $gatewayResponse['intent'] ?? 'general',
                    'confidence' => $gatewayResponse['confidence'] ?? 0.7,
                    'source' => 'ai_gateway'
                ];
            }
        } catch (Exception $e) {
            error_log("AI Gateway error: " . $e->getMessage());
        }

        // Ultimate fallback
        return [
            'response' => $this->getDefaultResponse($channel),
            'intent' => 'fallback',
            'confidence' => 0.5,
            'source' => 'fallback'
        ];
    }

    /**
     * Send message via specified channel
     */
    public function sendMessage(string $channel, string $to, string $message): array
    {
        if (!($this->channels[$channel]['enabled'] ?? false)) {
            return ['success' => false, 'error' => "Channel $channel not enabled"];
        }

        try {
            switch ($channel) {
                case 'whatsapp':
                    return $this->sendWhatsApp($to, $message);
                case 'telegram':
                    return $this->sendTelegram($to, $message);
                case 'sms':
                    return $this->sendSMS($to, $message);
                case 'email':
                    return $this->sendEmail($to, $message);
                default:
                    return ['success' => false, 'error' => "Unknown channel: $channel"];
            }
        } catch (Exception $e) {
            error_log("Send $channel message failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendWhatsApp(string $to, string $message): array
    {
        if (!$this->whatsappService) {
            return ['success' => false, 'error' => 'WhatsApp service not available'];
        }
        
        // Use template for first message if needed, otherwise text
        return $this->whatsappService->sendTextMessage($to, $message);
    }

    private function sendTelegram(string $chatId, string $message): array
    {
        $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            return ['success' => false, 'error' => 'Telegram bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);
        
        if ($httpCode === 200 && ($result['ok'] ?? false)) {
            return ['success' => true, 'message_id' => $result['result']['message_id'] ?? null];
        }

        return ['success' => false, 'error' => $result['description'] ?? 'Telegram API error'];
    }

    private function sendSMS(string $to, string $message): array
    {
        if (!$this->smsService) {
            return ['success' => false, 'error' => 'SMS service not available'];
        }
        
        return $this->smsService->sendSMS($to, $message);
    }

    private function sendEmail(string $to, string $message): array
    {
        if (!$this->emailService) {
            return ['success' => false, 'error' => 'Email service not available'];
        }
        
        $subject = "Message from APS Dream Home";
        return ['success' => $this->emailService->send($to, $subject, $message)];
    }

    /**
     * Store message in database
     */
    private function storeMessage(string $channel, string $from, string $text, ?string $messageId, string $direction, int $timestamp): void
    {
        try {
            $this->db->insert('communication_logs', [
                
                'tenant_id' => TenantContext::getId(),
                'channel' => $channel,
                'contact_identifier' => $from,
                'message_text' => $text,
                'message_id' => $messageId,
                'direction' => $direction,
                'status' => $direction === 'outbound' ? 'sent' : 'received',
                'created_at' => date('Y-m-d H:i:s', $timestamp)
            ]);
        } catch (Exception $e) {
            error_log("Failed to store communication log: " . $e->getMessage());
        }
    }

    /**
     * Normalize phone number to E.164 format
     */
    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        
        if (strlen($phone) === 10) {
            return '+91' . $phone;
        }
        
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            return '+' . $phone;
        }
        
        return strlen($phone) >= 10 ? '+' . $phone : null;
    }

    /**
     * Extract name from message or metadata
     */
    private function extractName(string $text, array $messageData): string
    {
        // Check metadata first
        if (!empty($messageData['first_name'])) {
            return trim($messageData['first_name'] . ' ' . ($messageData['last_name'] ?? ''));
        }
        if (!empty($messageData['username'])) {
            return '@' . $messageData['username'];
        }

        // Try to extract name from message (simple heuristic)
        // "Hi, I'm John" or "My name is John"
        if (preg_match('/(?:i\'?m|my name is|this is)\s+([a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return 'Lead from ' . ucfirst($messageData['type'] ?? 'message');
    }

    private function getDefaultResponse(string $channel): string
    {
        $responses = [
            'whatsapp' => "Thank you for messaging APS Dream Home! 🏠 Our team will get back to you shortly. For immediate assistance, call us at +91-9876543210.",
            'telegram' => "Thank you for contacting APS Dream Home! 🏠 Our team will respond soon. You can also call us at +91-9876543210.",
            'sms' => "Thanks for contacting APS Dream Home. We'll call you back soon. Call: +91-9876543210",
            'email' => "Thank you for your inquiry. Our team will respond within 24 hours."
        ];

        return $responses[$channel] ?? $responses['whatsapp'];
    }

    // ============ AUTOMATED MESSAGING (Birthday, Anniversary, Festivals) ============

    /**
     * Send automated greetings for birthdays, anniversaries, festivals
     */
    public function sendAutomatedGreetings(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        
        try {
            $today = date('m-d');
            $currentDate = date('Y-m-d');
            
            // Get leads with birthdays today
            $birthdayLeads = $this->db->fetchAll(
                "SELECT id, name, phone, whatsapp_number, email, telegram_id 
                 FROM leads 
                 WHERE DATE_FORMAT(dob, '%m-%d') = ? 
                 AND (status != 'closed_lost' OR status IS NULL)
                 AND (phone IS NOT NULL OR whatsapp_number IS NOT NULL OR email IS NOT NULL)",
                [$today]
            );

            foreach ($birthdayLeads as $lead) {
                $message = $this->getBirthdayMessage($lead['name']);
                
                // Try WhatsApp first, then SMS, then Email
                $sent = false;
                
                if (!empty($lead['whatsapp_number']) && $this->channels['whatsapp']['enabled']) {
                    $result = $this->sendWhatsApp($lead['whatsapp_number'], $message);
                    $sent = $result['success'];
                }
                
                if (!$sent && !empty($lead['phone']) && $this->channels['sms']['enabled']) {
                    $result = $this->sendSMS($lead['phone'], $message);
                    $sent = $result['success'];
                }
                
                if (!$sent && !empty($lead['email']) && $this->channels['email']['enabled']) {
                    $result = $this->sendEmail($lead['email'], $message);
                    $sent = $result['success'];
                }

                if ($sent) {
                    $results['sent']++;
                    $this->logAutomatedMessage($lead['id'], 'birthday', $message, 'sent');
                } else {
                    $results['failed']++;
                    $this->logAutomatedMessage($lead['id'], 'birthday', $message, 'failed');
                }
            }

            // Check for festival greetings
            $festivalResults = $this->sendFestivalGreetings();
            $results['sent'] += $festivalResults['sent'];
            $results['failed'] += $festivalResults['failed'];

        } catch (Exception $e) {
            error_log("Automated greetings error: " . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    private function getBirthdayMessage(string $name): string
    {
        $messages = [
            "🎉 Happy Birthday {$name}! Wishing you a year filled with joy, success, and your dream home! 🏠✨ - Team APS Dream Home",
            "🎂 Happy Birthday {$name}! May this special day bring you closer to your dream property. Here's to new beginnings! 🏡 - APS Dream Home",
            "🎈 Many happy returns, {$name}! Celebrate today knowing your perfect home is waiting for you. 🏠💫 - APS Dream Home Family"
        ];
        
        return $messages[array_rand($messages)];
    }

    private function sendFestivalGreetings(): array
    {
        $results = ['sent' => 0, 'failed' => 0];
        
        $festivals = $this->getTodaysFestivals();
        if (empty($festivals)) return $results;

        $leads = $this->db->fetchAll(
            "SELECT id, name, phone, whatsapp_number, email 
             FROM leads 
             WHERE status != 'closed_lost' 
             AND (phone IS NOT NULL OR whatsapp_number IS NOT NULL OR email IS NOT NULL)
             LIMIT 5000"
        );

        foreach ($festivals as $festival) {
            $message = $this->getFestivalMessage($festival);
            
            foreach ($leads as $lead) {
                $sent = false;
                
                if (!empty($lead['whatsapp_number'])) {
                    $result = $this->sendWhatsApp($lead['whatsapp_number'], $message);
                    $sent = $result['success'];
                }
                
                if (!$sent && !empty($lead['phone'])) {
                    $result = $this->sendSMS($lead['phone'], $message);
                    $sent = $result['success'];
                }

                if ($sent) {
                    $results['sent']++;
                    $this->logAutomatedMessage($lead['id'], 'festival_' . $festival['key'], $message, 'sent');
                } else {
                    $results['failed']++;
                    $this->logAutomatedMessage($lead['id'], 'festival_' . $festival['key'], $message, 'failed');
                }
            }
        }

        return $results;
    }

    private function getTodaysFestivals(): array
    {
        $today = date('m-d');
        $year = date('Y');
        
        // Major Indian festivals (simplified - in production use a proper calendar)
        $festivalCalendar = [
            '01-01' => ['key' => 'new_year', 'name' => 'New Year'],
            '01-14' => ['key' => 'makar_sankranti', 'name' => 'Makar Sankranti'],
            '01-26' => ['key' => 'republic_day', 'name' => 'Republic Day'],
            '03-08' => ['key' => 'womens_day', 'name' => 'Women\'s Day'],
            '08-15' => ['key' => 'independence_day', 'name' => 'Independence Day'],
            '10-02' => ['key' => 'gandhi_jayanti', 'name' => 'Gandhi Jayanti'],
            '12-25' => ['key' => 'christmas', 'name' => 'Christmas'],
            // Diwali, Holi, Eid dates vary - would need lunar calendar
        ];

        return isset($festivalCalendar[$today]) ? [$festivalCalendar[$today]] : [];
    }

    private function getFestivalMessage(array $festival): string
    {
        $messages = [
            "🎊 Happy {$festival['name']}! Wishing you prosperity and happiness. May you find your dream home this festive season! 🏠✨ - APS Dream Home",
            "🪔 On this auspicious occasion of {$festival['name']}, we wish you joy, success, and the keys to your perfect home! 🏡 - Team APS Dream Home",
            "🌟 {$festival['name']} greetings! May this festival bring new beginnings. Let us help you find your dream property! 🏠 - APS Dream Home"
        ];

        return $messages[array_rand($messages)];
    }

    private function logAutomatedMessage(int $leadId, string $type, string $message, string $status): void
    {
        try {
            $this->db->insert('automated_messages_log', [
                
                'tenant_id' => TenantContext::getId(),
                'lead_id' => $leadId,
                'message_type' => $type,
                'message_content' => $message,
                'status' => $status,
                'sent_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log automated message: " . $e->getMessage());
        }
    }

    // ============ NEWSLETTER AUTOMATION ============

    /**
     * Process newsletter subscriptions and send automated newsletters
     */
    public function processNewsletterAutomation(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'subscribers' => 0];

        try {
            // Get active subscribers
            $subscribers = $this->db->fetchAll(
                "SELECT id, email, name, preferences, frequency 
                 FROM newsletter_subscribers 
                 WHERE status = 'active' 
                 AND (last_sent_at IS NULL OR last_sent_at < DATE_SUB(NOW(), INTERVAL frequency DAY))"
            );

            $results['subscribers'] = count($subscribers);

            if (empty($subscribers)) return $results;

            // Get latest content for newsletter
            $content = $this->getNewsletterContent();
            if (empty($content)) return $results;

            foreach ($subscribers as $subscriber) {
                $personalized = $this->personalizeNewsletter($content, $subscriber);
                
                $result = $this->sendEmail($subscriber['email'], $personalized);
                
                if ($result['success']) {
                    $results['sent']++;
                    $this->db->update('newsletter_subscribers', [
                        'last_sent_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$subscriber['id']]);
                } else {
                    $results['failed']++;
                }
            }

        } catch (Exception $e) {
            error_log("Newsletter automation error: " . $e->getMessage());
        }

        return $results;
    }

    private function getNewsletterContent(): array
    {
        // Get latest properties, blogs, offers
        $properties = $this->db->fetchAll(
            "SELECT id, title, location, price, image, slug 
             FROM properties 
             WHERE status = 'available' 
             ORDER BY created_at DESC LIMIT 5"
        );

        $blogs = $this->db->fetchAll(
            "SELECT id, title, excerpt, slug, featured_image 
             FROM blog_posts 
             WHERE status = 'published' 
             ORDER BY published_at DESC LIMIT 3"
        );

        $offers = $this->db->fetchAll(
            "SELECT title, description, discount_percentage, valid_until 
             FROM promotional_offers 
             WHERE status = 'active' AND valid_until >= CURDATE() 
             ORDER BY created_at DESC LIMIT 2"
        );

        return [
            'properties' => $properties,
            'blogs' => $blogs,
            'offers' => $offers,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function personalizeNewsletter(array $content, array $subscriber): string
    {
        $name = $subscriber['name'] ?? 'Valued Customer';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>APS Dream Home</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Your Weekly Property Digest</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;'>
                <p style='font-size: 16px;'>Hi <strong>{$name}</strong>,</p>
                
                <p>Here are this week's top picks handpicked for you:</p>
        ";

        // Properties
        if (!empty($content['properties'])) {
            $html .= "<h3 style='color: #0f172a; border-bottom: 2px solid #0d9488; padding-bottom: 10px;'>🏠 Featured Properties</h3>";
            foreach ($content['properties'] as $prop) {
                $html .= "
                <div style='border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 15px 0;'>
                    <h4 style='margin: 0 0 10px; color: #0f172a;'>{$prop['title']}</h4>
                    <p style='margin: 5px 0; color: #64748b;'>📍 {$prop['location']} | 💰 ₹" . number_format($prop['price']) . "</p>
                    <a href='" . BASE_URL . "/property/{$prop['slug']}' style='display: inline-block; background: #0d9488; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Details</a>
                </div>
                ";
            }
        }

        // Blogs
        if (!empty($content['blogs'])) {
            $html .= "<h3 style='color: #0f172a; border-bottom: 2px solid #0d9488; padding-bottom: 10px;'>📰 Latest Insights</h3>";
            foreach ($content['blogs'] as $blog) {
                $html .= "
                <div style='padding: 15px 0; border-bottom: 1px solid #e2e8f0;'>
                    <h4 style='margin: 0 0 5px;'><a href='" . BASE_URL . "/blog/{$blog['slug']}' style='color: #0d9488; text-decoration: none;'>{$blog['title']}</a></h4>
                    <p style='margin: 5px 0; color: #64748b; font-size: 14px;'>{$blog['excerpt']}</p>
                </div>
                ";
            }
        }

        // Offers
        if (!empty($content['offers'])) {
            $html .= "<h3 style='color: #0f172a; border-bottom: 2px solid #f59e0b; padding-bottom: 10px;'>🎁 Special Offers</h3>";
            foreach ($content['offers'] as $offer) {
                $html .= "
                <div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin: 15px 0;'>
                    <h4 style='margin: 0 0 5px; color: #92400e;'>{$offer['title']}</h4>
                    <p style='margin: 5px 0; color: #92400e;'>{$offer['description']}</p>
                    <p style='margin: 5px 0;'><strong>{$offer['discount_percentage']}% OFF</strong> - Valid until {$offer['valid_until']}</p>
                </div>
                ";
            }
        }

        $html .= "
                <hr style='margin: 30px 0; border-color: #e2e8f0;'>
                <p style='text-align: center; color: #64748b; font-size: 14px;'>
                    <a href='" . BASE_URL . "/properties' style='color: #0d9488;'>Browse All Properties</a> | 
                    <a href='" . BASE_URL . "/contact' style='color: #0d9488;'>Contact Us</a> | 
                    <a href='" . BASE_URL . "/unsubscribe?email=" . urlencode($subscriber['email']) . "' style='color: #94a3b8;'>Unsubscribe</a>
                </p>
                <p style='text-align: center; color: #94a3b8; font-size: 12px;'>
                    APS Dream Home | Gorakhpur, Lucknow, UP | +91-9876543210
                </p>
            </div>
        </body>
        </html>
        ";

        return $html;
    }

    // ============ BULK CAMPAIGN SENDING ============

    /**
     * Send bulk campaign to leads
     */
    public function sendBulkCampaign(array $campaign): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        $leads = $this->db->fetchAll(
            "SELECT id, name, phone, whatsapp_number, email, telegram_id 
             FROM leads 
             WHERE status IN ('new', 'contacted', 'qualified') 
             AND ({$campaign['channels']})
             LIMIT {$campaign['limit']}"
        );

        foreach ($leads as $lead) {
            $personalized = $this->personalizeMessage($campaign['message'], $lead);

            foreach ($campaign['channels'] as $channel) {
                if ($channel === 'whatsapp' && !empty($lead['whatsapp_number'])) {
                    $result = $this->sendWhatsApp($lead['whatsapp_number'], $personalized);
                    if ($result['success']) $results['sent']++;
                    else $results['failed']++;
                }
                elseif ($channel === 'sms' && !empty($lead['phone'])) {
                    $result = $this->sendSMS($lead['phone'], $personalized);
                    if ($result['success']) $results['sent']++;
                    else $results['failed']++;
                }
                elseif ($channel === 'email' && !empty($lead['email'])) {
                    $result = $this->sendEmail($lead['email'], $personalized);
                    if ($result['success']) $results['sent']++;
                    else $results['failed']++;
                }
                elseif ($channel === 'telegram' && !empty($lead['telegram_id'])) {
                    $result = $this->sendTelegram($lead['telegram_id'], $personalized);
                    if ($result['success']) $results['sent']++;
                    else $results['failed']++;
                }
            }
        }

        return $results;
    }

    private function personalizeMessage(string $template, array $lead): string
    {
        return str_replace(
            ['{{name}}', '{{phone}}', '{{lead_id}}'],
            [$lead['name'] ?? 'Customer', $lead['phone'] ?? '', $lead['id'] ?? ''],
            $template
        );
    }

    // ============ WEBHOOK ENDPOINTS ============

    /**
     * WhatsApp Webhook endpoint
     * URL: /api/communication/whatsapp-webhook
     */
    public function handleWhatsAppWebhook(array $payload): array
    {
        return $this->processIncomingMessage('whatsapp', $payload);
    }

    /**
     * Telegram Webhook endpoint
     * URL: /api/communication/telegram-webhook
     */
    public function handleTelegramWebhook(array $payload): array
    {
        return $this->processIncomingMessage('telegram', $payload);
    }

    /**
     * SMS Webhook endpoint
     * URL: /api/communication/sms-webhook
     */
    public function handleSMSWebhook(array $payload): array
    {
        return $this->processIncomingMessage('sms', $payload);
    }

    // ============ UTILITY METHODS ============

    public function enableChannel(string $channel): bool
    {
        if (isset($this->channels[$channel])) {
            $this->channels[$channel]['enabled'] = true;
            return true;
        }
        return false;
    }

    public function disableChannel(string $channel): bool
    {
        if (isset($this->channels[$channel])) {
            $this->channels[$channel]['enabled'] = false;
            return true;
        }
        return false;
    }

    public function isChannelEnabled(string $channel): bool
    {
        return $this->channels[$channel]['enabled'] ?? false;
    }

    public function getChannelStatus(): array
    {
        return $this->channels;
    }
}