<?php

namespace App\Services\Legacy\Notification;

use Twilio\Rest\Client;
use Exception;
use App\Services\Legacy\SecurityLogger;

/**
 * SMS Notification Manager
 * Handles sending SMS notifications for critical alerts using Twilio
 */
class SmsManager {
    private $client;
    private $logger;
    private $fromNumber;
    private $enabled;
    private $recipientNumbers;
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
     * Send SMS notification
     */
    public function send($message, $type = 'system_error') {
        if (!$this->enabled) {
            return false;
        }

        // Check cooldown
        $cooldown = self::ALERT_COOLDOWNS[$type] ?? 300;
        if (isset($this->lastSentTime[$type]) && (time() - $this->lastSentTime[$type]) < $cooldown) {
            return false;
        }

        $successCount = 0;
        foreach ($this->recipientNumbers as $number) {
            try {
                $this->client->messages->create($number, [
                    'from' => $this->fromNumber,
                    'body' => "[APS ALERT] $message"
                ]);
                $successCount++;
            } catch (Exception $e) {
                $this->logger->error('Failed to send SMS', [
                    'to' => $number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($successCount > 0) {
            $this->lastSentTime[$type] = time();
            return true;
        }

        return false;
    }
}
