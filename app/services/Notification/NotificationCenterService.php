<?php

namespace App\Services\Notification;

use App\Core\Database\Database;

/**
 * Notification Center Service
 * Unified notification system for all channels
 */
class NotificationCenterService
{
    private $database;
    private $channels;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->channels = [
            'database' => true,
            'email' => true,
            'sms' => true,
            'push' => true,
            'whatsapp' => true
        ];
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure notification tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Notifications table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Notification preferences
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Notification templates
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Notification delivery logs
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Seed default templates
        $this->seedTemplates();
    }
    
    /**
     * Seed notification templates
     */
    private function seedTemplates(): void
    {
        $templates = [
            [
                'booking_confirmed',
                'Booking Confirmed',
                'Sent when a booking is confirmed',
                'Booking Confirmed: {property_name}',
                'Your booking for {property_name} has been confirmed. Booking ID: {booking_id}. Amount: ₹{amount}',
                json_encode(['property_name', 'booking_id', 'amount']),
                json_encode(['database', 'email', 'sms']),
                'high'
            ],
            [
                'payment_received',
                'Payment Received',
                'Sent when payment is received',
                'Payment Received: ₹{amount}',
                'We have received your payment of ₹{amount} for {property_name}. Transaction ID: {transaction_id}',
                json_encode(['amount', 'property_name', 'transaction_id']),
                json_encode(['database', 'email', 'push']),
                'normal'
            ],
            [
                'site_visit_reminder',
                'Site Visit Reminder',
                'Reminder for upcoming site visit',
                'Reminder: Site Visit Tomorrow',
                'This is a reminder for your site visit scheduled for {visit_date} at {visit_time} for {property_name}',
                json_encode(['visit_date', 'visit_time', 'property_name']),
                json_encode(['database', 'email', 'sms', 'push']),
                'high'
            ],
            [
                'price_drop_alert',
                'Price Drop Alert',
                'Alert for price drop on wishlisted property',
                'Price Drop: {property_name}',
                'Good news! The price of {property_name} has dropped from ₹{old_price} to ₹{new_price}. Save ₹{savings}!',
                json_encode(['property_name', 'old_price', 'new_price', 'savings']),
                json_encode(['database', 'email', 'push']),
                'normal'
            ],
            [
                'new_property_match',
                'New Property Match',
                'New property matching saved search',
                'New Property: {property_name}',
                'A new property matching your search criteria is available: {property_name} in {location} for ₹{price}',
                json_encode(['property_name', 'location', 'price']),
                json_encode(['database', 'email', 'push']),
                'normal'
            ],
            [
                'lead_assigned',
                'Lead Assigned',
                'New lead assigned to agent',
                'New Lead Assigned',
                'A new lead has been assigned to you: {customer_name} - {customer_phone}. Interested in: {property_type}',
                json_encode(['customer_name', 'customer_phone', 'property_type']),
                json_encode(['database', 'email', 'push']),
                'urgent'
            ],
            [
                'emi_due_reminder',
                'EMI Due Reminder',
                'Reminder for upcoming EMI payment',
                'EMI Due: ₹{amount}',
                'Your EMI payment of ₹{amount} is due on {due_date}. Please ensure sufficient balance.',
                json_encode(['amount', 'due_date']),
                json_encode(['database', 'email', 'sms', 'push']),
                'urgent'
            ],
            [
                'chat_message',
                'New Chat Message',
                'New message in chat',
                'New Message from {sender_name}',
                'You have a new message: {message_preview}',
                json_encode(['sender_name', 'message_preview']),
                json_encode(['database', 'push']),
                'normal'
            ],
            [
                'commission_credited',
                'Commission Credited',
                'Commission credited to associate',
                'Commission Credited: ₹{amount}',
                'Congratulations! Commission of ₹{amount} has been credited for {customer_name}\'s booking.',
                json_encode(['amount', 'customer_name']),
                json_encode(['database', 'email', 'push']),
                'high'
            ],
            [
                'account_verified',
                'Account Verified',
                'Account verification complete',
                'Account Verified Successfully',
                'Your account has been verified. You can now access all features of APS Dream Home.',
                json_encode([]),
                json_encode(['database', 'email']),
                'normal'
            ]
        ];
        
        try {
            $sql = "INSERT INTO notification_templates 
                (template_code, template_name, channel, subject, body, variables, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE 
                template_name = VALUES(template_name),
                subject = VALUES(subject),
                body = VALUES(body),
                variables = VALUES(variables)";
            $stmt = $this->database->prepare($sql);
            foreach ($templates as $t) {
                $channels = json_decode($t[6], true) ?: [];
                $channel = 'email';
                foreach ($channels as $c) {
                    if (in_array($c, ['email', 'sms', 'whatsapp', 'push', 'in_app'])) {
                        $channel = $c;
                        break;
                    }
                }
                $stmt->execute([
                    $t[0], // template_code
                    $t[1], // template_name
                    $channel, // channel
                    $t[3], // subject (title_template)
                    $t[4], // body (message_template)
                    $t[5], // variables
                ]);
            }
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref or schema mismatch
        }
    }
    
    /**
     * Send notification
     */
    public function send(int $userId, string $userType, string $type, array $data = [], array $options = []): array
    {
        try {
            // Get template
            $template = $this->getTemplate($type);
            if (!$template) {
                return ['success' => false, 'error' => 'Template not found: ' . $type];
            }
            
            // Get user preferences
            $preferences = $this->getUserPreferences($userId, $userType);
            
            // Determine channels based on preferences and template defaults
            $channels = $options['channels'] ?? $template['default_channels'];
            $channels = json_decode($channels, true);
            
            // Check quiet hours
            if ($this->isQuietHours($userId, $userType)) {
                // Filter to database only during quiet hours
                $channels = ['database'];
            }
            
            // Prepare notification
            $title = $this->interpolateTemplate($template['title_template'], $data);
            $message = $this->interpolateTemplate($template['message_template'], $data);
            
            $notification = [
                'user_id' => $userId,
                'user_type' => $userType,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'channels' => json_encode($channels),
                'priority' => $options['priority'] ?? $template['default_priority'],
                'action_url' => $options['action_url'] ?? null,
                'action_text' => $options['action_text'] ?? null,
                'image_url' => $options['image_url'] ?? null,
                'icon' => $options['icon'] ?? $this->getIconForType($type),
                'color' => $options['color'] ?? $this->getColorForType($type),
                'expires_at' => $options['expires_at'] ?? null
            ];
            
            // Save to database
            $sql = "INSERT INTO notifications 
                (user_id, user_type, type, title, message, data, channels, priority, 
                 action_url, action_text, image_url, icon, color, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $notification['user_id'],
                $notification['user_type'],
                $notification['type'],
                $notification['title'],
                $notification['message'],
                $notification['data'],
                $notification['channels'],
                $notification['priority'],
                $notification['action_url'],
                $notification['action_text'],
                $notification['image_url'],
                $notification['icon'],
                $notification['color'],
                $notification['expires_at']
            ]);
            
            $notificationId = $this->database->lastInsertId();
            
            // Send via channels
            $sentVia = [];
            foreach ($channels as $channel) {
                $result = $this->sendViaChannel($notificationId, $channel, $notification);
                if ($result['success']) {
                    $sentVia[] = $channel;
                }
            }
            
            // Update notification status
            $updateSql = "UPDATE notifications SET 
                status = ?,
                sent_via = ?
                WHERE id = ?";
            
            $updateStmt = $this->database->prepare($updateSql);
            $updateStmt->execute([
                empty($sentVia) ? 'failed' : 'sent',
                json_encode($sentVia),
                $notificationId
            ]);
            
            return [
                'success' => true,
                'notification_id' => $notificationId,
                'sent_via' => $sentVia
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send via specific channel
     */
    private function sendViaChannel(int $notificationId, string $channel, array $notification): array
    {
        $startTime = microtime(true);
        
        try {
            switch ($channel) {
                case 'database':
                    // Already saved in database
                    $result = ['success' => true];
                    break;
                    
                case 'email':
                    // Use EmailQueueService
                    $result = $this->sendEmail($notification);
                    break;
                    
                case 'sms':
                    // Use CommunicationService
                    $result = $this->sendSMS($notification);
                    break;
                    
                case 'push':
                    // Use Firebase/OneSignal
                    $result = $this->sendPush($notification);
                    break;
                    
                case 'whatsapp':
                    // Use WhatsApp Business API
                    $result = $this->sendWhatsApp($notification);
                    break;
                    
                default:
                    $result = ['success' => false, 'error' => 'Unknown channel'];
            }
            
            // Log delivery
            $this->logDelivery($notificationId, $channel, $result);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logDelivery($notificationId, $channel, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user notifications
     */
    public function getNotifications(int $userId, string $userType, array $filters = []): array
    {
        $sql = "SELECT * FROM notifications 
            WHERE user_id = ? AND user_type = ?";
        
        $params = [$userId, $userType];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['unread_only'])) {
            $sql .= " AND read_at IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int) $filters['limit'];
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead(int $notificationId, int $userId, string $userType): bool
    {
        $sql = "UPDATE notifications SET
            status = 'read',
            read_at = NOW()
            WHERE id = ? AND user_id = ? AND user_type = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->execute([$notificationId, $userId, $userType]);

        // Invalidate the unread-count cache for this user so the badge refreshes.
        \App\Services\CacheService::invalidateUnreadCount($userId);

        return $stmt->rowCount() > 0;
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead(int $userId, string $userType): int
    {
        $sql = "UPDATE notifications SET 
            status = 'read',
            read_at = NOW()
            WHERE user_id = ? AND user_type = ? AND read_at IS NULL";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId, string $userType): int
    {
        $sql = "SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? AND user_type = ? AND read_at IS NULL";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Delete notification
     */
    public function delete(int $notificationId, int $userId, string $userType): bool
    {
        $sql = "DELETE FROM notifications 
            WHERE id = ? AND user_id = ? AND user_type = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$notificationId, $userId, $userType]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get template
     */
    private function getTemplate(string $type): ?array
    {
        try {
            $sql = "SELECT * FROM notification_templates WHERE template_code = ? AND is_active = 1";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$type]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'type' => $row['template_code'],
                    'name' => $row['template_name'],
                    'description' => $row['template_name'],
                    'title_template' => $row['subject'],
                    'message_template' => $row['body'],
                    'data_schema' => $row['variables'],
                    'default_channels' => json_encode([$row['channel'], 'database']),
                    'default_priority' => 'normal',
                    'is_system' => 1,
                    'is_active' => $row['is_active']
                ];
            }
        } catch (\Throwable $e) {
            // Gracefully handle errors
        }
        return null;
    }
    
    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId, string $userType): array
    {
        $sql = "SELECT * FROM notification_preferences 
            WHERE user_id = ? AND user_type = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check quiet hours
     */
    private function isQuietHours(int $userId, string $userType): bool
    {
        // Check if current time is in user's quiet hours
        // Simplified - returns false for now
        return false;
    }
    
    /**
     * Interpolate template
     */
    private function interpolateTemplate(string $template, array $data): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($data) {
            return $data[$matches[1]] ?? $matches[0];
        }, $template);
    }
    
    /**
     * Get icon for type
     */
    private function getIconForType(string $type): string
    {
        $icons = [
            'booking_confirmed' => 'fa-check-circle',
            'payment_received' => 'fa-money-bill',
            'site_visit_reminder' => 'fa-calendar',
            'price_drop_alert' => 'fa-tag',
            'new_property_match' => 'fa-home',
            'lead_assigned' => 'fa-user-plus',
            'emi_due_reminder' => 'fa-bell',
            'chat_message' => 'fa-comments',
            'commission_credited' => 'fa-award',
            'account_verified' => 'fa-shield-alt'
        ];
        
        return $icons[$type] ?? 'fa-bell';
    }
    
    /**
     * Get color for type
     */
    private function getColorForType(string $type): string
    {
        $colors = [
            'booking_confirmed' => '#28a745',
            'payment_received' => '#17a2b8',
            'site_visit_reminder' => '#ffc107',
            'price_drop_alert' => '#dc3545',
            'new_property_match' => '#007bff',
            'lead_assigned' => '#6f42c1',
            'emi_due_reminder' => '#fd7e14',
            'chat_message' => '#20c997',
            'commission_credited' => '#28a745',
            'account_verified' => '#28a745'
        ];
        
        return $colors[$type] ?? '#6c757d';
    }
    
    /**
     * Send email (placeholder)
     */
    private function sendEmail(array $notification): array
    {
        // Integration with EmailQueueService
        return ['success' => true];
    }
    
    /**
     * Send SMS (placeholder)
     */
    private function sendSMS(array $notification): array
    {
        // Integration with CommunicationService
        return ['success' => true];
    }
    
    /**
     * Send push notification (placeholder)
     */
    private function sendPush(array $notification): array
    {
        // Integration with Firebase/OneSignal
        return ['success' => true];
    }
    
    /**
     * Send WhatsApp (placeholder)
     */
    private function sendWhatsApp(array $notification): array
    {
        // Integration with WhatsApp Business API
        return ['success' => true];
    }
    
    /**
     * Log delivery
     */
    private function logDelivery(int $notificationId, string $channel, array $result): void
    {
        $sql = "INSERT INTO notification_delivery_logs 
            (notification_id, channel, status, provider_response, error_message)
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $notificationId,
            $channel,
            $result['success'] ? 'sent' : 'failed',
            json_encode($result['response'] ?? []),
            $result['error'] ?? null
        ]);
    }
    
    /**
     * Create notification preference
     */
    public function setPreference(int $userId, string $userType, string $channel, 
        string $notificationType, bool $enabled, array $options = []): bool
    {
        $sql = "INSERT INTO notification_preferences 
            (user_id, user_type, channel, notification_type, is_enabled, 
             quiet_hours_start, quiet_hours_end, frequency)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            is_enabled = VALUES(is_enabled),
            quiet_hours_start = VALUES(quiet_hours_start),
            quiet_hours_end = VALUES(quiet_hours_end),
            frequency = VALUES(frequency)";
        
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([
            $userId,
            $userType,
            $channel,
            $notificationType,
            $enabled ? 1 : 0,
            $options['quiet_hours_start'] ?? null,
            $options['quiet_hours_end'] ?? null,
            $options['frequency'] ?? 'immediate'
        ]);
    }
    
    /**
     * Get notification stats
     */
    public function getStats(string $dateFrom = null, string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');
        
        $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as unread_count,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
            COUNT(DISTINCT user_id) as unique_users
            FROM notifications 
            WHERE DATE(created_at) BETWEEN ? AND ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Cleanup old notifications
     */
    public function cleanup(int $days = 30): int
    {
        $sql = "DELETE FROM notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
            AND status = 'read'";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$days]);
        
        return $stmt->rowCount();
    }
}
