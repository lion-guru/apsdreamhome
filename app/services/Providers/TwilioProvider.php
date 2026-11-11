<?php
/**
 * TwilioProvider — Twilio voice provider implementation
 * Twilio is a global cloud communications platform
 */

namespace App\Services\Providers;

class TwilioProvider extends AbstractVoiceProvider
{
    protected $baseUrl;
    protected $accountSid;
    protected $authToken;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        
        $this->accountSid = $this->getConfig('account_sid');
        $this->authToken = $this->getConfig('auth_token');
        $this->baseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}";
    }

    /**
     * Make an outbound call
     */
    public function makeCall(string $to, string $from = '', array $options = []): array
    {
        if (!$this->accountSid || !$this->authToken) {
            return ['success' => false, 'error' => 'Twilio credentials not configured'];
        }

        $from = $from ?: $this->getConfig('caller_id');
        
        $data = [
            'To' => $to,
            'From' => $from,
            'Url' => $options['webhook_url'] ?? $this->getConfig('voice_webhook_url'),
        ];

        if (!empty($options['status_callback_url'])) {
            $data['StatusCallback'] = $options['status_callback_url'];
            $data['StatusCallbackEvent'] = 'initiated,ringing,answered,completed';
        }
        
        if (!empty($options['recording'])) {
            $data['Record'] = 'true';
            $data['RecordingChannels'] = 'dual';
        }
        
        if (!empty($options['timeout'])) {
            $data['Timeout'] = (int)$options['timeout'];
        }
        
        if (!empty($options['machine_detection'])) {
            $data['MachineDetection'] = $options['machine_detection'] === 'enable' ? 'Enable' : 'DetectMessageEnd';
        }

        $url = $this->baseUrl . '/Calls.json';
        
        return $this->httpRequest('POST', $url, $data, [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->authToken),
        ]);
    }

    /**
     * Generate webhook response (TwiML)
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
     * Generate TwiML for incoming call
     */
    private function generateIncomingCallResponse(array $params): string
    {
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

        if (!empty($params['recording'])) {
            $elements[0]['content'][] = [
                'tag' => 'Record',
                'attributes' => [
                    'action' => ($params['action_url'] ?? '') . '/recording',
                    'maxLength' => '3600',
                    'playBeep' => 'true',
                    'recordingChannels' => 'dual',
                ],
            ];
        }

        $elements[0]['content'][] = [
            'tag' => 'Dial',
            'attributes' => [
                'action' => ($params['action_url'] ?? '') . '/status',
                'record' => !empty($params['recording']) ? 'record-from-answer-dual' : 'do-not-record',
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
            'call_sid' => $params['CallSid'] ?? '',
            'from' => $params['From'] ?? '',
            'to' => $params['To'] ?? '',
            'direction' => $params['Direction'] ?? 'inbound',
            'provider' => 'twilio',
        ];
    }

    /**
     * Handle call status
     */
    public function handleCallStatus(array $params): array
    {
        return [
            'success' => true,
            'call_sid' => $params['CallSid'] ?? '',
            'status' => $this->normalizeStatus($params['CallStatus'] ?? ''),
            'duration' => (int)($params['CallDuration'] ?? 0),
            'recording_url' => $params['RecordingUrl'] ?? '',
            'provider' => 'twilio',
        ];
    }

    /**
     * Handle recording
     */
    public function handleRecording(array $params): array
    {
        return [
            'success' => true,
            'recording_sid' => $params['RecordingSid'] ?? '',
            'recording_url' => $params['RecordingUrl'] ?? '',
            'duration' => (int)($params['RecordingDuration'] ?? 0),
            'channels' => (int)($params['RecordingChannels'] ?? 1),
            'provider' => 'twilio',
        ];
    }

    /**
     * Handle transcription
     */
    public function handleTranscription(array $params): array
    {
        return [
            'success' => true,
            'transcription_sid' => $params['TranscriptionSid'] ?? '',
            'transcription_text' => $params['TranscriptionText'] ?? '',
            'call_sid' => $params['CallSid'] ?? '',
            'provider' => 'twilio',
        ];
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        $url = $this->baseUrl . "/Calls/{$callSid}.json";
        
        return $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->authToken),
        ]);
    }

    /**
     * Get recording URL
     */
    public function getRecordingUrl(string $recordingSid): ?string
    {
        $url = $this->baseUrl . "/Recordings/{$recordingSid}.json";
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->authToken),
        ]);
        
        if ($response['success'] && !empty($response['body'])) {
            $data = json_decode($response['body'], true);
            return $data['Recording']['Uri'] ?? null;
        }
        
        return null;
    }

    /**
     * Download recording
     */
    public function downloadRecording(string $recordingSid, string $savePath): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Recordings/{$recordingSid}.mp3";
        
        $ch = curl_init($url);
        $fp = fopen($savePath, 'wb');
        
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERPWD => $this->accountSid . ':' . $this->authToken,
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
        // Twilio uses X-Twilio-Signature header
        $authToken = $this->getConfig('auth_token');
        if (!$authToken) {
            return true;
        }
        
        // Sort params and build string
        ksort($params);
        $data = http_build_query($params, '', '&');
        $expectedSignature = base64_encode(hash_hmac('sha1', $data, $authToken, true));
        
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
            'call_transcription' => true,
            'status_callbacks' => true,
            'webhook_signatures' => true,
            'answering_machine_detection' => true,
            'conference_calls' => true,
            'sms' => true,
            'whatsapp' => true,
            'video' => true,
            'countries' => ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'JP', 'IN', 'BR', 'MX', 'Global'],
        ];
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        $url = $this->baseUrl . '.json';
        
        $response = $this->httpRequest('GET', $url, [], [
            'Authorization: Basic ' . base64_encode($this->accountSid . ':' . $this->authToken),
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'provider' => 'twilio',
                'message' => 'Connection successful',
            ];
        }
        
        return [
            'success' => false,
            'provider' => 'twilio',
            'error' => $response['error'] ?? 'Connection failed',
        ];
    }

    /**
     * Normalize Twilio call status to standard status
     */
    private function normalizeStatus(string $status): string
    {
        $statusMap = [
            'queued' => 'queued',
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