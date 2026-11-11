<?php
/**
 * PlivoProvider — Plivo voice provider implementation
 * Plivo is a cloud communications platform with global coverage
 */

namespace App\Services\Providers;

class PlivoProvider extends AbstractVoiceProvider
{
    protected $baseUrl;
    protected $authId;
    protected $authToken;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        
        $this->authId = $this->getConfig('auth_id');
        $this->authToken = $this->getConfig('auth_token');
        $this->baseUrl = "https://api.plivo.com/v1/Account/{$this->authId}";
    }

    /**
     * Make an outbound call
     */
    public function makeCall(string $to, string $from = '', array $options = []): array
    {
        if (!$this->authId || !$this->authToken) {
            return ['success' => false, 'error' => 'Plivo credentials not configured'];
        }

        $from = $from ?: $this->getConfig('caller_id');
        
        $data = [
            'to' => $to,
            'from' => $from,
            'answer_url' => $options['webhook_url'] ?? $this->getConfig('answer_url'),
            'answer_method' => 'POST',
        ];

        if (!empty($options['status_callback_url'])) {
            $data['hangup_url'] = $options['status_callback_url'];
        }
        
        if (!empty($options['recording'])) {
            $data['record'] = 'true';
            $data['record_file_format'] = 'mp3';
        }
        
        if (!empty($options['timeout'])) {
            $data['time_limit'] = (int)$options['timeout'];
        }
        
        if (!empty($options['machine_detection'])) {
            $data['machine_detection'] = $options['machine_detection'] === 'enable' ? 'true' : 'false';
        }

        $url = $this->baseUrl . '/Call/';
        
        return $this->httpRequest('POST', $url, $data, [
            'Authorization: Basic ' . base64_encode($this->authId . ':' . $this->authToken),
        ]);
    }

    /**
     * Generate webhook response (Plivo XML)
     */
    public function generateWebhookResponse(string $event, array $params): string
    {
        switch ($event) {
            case 'incoming':
                return $this->generateIncomingCallResponse($params);
            case 'status':
                return '<?xml version="1.0" encoding="UTF-8"?><Response/>';
            case 'recording':
                return '<?xml version="1.0" encoding="UTF-8"?><Response/>';
            case 'transcription':
                return '<?xml version="1.0" encoding="UTF-8"?><Response/>';
            default:
                return $this->getDefaultResponse($event);
        }
    }

    /**
     * Generate Plivo XML for incoming call
     */
    private function generateIncomingCallResponse(array $params): string
    {
        $elements = [
            [
                'tag' => 'Response',
                'content' => [
                    [
                        'tag' => 'Speak',
                        'content' => 'Welcome to APS Dream Home. Please hold while we connect you.',
                        'attributes' => ['voice' => 'WOMAN', 'language' => 'en-IN'],
                    ],
                ],
            ],
        ];

        if (!empty($params['recording'])) {
            $elements[0]['content'][] = [
                'tag' => 'Record',
                'attributes' => [
                    'action' => ($params['action_url'] ?? '') . '/recording',
                    'maxLength' => '3600',
                    'playBeep' => 'true',
                    'fileFormat' => 'mp3',
                ],
            ];
        }

        $elements[0]['content'][] = [
            'tag' => 'Dial',
            'attributes' => [
                'action' => ($params['action_url'] ?? '') . '/status',
                'record' => !empty($params['recording']) ? 'true' : 'false',
                'timeLimit' => '14400',
            ],
            'content' => $this->getConfig('fallback_number') ?? '',
        ];

        return $this->xmlResponse($elements);
    }

    /**
     * Handle incoming call
     */
    public function handleIncomingCall(array $params): array
    {
        return [
            'success' => true,
            'call_sid' => $params['CallUUID'] ?? '',
            'from' => $params['From'] ?? '',
            'to' => $params['To'] ?? '',
            'direction' => $params['Direction'] ?? 'inbound',
            'provider' => 'plivo',
        ];
    }

    /**
     * Handle call status
     */
    public function handleCallStatus(array $params): array
    {
        return [
            'success' => true,
            'call_sid' => $params['CallUUID'] ?? '',
            'status' => $this->normalizeStatus($params['CallStatus'] ?? ''),
            'duration' => (int)($params['BillDuration'] ?? 0),
            'recording_url' => $params['RecordUrl'] ?? '',
            'provider' => 'plivo',
        ];
    }

    /**
     * Handle recording
     */
    public function handleRecording(array $params): array
    {
        return [
            'success' => true,
            'recording_sid' => $params['RecordId'] ?? '',
            'recording_url' => $params['RecordUrl'] ?? '',
            'duration' => (int)($params['RecordingDuration'] ?? 0),
            'provider' => 'plivo',
        ];
    }

    /**
     * Handle transcription
     */
    public function handleTranscription(array $params): array
    {
        return [
            'success' => true,
            'transcription_text' => $params['Transcription'] ?? '',
            'call_sid' => $params['CallUUID'] ?? '',
            'provider' => 'plivo',
        ];
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        $url = $this->baseUrl . "/Call/{$callSid}/";
        
        return $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->authId . ':' . $this->authToken),
        ]);
    }

    /**
     * Get recording URL
     */
    public function getRecordingUrl(string $recordingSid): ?string
    {
        $url = $this->baseUrl . "/Recording/{$recordingSid}/";
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->authId . ':' . $this->authToken),
        ]);
        
        if ($response['success'] && !empty($response['body'])) {
            $data = json_decode($response['body'], true);
            return $data['recording_url'] ?? null;
        }
        
        return null;
    }

    /**
     * Download recording
     */
    public function downloadRecording(string $recordingSid, string $savePath): bool
    {
        $url = $this->getRecordingUrl($recordingSid);
        if (!$url) {
            return false;
        }
        
        $ch = curl_init($url);
        $fp = fopen($savePath, 'wb');
        
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERPWD => $this->authId . ':' . $this->authToken,
        ]);
        
        $result = curl_exec($ch);
        fclose($fp);
        curl_close($ch);
        
        return $result !== false;
    }

    /**
     * Validate webhook signature
     */
    public function validateSignature(array $params, string $signature): bool
    {
        // Plivo uses X-Plivo-Signature header
        $authToken = $this->getConfig('auth_token');
        if (!$authToken) {
            return true;
        }
        
        ksort($params);
        $data = http_build_query($params, '', '&');
        $expectedSignature = base64_encode(hash_hmac('sha256', $data, $authToken, true));
        
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
            'answering_machine_detection' => true,
            'conference_calls' => true,
            'sms' => true,
            'whatsapp' => false,
            'countries' => ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IN', 'BR', 'MX', 'Global'],
        ];
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        $url = $this->baseUrl . '/';
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->authId . ':' . $this->authToken),
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'provider' => 'plivo',
                'message' => 'Connection successful',
            ];
        }
        
        return [
            'success' => false,
            'provider' => 'plivo',
            'error' => $response['error'] ?? 'Connection failed',
        ];
    }

    /**
     * Normalize Plivo call status to standard status
     */
    private function normalizeStatus(string $status): string
    {
        $statusMap = [
            'in-progress' => 'answered',
            'completed' => 'completed',
            'busy' => 'busy',
            'failed' => 'failed',
            'no-answer' => 'no_answer',
            'canceled' => 'cancelled',
            'timeout' => 'timeout',
        ];
        
        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}