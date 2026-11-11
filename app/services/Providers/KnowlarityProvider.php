<?php
/**
 * KnowlarityProvider — Knowlarity voice provider implementation
 * Knowlarity is an Indian cloud telephony platform
 */

namespace App\Services\Providers;

class KnowlarityProvider extends AbstractVoiceProvider
{
    protected $baseUrl = 'https://api.knowlarity.com/v1';
    protected $apiKey;
    protected $secretKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        
        $this->apiKey = $this->getConfig('api_key');
        $this->secretKey = $this->getConfig('secret_key');
    }

    /**
     * Make an outbound call
     */
    public function makeCall(string $to, string $from = '', array $options = []): array
    {
        if (!$this->apiKey || !$this->secretKey) {
            return ['success' => false, 'error' => 'Knowlarity credentials not configured'];
        }

        $from = $from ?: $this->getConfig('caller_id');
        
        $data = [
            'to' => $to,
            'from' => $from,
            'agent_number' => $from,
            'caller_id' => $from,
            'call_type' => 'outbound',
        ];

        if (!empty($options['webhook_url'])) {
            $data['callback_url'] = $options['webhook_url'];
        }
        
        if (!empty($options['recording'])) {
            $data['record'] = 'true';
        }
        
        if (!empty($options['timeout'])) {
            $data['time_out'] = (int)$options['timeout'];
        }

        $url = $this->baseUrl . '/call/connect';
        
        return $this->httpRequest('POST', $url, $data, [
            'x-api-key: ' . $this->apiKey,
            'Authorization: ' . $this->secretKey,
        ]);
    }

    /**
     * Generate webhook response
     */
    public function generateWebhookResponse(string $event, array $params): string
    {
        // Knowlarity uses JSON responses for webhooks
        return json_encode(['status' => 'ok']);
    }

    /**
     * Handle incoming call
     */
    public function handleIncomingCall(array $params): array
    {
        return [
            'success' => true,
            'call_sid' => $params['call_id'] ?? '',
            'from' => $params['from_number'] ?? '',
            'to' => $params['to_number'] ?? '',
            'direction' => 'inbound',
            'provider' => 'knowlarity',
        ];
    }

    /**
     * Handle call status
     */
    public function handleCallStatus(array $params): array
    {
        return [
            'success' => true,
            'call_sid' => $params['call_id'] ?? '',
            'status' => $this->normalizeStatus($params['call_status'] ?? ''),
            'duration' => (int)($params['duration'] ?? 0),
            'recording_url' => $params['recording_url'] ?? '',
            'provider' => 'knowlarity',
        ];
    }

    /**
     * Handle recording
     */
    public function handleRecording(array $params): array
    {
        return [
            'success' => true,
            'recording_url' => $params['recording_url'] ?? '',
            'provider' => 'knowlarity',
        ];
    }

    /**
     * Handle transcription
     */
    public function handleTranscription(array $params): array
    {
        return [
            'success' => true,
            'transcription_text' => $params['transcript'] ?? '',
            'call_sid' => $params['call_id'] ?? '',
            'provider' => 'knowlarity',
        ];
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        $url = $this->baseUrl . "/call/details/{$callSid}";
        
        return $this->httpRequest('GET', $url, [], [
            'x-api-key: ' . $this->apiKey,
            'Authorization: ' . $this->secretKey,
        ]);
    }

    /**
     * Get recording URL
     */
    public function getRecordingUrl(string $recordingSid): ?string
    {
        // Knowlarity provides recording URL in webhook
        return null;
    }

    /**
     * Download recording
     */
    public function downloadRecording(string $recordingSid, string $savePath): bool
    {
        return false; // Not supported via API
    }

    /**
     * Validate webhook signature
     */
    public function validateSignature(array $params, string $signature): bool
    {
        // Knowlarity signature validation
        $secret = $this->getConfig('webhook_secret');
        if (!$secret) {
            return true;
        }
        
        $data = json_encode($params);
        $expectedSignature = hash_hmac('sha256', $data, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get provider capabilities
     */
    public function getCapabilities(): array
    {
        return [
            'outbound_calls' => true,
            'incoming_calls' => true,
            'call_recording' => true,
            'call_transcription' => false,
            'status_callbacks' => true,
            'webhook_signatures' => true,
            'answering_machine_detection' => false,
            'conference_calls' => false,
            'sms' => false,
            'whatsapp' => false,
            'countries' => ['IN'],
        ];
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        $url = $this->baseUrl . '/account/profile';
        
        $response = $this->httpRequest('GET', $url, [], [
            'x-api-key: ' . $this->apiKey,
            'Authorization: ' . $this->secretKey,
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'provider' => 'knowlarity',
                'message' => 'Connection successful',
            ];
        }
        
        return [
            'success' => false,
            'provider' => 'knowlarity',
            'error' => $response['error'] ?? 'Connection failed',
        ];
    }

    /**
     * Normalize Knowlarity call status
     */
    private function normalizeStatus(string $status): string
    {
        $statusMap = [
            'ringing' => 'ringing',
            'answered' => 'answered',
            'completed' => 'completed',
            'busy' => 'busy',
            'failed' => 'failed',
            'no_answer' => 'no_answer',
            'cancelled' => 'cancelled',
        ];
        
        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}