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
}
