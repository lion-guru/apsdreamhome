<?php
namespace App\Services\Auc;

/**
 * WhatsApp Business API Integration
 * Handles incoming/outgoing WhatsApp messages via APS Dream Homes number
 */
class WhatsAppService
{
    private $db;
    private $phoneNumberId;
    private $accessToken;
    private $verifyToken;
    private $apiUrl = 'https://graph.facebook.com/v21.0/';

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        $this->phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '';
        $this->accessToken = getenv('WHATSAPP_ACCESS_TOKEN') ?: '';
        $this->verifyToken = getenv('WHATSAPP_VERIFY_TOKEN') ?: 'aps_dream_homes_verify';
    }

    /**
     * Handle incoming WhatsApp webhook (GET — verification)
     */
    public function verifyWebhook(array $query): string
    {
        $mode = $query['hub.mode'] ?? '';
        $token = $query['hub.verify_token'] ?? '';
        $challenge = $query['hub.challenge'] ?? '';

        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return $challenge;
        }
        http_response_code(403);
        return 'Verification failed';
    }

    /**
     * Handle incoming WhatsApp message (POST — from Meta)
     */
    public function handleIncomingMessage(array $payload): void
    {
        $entries = $payload['entry'] ?? [];
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                if (($change['field'] ?? '') !== 'messages') continue;
                $value = $change['value'] ?? [];

                $messages = $value['messages'] ?? [];
                foreach ($messages as $msg) {
                    $from = $msg['from'] ?? '';
                    $type = $msg['type'] ?? 'text';
                    $text = '';

                    if ($type === 'text') {
                        $text = $msg['text']['body'] ?? '';
                    } elseif ($type === 'interactive') {
                        $text = $msg['interactive']['button_reply']['title'] ?? $msg['interactive']['list_reply']['title'] ?? '';
                    }

                    if ($text && $from) {
                        $this->processUserMessage($from, $text, $msg['id'] ?? '');
                    }
                }
            }
        }
    }

    /**
     * Process a user's WhatsApp message through the AI brain
     */
    private function processUserMessage(string $phone, string $message, string $waMessageId): void
    {
        $brain = new AucBrainService();

        $context = [
            'channel' => 'whatsapp',
            'language' => $this->detectLanguage($message),
            'phone' => $phone,
        ];

        $result = $brain->processMessage($message, 'whatsapp', $context);

        $this->sendTextMessage($phone, $result['text']);

        $this->logMessage($phone, $message, $result['text'], 'incoming', $result['intent'], $waMessageId);
    }

    /**
     * Send a text message via WhatsApp Business API
     */
    public function sendTextMessage(string $to, string $text): bool
    {
        if (!$this->phoneNumberId || !$this->accessToken) {
            error_log("WhatsApp: Missing credentials, logging message for: $to");
            $this->logMessage($to, '', $text, 'outgoing_queued', 'system');
            return false;
        }

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("WhatsApp send failed ($httpCode): $response");
            return false;
        }
        return true;
    }

    /**
     * Send a property listing with buttons
     */
    public function sendPropertyButtons(string $to, array $properties): bool
    {
        $buttons = [];
        foreach (array_slice($properties, 0, 3) as $i => $p) {
            $buttons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => 'prop_' . $p['id'],
                    'title' => substr($p['title'], 0, 20),
                ],
            ];
        }

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => "🏠 APS Dream Homes — Select a property:"],
                'action' => ['buttons' => $buttons],
            ],
        ]);

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return true;
    }

    /**
     * Send location message
     */
    public function sendLocation(string $to, float $lat, float $lng, string $name, string $address): bool
    {
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'location',
            'location' => [
                'latitude' => $lat,
                'longitude' => $lng,
                'name' => $name,
                'address' => $address,
            ],
        ]);

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    /**
     * Send template message (for outbound marketing, follow-ups)
     */
    public function sendTemplate(string $to, string $templateName, array $params = []): bool
    {
        $components = [];
        if (!empty($params)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params),
            ];
        }

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'hi'],
                'components' => $components,
            ],
        ]);

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    /**
     * Simple Hindi/English detection
     */
    private function detectLanguage(string $text): string
    {
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) return 'hi';
        return 'en';
    }

    private function logMessage(string $phone, string $inbound, string $outbound, string $direction, string $intent, string $waId = ''): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO auc_conversations (session_id, channel, user_message, bot_response, intent, phone, direction, wa_message_id, created_at)
                VALUES (?, 'whatsapp', ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$waId ?: uniqid('wa_'), $inbound, $outbound, $intent, $phone, $direction, $waId]);
        } catch (\Exception $e) {
            error_log("WA log error: " . $e->getMessage());
        }
    }
}
