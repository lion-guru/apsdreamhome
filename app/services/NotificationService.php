<?php
namespace App\Services;

use PDO;

/**
 * NotificationService - multi-channel notification (email, SMS, push, WhatsApp, in-app)
 */
class NotificationService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function send(int $userId, string $channel, string $subject, string $message, array $data = []): array
    {
        // Respect customer notification preferences. If the caller passes
        // 'notification_type' in $data, the user's channel toggle for that
        // type is consulted; if the channel is disabled we skip delivery
        // and persist a 'skipped' record for auditability.
        $notificationType = $data['notification_type'] ?? null;
        if ($notificationType && !$this->isChannelEnabled($userId, $notificationType, $channel)) {
            $this->logRealtime($userId, $channel, $subject, $message, $data, 'skipped');
            return ['ok' => false, 'id' => 0, 'skipped' => true, 'reason' => 'channel_disabled_by_user'];
        }

        $template = $this->getTemplate($data['template_code'] ?? $channel);

        $id = $this->logRealtime($userId, $channel, $subject, $message, $data, 'pending');

        switch ($channel) {
            case 'email': $this->trackEmail($id, $userId, $subject, $message, $data); break;
            case 'sms': $this->trackSms($id, $userId, $message, $data); break;
            case 'push': $this->sendPush($userId, $subject, $message, $data); break;
            case 'whatsapp': $this->sendWhatsapp($userId, $message, $data); break;
        }

        $this->markRealtimeSent($id);

        return ['ok' => true, 'id' => $id];
    }

    /**
     * Insert a realtime_notifications row using the actual schema
     * (channel_name, event_type, payload, delivered_at, read_at, expires_at, created_at).
     * Returns the inserted id, or 0 on failure.
     */
    private function logRealtime(int $userId, string $channel, string $subject, string $message, array $data, string $status): int
    {
        $payload = json_encode(['subject' => $subject, 'message' => $message, 'data' => $data, 'status' => $status], JSON_UNESCAPED_UNICODE);
        $eventType = $data['event_type'] ?? ('pref_' . $status);
        $sql = "INSERT INTO realtime_notifications (channel_name, user_id, event_type, payload, delivered_at, created_at)
                VALUES (:c, :u, :e, :p, :d, NOW())";
        try {
            $st = $this->db->prepare($sql);
            $st->execute([
                ':c' => $channel,
                ':u' => $userId,
                ':e' => $eventType,
                ':p' => $payload,
                ':d' => $status === 'sent' || $status === 'pending' ? date('Y-m-d H:i:s') : null,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('NotificationService::logRealtime error: ' . $e->getMessage());
            return 0;
        }
    }

    private function markRealtimeSent(int $id): void
    {
        if ($id <= 0) return;
        try {
            $this->db->prepare("UPDATE realtime_notifications SET delivered_at = NOW() WHERE id = :id")
                ->execute([':id' => $id]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Check whether the user has the given channel enabled for the given
     * notification type. Returns true when no preference row exists yet
     * (default opt-in behaviour). Critical/security notification types
     * bypass the check.
     */
    public function isChannelEnabled(int $userId, string $notificationType, string $channel): bool
    {
        $criticalTypes = ['security', 'password_reset', '2fa', 'login_alert', 'fraud'];
        if (in_array($notificationType, $criticalTypes, true)) {
            return true;
        }

        $columnMap = [
            'email'    => 'email_enabled',
            'sms'      => 'sms_enabled',
            'whatsapp' => 'whatsapp_enabled',
            'push'     => 'push_enabled',
        ];
        if (!isset($columnMap[$channel])) {
            return true;
        }
        $col = $columnMap[$channel];

        try {
            $st = $this->db->prepare(
                "SELECT {$col} AS enabled
                 FROM user_notification_preferences
                 WHERE user_id = ? AND notification_type = ?"
            );
            $st->execute([$userId, $notificationType]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                // No preference row yet - default to enabled
                return true;
            }
            return (int) $row['enabled'] === 1;
        } catch (\Throwable $e) {
            // If the table is missing or the query fails, default to enabled
            // so that we don't accidentally silence all notifications.
            error_log('NotificationService::isChannelEnabled error: ' . $e->getMessage());
            return true;
        }
    }

    public function getTemplate(string $code): ?array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM notification_templates WHERE template_code = :c AND is_active = 1 LIMIT 1");
            $st->execute([':c' => $code]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return $r ?: null;
        } catch (\Throwable $e) {
            error_log('NotificationService::getTemplate error: ' . $e->getMessage());
            return null;
        }
    }

    public function saveTemplate(string $code, string $channel, string $subject, string $body, array $variables = [], string $templateName = ''): array
    {
        try {
            $name = $templateName !== '' ? $templateName : $code;
            $st = $this->db->prepare("INSERT INTO notification_templates (template_code, template_name, channel, subject, body, variables, is_active, created_at)
                                      VALUES (:c, :n, :ch, :s, :b, :v, 1, NOW())
                                      ON DUPLICATE KEY UPDATE template_name = VALUES(template_name), subject = VALUES(subject), body = VALUES(body), variables = VALUES(variables), is_active = 1, updated_at = NOW()");
            $st->execute([':c' => $code, ':n' => $name, ':ch' => $channel, ':s' => $subject, ':b' => $body, ':v' => json_encode($variables, JSON_UNESCAPED_UNICODE)]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            error_log('NotificationService::saveTemplate error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function listTemplates(string $channel = ''): array
    {
        try {
            $sql = "SELECT * FROM notification_templates WHERE 1=1";
            $params = [];
            if ($channel) { $sql .= " AND channel = :c"; $params[':c'] = $channel; }
            $sql .= " ORDER BY template_code";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('NotificationService::listTemplates error: ' . $e->getMessage());
            return [];
        }
    }

    public function render(string $templateCode, array $vars): array
    {
        $tpl = $this->getTemplate($templateCode);
        if (!$tpl) return ['error' => 'Template not found'];
        $subject = $this->replaceVars($tpl['subject'], $vars);
        $body = $this->replaceVars($tpl['body'], $vars);
        return ['subject' => $subject, 'body' => $body, 'channel' => $tpl['channel']];
    }

    private function replaceVars(string $str, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $str = str_replace(['{{' . $k . '}}', '{' . $k . '}'], $v, $str);
        }
        return $str;
    }

    private function trackEmail(int $notifId, int $userId, string $subject, string $body, array $data): void
    {
        try {
            $st = $this->db->prepare("SELECT email, name FROM users WHERE id = :u");
            $st->execute([':u' => $userId]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            $to = $data['email'] ?? $u['email'] ?? '';
            $st2 = $this->db->prepare("INSERT INTO email_tracking (email_id, recipient, event_type, ip_address, user_agent, event_at) VALUES (:n, :e, 'sent', :ip, :ua, NOW())");
            $st2->execute([':n' => $notifId, ':e' => $to, ':ip' => $_SERVER['REMOTE_ADDR'] ?? null, ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null]);
        } catch (\Throwable $e) {
            // table might not have the columns we expect; ignore
        }
    }

    private function trackSms(int $notifId, int $userId, string $message, array $data): void
    {
        try {
            $st = $this->db->prepare("SELECT phone FROM users WHERE id = :u");
            $st->execute([':u' => $userId]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            $to = $data['phone'] ?? $u['phone'] ?? '';
            $st2 = $this->db->prepare("INSERT INTO email_tracking (email_id, recipient, event_type, ip_address, user_agent, event_at) VALUES (:n, :e, 'sms_sent', :ip, :ua, NOW())");
            $st2->execute([':n' => $notifId, ':e' => $to, ':ip' => $_SERVER['REMOTE_ADDR'] ?? null, ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function sendPush(int $userId, string $title, string $body, array $data): void
    {
        $st = $this->db->prepare("SELECT * FROM push_subscriptions WHERE user_id = :u AND active = 1");
        $st->execute([':u' => $userId]);
        $subs = $st->fetchAll(PDO::FETCH_ASSOC);

        $st2 = $this->db->prepare("INSERT INTO push_notifications (user_id, title, body, data, sent_at, created_at) VALUES (:u, :t, :b, :d, NOW(), NOW())");
        $st2->execute([':u' => $userId, ':t' => $title, ':b' => $body, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
    }

    private function sendWhatsapp(int $userId, string $message, array $data): void
    {
        $st = $this->db->prepare("SELECT phone FROM users WHERE id = :u");
        $st->execute([':u' => $userId]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        $to = $data['phone'] ?? $u['phone'] ?? '';
        $st2 = $this->db->prepare("INSERT INTO whatsapp_messages (user_id, to_phone, message, status, sent_at, created_at) VALUES (:u, :p, :m, 'sent', NOW(), NOW())");
        try { $st2->execute([':u' => $userId, ':p' => $to, ':m' => $message]); } catch (\Throwable $e) {}
    }

    public function shareLead(int $userId, int $leadId, string $to, string $channel = 'whatsapp'): array
    {
        $st = $this->db->prepare("INSERT INTO whatsapp_lead_shares (user_id, lead_id, shared_to, channel, shared_at) VALUES (:u, :l, :t, :c, NOW())");
        $st->execute([':u' => $userId, ':l' => $leadId, ':t' => $to, ':c' => $channel]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getUserNotifications(int $userId, int $limit = 50): array
    {
        $st = $this->db->prepare("SELECT * FROM realtime_notifications WHERE user_id = :u ORDER BY created_at DESC LIMIT :lim");
        $st->bindValue(':u', $userId, PDO::PARAM_INT);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSettings(int $userId = 0): array
    {
        $sql = "SELECT * FROM notification_settings WHERE 1=1";
        $params = [];
        if ($userId) { $sql .= " AND user_id = :u"; $params[':u'] = $userId; }
        $sql .= " ORDER BY user_id, channel";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSetting(int $userId, string $channel, bool $enabled, array $prefs = []): array
    {
        $st = $this->db->prepare("INSERT INTO notification_settings (user_id, channel, enabled, preferences, updated_at) VALUES (:u, :c, :e, :p, NOW())
                                  ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), preferences = VALUES(preferences), updated_at = NOW()");
        $st->execute([':u' => $userId, ':c' => $channel, ':e' => $enabled ? 1 : 0, ':p' => json_encode($prefs, JSON_UNESCAPED_UNICODE)]);
        return ['ok' => true];
    }

    public function getSmsTemplates(): array
    {
        try {
            $st = $this->db->query("SELECT * FROM sms_templates WHERE is_active = 1 ORDER BY template_code");
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('NotificationService::getSmsTemplates error: ' . $e->getMessage());
            return [];
        }
    }

    public function saveSmsTemplate(string $code, string $body, string $templateName = ''): array
    {
        try {
            $name = $templateName !== '' ? $templateName : $code;
            $st = $this->db->prepare("INSERT INTO sms_templates (template_code, template_name, body, is_active, created_at) VALUES (:c, :n, :b, 1, NOW())
                                      ON DUPLICATE KEY UPDATE template_name = VALUES(template_name), body = VALUES(body), is_active = 1");
            $st->execute([':c' => $code, ':n' => $name, ':b' => $body]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            error_log('NotificationService::saveSmsTemplate error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // =====================================================================
    // ADMIN NOTIFICATION FEED (notifications table)
    // =====================================================================

    /**
     * Insert an admin notification into the `notifications` table.
     * Replaces AdminNotificationService::notify().
     */
    public function notify(string $type, string $message, ?int $userId = null, ?string $actionUrl = null, ?string $title = null): bool
    {
        try {
            $this->db->prepare(
                'INSERT INTO notifications (user_id, type, title, message, action_url, is_read, status, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())'
            )->execute([$userId, $type, $title ?? ucfirst($type), $message, $actionUrl, 'unread']);
            return true;
        } catch (\Throwable $e) {
            error_log('NotificationService::notify error: ' . $e->getMessage());
            return false;
        }
    }

    public function getUnread(?int $userId = null, int $limit = 20): array
    {
        try {
            $sql = 'SELECT * FROM notifications WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $sql .= ' ORDER BY created_at DESC LIMIT ?';
            $params[] = $limit;
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getRecent(?int $userId = null, int $limit = 50): array
    {
        try {
            $sql = 'SELECT * FROM notifications';
            $params = [];
            if ($userId) {
                $sql .= ' WHERE (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $sql .= ' ORDER BY created_at DESC LIMIT ?';
            $params[] = $limit;
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get customer-facing notifications from `notifications` table.
     * Replaces Communication\NotificationService::getCustomerNotifications().
     */
    public function getCustomerNotifications(int $userId, int $limit = 20): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            $st->execute([$userId, $limit]);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Count unread notifications from `notifications` table.
     * Replaces AdminNotificationService::getUnreadCount() and
     * Communication\NotificationService::getUnreadCount().
     */
    public function getUnreadCount(?int $userId = null): int
    {
        try {
            $sql = 'SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $st = $this->db->prepare($sql);
            $st->execute($params);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['cnt'] : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Mark a single notification as read.
     * Replaces AdminNotificationService::markRead() and
     * Communication\NotificationService::markAsRead().
     */
    public function markRead(int $id): bool
    {
        try {
            $this->db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?')->execute([(int)$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Alias for markRead() — matches Communication\NotificationService API.
     */
    public function markAsRead(int $notificationId): bool
    {
        return $this->markRead($notificationId);
    }

    /**
     * Mark all notifications as read for a user.
     * Replaces AdminNotificationService::markAllRead() and
     * Communication\NotificationService::markAllAsRead().
     */
    public function markAllRead(?int $userId = null): bool
    {
        try {
            $sql = 'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            $this->db->prepare($sql)->execute($params);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Alias for markAllRead() — matches Communication\NotificationService API.
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->markAllRead($userId);
    }

    // =====================================================================
    // BOOKING LIFECYCLE NOTIFICATIONS
    // =====================================================================

    /**
     * Get the customer user_id from a booking.
     */
    private function getBookingCustomerUserId(int $bookingId): ?int
    {
        try {
            $st = $this->db->prepare("SELECT customer_id, user_id FROM bookings WHERE id = ?");
            $st->execute([$bookingId]);
            $booking = $st->fetch(PDO::FETCH_ASSOC);
            if (!$booking) return null;
            return (int)($booking['customer_id'] ?? $booking['user_id'] ?? 0) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Send multi-channel booking confirmed notification.
     * Replaces Communication\NotificationService::sendBookingConfirmed().
     */
    public function sendBookingConfirmed(int $bookingId): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Booking Confirmed';
        $message = 'Your booking has been confirmed. Please check your account for plot details and payment information.';
        $data = ['event_type' => 'booking', 'booking_id' => $bookingId, 'action_url' => '/user/bookings/' . $bookingId, 'priority' => 'high'];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'sms', $title, $message, $data);
        $this->send($userId, 'push', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    public function sendBookingConfirmedEmail(int $bookingId): void
    {
        $this->sendBookingConfirmed($bookingId);
    }

    public function sendAgreementGenerated(int $bookingId, string $agreementType): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $typeLabel = ucwords(str_replace('_', ' ', $agreementType));
        $title = $typeLabel . ' Ready';
        $message = 'Your ' . $typeLabel . ' has been generated. You can download it from your account.';
        $data = ['event_type' => 'agreement', 'booking_id' => $bookingId, 'agreement_type' => $agreementType, 'action_url' => '/user/bookings/' . $bookingId];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    public function sendPaymentReceived(int $bookingId, float $amount): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Payment Received';
        $message = 'Payment of ₹' . number_format($amount) . ' has been received for your booking.';
        $data = ['event_type' => 'payment', 'booking_id' => $bookingId, 'amount' => $amount, 'action_url' => '/user/bookings/' . $bookingId];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'sms', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    public function sendRegistryUpdate(int $bookingId, string $status): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $statusLabels = [
            'documents_pending' => 'Documents Pending',
            'stamp_duty_pending' => 'Stamp Duty Pending',
            'appointment_scheduled' => 'Registry Appointment Scheduled',
            'registered' => 'Property Registered',
            'completed' => 'Registry Completed',
        ];
        $label = $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status));

        $title = 'Registry Update';
        $message = 'Your registry status has been updated to: ' . $label . '.';
        $data = ['event_type' => 'registry', 'booking_id' => $bookingId, 'registry_status' => $status, 'action_url' => '/user/bookings/' . $bookingId];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    public function sendPossessionScheduled(int $bookingId, string $date): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Possession Scheduled';
        $message = 'Your property possession has been scheduled for ' . date('d F Y', strtotime($date)) . '.';
        $data = ['event_type' => 'possession', 'booking_id' => $bookingId, 'possession_date' => $date, 'action_url' => '/user/bookings/' . $bookingId, 'priority' => 'high'];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'sms', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    public function sendPossessionCompleted(int $bookingId): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'Possession Completed';
        $message = 'Congratulations! Your property possession has been completed. You can now report any defects through your account.';
        $data = ['event_type' => 'possession', 'booking_id' => $bookingId, 'possession_completed' => true, 'action_url' => '/user/bookings/' . $bookingId, 'priority' => 'high'];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'sms', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
    }

    // =====================================================================
    // DOMAIN TRIGGER HELPERS
    // =====================================================================

    public function newLead(int $leadId, string $leadName): bool
    {
        return $this->notify('lead', "New lead: $leadName", null, '/admin/leads/show/' . $leadId, 'New Lead');
    }

    public function newProperty(int $propertyId, string $propertyTitle): bool
    {
        return $this->notify('property', "New property listed: $propertyTitle", null, '/admin/user-properties/verify/' . $propertyId, 'New Property');
    }

    public function newRegistration(int $userId, string $userName): bool
    {
        return $this->notify('user', "New user registered: $userName", null, '/admin/users/' . $userId, 'New Registration');
    }

    public function newBooking(int $bookingId, string $buyerName): bool
    {
        return $this->notify('booking', "New booking: $buyerName", null, '/admin/bookings/' . $bookingId, 'New Booking');
    }

    public function paymentReceived(int $transactionId, float $amount): bool
    {
        return $this->notify('payment', "Payment received: ₹$amount", null, '/admin/payments/' . $transactionId, 'Payment Received');
    }

    // =====================================================================
    // REALTIME NOTIFICATIONS (realtime_notifications table)
    // =====================================================================

    /**
     * Publish a notification to realtime_notifications + WebSocket broadcast.
     * Replaces NotificationCenter::publish().
     */
    public function publish(string $channel, string $eventType, ?int $userId, array $payload, ?int $ttlSeconds = null): int
    {
        $expires = $ttlSeconds ? date('Y-m-d H:i:s', time() + $ttlSeconds) : null;
        try {
            $st = $this->db->prepare("INSERT INTO realtime_notifications (channel_name, user_id, event_type, payload, expires_at) VALUES (:c, :u, :e, :p, :exp)");
            $st->execute([':c' => $channel, ':u' => $userId, ':e' => $eventType, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':exp' => $expires]);
            $id = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('NotificationService::publish error: ' . $e->getMessage());
            return 0;
        }

        // Best-effort WebSocket broadcast
        try {
            if (class_exists('\App\Services\WebSocketBroadcaster')) {
                \App\Services\WebSocketBroadcaster::broadcastToUser((int)$userId, [
                    'event' => $eventType, 'id' => $id, 'payload' => $payload, 'ts' => time()
                ], $channel);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $id;
    }

    /**
     * Fetch undelivered notifications for polling.
     * Replaces NotificationCenter::fetchPending().
     */
    public function fetchPending(int $userId, string $channel = 'global', int $limit = 20, ?int $sinceId = null): array
    {
        $sql = "SELECT * FROM realtime_notifications WHERE channel_name = :c AND delivered_at IS NULL AND (user_id IS NULL OR user_id = :u)";
        $params = [':c' => $channel, ':u' => $userId];
        if ($sinceId) { $sql .= " AND id > :sid"; $params[':sid'] = $sinceId; }
        $sql .= " ORDER BY id ASC LIMIT :lim";
        try {
            $st = $this->db->prepare($sql);
            foreach ($params as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mark notifications as delivered.
     * Replaces NotificationCenter::markDelivered().
     */
    public function markDelivered(array $ids): int
    {
        if (empty($ids)) return 0;
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $st = $this->db->prepare("UPDATE realtime_notifications SET delivered_at = NOW() WHERE id IN ($placeholders) AND delivered_at IS NULL");
            $st->execute($ids);
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Purge old notifications.
     * Replaces NotificationCenter::cleanup().
     */
    public function cleanup(int $daysToKeep = 30): int
    {
        try {
            $st = $this->db->prepare("DELETE FROM realtime_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $st->execute([$daysToKeep]);
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // =====================================================================
    // ALIASES for backward compatibility
    // =====================================================================

    /**
     * Alias for send() — matches Communication\NotificationService::sendNotification() signature.
     */
    public function sendNotification(int $userId, string $channel, string $title, string $message, array $data = []): array
    {
        return $this->send($userId, $channel, $title, $message, $data);
    }
}
