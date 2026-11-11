<?php
/**
 * VoiceProviderManager — Unified interface for multiple voice providers
 * Supports: Exotel, Twilio, Plivo, Knowlarity, Kaleyra
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class VoiceProviderManager
{
    use ServiceTenantTrait;

    private $db;
    private $activeProvider = null;
    private $config = [];

    // Provider constants
    const PROVIDER_EXOTEL = 'exotel';
    const PROVIDER_TWILIO = 'twilio';
    const PROVIDER_PLIVO = 'plivo';
    const PROVIDER_KNOWLARITY = 'knowlarity';
    const PROVIDER_KALEYRA = 'kaleyra';

    // Supported providers
    const SUPPORTED_PROVIDERS = [
        self::PROVIDER_EXOTEL => 'Exotel (India)',
        self::PROVIDER_TWILIO => 'Twilio (Global)',
        self::PROVIDER_PLIVO => 'Plivo (Global)',
        self::PROVIDER_KNOWLARITY => 'Knowlarity (India)',
        self::PROVIDER_KALEYRA => 'Kaleyra (Global)',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadActiveProvider();
    }

    /**
     * Load active provider from service_configs
     */
    private function loadActiveProvider(): void
    {
        $voiceConfig = $this->db->fetch(
            "SELECT * FROM service_configs WHERE group_name = 'voice' ORDER BY sort_order ASC LIMIT 1"
        );
        
        if ($voiceConfig) {
            $this->activeProvider = $voiceConfig['service_name'];
            $this->config = json_decode($voiceConfig['config_value'] ?? '{}', true);
        }
    }

    /**
     * Get active provider
     */
    public function getActiveProvider(): ?string
    {
        return $this->activeProvider;
    }

    /**
     * Get provider config
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get all supported providers
     */
    public static function getSupportedProviders(): array
    {
        return self::SUPPORTED_PROVIDERS;
    }

    /**
     * Make an outbound call
     * 
     * @param string $to Phone number to call (E.164 format)
     * @param string $from Caller ID (optional, uses provider default)
     * @param array $options Additional options: 
     *   - status_callback_url: Webhook for call status updates
     *   - recording: Enable call recording
     *   - timeout: Ring timeout in seconds
     *   - machine_detection: Enable answering machine detection
     * @return array Result with call_sid, status, etc.
     */
    public function makeCall(string $to, string $from = '', array $options = []): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->makeCall($to, $from, $options);
    }

    /**
     * Generate webhook response for incoming calls
     * 
     * @param string $event Event type: 'incoming', 'status', 'recording', 'transcription'
     * @param array $params Provider-specific parameters
     * @return string XML/TwiML response
     */
    public function generateWebhookResponse(string $event, array $params): string
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return $this->getDefaultResponse($event);
        }

        return $provider->generateWebhookResponse($event, $params);
    }

    /**
     * Handle incoming call webhook
     */
    public function handleIncomingCall(array $params): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->handleIncomingCall($params);
    }

    /**
     * Handle call status webhook
     */
    public function handleCallStatus(array $params): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->handleCallStatus($params);
    }

    /**
     * Handle recording webhook
     */
    public function handleRecording(array $params): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->handleRecording($params);
    }

    /**
     * Handle transcription webhook
     */
    public function handleTranscription(array $params): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->handleTranscription($params);
    }

    /**
     * Get call details
     */
    public function getCallDetails(string $callSid): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->getCallDetails($callSid);
    }

    /**
     * Get recording URL
     */
    public function getRecordingUrl(string $recordingSid): ?string
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return null;
        }

        return $provider->getRecordingUrl($recordingSid);
    }

    /**
     * Download recording
     */
    public function downloadRecording(string $recordingSid, string $savePath): bool
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return false;
        }

        return $provider->downloadRecording($recordingSid, $savePath);
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(array $params, string $signature): bool
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return false;
        }

        return $provider->validateSignature($params, $signature);
    }

    /**
     * Get provider instance
     */
    private function getProviderInstance(): ?\App\Services\Providers\VoiceProviderInterface
    {
        if (!$this->activeProvider) {
            return null;
        }

        $className = __NAMESPACE__ . '\\Providers\\' . ucfirst($this->activeProvider) . 'Provider';
        
        if (!class_exists($className)) {
            error_log("Voice provider class not found: $className");
            return null;
        }

        return new $className($this->config);
    }

    /**
     * Get default response when no provider configured
     */
    private function getDefaultResponse(string $event): string
    {
        $responses = [
            'incoming' => '<?xml version="1.0" encoding="UTF-8"?><Response><Say>No voice provider configured. Please contact administrator.</Say><Hangup/></Response>',
            'status' => 'OK',
            'recording' => 'OK',
            'transcription' => 'OK',
        ];

        return $responses[$event] ?? 'OK';
    }

    /**
     * Get provider status for admin dashboard
     */
    public function getProviderStatus(): array
    {
        $provider = $this->getProviderInstance();
        
        return [
            'active_provider' => $this->activeProvider,
            'supported_providers' => self::SUPPORTED_PROVIDERS,
            'is_configured' => $provider !== null,
            'config_keys' => array_keys($this->config),
            'capabilities' => $provider ? $provider->getCapabilities() : [],
        ];
    }

    /**
     * Test provider connection
     */
    public function testConnection(): array
    {
        $provider = $this->getProviderInstance();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        return $provider->testConnection();
    }
}

/**
 * Voice Provider Interface
 */
interface VoiceProviderInterface
{
    public function makeCall(string $to, string $from, array $options): array;
    public function generateWebhookResponse(string $event, array $params): string;
    public function handleIncomingCall(array $params): array;
    public function handleCallStatus(array $params): array;
    public function handleRecording(array $params): array;
    public function handleTranscription(array $params): array;
    public function getCallDetails(string $callSid): array;
    public function getRecordingUrl(string $recordingSid): ?string;
    public function downloadRecording(string $recordingSid, string $savePath): bool;
    public function validateSignature(array $params, string $signature): bool;
    public function getCapabilities(): array;
    public function testConnection(): array;
}