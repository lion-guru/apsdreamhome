<?php

namespace App\Services\Communication;

use App\Core\Database;
use App\Core\Security;
use App\Traits\ServiceTenantTrait;

/**
 * WhatsApp Business API Integration
 * Official WhatsApp Business Platform
 */
class WhatsAppService
{
    use ServiceTenantTrait;
    private $accessToken;
    private $phoneNumberId;
    private $webhookVerifyToken;
    private $apiVersion = 'v18.0';
    private $baseUrl = 'https://graph.facebook.com';

    public function __construct(array $config = [])
    {
        $this->accessToken = $config['access_token'] ?? $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '';
        $this->webhookVerifyToken = $config['verify_token'] ?? $_ENV['WHATSAPP_VERIFY_TOKEN'] ?? '';
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->phoneNumberId);
    }

    /**
     * Send text message
     */
    public function sendTextMessage(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message
            ]
        ];

        return $this->sendRequest($data);
    }

    /**
     * Send template message
     */
    public function sendTemplateMessage(string $to, string $templateName, array $components = [], string $language = 'en'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ],
                'components' => $components
            ]
        ];

        return $this->sendRequest($data);
    }

    /**
     * Send interactive message with buttons
     */
    public function sendInteractiveMessage(string $to, string $bodyText, array $buttons): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'buttons' => array_map(function ($btn, $index) {
                        return [
                            'type' => 'reply',
                            'reply' => [
                                'id' => $btn['id'] ?? 'btn_' . $index,
                                'title' => $btn['title']
                            ]
                        ];
                    }, $buttons, array_keys($buttons))
                ]
            ]
        ];

        return $this->sendRequest($data);
    }

    /**
     * Send list message
     */
    public function sendListMessage(string $to, string $bodyText, string $buttonText, array $sections): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections
                ]
            ]
        ];

        return $this->sendRequest($data);
    }

    /**
     * Send image message
     */
    public function sendImageMessage(string $to, string $imageUrl, string $caption = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl
            ]
        ];

        if ($caption) {
            $data['image']['caption'] = $caption;
        }

        return $this->sendRequest($data);
    }

    /**
     * Send document message
     */
    public function sendDocumentMessage(string $to, string $documentUrl, string $filename, string $caption = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
                'filename' => $filename
            ]
        ];

        if ($caption) {
            $data['document']['caption'] = $caption;
        }

        return $this->sendRequest($data);
    }

    /**
     * Send property listing message
     */
    public function sendPropertyListing(string $to, array $property): array
    {
        $message = "🏠 *{$property['title']}*\n\n";
        $message .= "📍 Location: {$property['location']}\n";
        $message .= "💰 Price: ₹" . number_format($property['price']) . "\n";
        $message .= "📐 Area: {$property['area']} sq.ft\n";
        $message .= "🛏️ Bedrooms: {$property['bedrooms']}\n";
        $message .= "🚿 Bathrooms: {$property['bathrooms']}\n\n";
        $message .= "📝 {$property['description']}\n\n";
        $message .= "📞 Contact us for more details!";

        if (!empty($property['images'][0])) {
            return $this->sendImageMessage($to, $property['images'][0], $message);
        }

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Send booking confirmation
     */
    public function sendBookingConfirmation(string $to, array $booking): array
    {
        return $this->sendTemplateMessage($to, 'booking_confirmation', [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $booking['customer_name']],
                    ['type' => 'text', 'text' => $booking['property_title']],
                    ['type' => 'text', 'text' => $booking['booking_date']],
                    ['type' => 'text', 'text' => $booking['booking_time']]
                ]
            ]
        ]);
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder(string $to, array $payment): array
    {
        return $this->sendTemplateMessage($to, 'payment_reminder', [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $payment['customer_name']],
                    ['type' => 'text', 'text' => '₹' . number_format($payment['amount'])],
                    ['type' => 'text', 'text' => $payment['due_date']]
                ]
            ]
        ]);
    }

    /**
     * Send lead follow-up
     */
    public function sendLeadFollowUp(string $to, string $customerName, string $agentName): array
    {
        return $this->sendTemplateMessage($to, 'lead_followup', [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customerName],
                    ['type' => 'text', 'text' => $agentName]
                ]
            ]
        ]);
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook(array $payload): array
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return ['success' => false, 'error' => 'Invalid payload'];
        }

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) {
            return ['success' => false, 'error' => 'No changes'];
        }

        $value = $changes['value'] ?? [];
        $messages = $value['messages'] ?? [];

        $processed = [];

        foreach ($messages as $message) {
            $processed[] = $this->processIncomingMessage($message);
        }

        return ['success' => true, 'processed' => count($processed), 'messages' => $processed];
    }

    /**
     * Process incoming message
     */
    private function processIncomingMessage(array $message): array
    {
        $from = $message['from'] ?? '';
        $type = $message['type'] ?? '';
        $timestamp = $message['timestamp'] ?? time();

        $processed = [
            'from' => $from,
            'type' => $type,
            'timestamp' => date('Y-m-d H:i:s', $timestamp)
        ];

        switch ($type) {
            case 'text':
                $processed['text'] = $message['text']['body'] ?? '';
                break;
            case 'image':
                $processed['image_id'] = $message['image']['id'] ?? '';
                $processed['caption'] = $message['image']['caption'] ?? '';
                break;
            case 'document':
                $processed['document_id'] = $message['document']['id'] ?? '';
                $processed['filename'] = $message['document']['filename'] ?? '';
                break;
            case 'interactive':
                $processed['interactive_type'] = $message['interactive']['type'] ?? '';
                $processed['button_id'] = $message['interactive']['button_reply']['id'] ?? '';
                break;
        }

        // Store message in database
        $this->storeMessage($processed);

        // 2-way smart menu bot (best-effort; never breaks inbound logging)
        try {
            if (in_array($type, ['text', 'interactive'], true)) {
                $inbound = $type === 'interactive'
                    ? ($message['interactive']['button_reply']['id'] ?? $message['interactive']['button_reply']['title'] ?? '')
                    : ($message['text']['body'] ?? '');
                if (trim((string)$inbound) !== '') {
                    $reply = $this->autoReply($from, (string)$inbound);
                    $processed['auto_reply'] = $reply;
                }
            }
        } catch (\Throwable $e) {
            error_log('[WhatsAppService] autoReply failed: ' . $e->getMessage());
        }

        return $processed;
    }

    /**
     * 2-way inbound smart menu bot.
     *
     * Stateless keyword menu (no session state to corrupt):
     *  1/plot  → townships + rates + live map link
     *  2/visit → Sunday cab visit info + creates a CRM lead
     *  3/emi   → passbook/booking self-service link
     *  4/join  → associate onboarding link + earning pitch
     *  hi/menu → main menu. Anything else → menu + hint.
     *
     * Returns ['replied'=>bool, 'intent'=>string]. Sends nothing when the
     * provider is unconfigured (dev/staging safe).
     */
    public function autoReply(string $from, string $text): array
    {
        $digits = preg_replace('/[^0-9]/', '', $from);
        $t = mb_strtolower(trim($text));
        $intent = 'menu';
        $reply = '';

        $flowService = new \App\Services\Communication\WhatsAppFlowService();
        $flowService->logMessage($from, 'inbound', $text, null, 'received');

        $matchedFlow = $flowService->matchFlow($text);
        if ($matchedFlow) {
            $intent = $matchedFlow['title'];
            $reply = $flowService->formatReply($matchedFlow, $from, $text);
            if (($matchedFlow['action_hook'] ?? '') === 'book_visit') {
                $this->createInboundLead($digits, $t, 'site_visit');
            } elseif (($matchedFlow['action_hook'] ?? '') === 'create_lead') {
                $this->createInboundLead($digits, $t, 'associate_join');
            }
        } else {
            if (preg_match('/\b(2|visit|cab|site|sunday|tour|dekhna|pickup)\b/u', $t)) {
                $intent = 'site_visit';
            } elseif (preg_match('/\b(3|emi|book|payment|passbook|receipt|due|kist)\b/u', $t)) {
                $intent = 'passbook';
            } elseif (preg_match('/\b(4|join|associate|agent|earn|job|kamana|commission)\b/u', $t)) {
                $intent = 'join';
            } elseif (preg_match('/\b(1|plot|township|colony|rate|price|flat|villa|land|jameen)\b/u', $t)) {
                $intent = 'plots';
            } elseif (preg_match('/\b(hi|hii+|hello|hey|namaste|namaskar|ram ram|start|menu|help)\b/u', $t)) {
                $intent = 'menu';
            }

            $base = defined('BASE_URL') ? BASE_URL : '';
            switch ($intent) {
                case 'plots':
                    $reply = "*APS Dream Home — New Townships*\n" . $this->townshipLines()
                        . "\nTap a live layout to see vacant plots in green:\n{$base}/properties\n"
                        . "Reply 2 for a FREE Sunday cab site visit.";
                    break;
                case 'site_visit':
                    $this->createInboundLead($digits, $t, 'site_visit');
                    $reply = "*FREE Sunday Site Visit booked (request noted)!*\n"
                        . "Pickup points: Railway Station, Golghar, Medical Road.\n"
                        . "Our team will call you to confirm cab + time.\n"
                        . "Meanwhile see live layouts: {$base}/properties";
                    break;
                case 'passbook':
                    $reply = "*Booking & EMI Passbook*\nCheck dues, receipts & pay online here:\n"
                        . "{$base}/login\n"
                        . "For help call our support. Reply 1 for new townships.";
                    break;
                case 'join':
                    $this->createInboundLead($digits, $t, 'associate_join');
                    $reply = "*Become an Associate & earn up to Rs.50,000/month!*\n"
                        . "Zero joining fee. Free training + Sunday site-visit leads.\n"
                        . "Register free: {$base}/register\n"
                        . "Our team will call you with details.";
                    break;
                case 'menu':
                default:
                    $reply = "*Namaste! APS Dream Home* at your service. Reply a number:\n"
                        . "1 - New Townships & Rates\n"
                        . "2 - FREE Sunday Cab Site Visit\n"
                        . "3 - My Booking & EMI Passbook\n"
                        . "4 - Become Associate (earn Rs.50,000/mo)";
                    break;
            }
        }

        $sent = false;
        try {
            $res = $this->sendTextMessage($from, $reply);
            $sent = (bool)($res['success'] ?? false);
        } catch (\Throwable $e) {
            error_log('[WhatsAppService] autoReply send failed: ' . $e->getMessage());
        }

        $flowService->logMessage($from, 'outbound', $reply, $intent, $sent ? 'delivered' : 'pending');

        return ['replied' => $sent, 'intent' => $intent];
    }

    /**
     * Top 3 active colonies as menu lines (name + rate + live map link).
     */
    private function townshipLines(): string
    {
        try {
            $db = Database::getInstance()->getConnection();
            $rows = $db->query(
                "SELECT name, min_price_per_sqft, starting_price, available_plots
                 FROM colonies WHERE is_active = 1 ORDER BY available_plots DESC LIMIT 3"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            if (!$rows) return "Premium townships in Gorakhpur — ask us for today's rates.\n";
            $out = '';
            $n = 0;
            foreach ($rows as $r) {
                $n++;
                $rate = 0.0;
                foreach ([(float)($r['min_price_per_sqft'] ?? 0), (float)($r['starting_price'] ?? 0)] as $cand) {
                    if ($cand >= 100 && $cand < 100000) { $rate = $cand; break; }
                }
                $out .= "{$n}. " . ($r['name'] ?? 'Township')
                    . ($rate > 0 ? ' — Rs.' . number_format($rate) . '/sqft' : '')
                    . ' (' . (int)($r['available_plots'] ?? 0) . " plots)\n";
            }
            return $out;
        } catch (\Throwable $e) {
            return "Premium townships in Gorakhpur — ask us for today's rates.\n";
        }
    }

    /**
     * CRM lead for a new inbound sender (idempotent 30d per number).
     */
    private function createInboundLead(string $digits, string $text, string $interest): void
    {
        try {
            if (strlen($digits) < 10) return;
            $db = Database::getInstance()->getConnection();
            $chk = $db->prepare(
                "SELECT id FROM leads WHERE (phone LIKE ? OR whatsapp_number LIKE ?) AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 1"
            );
            $like = '%' . substr($digits, -10);
            $chk->execute([$like, $like]);
            if ($chk->fetchColumn()) return;
            $ins = $db->prepare(
                "INSERT INTO leads (name, phone, whatsapp_number, source, property_interest, status, notes, created_at)
                 VALUES ('WhatsApp User', ?, ?, 'whatsapp', ?, 'new', ?, NOW())"
            );
            $ins->execute([$digits, $digits, $interest, mb_substr($text, 0, 500)]);
        } catch (\Throwable $e) {
            error_log('[WhatsAppService] inbound lead failed: ' . $e->getMessage());
        }
    }

    /**
     * Store message in database
     */
    private function storeMessage(array $message): void
    {
        $db = Database::getInstance();
        $tid = $this->tenantId();

        $columns = "phone_number, message_type, message_data, direction, created_at";
        $values = "?, ?, ?, 'inbound', NOW()";
        $params = [
            $message['from'],
            $message['type'],
            json_encode($message)
        ];
        if ($tid > 1) {
            $columns .= ", tenant_id";
            $values .= ", ?";
            $params[] = $tid;
        }

        $db->query(
            "INSERT INTO whatsapp_messages ($columns) VALUES ($values)",
            $params
        );
    }

    /**
     * Verify webhook token
     */
    public function verifyWebhook(string $mode, string $token): ?string
    {
        if ($mode === 'subscribe' && $token === $this->webhookVerifyToken) {
            return Security::sanitize($_GET['challenge']) ?? 'verified';
        }
        return null;
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $messageId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->sendRequest($data);
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add India country code if not present
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    /**
     * Send API request
     */
    private function sendRequest(array $data): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'API request failed',
                'details' => $result
            ];
        }

        return [
            'success' => true,
            'message_id' => $result['messages'][0]['id'] ?? null,
            'details' => $result
        ];
    }
}
