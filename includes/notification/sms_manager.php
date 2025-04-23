<?php
/**
 * SMS Notification Manager
 * Handles sending SMS notifications for critical alerts using Twilio
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../security_logger.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use Twilio\Rest\Client;

class SmsManager {
    private $client;
    private $logger;
    private $fromNumber;
    private $enabled;
    private $recipientNumbers;
    private $rateLimiter;
    private $lastSentTime = [];

    // Alert types and their cooldown periods (in seconds)
    private const ALERT_COOLDOWNS = [
        'security_breach' => 300,    // 5 minutes
        'api_abuse' => 600,          // 10 minutes
        'system_error' => 900,       // 15 minutes
        'backup_failure' => 1800,    // 30 minutes
        'rate_limit' => 1800,        // 30 minutes
        'disk_space' => 3600,        // 1 hour
        'database_error' => 1800,    // 30 minutes
        'auth_failure' => 900        // 15 minutes
    ];

    // Priority levels for different alert types
    private const ALERT_PRIORITIES = [
        'security_breach' => 1,  // Highest priority
        'api_abuse' => 2,
        'system_error' => 2,
        'backup_failure' => 3,
        'rate_limit' => 3,
        'disk_space' => 3,
        'database_error' => 2,
        'auth_failure' => 2
    ];

    public function __construct($security_logger = null) {
        $this->logger = $security_logger ?? new SecurityLogger();
        $this->enabled = getenv('SMS_NOTIFICATIONS_ENABLED') === 'true';
        
        if ($this->enabled) {
            $this->initializeTwilio();
            $this->loadRecipients();
        }
    }

    /**
     * Initialize Twilio client
     */
    private function initializeTwilio() {
        try {
            $accountSid = getenv('TWILIO_ACCOUNT_SID');
            $authToken = getenv('TWILIO_AUTH_TOKEN');
            $this->fromNumber = getenv('TWILIO_FROM_NUMBER');

            if (!$accountSid || !$authToken || !$this->fromNumber) {
                throw new Exception('Missing Twilio configuration');
            }

            $this->client = new Client($accountSid, $authToken);
            
        } catch (Exception $e) {
            $this->enabled = false;
            $this->logger->error('Failed to initialize SMS manager', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Load SMS recipient numbers from environment
     */
    private function loadRecipients() {
        $recipients = getenv('SMS_RECIPIENT_NUMBERS');
        if (!$recipients) {
            $this->enabled = false;
            $this->logger->error('No SMS recipients configured');
            return;
        }

        // Parse comma-separated list of numbers
        $this->recipientNumbers = array_map('trim', explode(',', $recipients));
        
        // Validate numbers
        $this->recipientNumbers = array_filter($this->recipientNumbers, function($number) {
            return preg_match('/^\+[1-9]\d{1,14}$/', $number);
        });

        if (empty($this->recipientNumbers)) {
            $this->enabled = false;
            $this->logger->error('No valid SMS recipients found');
        }
    }

    /**
     * Send SMS alert
     */
    public function sendAlert($type, $message, $data = []) {
        if (!$this->enabled || !isset(self::ALERT_COOLDOWNS[$type])) {
            return false;
        }

        // Check cooldown period
        $now = time();
        if (isset($this->lastSentTime[$type]) && 
            ($now - $this->lastSentTime[$type]) < self::ALERT_COOLDOWNS[$type]) {
            return false;
        }

        // Format message
        $formattedMessage = $this->formatAlertMessage($type, $message, $data);
        
        $success = true;
        foreach ($this->recipientNumbers as $number) {
            try {
                $this->client->messages->create($number, [
                    'from' => $this->fromNumber,
                    'body' => $formattedMessage
                ]);

                $this->logger->info('SMS alert sent', [
                    'type' => $type,
                    'number' => $this->maskPhoneNumber($number)
                ]);

            } catch (Exception $e) {
                $success = false;
                $this->logger->error('Failed to send SMS alert', [
                    'type' => $type,
                    'number' => $this->maskPhoneNumber($number),
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($success) {
            $this->lastSentTime[$type] = $now;
        }

        return $success;
    }

    /**
     * Format alert message with consistent structure
     */
    private function formatAlertMessage($type, $message, $data) {
        $priority = self::ALERT_PRIORITIES[$type] ?? 3;
        $prefix = str_repeat('🚨', $priority); // Visual priority indicator
        
        $timestamp = date('Y-m-d H:i:s');
        $site = getenv('SITE_NAME') ?: 'APS Dream Homes';
        
        $formattedMessage = "{$prefix} {$site}\n";
        $formattedMessage .= "Type: " . ucwords(str_replace('_', ' ', $type)) . "\n";
        $formattedMessage .= "Time: {$timestamp}\n\n";
        $formattedMessage .= $message;

        if (!empty($data)) {
            $formattedMessage .= "\n\nDetails:";
            foreach ($data as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $formattedMessage .= "\n- " . ucwords(str_replace('_', ' ', $key)) . ": {$value}";
                }
            }
        }

        return $formattedMessage;
    }

    /**
     * Mask phone number for logging
     */
    private function maskPhoneNumber($number) {
        return substr($number, 0, 4) . str_repeat('*', strlen($number) - 7) . substr($number, -3);
    }

    /**
     * Send test SMS alert
     */
    public function sendTestAlert() {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'SMS notifications are not enabled'
            ];
        }

        try {
            $message = "This is a test alert from your APS Dream Homes notification system.";
            $success = $this->sendAlert('system_error', $message, [
                'test' => true,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => $success,
                'message' => $success ? 'Test alert sent successfully' : 'Failed to send test alert'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if SMS notifications are enabled
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Get alert types and their cooldown periods
     */
    public function getAlertTypes() {
        return self::ALERT_COOLDOWNS;
    }
}

// Create global SMS manager instance
$smsManager = new SmsManager($securityLogger ?? null);
