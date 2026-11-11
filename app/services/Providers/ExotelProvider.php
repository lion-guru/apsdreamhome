<?php
/**
 * ExotelProvider — Exotel voice provider implementation
 * Exotel is a leading cloud telephony platform in India
 */

namespace App\Services\Providers;

class ExotelProvider extends AbstractVoiceProvider
{
    protected $baseUrl;
    protected $accountSid;
    protected $apiKey;
    protected $apiToken;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        
        $this->accountSid = $this->getConfig('account_sid');
        $this->apiKey = $this->getConfig('api_key');
        $this->apiToken = $this->getConfig('api_token');
        $this->baseUrl = "https://{$this->accountSid}:{$this->apiToken}@api.exotel.com/v1/Accounts/{$this->accountSid}";
    }

    /**
     * Make an outbound call
     */
    public function makeCall(string $to, string $from = '', array $options = []): array
    {
        if (!$this->accountSid || !$this->apiToken) {
            return ['success' => false, 'error' => 'Exotel credentials not configured'];
        }

        $from = $from ?: $this->getConfig('caller_id');
        
        $data = [
            'From' => $from,
            'To' => $to,
            'CallerId' => $from,
            'CallType' => 'trans',
        ];

        if (!empty($options['status_callback_url'])) {
            $data['StatusCallback'] = $options['status_callback_url'];
        }
        
        if (!empty($options['recording'])) {
            $data['Record'] = 'true';
        }
        
        if (!empty($options['timeout'])) {
            $data['TimeOut'] = (int)$options['timeout'];
        }
        
        if (!empty($options['custom_data'])) {
            $data['CustomField'] = $options['custom_data'];
        }

        $url = $this->baseUrl . '/Calls/connect.json';
        
        return $this->httpRequest('POST', $url, $data, [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->apiToken),
        ]);
    }

    /**
     * Generate webhook response (ExoML)
     */
    public function generateWebhookResponse(string $event, array $params): string
    {
        switch ($event) {
            case 'incoming':
                return $this->generateIncomingCallResponse($params);
            case 'status':
                return 'OK';
            case 'recording':
                return 'OK';
            case 'transcription':
                return 'OK';
            default:
                return $this->getDefaultResponse($event);
        }
    }

    /**
     * Generate ExoML for incoming call
     */
    private function generateIncomingCallResponse(array $params): string
    {
        $actionUrl = $params['action_url'] ?? '';
        $recordingEnabled = $params['recording'] ?? false;
        
        $elements = [
            [
                'tag' => 'Response',
                'content' => [
                    [
                        'tag' => 'Say',
                        'content' => 'Welcome to APS Dream Home. Please hold while we connect you.',
                        'attributes' => ['voice' => 'alice', 'language' => 'en-IN'],
                    ],
                ],
            ],
        ];

        if ($recordingEnabled) {
            $elements[0]['content'][] = [
                'tag' => 'Record',
                'attributes' => [
                    'action' => $actionUrl . '/recording',
                    'maxLength' => '3600',
                    'playBeep' => 'true',
                ],
            ];
        }

        $elements[0]['content'][] = [
            'tag' => 'Dial',
            'attributes' => [
                'action' => $actionUrl . '/status',
                'record' => $recordingEnabled ? 'true' : 'false',
                'timeLimit' => '3600',
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
        $callSid = $params['CallSid'] ?? $params['CallUUID'] ?? '';
        $from = $params['From'] ?? $params['Caller'] ?? '';
        $to = $params['To'] ?? $params['DialWhomNumber'] ?? '';
        $direction = $params['Direction'] ?? 'incoming';
        
        return [
            'success' => true,
            'call_sid' => $callSid,
            'from' => $from,
            'to' => $to,
            'direction' => $direction,
            'provider' => 'exotel',
        ];
    }

    /**
     * Handle call status
     */
    public function handleCallStatus(array $params): array
    {
        $callSid = $params['CallSid'] ?? $params['CallUUID'] ?? '';
        $status = $params['CallStatus'] ?? $params['Status'] ?? '';
        $duration = $params['CallDuration'] ?? $params['Duration'] ?? 0;
        $recordingUrl = $params['RecordingUrl'] ?? '';
        
        return [
            'success' => true,
            'call_sid' => $callSid,
            'status' => $this->normalizeStatus($status),
            'duration' => (int)$duration,
            'recording_url' => $recordingUrl,
            'provider' => 'exotel',
        ];
    }

    /**
     * Handle recording
     */
    public function handleRecording(array $params): array
    {
        $recordingSid = $params['RecordingSid'] ?? $params['RecordingUUID'] ?? '';
        $recordingUrl = $params['RecordingUrl'] ?? $params['RecordingURL'] ?? '';
        $duration = $params['RecordingDuration'] ?? 0;
        
        return [
            'success' => true,
            'recording_sid' => $recordingSid,
            'recording_url' => $recordingUrl,
            'duration' => (int)$duration,
            'provider' => 'exotel',
        ];
    }

    /**
     * Handle transcription
     */
    public function handleTranscription(array $params): array
    {
        $transcriptionSid = $params['TranscriptionSid'] ?? '';
        $transcriptionText = $params['TranscriptionText'] ?? '';
        $callSid = $params['CallSid'] ?? '';
        
        return [
            'success' => true,
            'transcription_sid' => $transcriptionSid,
            'transcription_text' => $transcriptionText,
            'call_sid' => $callSid,
            'provider' => 'exotel',
        ];
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        $url = $this->baseUrl . "/Calls/{$callSid}.json";
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->apiToken),
        ]);
        
        return $response;
    }

    /**
     * Get recording URL
     */
    public function getRecordingUrl(string $recordingSid): ?string
    {
        $url = $this->baseUrl . "/Recordings/{$recordingSid}.json";
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->apiToken),
        ]);
        
        if ($response['success'] && !empty($response['body'])) {
            $data = json_decode($response['body'], true);
            return $data['Recording']['RecordingUrl'] ?? null;
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
            CURLOPT_USERPWD => $this->accountSid . ':' . $this->apiToken,
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
        // Exotel uses HMAC-SHA256 signature
        $secret = $this->getConfig('signature_secret');
        if (!$secret) {
            return true; // Skip validation if secret not configured
        }
        
        ksort($params);
        $data = http_build_query($params, '', '&');
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
            'answering_machine_detection' => true,
            'conference_calls' => true,
            'sms' => true,
            'whatsapp' => false,
            'countries' => ['IN'],
        ];
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        $url = $this->baseUrl . '/Account.json';
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->apiToken),
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'provider' => 'exotel',
                'message' => 'Connection successful',
            ];
        }
        
        return [
            'success' => false,
            'provider' => 'exotel',
            'error' => $response['error'] ?? 'Connection failed',
        ];
    }

    /**
     * Normalize Exotel call status to standard status
     */
    private function normalizeStatus(string $status): string
    {
        $statusMap = [
            'ringing' => 'ringing',
            'in-progress' => 'answered',
            'completed' => 'completed',
            'busy' => 'busy',
            'failed' => 'failed',
            'no-answer' => 'no_answer',
            'canceled' => 'cancelled',
        ];
        
        return $statusMap[strtolower($status)] ?? 'unknown';
    }
}