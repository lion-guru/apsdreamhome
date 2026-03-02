<?php

namespace App\Services\Legacy\Classes;
require_once __DIR__ . '/../app/core/App.php';

use App\Services\NotificationService;

/**
 * Proxy for SmsNotifier to maintain backward compatibility.
 * Redirects calls to the modern NotificationService.
 */
class SmsNotifier {
    private $notificationService;

    public function __construct() {
        $this->notificationService = new NotificationService();
    }

    /**
     * Send SMS notification
     */
    public function send($to, $message, $options = []) {
        // Map legacy 'send' to NotificationService 'sendSms'
        // Type is defaulted to 'legacy_sms'
        return $this->notificationService->sendSms($to, $message, 'legacy_sms', null, $options);
    }

    /**
     * Validate phone number format
     */
    public static function validatePhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Check if it's a valid format (adjust regex based on your requirements)
        if (!preg_match('/^[1-9][0-9]{9,14}$/', $phone)) {
            return false;
        }

        return $phone;
    }

    /**
     * Handle other method calls by redirecting to NotificationService if they exist
     */
    public function __call($name, $arguments) {
        if (method_exists($this->notificationService, $name)) {
            return call_user_func_array([$this->notificationService, $name], $arguments);
        }
        throw new \Exception("Method {$name} does not exist in SmsNotifier proxy.");
    }
}
?>
