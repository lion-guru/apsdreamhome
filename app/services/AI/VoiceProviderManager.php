<?php
/**
 * VoiceProviderManager - Unified interface for multiple voice providers
 * Supports: Exotel, Twilio, Plivo, Knowlarity
 * All configuration via ai_settings table
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class VoiceProviderManager
{
    private static $instance = null;
    private $db;
    private $config;
    private $provider;
    private $client;

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        try {
            $tid = TenantContext::getId();
            $stmt = $this->db->prepare("SELECT * FROM ai_settings WHERE id = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $stmt->execute($tid > 1 ? [$tid] : []);
            $this->config = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            
            $settings = json_decode($this->config['settings'] ?? '{}', true) ?: [];
            $this->provider = $this->config['voice_provider'] ?? $settings['voice_provider'] ?? 'exotel';
        } catch (\Throwable $e) {
            $this->provider = 'exotel';
            $this->config = [];
        }
    }

    /**
     * Make an outbound call
     * @param array $params ['to', 'from', 'script_id', 'lead_id', 'campaign_id']
     * @return array ['success' => bool, 'call_sid' => string, 'error' => string]
     */
    public function makeCall(array $params): array
    {
        $method = 'call' . ucfirst(strtolower($this->provider));
        if (!method_exists($this, $method)) {
            return ['success' => false, 'error' => "Provider {$this->provider} not supported"];
        }
        return $this->$method($params);
    }

    /**
     * Exotel call
     */
    private function callExotel(array $params): array
    {
        $sid = $this->config['voice_sid'] ?? getenv('EXOTEL_SID') ?? '';
        $token = $this->config['voice_token'] ?? getenv('EXOTEL_TOKEN') ?? '';
        $from = $params['from'] ?? $this->config['voice_from_number'] ?? getenv('EXOTEL_FROM') ?? '';

        if (empty($sid) || empty($token) || empty($from)) {
            return ['success' => false, 'error' => 'Exotel credentials not configured'];
        }

        $url = "https://{$sid}:{$token}@api.exotel.com/v1/Accounts/{$sid}/Calls/connect.json";
        $postData = [
            'From' => $from,
            'To' => $params['to'],
            'CallerId' => $from,
            'Url' => BASE_URL . '/api/voice/exotel/xml?script_id=' . ($params['script_id'] ?? 1) . '&lead_id=' . ($params['lead_id'] ?? 0),
            'StatusCallback' => BASE_URL . '/api/voice/exotel/status',
            'StatusCallbackEvents' => 'initiated,ringing,answered,completed,failed,busy,no-answer',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $xml = simplexml_load_string($response);
            $callSid = (string)($xml->Call->Sid ?? '');
            return ['success' => true, 'call_sid' => $callSid, 'provider' => 'exotel'];
        }

        return ['success' => false, 'error' => 'Exotel API error: ' . $response, 'http_code' => $httpCode];
    }

    /**
     * Twilio call
     */
    private function callTwilio(array $params): array
    {
        $sid = $this->config['twilio_sid'] ?? getenv('TWILIO_SID') ?? '';
        $token = $this->config['twilio_token'] ?? getenv('TWILIO_TOKEN') ?? '';
        $from = $params['from'] ?? $this->config['twilio_from'] ?? getenv('TWILIO_FROM') ?? '';

        if (empty($sid) || empty($token) || empty($from)) {
            return ['success' => false, 'error' => 'Twilio credentials not configured'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Calls.json";
        $postData = [
            'From' => $from,
            'To' => $params['to'],
            'Url' => BASE_URL . '/api/voice/twilio/xml?script_id=' . ($params['script_id'] ?? 1) . '&lead_id=' . ($params['lead_id'] ?? 0),
            'StatusCallback' => BASE_URL . '/api/voice/twilio/status',
            'StatusCallbackEvent' => 'initiated,ringing,answered,completed,failed,busy,no-answer',
            'StatusCallbackMethod' => 'POST',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_USERPWD => "$sid:$token",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $json = json_decode($response, true);
            return ['success' => true, 'call_sid' => $json['sid'] ?? '', 'provider' => 'twilio'];
        }

        return ['success' => false, 'error' => 'Twilio API error: ' . $response, 'http_code' => $httpCode];
    }

    /**
     * Plivo call
     */
    private function callPlivo(array $params): array
    {
        $authId = $this->config['plivo_auth_id'] ?? getenv('PLIVO_AUTH_ID') ?? '';
        $authToken = $this->config['plivo_auth_token'] ?? getenv('PLIVO_AUTH_TOKEN') ?? '';
        $from = $params['from'] ?? $this->config['plivo_from'] ?? getenv('PLIVO_FROM') ?? '';

        if (empty($authId) || empty($authToken) || empty($from)) {
            return ['success' => false, 'error' => 'Plivo credentials not configured'];
        }

        $url = "https://api.plivo.com/v1/Account/{$authId}/Call/";
        $postData = [
            'from' => $from,
            'to' => $params['to'],
            'answer_url' => BASE_URL . '/api/voice/plivo/xml?script_id=' . ($params['script_id'] ?? 1) . '&lead_id=' . ($params['lead_id'] ?? 0),
            'answer_method' => 'GET',
            'hangup_url' => BASE_URL . '/api/voice/plivo/hangup',
            'hangup_method' => 'POST',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_USERPWD => "$authId:$authToken",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $json = json_decode($response, true);
            return ['success' => true, 'call_sid' => $json['request_uuid'] ?? '', 'provider' => 'plivo'];
        }

        return ['success' => false, 'error' => 'Plivo API error: ' . $response, 'http_code' => $httpCode];
    }

    /**
     * Knowlarity call
     */
    private function callKnowlarity(array $params): array
    {
        $apiKey = $this->config['knowlarity_api_key'] ?? getenv('KNOWLARITY_API_KEY') ?? '';
        $from = $params['from'] ?? $this->config['knowlarity_from'] ?? getenv('KNOWLARITY_FROM') ?? '';

        if (empty($apiKey) || empty($from)) {
            return ['success' => false, 'error' => 'Knowlarity credentials not configured'];
        }

        $url = "https://api.knowlarity.com/v1/calls";
        $postData = [
            'from' => $from,
            'to' => $params['to'],
            'call_type' => 'outbound',
            'script_id' => $params['script_id'] ?? 1,
            'lead_id' => $params['lead_id'] ?? 0,
            'webhook_url' => BASE_URL . '/api/voice/knowlarity/webhook',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $json = json_decode($response, true);
            return ['success' => true, 'call_sid' => $json['call_id'] ?? '', 'provider' => 'knowlarity'];
        }

        return ['success' => false, 'error' => 'Knowlarity API error: ' . $response, 'http_code' => $httpCode];
    }

    /**
     * Generate XML for call flow (TwiML/ExoML/Plivo XML)
     */
    public function generateCallXml(array $params): string
    {
        $script = $params['script'] ?? [];
        $lead = $params['lead'] ?? [];
        
        $greeting = $script['greeting'] ?? "Namaste, main APS Dream Home se bol raha hoon.";
        $questions = $script['questions'] ?? [
            'interest' => 'Kya aap property khareedna chahte hain?',
            'budget' => 'Aapka budget kya hai?',
            'location' => 'Kahan property chahiye?',
        ];
        
        // Generate provider-specific XML
        return match ($this->provider) {
            'twilio' => $this->generateTwiml($greeting, $questions),
            'plivo' => $this->generatePlivoXml($greeting, $questions),
            'knowlarity' => $this->generateKnowlarityXml($greeting, $questions),
            default => $this->generateExoml($greeting, $questions),
        };
    }

    private function generateExoml(string $greeting, array $questions): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response>';
        $xml .= '<Speak>' . htmlspecialchars($greeting) . '</Speak>';
        
        foreach ($questions as $key => $question) {
            $xml .= '<GetDigits finishOnKey="#" maxDigits="1" timeout="10" playBeep="true" callbackUrl="' . BASE_URL . "/api/voice/exotel/dtmf?key=$key\">";
            $xml .= '<Speak>' . htmlspecialchars($question) . '</Speak>';
            $xml .= '</GetDigits>';
        }
        
        $xml .= '<Hangup/></Response>';
        return $xml;
    }

    private function generateTwiml(string $greeting, array $questions): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response>';
        $xml .= '<Say voice="Polly.Aditi" language="hi-IN">' . htmlspecialchars($greeting) . '</Say>';
        
        foreach ($questions as $key => $question) {
            $xml .= '<Gather input="dtmf speech" action="' . BASE_URL . "/api/voice/twilio/gather?key=$key\" method=\"POST\" timeout=\"10\" speechTimeout=\"auto\">";
            $xml .= '<Say voice="Polly.Aditi" language="hi-IN">' . htmlspecialchars($question) . '</Say>';
            $xml .= '</Gather>';
        }
        
        $xml .= '<Hangup/></Response>';
        return $xml;
    }

    private function generatePlivoXml(string $greeting, array $questions): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response>';
        $xml .= '<Speak voice="Polly.Aditi" language="hi-IN">' . htmlspecialchars($greeting) . '</Speak>';
        
        foreach ($questions as $key => $question) {
            $xml .= '<GetInput inputType="dtmf speech" action="' . BASE_URL . "/api/voice/plivo/gather?key=$key\" method=\"POST\" timeout=\"10\" speechModel=\"default\" language=\"hi-IN\">";
            $xml .= '<Speak voice="Polly.Aditi" language="hi-IN">' . htmlspecialchars($question) . '</Speak>';
            $xml .= '</GetInput>';
        }
        
        $xml .= '<Hangup/></Response>';
        return $xml;
    }

    private function generateKnowlarityXml(string $greeting, array $questions): string
    {
        // Knowlarity uses JSON format
        $response = [
            'actions' => [
                ['type' => 'speak', 'text' => $greeting, 'voice' => 'hi-IN', 'language' => 'hi'],
            ]
        ];
        
        foreach ($questions as $key => $question) {
            $response['actions'][] = [
                'type' => 'collect_input',
                'input_type' => 'dtmf',
                'prompt' => $question,
                'max_digits' => 1,
                'timeout' => 10,
                'action_url' => BASE_URL . "/api/voice/knowlarity/collect?key=$key",
            ];
        }
        
        $response['actions'][] = ['type' => 'hangup'];
        
        return json_encode($response);
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['voice_sid']) || !empty($this->config['twilio_sid']) || 
               !empty($this->config['plivo_auth_id']) || !empty($this->config['knowlarity_api_key']);
    }
}