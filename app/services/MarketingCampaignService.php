<?php

namespace App\Services;

/**
 * Marketing Campaign Service
 * Create, manage, and track marketing campaigns
 */
class MarketingCampaignService
{
    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = new \PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
        $this->pdo = $db;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO marketing_campaigns
            (name, description, type, status, target_audience, target_filters, subject, content, template_id, scheduled_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['type'] ?? 'email',
            $data['status'] ?? 'draft',
            $data['target_audience'] ?? null,
            isset($data['target_filters']) ? json_encode($data['target_filters']) : null,
            $data['subject'] ?? null,
            $data['content'],
            $data['template_id'] ?? null,
            $data['scheduled_at'] ?? null,
            $data['created_by'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getAll(int $limit = 50, string $status = ''): array
    {
        $sql = "SELECT c.*, COALESCE(u.name, 'System') as creator_name FROM marketing_campaigns c LEFT JOIN users u ON u.id = c.created_by";
        $params = [];
        if ($status) {
            $sql .= " WHERE c.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY c.created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_campaigns WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateStats(int $id, array $stats): void
    {
        $stmt = $this->pdo->prepare("UPDATE marketing_campaigns SET sent_count = ?, delivered_count = ?, opened_count = ?, clicked_count = ?, failed_count = ?, unsubscribed_count = ? WHERE id = ?");
        $stmt->execute([
            $stats['sent'] ?? 0,
            $stats['delivered'] ?? 0,
            $stats['opened'] ?? 0,
            $stats['clicked'] ?? 0,
            $stats['failed'] ?? 0,
            $stats['unsubscribed'] ?? 0,
            $id
        ]);
    }

    public function getRecipients(int $campaignId, string $status = '', int $limit = 100): array
    {
        $sql = "SELECT * FROM marketing_campaign_recipients WHERE campaign_id = ?";
        $params = [$campaignId];
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addRecipient(int $campaignId, array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO marketing_campaign_recipients (campaign_id, user_id, email, phone, name, channel) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $campaignId,
            $data['user_id'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['name'] ?? null,
            $data['channel']
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function renderTemplate(string $body, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $body = str_replace('{{' . $k . '}}', (string)$v, $body);
        }
        return $body;
    }

    public function getTemplates(string $type = ''): array
    {
        $sql = "SELECT * FROM marketing_campaign_templates";
        $params = [];
        if ($type) {
            $sql .= " WHERE type = ? AND is_active = 1";
            $params[] = $type;
        } else {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY usage_count DESC, name ASC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getTemplateById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM marketing_campaign_templates WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function incrementTemplateUsage(int $id): void
    {
        $this->pdo->prepare("UPDATE marketing_campaign_templates SET usage_count = usage_count + 1 WHERE id = ?")->execute([$id]);
    }

    public function getAudienceList(array $filters): array
    {
        $sql = "SELECT id, name, email, phone FROM users WHERE status = 'active'";
        $params = [];
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['has_email'])) {
            $sql .= " AND email IS NOT NULL AND email != ''";
        }
        if (!empty($filters['has_phone'])) {
            $sql .= " AND phone IS NOT NULL AND phone != ''";
        }
        if (!empty($filters['city'])) {
            $sql .= " AND (address LIKE ? OR name LIKE ?)";
            $params[] = '%' . $filters['city'] . '%';
            $params[] = '%' . $filters['city'] . '%';
        }
        $sql .= " LIMIT 1000";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function recordUnsubscribe(array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO marketing_unsubscribes (user_id, email, phone, channel, reason, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['channel'],
            $data['reason'] ?? null,
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function isUnsubscribed(string $channel, ?string $email = null, ?string $phone = null): bool
    {
        $sql = "SELECT 1 FROM marketing_unsubscribes WHERE channel = ?";
        $params = [$channel];
        if ($email) {
            $sql .= " AND email = ?";
            $params[] = $email;
        } elseif ($phone) {
            $sql .= " AND phone = ?";
            $params[] = $phone;
        } else {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getStats(): array
    {
        $stats = [
            'total_campaigns' => 0, 'draft' => 0, 'sent' => 0, 'scheduled' => 0, 'sending' => 0,
            'total_recipients' => 0, 'total_sent' => 0, 'total_delivered' => 0, 'total_opened' => 0, 'total_clicked' => 0,
            'total_unsubscribed' => 0, 'avg_open_rate' => 0, 'avg_click_rate' => 0, 'by_type' => []
        ];
        try {
            $stats['total_campaigns'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaigns")->fetchColumn();
            foreach (['draft', 'sent', 'scheduled', 'sending'] as $s) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM marketing_campaigns WHERE status = ?");
                $stmt->execute([$s]);
                $stats[$s] = (int)$stmt->fetchColumn();
            }
            $stats['total_recipients'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaign_recipients")->fetchColumn();
            $stats['total_sent'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaign_recipients WHERE status IN ('sent','delivered','opened','clicked')")->fetchColumn();
            $stats['total_delivered'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaign_recipients WHERE status IN ('delivered','opened','clicked')")->fetchColumn();
            $stats['total_opened'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaign_recipients WHERE status IN ('opened','clicked')")->fetchColumn();
            $stats['total_clicked'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_campaign_recipients WHERE status = 'clicked'")->fetchColumn();
            $stats['total_unsubscribed'] = (int)$this->pdo->query("SELECT COUNT(*) FROM marketing_unsubscribes")->fetchColumn();
            if ($stats['total_delivered'] > 0) {
                $stats['avg_open_rate'] = round(($stats['total_opened'] / $stats['total_delivered']) * 100, 2);
                $stats['avg_click_rate'] = round(($stats['total_clicked'] / $stats['total_delivered']) * 100, 2);
            }
            $stmt = $this->pdo->query("SELECT type, COUNT(*) as count, SUM(sent_count) as sent FROM marketing_campaigns GROUP BY type ORDER BY count DESC");
            $stats['by_type'] = $stmt->fetchAll();
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }
}
