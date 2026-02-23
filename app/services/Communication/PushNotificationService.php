<?php

namespace App\Services\Communication;

use App\Core\Database;
use App\Models\MobileDevice;

/**
 * Push Notification Service
 * FCM (Firebase Cloud Messaging) and APNS (Apple Push Notification Service)
 */
class PushNotificationService
{
    private $db;
    private $fcmServerKey;
    private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fcmServerKey = $_ENV['FCM_SERVER_KEY'] ?? '';
    }

    /**
     * Send push notification to single device
     */
    public function sendToDevice(string $deviceToken, array $notification): array
    {
        $data = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'icon' => $notification['icon'] ?? 'default',
                'sound' => $notification['sound'] ?? 'default',
                'click_action' => $notification['click_action'] ?? null
            ],
            'data' => $notification['data'] ?? []
        ];

        return $this->sendFCMRequest($data);
    }

    /**
     * Send push notification to user (all devices)
     */
    public function sendToUser(int $userId, array $notification): array
    {
        $devices = (new MobileDevice())->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if (empty($devices)) {
            return ['success' => false, 'error' => 'No active devices found'];
        }

        $tokens = array_column($devices, 'device_token');
        return $this->sendToDevices($tokens, $notification);
    }

    /**
     * Send push notification to multiple devices
     */
    public function sendToDevices(array $deviceTokens, array $notification): array
    {
        if (empty($deviceTokens)) {
            return ['success' => false, 'error' => 'No device tokens provided'];
        }

        $data = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'icon' => $notification['icon'] ?? 'default',
                'sound' => $notification['sound'] ?? 'default'
            ],
            'data' => $notification['data'] ?? []
        ];

        return $this->sendFCMRequest($data);
    }

    /**
     * Send to topic (for broadcast notifications)
     */
    public function sendToTopic(string $topic, array $notification): array
    {
        $data = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'icon' => $notification['icon'] ?? 'default',
                'sound' => $notification['sound'] ?? 'default'
            ],
            'data' => $notification['data'] ?? []
        ];

        return $this->sendFCMRequest($data);
    }

    /**
     * Send broadcast to all users
     */
    public function sendBroadcast(array $notification): array
    {
        return $this->sendToTopic('all', $notification);
    }

    /**
     * Send to role-based topic
     */
    public function sendToRole(string $role, array $notification): array
    {
        return $this->sendToTopic('role_' . $role, $notification);
    }

    /**
     * Subscribe device to topic
     */
    public function subscribeToTopic(string $deviceToken, string $topic): bool
    {
        $url = "https://iid.googleapis.com/iid/v1/{$deviceToken}/rel/topics/{$topic}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: key=' . $this->fcmServerKey,
                'Content-Length: 0'
            ]
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Register device token
     */
    public function registerDevice(int $userId, string $deviceToken, string $platform): array
    {
        // Check if device already registered
        $existing = $this->db->query(
            "SELECT id FROM mobile_devices WHERE device_token = ?",
            [$deviceToken]
        )->fetchColumn();

        if ($existing) {
            // Update existing
            $this->db->query(
                "UPDATE mobile_devices SET user_id = ?, platform = ?, last_used = NOW(), is_active = 1 WHERE device_token = ?",
                [$userId, $platform, $deviceToken]
            );
        } else {
            // Insert new
            $this->db->query(
                "INSERT INTO mobile_devices (user_id, device_token, platform, last_used, is_active, created_at) VALUES (?, ?, ?, NOW(), 1, NOW())",
                [$userId, $deviceToken, $platform]
            );
        }

        // Subscribe to user role topic
        $user = $this->db->query(
            "SELECT role FROM users WHERE id = ?",
            [$userId]
        )->fetchColumn();

        if ($user) {
            $this->subscribeToTopic($deviceToken, 'role_' . $user);
        }
        $this->subscribeToTopic($deviceToken, 'all');

        return ['success' => true, 'device_id' => $existing ?: $this->db->lastInsertId()];
    }

    /**
     * Unregister device
     */
    public function unregisterDevice(string $deviceToken): bool
    {
        return $this->db->query(
            "UPDATE mobile_devices SET is_active = 0 WHERE device_token = ?",
            [$deviceToken]
        )->rowCount() > 0;
    }

    /**
     * Send FCM request
     */
    private function sendFCMRequest(array $data): array
    {
        if (empty($this->fcmServerKey)) {
            return ['success' => false, 'error' => 'FCM not configured'];
        }

        $ch = curl_init($this->fcmUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: key=' . $this->fcmServerKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);

        // Log notification
        $this->logNotification($data, $result);

        if ($httpCode === 200 && isset($result['success']) && $result['success'] > 0) {
            return [
                'success' => true,
                'message_id' => $result['results'][0]['message_id'] ?? null,
                'success_count' => $result['success'] ?? 0,
                'failure_count' => $result['failure'] ?? 0
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to send notification',
            'details' => $result
        ];
    }

    /**
     * Log notification
     */
    private function logNotification(array $data, array $result): void
    {
        $this->db->query(
            "INSERT INTO notification_logs (type, payload, response, created_at) VALUES ('push', ?, ?, NOW())",
            [json_encode($data), json_encode($result)]
        );
    }

    /**
     * Send property alert notification
     */
    public function sendPropertyAlert(int $userId, array $property): array
    {
        return $this->sendToUser($userId, [
            'title' => 'New Property Alert!',
            'body' => "{$property['title']} - ₹" . number_format($property['price']),
            'icon' => 'property',
            'data' => [
                'type' => 'property_alert',
                'property_id' => $property['id']
            ]
        ]);
    }

    /**
     * Send booking confirmation notification
     */
    public function sendBookingConfirmation(int $userId, array $booking): array
    {
        return $this->sendToUser($userId, [
            'title' => 'Booking Confirmed',
            'body' => "Your visit to {$booking['property_title']} is scheduled for {$booking['date']}",
            'icon' => 'calendar',
            'data' => [
                'type' => 'booking_confirmation',
                'booking_id' => $booking['id']
            ]
        ]);
    }

    /**
     * Send payment notification
     */
    public function sendPaymentNotification(int $userId, array $payment): array
    {
        $status = $payment['status'] === 'completed' ? 'Successful' : 'Failed';
        return $this->sendToUser($userId, [
            'title' => "Payment {$status}",
            'body' => "Payment of ₹" . number_format($payment['amount']) . " for {$payment['property_title']}",
            'icon' => 'payment',
            'data' => [
                'type' => 'payment_update',
                'payment_id' => $payment['id']
            ]
        ]);
    }

    /**
     * Send lead assignment notification
     */
    public function sendLeadAssignment(int $userId, array $lead): array
    {
        return $this->sendToUser($userId, [
            'title' => 'New Lead Assigned',
            'body' => "Lead: {$lead['name']} - {$lead['phone']}",
            'icon' => 'lead',
            'data' => [
                'type' => 'lead_assignment',
                'lead_id' => $lead['id']
            ]
        ]);
    }

    /**
     * Send commission notification
     */
    public function sendCommissionNotification(int $userId, float $amount): array
    {
        return $this->sendToUser($userId, [
            'title' => 'Commission Credited!',
            'body' => "₹" . number_format($amount) . " commission has been credited to your account",
            'icon' => 'money',
            'data' => [
                'type' => 'commission',
                'amount' => $amount
            ]
        ]);
    }
}
