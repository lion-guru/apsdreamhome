<?php

namespace App\Services\Communication;

use App\Core\Database;
use App\Core\Middleware\TenantContext;

/**
 * Push Notification Service — FCM HTTP v1 API
 *
 * Uses Google's current FCM v1 endpoint with OAuth 2.0 service account tokens.
 * Falls back gracefully when credentials are not configured.
 *
 * Required env vars (one of two auth methods):
 *   Method A (service account JSON file):
 *     FCM_SERVICE_ACCOUNT_PATH — absolute path to service-account.json
 *     FCM_PROJECT_ID           — Firebase project ID
 *   Method B (legacy server key — still works for v1 with legacy token auth):
 *     FCM_SERVER_KEY           — legacy server key (deprecated by Google but functional)
 *     FCM_PROJECT_ID           — Firebase project ID
 */
class PushNotificationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $projectId;
    private $serviceAccountPath;
    private $fcmServerKey;
    private $fcmV1Url;

    /** @var string|null cached OAuth2 access token */
    private static $accessToken = null;
    /** @var int|null token expiry timestamp */
    private static $accessTokenExpiry = null;

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->projectId = $_ENV['FCM_PROJECT_ID'] ?? '';
        $this->serviceAccountPath = $_ENV['FCM_SERVICE_ACCOUNT_PATH'] ?? '';
        $this->fcmServerKey = $_ENV['FCM_SERVER_KEY'] ?? '';

        if ($this->projectId) {
            $this->fcmV1Url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        }
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
        $tokens = [];

        // Primary: read from push_tokens (written by Flutter app via registerFcmToken)
        try {
            $stmt = $this->db->query(
                "SELECT device_token FROM push_tokens WHERE user_id = ? AND is_active = 1",
                [$userId]
            );
            $tokens = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
        // Table might not exist
        error_log($e->getMessage());
        }

        // Fallback: read from mobile_devices (legacy path)
        if (empty($tokens)) {
            try {
                $stmt = $this->db->query(
                    "SELECT device_token FROM mobile_devices WHERE user_id = ? AND is_active = 1",
                    [$userId]
                );
                $tokens = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            } catch (\Throwable $e) {
            // Table might not exist
            error_log($e->getMessage());
            }
        }

        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No active devices found'];
        }

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
     * Subscribe device to topic (FCM v1 uses token management API).
     */
    public function subscribeToTopic(string $deviceToken, string $topic): bool
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/tokens:subscribe";
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'topic' => $topic,
                'token' => $deviceToken,
            ]),
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Register device token — writes to BOTH push_tokens and mobile_devices
     */
    public function registerDevice(int $userId, string $deviceToken, string $platform): array
    {
        // Write to push_tokens (primary table for mobile app)
        try {
            $this->db->query(
                "INSERT INTO push_tokens (user_id, user_type, device_token, platform, is_active, last_used_at, created_at, updated_at, tenant_id)
                 VALUES (?, 'customer', ?, ?, 1, NOW(), NOW(), NOW(), ?)
                 ON DUPLICATE KEY UPDATE is_active = 1, platform = VALUES(platform), last_used_at = NOW(), updated_at = NOW()",
                [$userId, $deviceToken, $platform, $this->getTenantId()]
            );
        } catch (\Throwable $e) {
        // Table might not exist
        error_log($e->getMessage());
        }

        // Also write to mobile_devices (backward compatibility)
        try {
            $existing = $this->db->query(
                "SELECT id FROM mobile_devices WHERE device_token = ?",
                [$deviceToken]
            )->fetchColumn();

            if ($existing) {
                $this->db->query(
                    "UPDATE mobile_devices SET user_id = ?, platform = ?, last_used_at = NOW(), is_active = 1 WHERE device_token = ?",
                    [$userId, $platform, $deviceToken]
                );
            } else {
                $this->db->query(
                    "INSERT INTO mobile_devices (user_id, device_token, platform, last_used_at, is_active, created_at, tenant_id) VALUES (?, ?, ?, NOW(), 1, NOW(), ?)",
                    [$userId, $deviceToken, $platform, $this->getTenantId()]
                );
            }
        } catch (\Throwable $e) {
        // mobile_devices table might not exist — push_tokens is sufficient
        error_log($e->getMessage());
        }

        // Subscribe to user role topic
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $user = $this->db->query(
            "SELECT role FROM users WHERE id = ?{$tenantSql}",
            $tid > 1 ? [$userId, $tid] : [$userId]
        )->fetchColumn();

        if ($user) {
            $this->subscribeToTopic($deviceToken, 'role_' . $user);
        }
        $this->subscribeToTopic($deviceToken, 'all');

        return ['success' => true, 'device_id' => $existing ?: $this->db->lastInsertId()];
    }

    /**
     * Unregister device from both tables
     */
    public function unregisterDevice(string $deviceToken): bool
    {
        $deactivated = false;

        try {
            $deactivated = $this->db->query(
                "UPDATE push_tokens SET is_active = 0 WHERE device_token = ?",
                [$deviceToken]
            )->rowCount() > 0;
        } catch (\Throwable $e) {
        // Table might not exist
        error_log($e->getMessage());
        }

        try {
            $this->db->query(
                "UPDATE mobile_devices SET is_active = 0 WHERE device_token = ?",
                [$deviceToken]
            );
        } catch (\Throwable $e) {
        // Table might not exist
        error_log($e->getMessage());
        }

        return $deactivated;
    }

    // ----------------------------------------------------------------
    //  FCM HTTP v1 API
    // ----------------------------------------------------------------

    /**
     * Send push notification to single device (FCM v1 format).
     *
     * FCM v1 wraps the legacy `notification` + `data` fields inside a
     * `message` envelope.  Tokens go in `message.token`, topics in
     * `message.topic`.
     */
    private function sendFCMRequest(array $data): array
    {
        if (empty($this->projectId)) {
            return ['success' => false, 'error' => 'FCM_PROJECT_ID not configured'];
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'FCM OAuth2 token unavailable — check FCM_SERVICE_ACCOUNT_PATH or FCM_SERVER_KEY'];
        }

        // Build FCM v1 message envelope
        $message = [];

        // Target: token or topic
        if (!empty($data['to']) && strpos($data['to'], '/topics/') === 0) {
            $message['topic'] = substr($data['to'], 8); // strip '/topics/'
        } elseif (!empty($data['to'])) {
            $message['token'] = $data['to'];
        } elseif (!empty($data['registration_ids'])) {
            // Batch: send one-by-one (v1 has no batch endpoint)
            return $this->sendBatchV1($data['registration_ids'], $data);
        }

        // Notification payload (FCM v1 only accepts title, body, image)
        if (!empty($data['notification'])) {
            $message['notification'] = [
                'title' => $data['notification']['title'] ?? '',
                'body' => $data['notification']['body'] ?? '',
            ];
            // image is optional
            if (!empty($data['notification']['image'])) {
                $message['notification']['image'] = $data['notification']['image'];
            }
        }

        // Data payload (all values must be strings in v1)
        if (!empty($data['data'])) {
            $flat = [];
            foreach ($data['data'] as $k => $v) {
                $flat[$k] = is_string($v) ? $v : json_encode($v);
            }
            $message['data'] = $flat;
        }

        // Android-specific config (icon, sound, channel go here in v1)
        $message['android'] = [
            'priority' => 'high',
            'notification' => [
                'channel_id' => 'aps_default',
                'sound' => $data['notification']['sound'] ?? 'default',
            ],
        ];

        // APNs config (iOS)
        $message['apns'] = [
            'payload' => [
                'aps' => [
                    'sound' => $data['notification']['sound'] ?? 'default',
                    'badge' => 1,
                ],
            ],
        ];

        $payload = ['message' => $message];

        return $this->postV1($payload, $data);
    }

    /**
     * Send to multiple tokens via v1 (sequential — v1 has no batch endpoint).
     */
    private function sendBatchV1(array $tokens, array $originalData): array
    {
        $successCount = 0;
        $failureCount = 0;
        $lastMessageId = null;

        foreach ($tokens as $token) {
            $singleData = $originalData;
            unset($singleData['registration_ids']);
            $singleData['to'] = $token;

            $result = $this->sendFCMRequest($singleData);
            if ($result['success']) {
                $successCount++;
                $lastMessageId = $result['message_id'] ?? $lastMessageId;
            } else {
                $failureCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message_id' => $lastMessageId,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ];
    }

    /**
     * POST to FCM v1 endpoint with OAuth2 Bearer token.
     */
    private function postV1(array $payload, array $originalData): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'No access token'];
        }

        $ch = curl_init($this->fcmV1Url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logNotification($originalData, ['error' => $curlError], 'failed');
            return ['success' => false, 'error' => $curlError];
        }

        $result = json_decode($response, true) ?: ['raw' => $response];

        $status = ($httpCode >= 200 && $httpCode < 300) ? 'sent' : 'failed';
        $this->logNotification($originalData, $result, $status);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success'    => true,
                'message_id' => $result['name'] ?? null,
            ];
        }

        return [
            'success'  => false,
            'error'    => $result['error']['message'] ?? "HTTP $httpCode",
            'details'  => $result,
        ];
    }

    // ----------------------------------------------------------------
    //  OAuth 2.0 Access Token
    // ----------------------------------------------------------------

    /**
     * Get a valid OAuth2 access token for FCM v1.
     *
     * Priority:
     *   1. Service account JSON (proper OAuth2 — recommended)
     *   2. Legacy server key (Google accepts it as a Bearer token for v1 as of 2024)
     *   3. null (not configured)
     */
    private function getAccessToken(): ?string
    {
        // Return cached token if still valid (with 60s buffer)
        if (self::$accessToken && self::$accessTokenExpiry && time() < self::$accessTokenExpiry - 60) {
            return self::$accessToken;
        }

        // Method 1: Service account JSON → JWT → OAuth2 token
        if ($this->serviceAccountPath && file_exists($this->serviceAccountPath)) {
            return $this->getTokenFromServiceAccount();
        }

        // Method 2: Legacy server key (works as Bearer for v1 as of 2024)
        if ($this->fcmServerKey) {
            self::$accessToken = $this->fcmServerKey;
            self::$accessTokenExpiry = time() + 3600;
            return self::$accessToken;
        }

        return null;
    }

    /**
     * Exchange a Firebase service account JSON for a short-lived OAuth2 token.
     * Uses Google's JWT → token exchange (no SDK required).
     */
    private function getTokenFromServiceAccount(): ?string
    {
        $sa = json_decode(file_get_contents($this->serviceAccountPath), true);
        if (!$sa || empty($sa['client_email']) || empty($sa['private_key'])) {
            error_log('PushNotificationService: Invalid service account JSON');
            return null;
        }

        $now = time();
        $scope = 'https://www.googleapis.com/auth/firebase.messaging';

        // Build JWT
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claimSet = $this->base64Url(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => $scope,
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signInput = "$header.$claimSet";
        openssl_sign($signInput, $signature, $sa['private_key'], 'SHA256');
        $jwt = "$signInput." . $this->base64Url($signature);

        // Exchange JWT for access token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode === 200 && !empty($data['access_token'])) {
            self::$accessToken = $data['access_token'];
            self::$accessTokenExpiry = $now + ($data['expires_in'] ?? 3600);
            return self::$accessToken;
        }

        error_log('PushNotificationService: OAuth2 token exchange failed — ' . ($data['error_description'] ?? $response));
        return null;
    }

    private function base64Url($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Log notification to notification_logs table.
     */
    private function logNotification(array $data, array $result, string $status = 'sent'): void
    {
        try {
            $this->db->query(
                "INSERT INTO notification_logs (type, recipient_token, title, body, payload, response, status, created_at, tenant_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
                [
                    'push',
                    $data['to'] ?? $data['registration_ids'][0] ?? null,
                    $data['notification']['title'] ?? null,
                    $data['notification']['body'] ?? null,
                    json_encode($data),
                    json_encode($result),
                    $status,
                    $this->getTenantId(),
                ]
            );
        } catch (\Throwable $e) {
            // Table may not exist yet — fail silently
            error_log('PushNotificationService::logNotification failed: ' . $e->getMessage());
        }
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

    /**
     * Get push notification statistics
     */
    public function getStats(int $days = 30): array
    {
        try {
            $totalSent = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM push_notification_logs WHERE status = 'sent' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            );
            $totalFailed = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM push_notification_logs WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            );
            $totalTokens = $this->db->fetchColumn("SELECT COUNT(*) FROM push_tokens WHERE is_active = 1");
            $todaySent = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM push_notification_logs WHERE status = 'sent' AND DATE(created_at) = CURDATE()"
            );
            return [
                'total_sent' => (int)$totalSent,
                'total_failed' => (int)$totalFailed,
                'total_tokens' => (int)$totalTokens,
                'today_sent' => (int)$todaySent,
                'success_rate' => ($totalSent + $totalFailed) > 0 ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            return ['total_sent' => 0, 'total_failed' => 0, 'total_tokens' => 0, 'today_sent' => 0, 'success_rate' => 0];
        }
    }

    /**
     * Get recent push notification log
     */
    public function getLog(int $limit = 50): array
    {
        try {
            $tid = $this->getTenantId();
            $tenantJoin = $tid > 1 ? " AND u.tenant_id = ?" : "";
            return $this->db->fetchAll(
                "SELECT pl.*, u.name as user_name 
                 FROM push_notification_logs pl
                 LEFT JOIN users u ON pl.user_id = u.id{$tenantJoin}
                 ORDER BY pl.created_at DESC LIMIT ?",
                $tid > 1 ? [$tid, $limit] : [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Broadcast notification to all active device tokens
     */
    public function broadcast(string $title, string $body, string $url = '/'): array
    {
        return $this->sendBroadcast([
            'title' => $title,
            'body' => $body,
            'click_action' => $url,
        ]);
    }

    /**
     * Send to a single user by ID (alias for sendToUser with title/body/url)
     */
    public function send(int $userId, string $title, string $body, string $url = '/'): array
    {
        return $this->sendToUser($userId, [
            'title' => $title,
            'body' => $body,
            'click_action' => $url,
        ]);
    }
}
