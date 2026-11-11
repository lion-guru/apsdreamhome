<?php
/**
 * AbstractVoiceProvider — Base class for voice providers
 */

namespace App\Services\Providers;

abstract class AbstractVoiceProvider implements VoiceProviderInterface
{
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Make an outbound call
     */
    abstract public function makeCall(string $to, string $from, array $options): array;

    /**
     * Generate webhook response
     */
    abstract public function generateWebhookResponse(string $event, array $params): string;

    /**
     * Handle incoming call
     */
    abstract public function handleIncomingCall(array $params): array;

    /**
     * Handle call status
     */
    abstract public function handleCallStatus(array $params): array;

    /**
     * Handle recording
     */
    abstract public function handleRecording(array $params): array;

    /**
     * Handle transcription
     */
    abstract public function handleTranscription(array $params): array;

    /**
     * Get call details
     */
    abstract public function getCallDetails(string $callSid): array;

    /**
     * Get recording URL
     */
    abstract public function getRecordingUrl(string $recordingSid): ?string;

    /**
     * Download recording
     */
    abstract public function downloadRecording(string $recordingSid, string $savePath): bool;

    /**
     * Validate webhook signature
     */
    abstract public function validateSignature(array $params, string $signature): bool;

    /**
     * Get provider capabilities
     */
    abstract public function getCapabilities(): array;

    /**
     * Test connection
     */
    abstract public function testConnection(): array;

    /**
     * Get config value
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Generate XML response
     */
    protected function xmlResponse(array $elements): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response>';
        
        foreach ($elements as $element) {
            $xml .= $this->buildXmlElement($element);
        }
        
        $xml .= '</Response>';
        return $xml;
    }

    /**
     * Build XML element
     */
    private function buildXmlElement(array $element): string
    {
        $tag = $element['tag'] ?? 'Say';
        $content = $element['content'] ?? '';
        $attributes = $element['attributes'] ?? [];
        
        $attrStr = '';
        foreach ($attributes as $key => $value) {
            $attrStr .= " $key=\"" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (is_array($content)) {
            $xml = "<$tag$attrStr>";
            foreach ($content as $child) {
                $xml .= $this->buildXmlElement($child);
            }
            $xml .= "</$tag>";
            return $xml;
        }

        return "<$tag$attrStr>" . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "</$tag>";
    }

    /**
     * Make HTTP request
     */
    protected function httpRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        $ch = curl_init();
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'body' => $response,
            'error' => $error,
        ];
    }

    /**
     * Get basic auth header
     */
    protected function getBasicAuthHeader(): string
    {
        $accountSid = $this->getConfig('account_sid');
        $authToken = $this->getConfig('auth_token');
        
        return 'Basic ' . base64_encode("$accountSid:$authToken");
    }
}