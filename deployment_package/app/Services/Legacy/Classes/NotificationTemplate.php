<?php

namespace App\Services\Legacy\Classes;

use App\Services\NotificationService;
use Exception;

/**
 * Proxy for NotificationTemplate to maintain backward compatibility.
 * Redirects calls to the modern NotificationService.
 */
class NotificationTemplate {
    private $notificationService;

    public function __construct() {
        $this->notificationService = new NotificationService();
    }

    /**
     * Get template by type
     */
    public function getTemplate($type) {
        // Redirect to NotificationService which handles both DB and defaults
        return $this->notificationService->getTemplate($type);
    }

    /**
     * Parse template with variables
     */
    public function parseTemplate($template, $variables) {
        return $this->notificationService->parseTemplate($template, $variables);
    }

    /**
     * Send notification using template
     */
    public function sendNotification($type, $variables, $recipients) {
        // Recipient mapping might be needed if format differs
        // Modern expects array of ['uemail' => ..., 'uphone' => ..., 'uid' => ...]
        return $this->notificationService->sendTemplateNotification($type, $variables, $recipients);
    }

    /**
     * Get notification statistics
     */
    public function getStats($timeframe = '24h') {
        return $this->notificationService->getStats($timeframe);
    }

    /**
     * Handle other method calls by redirecting to NotificationService if they exist
     */
    public function __call($name, $arguments) {
        if (method_exists($this->notificationService, $name)) {
            return call_user_func_array([$this->notificationService, $name], $arguments);
        }
        throw new Exception("Method {$name} does not exist in NotificationTemplate proxy.");
    }
}
?>
