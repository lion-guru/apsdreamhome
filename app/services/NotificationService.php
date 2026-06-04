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
        $template = $this->getTemplate($data['template_code'] ?? $channel);
        $st = $this->db->prepare("INSERT INTO notification_templates (template_code, channel, subject, body, variables, active, created_at)
                                  VALUES (:c, :ch, :s, :b, :v, 0, NOW())
                                  ON DUPLICATE KEY UPDATE body = body");
        // templates aren't inserted via this method, only fetched

        $payload = json_encode(['subject' => $subject, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);

        $st2 = $this->db->prepare("INSERT INTO realtime_notifications (user_id, channel, subject, message, payload, status, created_at) VALUES (:u, :c, :s, :m, :p, 'pending', NOW())");
        $st2->execute([':u' => $userId, ':c' => $channel, ':s' => $subject, ':m' => $message, ':p' => $payload]);
        $id = (int)$this->db->lastInsertId();

        switch ($channel) {
            case 'email': $this->trackEmail($id, $userId, $subject, $message, $data); break;
            case 'sms': $this->trackSms($id, $userId, $message, $data); break;
            case 'push': $this->sendPush($userId, $subject, $message, $data); break;
            case 'whatsapp': $this->sendWhatsapp($userId, $message, $data); break;
        }

        $st3 = $this->db->prepare("UPDATE realtime_notifications SET status = 'sent', sent_at = NOW() WHERE id = :id");
        $st3->execute([':id' => $id]);

        return ['ok' => true, 'id' => $id];
    }

    public function getTemplate(string $code): ?array
    {
        $st = $this->db->prepare("SELECT * FROM notification_templates WHERE template_code = :c AND active = 1 LIMIT 1");
        $st->execute([':c' => $code]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function saveTemplate(string $code, string $channel, string $subject, string $body, array $variables = []): array
    {
        $st = $this->db->prepare("INSERT INTO notification_templates (template_code, channel, subject, body, variables, active, created_at)
                                  VALUES (:c, :ch, :s, :b, :v, 1, NOW())
                                  ON DUPLICATE KEY UPDATE subject = VALUES(subject), body = VALUES(body), variables = VALUES(variables), active = 1, updated_at = NOW()");
        $st->execute([':c' => $code, ':ch' => $channel, ':s' => $subject, ':b' => $body, ':v' => json_encode($variables, JSON_UNESCAPED_UNICODE)]);
        return ['ok' => true];
    }

    public function listTemplates(string $channel = ''): array
    {
        $sql = "SELECT * FROM notification_templates WHERE 1=1";
        $params = [];
        if ($channel) { $sql .= " AND channel = :c"; $params[':c'] = $channel; }
        $sql .= " ORDER BY template_code";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
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
        $st = $this->db->prepare("SELECT email, name FROM users WHERE id = :u");
        $st->execute([':u' => $userId]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        $to = $data['email'] ?? $u['email'] ?? '';
        $st2 = $this->db->prepare("INSERT INTO email_tracking (notification_id, user_id, to_email, subject, body, status, sent_at) VALUES (:n, :u, :e, :s, :b, 'sent', NOW())");
        $st2->execute([':n' => $notifId, ':u' => $userId, ':e' => $to, ':s' => $subject, ':b' => $body]);
    }

    private function trackSms(int $notifId, int $userId, string $message, array $data): void
    {
        $st = $this->db->prepare("SELECT phone FROM users WHERE id = :u");
        $st->execute([':u' => $userId]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        $to = $data['phone'] ?? $u['phone'] ?? '';
        $st2 = $this->db->prepare("INSERT INTO sms_logs (user_id, to_phone, message, status, sent_at, created_at) VALUES (:u, :p, :m, 'sent', NOW(), NOW())");
        try { $st2->execute([':u' => $userId, ':p' => $to, ':m' => $message]); } catch (\Throwable $e) {}
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
        $st = $this->db->query("SELECT * FROM sms_templates WHERE active = 1 ORDER BY template_code");
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveSmsTemplate(string $code, string $body, string $category = 'general'): array
    {
        $st = $this->db->prepare("INSERT INTO sms_templates (template_code, body, category, active, created_at) VALUES (:c, :b, :cat, 1, NOW())
                                  ON DUPLICATE KEY UPDATE body = VALUES(body), category = VALUES(category), active = 1");
        $st->execute([':c' => $code, ':b' => $body, ':cat' => $category]);
        return ['ok' => true];
    }
}
