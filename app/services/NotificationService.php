<?php
namespace App\Services;

use PDO;

/**
 * NotificationService - multi-channel notification (email, SMS, push, WhatsApp, in-app)
 */
class NotificationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    private function getTenantId(): int
    {
        return $this->tenantId();
    }

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
        $tid = $this->getTenantId();
        $sql = "INSERT INTO realtime_notifications (channel_name, user_id, event_type, payload, tenant_id, delivered_at, created_at)
                VALUES (:c, :u, :e, :p, :tid, :d, NOW())";
        try {
            $st = $this->db->prepare($sql);
            $st->execute([
                ':c' => $channel,
                ':u' => $userId,
                ':e' => $eventType,
                ':p' => $payload,
                ':tid' => $tid,
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
            $tid = $this->getTenantId();
            $sql = "UPDATE realtime_notifications SET delivered_at = NOW() WHERE id = :id";
            if ($tid > 1) { $sql .= " AND tenant_id = :tid"; }
            $st = $this->db->prepare($sql);
            $params = [':id' => $id];
            if ($tid > 1) { $params[':tid'] = $tid; }
            $st->execute($params);
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
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
            $tid = $this->getTenantId();
            $st2 = $this->db->prepare("INSERT INTO email_tracking (email_id, recipient, event_type, ip_address, user_agent, tenant_id, event_at) VALUES (:n, :e, 'sent', :ip, :ua, :tid, NOW())");
            $st2->execute([':n' => $notifId, ':e' => $to, ':ip' => $_SERVER['REMOTE_ADDR'] ?? null, ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null, ':tid' => $tid]);
        } catch (\Throwable $e) {
        // table might not have the columns we expect; ignore
        error_log($e->getMessage());
        }
    }

    private function trackSms(int $notifId, int $userId, string $message, array $data): void
    {
        try {
            $st = $this->db->prepare("SELECT phone FROM users WHERE id = :u");
            $st->execute([':u' => $userId]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            $to = $data['phone'] ?? $u['phone'] ?? '';
            $tid = $this->getTenantId();
            $st2 = $this->db->prepare("INSERT INTO email_tracking (email_id, recipient, event_type, ip_address, user_agent, tenant_id, event_at) VALUES (:n, :e, 'sms_sent', :ip, :ua, :tid, NOW())");
            $st2->execute([':n' => $notifId, ':e' => $to, ':ip' => $_SERVER['REMOTE_ADDR'] ?? null, ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null, ':tid' => $tid]);
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
    }

    private function sendPush(int $userId, string $title, string $body, array $data): void
    {
        $st = $this->db->prepare("SELECT * FROM push_subscriptions WHERE user_id = :u AND active = 1");
        $st->execute([':u' => $userId]);
        $subs = $st->fetchAll(PDO::FETCH_ASSOC);

        $tid = $this->getTenantId();
        $st2 = $this->db->prepare("INSERT INTO push_notifications (user_id, title, body, data, tenant_id, sent_at, created_at) VALUES (:u, :t, :b, :d, :tid, NOW(), NOW())");
        $st2->execute([':u' => $userId, ':t' => $title, ':b' => $body, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE), ':tid' => $tid]);
    }

    private function sendWhatsapp(int $userId, string $message, array $data): void
    {
        $st = $this->db->prepare("SELECT phone FROM users WHERE id = :u");
        $st->execute([':u' => $userId]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        $to = $data['phone'] ?? $u['phone'] ?? '';
        $tid = $this->getTenantId();
        $st2 = $this->db->prepare("INSERT INTO whatsapp_messages (phone_number, message, direction, status, tenant_id, created_at) VALUES (:p, :m, 'outbound', 'sent', :tid, NOW())");
        try { $st2->execute([':p' => $to, ':m' => $message, ':tid' => $tid]); } catch (\Throwable $e) { error_log($e->getMessage()); }
    }

    public function shareLead(int $userId, int $leadId, string $to, string $channel = 'whatsapp'): array
    {
        $tid = $this->getTenantId();
        $st = $this->db->prepare("INSERT INTO whatsapp_lead_shares (user_id, lead_id, shared_to, channel, tenant_id, shared_at) VALUES (:u, :l, :t, :c, :tid, NOW())");
        $st->execute([':u' => $userId, ':l' => $leadId, ':t' => $to, ':c' => $channel, ':tid' => $tid]);
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
            $tid = $this->getTenantId();
            $name = $templateName !== '' ? $templateName : $code;
            $cols = 'template_code, template_name, body, is_active, created_at';
            $vals = ':c, :n, :b, 1, NOW()';
            $updateCols = 'template_name = VALUES(template_name), body = VALUES(body), is_active = 1';
            if ($tid > 1) { $cols .= ', tenant_id'; $vals .= ', :tid'; $updateCols .= ', tenant_id = VALUES(tenant_id)'; }
            $st = $this->db->prepare("INSERT INTO sms_templates ($cols) VALUES ($vals) ON DUPLICATE KEY UPDATE $updateCols");
            $params = [':c' => $code, ':n' => $name, ':b' => $body];
            if ($tid > 1) { $params[':tid'] = $tid; }
            $st->execute($params);
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
            $tid = $this->getTenantId();
            $this->db->prepare(
                'INSERT INTO notifications (user_id, type, title, message, action_url, is_read, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, ?, NOW())'
            )->execute([$userId, $type, $title ?? ucfirst($type), $message, $actionUrl, 'unread', $tid]);
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
            $tid = $this->getTenantId();
            $sql = 'SELECT * FROM notifications';
            $params = [];
            $wheres = [];
            if ($tid > 1) { $wheres[] = 'tenant_id = ?'; $params[] = $tid; }
            if ($userId) { $wheres[] = '(user_id = ? OR user_id IS NULL)'; $params[] = $userId; }
            if ($wheres) { $sql .= ' WHERE ' . implode(' AND ', $wheres); }
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
            $tid = $this->getTenantId();
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$userId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            $st = $this->db->prepare($sql);
            $st->execute($params);
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
            $tid = $this->getTenantId();
            $sql = 'SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0';
            $params = [];
            if ($tid > 1) { $sql .= ' AND tenant_id = ?'; $params[] = $tid; }
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
            $tid = $this->getTenantId();
            $sql = 'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?';
            if ($tid > 1) { $sql .= ' AND tenant_id = ?'; }
            $params = [(int)$id];
            if ($tid > 1) { $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
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
            $tid = $this->getTenantId();
            $sql = 'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0';
            $params = [];
            if ($userId) {
                $sql .= ' AND (user_id = ? OR user_id IS NULL)';
                $params[] = $userId;
            }
            if ($tid > 1) {
                $sql .= ' AND tenant_id = ?';
                $params[] = $tid;
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
            if (!$booking) {
                $st2 = $this->db->prepare("SELECT customer_id FROM plot_bookings WHERE id = ?");
                $st2->execute([$bookingId]);
                $booking = $st2->fetch(PDO::FETCH_ASSOC);
            }
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

    public function sendNocApproved(int $bookingId): void
    {
        $userId = $this->getBookingCustomerUserId($bookingId);
        if (!$userId) return;

        $title = 'NOC Approved';
        $message = 'No Objection Certificate (NOC) has been approved for your booking. The property is now cleared for registry scheduling.';
        $data = ['event_type' => 'noc', 'booking_id' => $bookingId, 'noc_status' => 'approved', 'action_url' => '/user/bookings/' . $bookingId, 'priority' => 'high'];

        $this->send($userId, 'email', $title, $message, $data);
        $this->send($userId, 'whatsapp', $title, $message, $data);
        $this->send($userId, 'sms', $title, $message, $data);
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
        $tid = $this->getTenantId();
        try {
            $st = $this->db->prepare("INSERT INTO realtime_notifications (channel_name, user_id, event_type, payload, tenant_id, expires_at) VALUES (:c, :u, :e, :p, :tid, :exp)");
            $st->execute([':c' => $channel, ':u' => $userId, ':e' => $eventType, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':tid' => $tid, ':exp' => $expires]);
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
        error_log($e->getMessage());
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
            $tid = $this->getTenantId();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE realtime_notifications SET delivered_at = NOW() WHERE id IN ($placeholders) AND delivered_at IS NULL";
            $params = $ids;
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $st = $this->db->prepare($sql);
            $st->execute($params);
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
            $tid = $this->getTenantId();
            $sql = "DELETE FROM realtime_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params = [$daysToKeep];
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }
            $st = $this->db->prepare($sql);
            $st->execute($params);
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

    /**
     * Notify when a pricing plan is created/saved.
     */
    public function pricingPlanCreated(int $colonyId, int $planId, int $version, int $createdBy): bool
    {
        $title = 'New Pricing Plan Created';
        $message = "Pricing plan v{$version} has been created for colony. Base price: ₹" . number_format($basePrice, 2) . "/sqft";
        $data = ['event_type' => 'pricing_plan', 'colony_id' => $colonyId, 'plan_id' => $planId, 'version' => $version, 'action' => 'created', 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/pricing'];

        // Notify admins
        $this->notify('pricing_plan', $message, null, '/admin/colony-pipeline/' . $colonyId . '/pricing', $title);

        // Notify relevant users (colony team, pricing team)
        $this->sendToRole('admin', 'email', $title, $message, ['event_type' => 'pricing_plan_created', 'colony_id' => $colonyId, 'plan_id' => $planId]);
        $this->sendToRole('pricing_manager', 'email', $title, $message, ['event_type' => 'pricing_plan_created', 'colony_id' => $colonyId, 'plan_id' => $planId]);

        return true;
    }

    /**
     * Notify when a pricing plan is activated.
     */
    public function pricingPlanActivated(int $colonyId, int $planId, int $version, int $activatedBy): bool
    {
        $title = 'Pricing Plan Activated';
        $message = "Pricing plan v{$version} has been activated for the colony. This plan is now live for pricing calculations.";
        $data = ['event_type' => 'pricing_plan', 'colony_id' => $colonyId, 'plan_id' => $planId, 'version' => $version, 'action' => 'activated', 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/pricing'];

        $this->notify('pricing_plan', $message, null, '/admin/colony-pipeline/' . $colonyId . '/pricing', $title);
        $this->sendToRole('admin', 'email', 'Pricing Plan Activated', $message, ['event_type' => 'pricing_plan_activated', 'colony_id' => $colonyId, 'plan_id' => $planId]);
        $this->sendToRole('pricing_manager', 'email', 'Pricing Plan Activated', $message, ['event_type' => 'pricing_plan_activated', 'colony_id' => $colonyId, 'plan_id' => $planId]);

        return true;
    }

    /**
     * Notify when a pricing plan is applied to plots.
     */
    public function pricingPlanApplied(int $colonyId, int $planId, int $version, int $plotsUpdated, float $totalValue, int $appliedBy): bool
    {
        $title = 'Pricing Plan Applied';
        $message = "Pricing plan v{$version} has been applied to {$plotsUpdated} plots. Total inventory value: ₹" . number_format($totalValue, 2);
        $data = ['event_type' => 'pricing_plan', 'colony_id' => $colonyId, 'plan_id' => $planId, 'version' => $version, 'action' => 'applied', 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue, 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/pricing'];

        $this->notify('pricing_plan', $message, null, '/admin/colony-pipeline/' . $colonyId . '/pricing', $title);
        $this->sendToRole('admin', 'email', 'Pricing Plan Applied', $message, ['event_type' => 'pricing_plan_applied', 'colony_id' => $colonyId, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);
        $this->sendToRole('pricing_manager', 'email', 'Pricing Plan Applied', $message, ['event_type' => 'pricing_plan_applied', 'colony_id' => $colonyId, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);
        $this->sendToRole('sales_manager', 'email', 'Pricing Plan Applied', $message, ['event_type' => 'pricing_plan_applied', 'colony_id' => $colonyId, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);

        return true;
    }

    /**
     * Notify when a plot status changes.
     */
    public function plotStatusChanged(int $plotId, string $oldStatus, string $newStatus, int $changedBy): bool
    {
        $plot = $this->db->fetchOne("SELECT plot_number, colony_id FROM plots WHERE id = ?", [$plotId]);
        if (!$plot) return false;

        $statusLabels = [
            'available' => 'Available',
            'hold' => 'On Hold',
            'booked' => 'Booked',
            'sold' => 'Sold',
            'reserved' => 'Reserved',
            'under_construction' => 'Under Construction',
            'developed' => 'Developed',
        ];
        $oldLabel = $statusLabels[$oldStatus] ?? ucwords(str_replace('_', ' ', $oldStatus));
        $newLabel = $statusLabels[$newStatus] ?? ucwords(str_replace('_', ' ', $newStatus));

        $title = 'Plot Status Updated';
        $message = "Plot {$plot['plot_number']} status changed from {$oldLabel} to {$newLabel}.";
        $data = ['event_type' => 'plot_status', 'plot_id' => $plotId, 'plot_number' => $plot['plot_number'], 'old_status' => $oldStatus, 'new_status' => $newStatus, 'action_url' => '/admin/plots/' . $plotId];

        $this->notify('plot_status', $message, null, '/admin/plots/' . $plotId, $title);
        $this->sendToRole('admin', 'email', 'Plot Status Updated', $message, ['event_type' => 'plot_status_changed', 'plot_id' => $plotId, 'old_status' => $oldStatus, 'new_status' => $newStatus]);
        $this->sendToRole('sales_manager', 'email', 'Plot Status Updated', $message, ['event_type' => 'plot_status_changed', 'plot_id' => $plotId, 'old_status' => $oldStatus, 'new_status' => $newStatus]);

        return true;
    }

    /**
     * Notify when a colony is created.
     */
    public function colonyCreated(int $colonyId, string $colonyName, int $createdBy): bool
    {
        $title = 'New Colony Created';
        $message = "New colony '{$colonyName}' has been created.";
        $data = ['event_type' => 'colony', 'colony_id' => $colonyId, 'action' => 'created', 'action_url' => '/admin/colonies/' . $colonyId];

        $this->notify('colony', $message, null, '/admin/colonies/' . $colonyId, $title);
        $this->sendToRole('admin', 'email', 'New Colony Created', $message, ['event_type' => 'colony_created', 'colony_id' => $colonyId]);
        $this->sendToRole('land_manager', 'email', 'New Colony Created', $message, ['event_type' => 'colony_created', 'colony_id' => $colonyId]);
        $this->sendToRole('sales_manager', 'email', 'New Colony Created', $message, ['event_type' => 'colony_created', 'colony_id' => $colonyId]);

        return true;
    }

    /**
     * Notify when a colony is updated.
     */
    public function colonyUpdated(int $colonyId, string $colonyName, int $updatedBy, array $changedFields = []): bool
    {
        $title = 'Colony Updated';
        $message = "Colony '{$colonyName}' has been updated.";
        if (!empty($changedFields)) {
            $message .= ' Changed fields: ' . implode(', ', $changedFields);
        }
        $data = ['event_type' => 'colony', 'colony_id' => $colonyId, 'action' => 'updated', 'changed_fields' => $changedFields, 'action_url' => '/admin/colonies/' . $colonyId];

        $this->notify('colony', $message, null, '/admin/colonies/' . $colonyId, $title);
        $this->sendToRole('admin', 'email', 'Colony Updated', $message, ['event_type' => 'colony_updated', 'colony_id' => $colonyId, 'changed_fields' => $changedFields]);
        $this->sendToRole('land_manager', 'email', 'Colony Updated', $message, ['event_type' => 'colony_updated', 'colony_id' => $colonyId, 'changed_fields' => $changedFields]);

        return true;
    }

    /**
     * Notify when plots are generated for a colony.
     */
    public function plotsGenerated(int $colonyId, string $colonyName, int $plotsCount, int $generatedBy): bool
    {
        $title = 'Plots Generated';
        $message = "{$plotsCount} plots have been generated for colony '{$colonyName}'.";
        $data = ['event_type' => 'plots', 'colony_id' => $colonyId, 'plots_count' => $plotsCount, 'action' => 'generated', 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/plots'];

        $this->notify('plots', $message, null, '/admin/colony-pipeline/' . $colonyId . '/plots', $title);
        $this->sendToRole('admin', 'email', 'Plots Generated', $message, ['event_type' => 'plots_generated', 'colony_id' => $colonyId, 'plots_count' => $plotsCount]);
        $this->sendToRole('land_manager', 'email', 'Plots Generated', $message, ['event_type' => 'plots_generated', 'colony_id' => $colonyId, 'plots_count' => $plotsCount]);
        $this->sendToRole('sales_manager', 'email', 'Plots Generated', $message, ['event_type' => 'plots_generated', 'colony_id' => $colonyId, 'plots_count' => $plotsCount]);

        return true;
    }

    /**
     * Notify when plots are deleted.
     */
    public function plotsDeleted(int $colonyId, string $colonyName, int $deletedCount, int $deletedBy): bool
    {
        $title = 'Plots Deleted';
        $message = "{$deletedCount} plots have been deleted from colony '{$colonyName}'.";
        $data = ['event_type' => 'plots', 'colony_id' => $colonyId, 'deleted_count' => $deletedCount, 'action' => 'deleted', 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/layout'];

        $this->notify('plots', $message, null, '/admin/colony-pipeline/' . $colonyId . '/layout', $title);
        $this->sendToRole('admin', 'email', 'Plots Deleted', $message, ['event_type' => 'plots_deleted', 'colony_id' => $colonyId, 'deleted_count' => $deletedCount]);
        $this->sendToRole('land_manager', 'email', 'Plots Deleted', $message, ['event_type' => 'plots_deleted', 'colony_id' => $colonyId, 'deleted_count' => $deletedCount]);

        return true;
    }

    /**
     * Notify when pricing is applied to a colony.
     */
    public function colonyPricingApplied(int $colonyId, string $colonyName, float $basePrice, int $plotsUpdated, float $totalValue, int $appliedBy): bool
    {
        $title = 'Colony Pricing Applied';
        $message = "Pricing has been applied to colony '{$colonyName}' at ₹" . number_format($basePrice, 2) . "/sqft. {$plotsUpdated} plots updated. Total inventory value: ₹" . number_format($totalValue, 2);
        $data = ['event_type' => 'pricing', 'colony_id' => $colonyId, 'base_price' => $basePrice, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue, 'action_url' => '/admin/colony-pipeline/' . $colonyId . '/pricing'];

        $this->notify('pricing', $message, null, '/admin/colony-pipeline/' . $colonyId . '/pricing', $title);
        $this->sendToRole('admin', 'email', 'Colony Pricing Applied', $message, ['event_type' => 'colony_pricing_applied', 'colony_id' => $colonyId, 'base_price' => $basePrice, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);
        $this->sendToRole('sales_manager', 'email', 'Colony Pricing Applied', $message, ['event_type' => 'colony_pricing_applied', 'colony_id' => $colonyId, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);
        $this->sendToRole('pricing_manager', 'email', 'Colony Pricing Applied', $message, ['event_type' => 'colony_pricing_applied', 'colony_id' => $colonyId, 'base_price' => $basePrice, 'plots_updated' => $plotsUpdated, 'total_value' => $totalValue]);

        return true;
    }

    /**
     * Send notification to users with a specific role.
     */
    private function sendToRole(string $role, string $channel, string $title, string $message, array $data = []): void
    {
        try {
            $tid = $this->getTenantId();
            $st = $this->db->prepare("SELECT id FROM users WHERE role = ? AND status = 'active'");
            $st->execute([$role]);
            $users = $st->fetchAll(PDO::FETCH_COLUMN);

            foreach ($users as $userId) {
                $this->send((int)$userId, $channel, $title, $message, $data);
            }
        } catch (\Throwable $e) {
            error_log('NotificationService::sendToRole error: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // CRITICAL ACTION NOTIFICATIONS
    // =====================================================================

    /**
     * Notify customer + associate when booking is confirmed.
     */
    public function notifyBookingConfirmed(int $bookingId): void
    {
        try {
            $userId = $this->getBookingCustomerUserId($bookingId);
            if (!$userId) return;

            $booking = $this->db->fetchOne("SELECT id, plot_id, total_amount, booking_date FROM bookings WHERE id = ?", [$bookingId]);
            if (!$booking) {
                $booking = $this->db->fetchOne("SELECT id, plot_id, total_plot_value AS total_amount, created_at AS booking_date FROM plot_bookings WHERE id = ?", [$bookingId]);
            }

            $plotInfo = '';
            if (!empty($booking['plot_id'])) {
                $plot = $this->db->fetchOne("SELECT plot_number, colony_id FROM plots WHERE id = ?", [$booking['plot_id']]);
                if ($plot) $plotInfo = " Plot: {$plot['plot_number']}.";
            }

            $amount = $booking['total_amount'] ?? 0;
            $date = $booking['booking_date'] ?? date('Y-m-d');

            $title = 'Booking Confirmed';
            $message = "Your booking #{$bookingId} has been confirmed.{$plotInfo} Amount: ₹" . number_format((float)$amount, 2) . ". Date: " . date('d F Y', strtotime($date)) . ".";
            $data = ['event_type' => 'booking_confirmed', 'booking_id' => $bookingId, 'plot_id' => $booking['plot_id'] ?? null, 'amount' => $amount, 'action_url' => '/user/bookings/' . $bookingId, 'priority' => 'high'];

            $this->send($userId, 'email', $title, $message, $data);
            $this->send($userId, 'sms', $title, $message, $data);
            $this->send($userId, 'push', $title, $message, $data);
            $this->send($userId, 'whatsapp', $title, $message, $data);

            $this->notify('booking', "Booking #{$bookingId} confirmed for user #{$userId}.{$plotInfo} Amount: ₹" . number_format((float)$amount, 2), null, '/admin/bookings/' . $bookingId, 'Booking Confirmed');
            $this->sendToRole('admin', 'email', 'Booking Confirmed', "Booking #{$bookingId} confirmed.{$plotInfo} Amount: ₹" . number_format((float)$amount, 2), ['event_type' => 'booking_confirmed', 'booking_id' => $bookingId]);
            $this->sendToRole('sales_manager', 'email', 'Booking Confirmed', "Booking #{$bookingId} confirmed.{$plotInfo} Amount: ₹" . number_format((float)$amount, 2), ['event_type' => 'booking_confirmed', 'booking_id' => $bookingId]);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyBookingConfirmed error: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer + admin when payment is received.
     */
    public function notifyPaymentReceived(int $paymentId): void
    {
        try {
            $payment = $this->db->fetchOne("SELECT id, booking_id, amount, payment_method, transaction_id, created_at FROM payments WHERE id = ?", [$paymentId]);
            if (!$payment) {
                $payment = $this->db->fetchOne("SELECT id, booking_id, amount, payment_method, transaction_id, created_at FROM booking_payment_receipts WHERE id = ?", [$paymentId]);
            }
            if (!$payment) return;

            $userId = $this->getBookingCustomerUserId((int)$payment['booking_id']);
            $amount = (float)$payment['amount'];
            $method = $payment['payment_method'] ?? 'online';
            $txnId = $payment['transaction_id'] ?? 'N/A';

            $title = 'Payment Received';
            $message = "Payment of ₹" . number_format($amount, 2) . " received for booking #{$payment['booking_id']}. Method: " . ucfirst($method) . ". Txn ID: {$txnId}.";
            $data = ['event_type' => 'payment_received', 'payment_id' => $paymentId, 'booking_id' => $payment['booking_id'], 'amount' => $amount, 'payment_method' => $method, 'transaction_id' => $txnId, 'action_url' => '/user/bookings/' . $payment['booking_id'], 'priority' => 'high'];

            if ($userId) {
                $this->send($userId, 'email', $title, $message, $data);
                $this->send($userId, 'sms', $title, $message, $data);
                $this->send($userId, 'whatsapp', $title, $message, $data);
            }

            $this->notify('payment', "Payment #{$paymentId} received: ₹" . number_format($amount, 2) . " for booking #{$payment['booking_id']}. Method: " . ucfirst($method), null, '/admin/payments/' . $paymentId, 'Payment Received');
            $this->sendToRole('admin', 'email', 'Payment Received', "Payment #{$paymentId}: ₹" . number_format($amount, 2) . " for booking #{$payment['booking_id']}. Method: " . ucfirst($method), ['event_type' => 'payment_received', 'payment_id' => $paymentId, 'booking_id' => $payment['booking_id'], 'amount' => $amount]);
            $this->sendToRole('finance_manager', 'email', 'Payment Received', "Payment #{$paymentId}: ₹" . number_format($amount, 2) . " for booking #{$payment['booking_id']}. Method: " . ucfirst($method), ['event_type' => 'payment_received', 'payment_id' => $paymentId, 'booking_id' => $payment['booking_id'], 'amount' => $amount]);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyPaymentReceived error: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer when EMI is due (3 days before).
     */
    public function notifyEmiDue(int $emiId): void
    {
        try {
            $emi = $this->db->fetchOne("SELECT id, booking_id, installment_number, due_date, amount, pending_amount FROM booking_payment_schedules WHERE id = ?", [$emiId]);
            if (!$emi) {
                $emi = $this->db->fetchOne("SELECT id, booking_id, emi_number AS installment_number, due_date, emi_amount AS amount, emi_amount AS pending_amount FROM emi_schedule WHERE id = ?", [$emiId]);
            }
            if (!$emi) return;

            $userId = $this->getBookingCustomerUserId((int)$emi['booking_id']);
            if (!$userId) return;

            $amount = (float)($emi['pending_amount'] ?? $emi['amount']);
            $dueDate = $emi['due_date'];
            $daysLeft = (int)((strtotime($dueDate) - time()) / 86400);

            $title = 'EMI Due Soon';
            $message = "EMI #{$emi['installment_number']} of ₹" . number_format($amount, 2) . " is due in {$daysLeft} day(s) on " . date('d F Y', strtotime($dueDate)) . ". Please ensure timely payment to avoid penalties.";
            $data = ['event_type' => 'emi_due', 'emi_id' => $emiId, 'booking_id' => $emi['booking_id'], 'installment_number' => $emi['installment_number'], 'amount' => $amount, 'due_date' => $dueDate, 'days_remaining' => $daysLeft, 'action_url' => '/user/emi-schedule', 'priority' => 'high'];

            $this->send($userId, 'email', $title, $message, $data);
            $this->send($userId, 'sms', $title, $message, $data);
            $this->send($userId, 'push', $title, $message, $data);
            $this->send($userId, 'whatsapp', $title, $message, $data);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyEmiDue error: ' . $e->getMessage());
        }
    }

    /**
     * Notify customer + associate when EMI is overdue.
     */
    public function notifyEmiOverdue(int $emiId): void
    {
        try {
            $emi = $this->db->fetchOne("SELECT id, booking_id, installment_number, due_date, amount, pending_amount FROM booking_payment_schedules WHERE id = ?", [$emiId]);
            if (!$emi) {
                $emi = $this->db->fetchOne("SELECT id, booking_id, emi_number AS installment_number, due_date, emi_amount AS amount, emi_amount AS pending_amount FROM emi_schedule WHERE id = ?", [$emiId]);
            }
            if (!$emi) return;

            $userId = $this->getBookingCustomerUserId((int)$emi['booking_id']);
            $amount = (float)($emi['pending_amount'] ?? $emi['amount']);
            $dueDate = $emi['due_date'];
            $daysOverdue = (int)((time() - strtotime($dueDate)) / 86400);

            $title = 'EMI Overdue';
            $message = "EMI #{$emi['installment_number']} of ₹" . number_format($amount, 2) . " is overdue by {$daysOverdue} day(s). Due date was " . date('d F Y', strtotime($dueDate)) . ". Please pay immediately to avoid penalties.";
            $data = ['event_type' => 'emi_overdue', 'emi_id' => $emiId, 'booking_id' => $emi['booking_id'], 'installment_number' => $emi['installment_number'], 'amount' => $amount, 'due_date' => $dueDate, 'days_overdue' => $daysOverdue, 'action_url' => '/user/emi-schedule', 'priority' => 'high'];

            if ($userId) {
                $this->send($userId, 'email', $title, $message, $data);
                $this->send($userId, 'sms', $title, $message, $data);
                $this->send($userId, 'push', $title, $message, $data);
                $this->send($userId, 'whatsapp', $title, $message, $data);
            }

            $this->notify('emi_overdue', "EMI #{$emiId} overdue by {$daysOverdue} days. Booking #{$emi['booking_id']}, Amount: ₹" . number_format($amount, 2), null, '/admin/emi/overdue', 'EMI Overdue');
            $this->sendToRole('admin', 'email', 'EMI Overdue', "EMI #{$emiId} overdue by {$daysOverdue} days. Booking #{$emi['booking_id']}, Amount: ₹" . number_format($amount, 2), ['event_type' => 'emi_overdue', 'emi_id' => $emiId, 'booking_id' => $emi['booking_id'], 'days_overdue' => $daysOverdue]);
            $this->sendToRole('finance_manager', 'email', 'EMI Overdue', "EMI #{$emiId} overdue by {$daysOverdue} days. Booking #{$emi['booking_id']}, Amount: ₹" . number_format($amount, 2), ['event_type' => 'emi_overdue', 'emi_id' => $emiId, 'booking_id' => $emi['booking_id'], 'days_overdue' => $daysOverdue]);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyEmiOverdue error: ' . $e->getMessage());
        }
    }

    /**
     * Notify associate when promoted to a new rank.
     */
    public function notifyRankPromotion(int $userId, string $newRank): void
    {
        try {
            $user = $this->db->fetchOne("SELECT id, name, email, phone, mlm_rank FROM users WHERE id = ?", [$userId]);
            if (!$user) return;

            $oldRank = $user['mlm_rank'] ?? 'N/A';

            $title = 'Congratulations on Your Promotion!';
            $message = "Dear {$user['name']}, congratulations on being promoted from {$oldRank} to {$newRank}. Your hard work and dedication have been recognized. Keep up the excellent work!";
            $data = ['event_type' => 'rank_promotion', 'user_id' => $userId, 'old_rank' => $oldRank, 'new_rank' => $newRank, 'action_url' => '/associate/rank-eligibility', 'priority' => 'high'];

            $this->send($userId, 'email', $title, $message, $data);
            $this->send($userId, 'sms', $title, $message, $data);
            $this->send($userId, 'push', $title, $message, $data);
            $this->send($userId, 'whatsapp', $title, $message, $data);

            $this->notify('rank_promotion', "User #{$userId} ({$user['name']}) promoted from {$oldRank} to {$newRank}", null, '/admin/mlm/rank-promotions', 'Rank Promotion');
            $this->sendToRole('admin', 'email', 'Rank Promotion', "{$user['name']} promoted from {$oldRank} to {$newRank}", ['event_type' => 'rank_promotion', 'user_id' => $userId, 'old_rank' => $oldRank, 'new_rank' => $newRank]);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyRankPromotion error: ' . $e->getMessage());
        }
    }

    /**
     * Notify referrer when someone joins through their referral.
     */
    public function notifyReferralJoined(int $referrerUserId, string $newUserName): void
    {
        try {
            $referrer = $this->db->fetchOne("SELECT id, name, email, phone, referral_code FROM users WHERE id = ?", [$referrerUserId]);
            if (!$referrer) return;

            $title = 'New Referral Joined';
            $message = "Great news {$referrer['name']}! {$newUserName} has joined using your referral code {$referrer['referral_code']}. You will receive your referral bonus when they complete their first booking.";
            $data = ['event_type' => 'referral_joined', 'referrer_user_id' => $referrerUserId, 'new_user_name' => $newUserName, 'referral_code' => $referrer['referral_code'], 'action_url' => '/associate/referral', 'priority' => 'normal'];

            $this->send($referrerUserId, 'email', $title, $message, $data);
            $this->send($referrerUserId, 'sms', $title, $message, $data);
            $this->send($referrerUserId, 'push', $title, $message, $data);
            $this->send($referrerUserId, 'whatsapp', $title, $message, $data);

            $this->notify('referral', "New referral {$newUserName} joined via {$referrer['name']} (code: {$referrer['referral_code']})", null, '/admin/referrals', 'New Referral');
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyReferralJoined error: ' . $e->getMessage());
        }
    }

    /**
     * Notify relevant parties when plot status changes.
     */
    public function notifyPlotStatusChange(int $plotId, string $oldStatus, string $newStatus): void
    {
        try {
            $plot = $this->db->fetchOne("SELECT id, plot_number, colony_id, status FROM plots WHERE id = ?", [$plotId]);
            if (!$plot) return;

            $statusLabels = [
                'available' => 'Available',
                'hold' => 'On Hold',
                'booked' => 'Booked',
                'sold' => 'Sold',
                'reserved' => 'Reserved',
                'under_construction' => 'Under Construction',
                'developed' => 'Developed',
            ];
            $oldLabel = $statusLabels[$oldStatus] ?? ucwords(str_replace('_', ' ', $oldStatus));
            $newLabel = $statusLabels[$newStatus] ?? ucwords(str_replace('_', ' ', $newStatus));

            $title = 'Plot Status Changed';
            $message = "Plot {$plot['plot_number']} status changed from {$oldLabel} to {$newLabel}.";
            $data = ['event_type' => 'plot_status_change', 'plot_id' => $plotId, 'plot_number' => $plot['plot_number'], 'colony_id' => $plot['colony_id'], 'old_status' => $oldStatus, 'new_status' => $newStatus, 'action_url' => '/admin/plots/' . $plotId, 'priority' => 'high'];

            $this->notify('plot_status', $message, null, '/admin/plots/' . $plotId, 'Plot Status Changed');
            $this->sendToRole('admin', 'email', 'Plot Status Changed', $message, $data);
            $this->sendToRole('sales_manager', 'email', 'Plot Status Changed', $message, $data);
            $this->sendToRole('land_manager', 'email', 'Plot Status Changed', $message, $data);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyPlotStatusChange error: ' . $e->getMessage());
        }
    }

    /**
     * Notify approvers when approval is required.
     */
    public function notifyApprovalRequired(string $approvalType, int $approvalId, string $approverRole): void
    {
        try {
            $typeLabels = [
                'booking' => 'Booking',
                'payment' => 'Payment',
                'refund' => 'Refund',
                'cancellation' => 'Cancellation',
                'plot_transfer' => 'Plot Transfer',
                'price_change' => 'Price Change',
                'expense' => 'Expense',
                'payout' => 'Payout',
                'kyc' => 'KYC',
                'document' => 'Document',
            ];
            $typeLabel = $typeLabels[$approvalType] ?? ucwords(str_replace('_', ' ', $approvalType));

            $title = "Approval Required: {$typeLabel}";
            $message = "A {$typeLabel} request (ID: {$approvalId}) requires your approval. Please review and take action.";
            $data = ['event_type' => 'approval_required', 'approval_type' => $approvalType, 'approval_id' => $approvalId, 'approver_role' => $approverRole, 'action_url' => '/admin/approvals/' . $approvalId, 'priority' => 'high'];

            $this->notify('approval_required', $message, null, '/admin/approvals/' . $approvalId, $title);
            $this->sendToRole($approverRole, 'email', $title, $message, $data);
            $this->sendToRole('admin', 'email', $title, $message, $data);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyApprovalRequired error: ' . $e->getMessage());
        }
    }

    /**
     * Notify requester when approval is resolved.
     */
    public function notifyApprovalResolved(int $approvalId, bool $approved): void
    {
        try {
            $approval = $this->db->fetchOne("SELECT id, type, requested_by, entity_id, entity_type FROM approvals WHERE id = ?", [$approvalId]);
            if (!$approval) {
                $approval = $this->db->fetchOne("SELECT id, approval_type AS type, requester_id AS requested_by, entity_id, entity_type FROM approval_requests WHERE id = ?", [$approvalId]);
            }

            $userId = $approval['requested_by'] ?? null;
            $type = $approval['type'] ?? 'request';
            $status = $approved ? 'Approved' : 'Rejected';

            $title = "Approval {$status}";
            $message = "Your {$type} request (ID: {$approvalId}) has been {$status}.";
            $data = ['event_type' => 'approval_resolved', 'approval_id' => $approvalId, 'approval_type' => $type, 'approved' => $approved, 'status' => strtolower($status), 'action_url' => '/user/approvals', 'priority' => 'high'];

            if ($userId) {
                $this->send((int)$userId, 'email', $title, $message, $data);
                $this->send((int)$userId, 'sms', $title, $message, $data);
                $this->send((int)$userId, 'push', $title, $message, $data);
                $this->send((int)$userId, 'whatsapp', $title, $message, $data);
            }

            $this->notify('approval_resolved', "Approval #{$approvalId} ({$type}) {$status}", null, '/admin/approvals', "Approval {$status}");
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyApprovalResolved error: ' . $e->getMessage());
        }
    }

    /**
     * Notify team when colony milestone is reached.
     */
    public function notifyMilestoneReached(int $colonyId, string $milestoneName): void
    {
        try {
            $colony = $this->db->fetchOne("SELECT id, name, slug FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) return;

            $title = 'Milestone Reached';
            $message = "Colony '{$colony['name']}' has reached the milestone: {$milestoneName}. Great progress!";
            $data = ['event_type' => 'milestone_reached', 'colony_id' => $colonyId, 'colony_name' => $colony['name'], 'milestone_name' => $milestoneName, 'action_url' => '/admin/colonies/' . $colonyId, 'priority' => 'normal'];

            $this->notify('milestone', $message, null, '/admin/colonies/' . $colonyId, 'Milestone Reached');
            $this->sendToRole('admin', 'email', 'Milestone Reached', $message, $data);
            $this->sendToRole('land_manager', 'email', 'Milestone Reached', $message, $data);
            $this->sendToRole('sales_manager', 'email', 'Milestone Reached', $message, $data);
            $this->sendToRole('project_manager', 'email', 'Milestone Reached', $message, $data);
        } catch (\Throwable $e) {
            error_log('NotificationService::notifyMilestoneReached error: ' . $e->getMessage());
        }
    }
}
