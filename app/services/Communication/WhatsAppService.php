<?php

namespace App\Services\Communication;

use App\Core\Database;

/**
 * WhatsApp Business API Integration
 * Official WhatsApp Business Platform
 */
class WhatsAppService
{
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

        return $processed;
    }

    /**
     * Store message in database
     */
    private function storeMessage(array $message): void
    {
        $db = Database::getInstance();
        
        $db->query(
            "INSERT INTO whatsapp_messages (phone_number, message_type, message_data, direction, created_at) 
             VALUES (?, ?, ?, 'inbound', NOW())",
            [
                $message['from'],
                $message['type'],
                json_encode($message)
            ]
        );
    }

    /**
     * Verify webhook token
     */
    public function verifyWebhook(string $mode, string $token): ?string
    {
        if ($mode === 'subscribe' && $token === $this->webhookVerifyToken) {
            return $_GET['challenge'] ?? 'verified';
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
