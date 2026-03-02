<?php

namespace App\Services\Legacy;

use App\Services\NotificationService;
use Exception;

/**
 * Advanced Notification Manager Proxy
 * Delegates to modern NotificationService
 */
class NotificationManager {
    private $notificationService;

    public function __construct($db = null, $email_service = null, $sms_service = null) {
        $this->notificationService = new NotificationService();
    }

    /**
     * Send notification across multiple channels
     */
    public function send($params) {
        return $this->notificationService->send($params);
    }

    /**
     * Send templated email notification
     */
    public function sendTemplatedEmail($to, $template, $data = []) {
        return $this->notificationService->send([
            'email' => $to,
            'template' => $template,
            'data' => $data,
            'channels' => ['email']
        ]);
    }

    /**
     * Create templated in-app notification
     */
    public function createTemplatedNotification($userId, $template, $data = []) {
        return $this->notificationService->send([
            'user_id' => $userId,
            'template' => $template,
            'data' => $data,
            'channels' => ['db']
        ]);
    }

    /**
     * Handle other method calls by delegating to NotificationService
     */
    public function __call($name, $arguments) {
        if (method_exists($this->notificationService, $name)) {
            return call_user_func_array([$this->notificationService, $name], $arguments);
        }
        throw new Exception("Method {$name} does not exist in NotificationManager proxy.");
    }
}
