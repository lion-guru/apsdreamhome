<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Push Notifications Model
 * Handles push notifications, user preferences, templates, and notification campaigns
 */
class PushNotification extends Model
{
    protected $table = 'notification_queue';

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_IN_APP = 'in_app';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const STATUS_QUEUED = 'queued';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    /**
     * Send a notification to a user
     */
    public function sendNotification(array $notificationData): array
    {
        $userId = $notificationData['user_id'];
        $userType = $notificationData['user_type'] ?? 'customer';
        $channels = $notificationData['channels'] ?? ['push'];
        $templateKey = $notificationData['template_key'] ?? null;

        // Get user preferences
        $preferences = $this->getUserPreferences($userId, $userType);

        // Get template if specified
        $template = null;
        if ($templateKey) {
            $template = $this->getNotificationTemplate($templateKey);
        }

        // Prepare notification data
        $title = $notificationData['title'] ?? ($template['title'] ?? 'Notification');
        $message = $notificationData['message'] ?? ($template['message'] ?? '');

        // Replace placeholders in title and message
        $placeholders = $notificationData['placeholders'] ?? [];
        $title = $this->replacePlaceholders($title, $placeholders);
        $message = $this->replacePlaceholders($message, $placeholders);

        $notificationId = null;
        $sentChannels = [];

        // Send through each requested channel
        foreach ($channels as $channel) {
            if ($this->isChannelEnabled($preferences, $channel)) {
                $queueId = $this->queueNotification([
                    'user_id' => $userId,
                    'user_type' => $userType,
                    'channel' => $channel,
                    'template_key' => $templateKey,
                    'title' => $title,
                    'message' => $message,
                    'data' => $notificationData['data'] ?? null,
                    'priority' => $notificationData['priority'] ?? self::PRIORITY_NORMAL,
                    'scheduled_at' => $notificationData['scheduled_at'] ?? null
                ]);

                if ($queueId) {
                    $sentChannels[] = $channel;
                }
            }
        }

        // Process immediate notifications
        if (empty($notificationData['scheduled_at'])) {
            foreach ($sentChannels as $channel) {
                $this->processQueuedNotifications($channel);
            }
        }

        return [
            'success' => !empty($sentChannels),
            'notification_id' => $notificationId,
            'channels_sent' => $sentChannels,
            'message' => 'Notification queued successfully'
        ];
    }

    /**
     * Queue a notification for sending
     */
    private function queueNotification(array $data): ?int
    {
        $db = Database::getInstance();

        $queueData = [
            'notification_id' => $data['notification_id'] ?? null,
            'user_id' => $data['user_id'],
            'user_type' => $data['user_type'],
            'channel' => $data['channel'],
            'template_key' => $data['template_key'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ? json_encode($data['data']) : null,
            'priority' => $data['priority'],
            'status' => self::STATUS_QUEUED,
            'scheduled_at' => $data['scheduled_at'],
            'created_by' => $data['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO notification_queue
             (notification_id, user_id, user_type, channel, template_key, title, message, data, priority, status, scheduled_at, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($queueData)
        );

        return $db->lastInsertId();
    }

    /**
     * Process queued notifications for a channel
     */
    public function processQueuedNotifications(string $channel): array
    {
        $db = Database::getInstance();

        // Get queued notifications for this channel
        $queued = $db->query(
            "SELECT * FROM notification_queue
             WHERE channel = ? AND status = ? AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             ORDER BY priority DESC, created_at ASC
             LIMIT 50",
            [$channel, self::STATUS_QUEUED]
        )->fetchAll();

        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach ($queued as $notification) {
            // Mark as processing
            $db->query(
                "UPDATE notification_queue SET status = ?, updated_at = ? WHERE id = ?",
                [self::STATUS_PROCESSING, date('Y-m-d H:i:s'), $notification['id']]
            );

            // Send notification based on channel
            $result = $this->sendViaChannel($notification);

            if ($result['success']) {
                $db->query(
                    "UPDATE notification_queue SET status = ?, sent_at = ?, updated_at = ? WHERE id = ?",
                    [self::STATUS_SENT, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $notification['id']]
                );
                $sent++;

                // Log to history
                $this->logNotificationHistory($notification, 'sent');
            } else {
                $db->query(
                    "UPDATE notification_queue SET status = ?, error_message = ?, retry_count = retry_count + 1, updated_at = ? WHERE id = ?",
                    [self::STATUS_FAILED, $result['error'], date('Y-m-d H:i:s'), $notification['id']]
                );
                $failed++;
            }

            $processed++;
        }

        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed
        ];
    }

    /**
     * Send notification via specific channel
     */
    private function sendViaChannel(array $notification): array
    {
        switch ($notification['channel']) {
            case self::CHANNEL_PUSH:
                return $this->sendPushNotification($notification);

            case self::CHANNEL_EMAIL:
                return $this->sendEmailNotification($notification);

            case self::CHANNEL_SMS:
                return $this->sendSMSNotification($notification);

            case self::CHANNEL_WHATSAPP:
                return $this->sendWhatsAppNotification($notification);

            case self::CHANNEL_IN_APP:
                return $this->sendInAppNotification($notification);

            default:
                return ['success' => false, 'error' => 'Unsupported channel'];
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(array $notification): array
    {
        // Get user's push subscriptions
        $subscriptions = $this->getUserPushSubscriptions($notification['user_id'], $notification['user_type']);

        if (empty($subscriptions)) {
            return ['success' => false, 'error' => 'No push subscriptions found'];
        }

        $successCount = 0;
        foreach ($subscriptions as $subscription) {
            try {
                // Here you would integrate with a push service like Firebase, OneSignal, etc.
                // For now, simulate sending
                $payload = [
                    'title' => $notification['title'],
                    'body' => $notification['message'],
                    'icon' => '/favicon.ico',
                    'badge' => '/badge.png',
                    'data' => json_decode($notification['data'], true) ?? []
                ];

                // Simulate successful send
                $successCount++;

                // Update subscription last_used
                $this->updateSubscriptionLastUsed($subscription['id']);

            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'success' => $successCount > 0,
            'error' => $successCount === 0 ? 'Failed to send to all subscriptions' : null
        ];
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(array $notification): array
    {
        try {
            // Here you would integrate with an email service
            // For now, simulate sending
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMSNotification(array $notification): array
    {
        try {
            // Here you would integrate with an SMS service like Twilio, MSG91, etc.
            // For now, simulate sending
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsAppNotification(array $notification): array
    {
        try {
            // Here you would integrate with WhatsApp Business API
            // For now, simulate sending
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send in-app notification
     */
    private function sendInAppNotification(array $notification): array
    {
        // For in-app notifications, just log to history
        // The frontend will poll for new notifications
        return ['success' => true];
    }

    /**
     * Register push subscription
     */
    public function registerPushSubscription(array $subscriptionData): array
    {
        $db = Database::getInstance();

        $subscription = [
            'user_id' => $subscriptionData['user_id'],
            'user_type' => $subscriptionData['user_type'] ?? 'customer',
            'endpoint' => $subscriptionData['endpoint'],
            'public_key' => $subscriptionData['public_key'],
            'auth_token' => $subscriptionData['auth_token'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'device_type' => $this->detectDeviceType(),
            'browser' => $this->detectBrowser(),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO push_subscriptions
             (user_id, user_type, endpoint, public_key, auth_token, user_agent, ip_address, device_type, browser, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             public_key = VALUES(public_key),
             auth_token = VALUES(auth_token),
             user_agent = VALUES(user_agent),
             ip_address = VALUES(ip_address),
             device_type = VALUES(device_type),
             browser = VALUES(browser),
             updated_at = NOW()",
            array_values($subscription)
        );

        return ['success' => true, 'message' => 'Push subscription registered'];
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences(int $userId, string $userType, array $preferences): array
    {
        $db = Database::getInstance();

        foreach ($preferences as $notificationType => $prefs) {
            $db->query(
                "INSERT INTO user_notification_preferences
                 (user_id, user_type, notification_type, email_enabled, push_enabled, sms_enabled, whatsapp_enabled, frequency, quiet_hours_start, quiet_hours_end)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 email_enabled = VALUES(email_enabled),
                 push_enabled = VALUES(push_enabled),
                 sms_enabled = VALUES(sms_enabled),
                 whatsapp_enabled = VALUES(whatsapp_enabled),
                 frequency = VALUES(frequency),
                 quiet_hours_start = VALUES(quiet_hours_start),
                 quiet_hours_end = VALUES(quiet_hours_end),
                 updated_at = NOW()",
                [
                    $userId,
                    $userType,
                    $notificationType,
                    $prefs['email_enabled'] ?? 1,
                    $prefs['push_enabled'] ?? 1,
                    $prefs['sms_enabled'] ?? 0,
                    $prefs['whatsapp_enabled'] ?? 0,
                    $prefs['frequency'] ?? 'immediate',
                    $prefs['quiet_hours_start'] ?? null,
                    $prefs['quiet_hours_end'] ?? null
                ]
            );
        }

        return ['success' => true, 'message' => 'Preferences updated successfully'];
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId, string $userType): array
    {
        $db = Database::getInstance();

        $preferences = $db->query(
            "SELECT * FROM user_notification_preferences
             WHERE user_id = ? AND user_type = ?",
            [$userId, $userType]
        )->fetchAll();

        // Convert to associative array by notification type
        $prefsByType = [];
        foreach ($preferences as $pref) {
            $prefsByType[$pref['notification_type']] = $pref;
        }

        return $prefsByType;
    }

    /**
     * Get notification template
     */
    private function getNotificationTemplate(string $templateKey): ?array
    {
        return $this->query(
            "SELECT * FROM notification_templates WHERE template_key = ? AND is_active = 1",
            [$templateKey]
        )->fetch();
    }

    /**
     * Check if channel is enabled for user
     */
    private function isChannelEnabled(array $preferences, string $channel): bool
    {
        // Check general preferences first
        if (isset($preferences['general'])) {
            $generalPrefs = $preferences['general'];
            switch ($channel) {
                case self::CHANNEL_EMAIL:
                    return $generalPrefs['email_enabled'] ?? true;
                case self::CHANNEL_PUSH:
                    return $generalPrefs['push_enabled'] ?? true;
                case self::CHANNEL_SMS:
                    return $generalPrefs['sms_enabled'] ?? false;
                case self::CHANNEL_WHATSAPP:
                    return $generalPrefs['whatsapp_enabled'] ?? false;
            }
        }

        // Default to enabled for push, disabled for others
        return $channel === self::CHANNEL_PUSH;
    }

    /**
     * Get user push subscriptions
     */
    private function getUserPushSubscriptions(int $userId, string $userType): array
    {
        return $this->query(
            "SELECT * FROM push_subscriptions
             WHERE user_id = ? AND user_type = ? AND is_active = 1",
            [$userId, $userType]
        )->fetchAll();
    }

    /**
     * Update subscription last used
     */
    private function updateSubscriptionLastUsed(int $subscriptionId): void
    {
        $this->query(
            "UPDATE push_subscriptions SET last_used = NOW() WHERE id = ?",
            [$subscriptionId]
        );
    }

    /**
     * Log notification to history
     */
    private function logNotificationHistory(array $notification, string $status): void
    {
        $db = Database::getInstance();

        $historyData = [
            'user_id' => $notification['user_id'],
            'user_type' => $notification['user_type'],
            'notification_type' => $notification['template_key'] ?? 'custom',
            'channel' => $notification['channel'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'status' => $status,
            'reference_type' => json_decode($notification['data'], true)['reference_type'] ?? null,
            'reference_id' => json_decode($notification['data'], true)['reference_id'] ?? null,
            'sent_at' => date('Y-m-d H:i:s'),
            'device_info' => json_encode([
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        $db->query(
            "INSERT INTO notification_history
             (user_id, user_type, notification_type, channel, title, message, status, reference_type, reference_id, sent_at, device_info, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($historyData)
        );
    }

    /**
     * Replace placeholders in text
     */
    private function replacePlaceholders(string $text, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }

    /**
     * Detect device type
     */
    private function detectDeviceType(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Detect browser
     */
    private function detectBrowser(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/chrome/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(string $period = '30 days'): array
    {
        $db = Database::getInstance();

        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $stats = $db->query(
            "SELECT
                COUNT(*) as total_sent,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                channel
             FROM notification_history
             WHERE sent_at >= ?
             GROUP BY channel",
            [$startDate]
        )->fetchAll();

        return [
            'period' => $period,
            'start_date' => $startDate,
            'channel_stats' => $stats,
            'total_notifications' => array_sum(array_column($stats, 'total_sent'))
        ];
    }

    /**
     * Create notification campaign
     */
    public function createCampaign(array $campaignData): array
    {
        $db = Database::getInstance();

        // Calculate target audience count
        $audienceCount = $this->calculateAudienceCount($campaignData['target_audience']);

        $campaign = [
            'campaign_name' => $campaignData['campaign_name'],
            'campaign_type' => $campaignData['campaign_type'] ?? 'marketing',
            'target_audience' => json_encode($campaignData['target_audience']),
            'template_key' => $campaignData['template_key'],
            'scheduled_at' => $campaignData['scheduled_at'] ?? null,
            'status' => $campaignData['scheduled_at'] ? 'scheduled' : 'draft',
            'total_recipients' => $audienceCount,
            'channels' => json_encode($campaignData['channels']),
            'budget_limit' => $campaignData['budget_limit'] ?? null,
            'cost_per_notification' => $campaignData['cost_per_notification'] ?? null,
            'created_by' => $campaignData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO notification_campaigns
             (campaign_name, campaign_type, target_audience, template_key, scheduled_at, status, total_recipients, channels, budget_limit, cost_per_notification, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($campaign)
        );

        return [
            'success' => true,
            'campaign_id' => $db->lastInsertId(),
            'message' => 'Campaign created successfully'
        ];
    }

    /**
     * Calculate audience count for campaign
     */
    private function calculateAudienceCount(array $criteria): int
    {
        // This would implement complex audience calculation logic
        // For now, return a placeholder
        return 100; // Placeholder count
    }

    /**
     * Get user's in-app notifications
     */
    public function getUserNotifications(int $userId, string $userType, int $limit = 20): array
    {
        return $this->query(
            "SELECT * FROM notification_history
             WHERE user_id = ? AND user_type = ? AND channel = ?
             ORDER BY sent_at DESC LIMIT ?",
            [$userId, $userType, self::CHANNEL_IN_APP, $limit]
        )->fetchAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): array
    {
        $notification = $this->query(
            "SELECT * FROM notification_history WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        )->fetch();

        if (!$notification) {
            return ['success' => false, 'message' => 'Notification not found'];
        }

        $this->query(
            "UPDATE notification_history SET status = 'read', read_at = NOW() WHERE id = ?",
            [$notificationId]
        );

        return ['success' => true, 'message' => 'Notification marked as read'];
    }
}
