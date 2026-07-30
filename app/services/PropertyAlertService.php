<?php

namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

/**
 * Property Alert Service
 * Customers subscribe to property alerts; auto-notified on matches
 */
class PropertyAlertService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        } elseif (is_object($db) && method_exists($db, 'getPdo')) {
            $db = $db->getPdo();
        }
        $this->db = $db;
        $this->pdo = $db;
    }

    public function subscribe(array $data): int
    {
        $required = ['property_type', 'listing_type', 'email', 'name'];
        foreach ($required as $r) {
            if (empty($data[$r])) throw new \InvalidArgumentException("Missing required field: $r");
        }
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("INSERT INTO property_alert_subscriptions
            (user_id, email, phone, name, property_type, listing_type, city, state, min_price, max_price, min_area_sqft, max_area_sqft, bedrooms, notify_email, notify_sms, notify_whatsapp, frequency, unsubscribe_token, tenant_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['email'],
            $data['phone'] ?? null,
            $data['name'],
            $data['property_type'],
            $data['listing_type'],
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['min_price'] ?? null,
            $data['max_price'] ?? null,
            $data['min_area_sqft'] ?? null,
            $data['max_area_sqft'] ?? null,
            $data['bedrooms'] ?? null,
            !empty($data['notify_email']) ? 1 : 0,
            !empty($data['notify_sms']) ? 1 : 0,
            !empty($data['notify_whatsapp']) ? 1 : 0,
            $data['frequency'] ?? 'daily',
            $token,
            $this->tenantId()
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function unsubscribe(string $token): bool
    {
        $stmt = $this->pdo->prepare("UPDATE property_alert_subscriptions SET is_active = 0 WHERE unsubscribe_token = ? AND tenant_id = ?");
        $stmt->execute([$token, $this->tenantId()]);
        return $stmt->rowCount() > 0;
    }

    public function getByUser(int $userId): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM property_alert_subscriptions WHERE user_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findMatches(array $criteria, int $limit = 20): array
    {
        $sql = "SELECT * FROM user_properties WHERE status = 'approved'";
        $params = [];
        if (!empty($criteria['property_type'])) {
            $sql .= " AND property_type = ?";
            $params[] = $criteria['property_type'];
        }
        if (!empty($criteria['listing_type'])) {
            $sql .= " AND listing_type = ?";
            $params[] = $criteria['listing_type'];
        }
        if (!empty($criteria['city'])) {
            $sql .= " AND (city LIKE ? OR address LIKE ?)";
            $params[] = '%' . $criteria['city'] . '%';
            $params[] = '%' . $criteria['city'] . '%';
        }
        if (!empty($criteria['min_price'])) {
            $sql .= " AND price >= ?";
            $params[] = (float)$criteria['min_price'];
        }
        if (!empty($criteria['max_price'])) {
            $sql .= " AND price <= ?";
            $params[] = (float)$criteria['max_price'];
        }
        if (!empty($criteria['min_area_sqft'])) {
            $sql .= " AND area_sqft >= ?";
            $params[] = (int)$criteria['min_area_sqft'];
        }
        if (!empty($criteria['max_area_sqft'])) {
            $sql .= " AND area_sqft <= ?";
            $params[] = (int)$criteria['max_area_sqft'];
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

    public function getActiveSubscriptions(string $frequency = ''): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM property_alert_subscriptions WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [];
        if ($frequency) {
            $sql .= " AND frequency = ?";
            $params[] = $frequency;
        }
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function logNotification(int $subId, int $propId, ?int $userId, string $channel, string $status, ?string $message = null): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO property_alert_log (subscription_id, property_id, user_id, channel, status, message, sent_at, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$subId, $propId, $userId, $channel, $status, $message, $status === 'sent' ? date('Y-m-d H:i:s') : null, $this->tenantId()]);
        return (int)$this->pdo->lastInsertId();
    }

    public function markNotified(int $subId): void
    {
        $stmt = $this->pdo->prepare("UPDATE property_alert_subscriptions SET last_notified_at = NOW(), total_notifications = total_notifications + 1 WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$subId, $this->tenantId()]);
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $stats = [
            'total' => 0, 'active' => 0, 'instant' => 0, 'daily' => 0, 'weekly' => 0,
            'notifications_sent' => 0, 'top_property_types' => []
        ];
        try {
            $stats['total'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_alert_subscriptions{$tenantWhere}", $tid > 1 ? [$tid] : [])->fetchColumn();
            $stats['active'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_alert_subscriptions WHERE is_active = 1{$tenantWhere}", $tid > 1 ? [$tid] : [])->fetchColumn();
            foreach (['instant', 'daily', 'weekly'] as $f) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM property_alert_subscriptions WHERE frequency = ? AND is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""));
                $stmt->execute($tid > 1 ? [$f, $tid] : [$f]);
                $stats[$f] = (int)$stmt->fetchColumn();
            }
            $stats['notifications_sent'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_alert_log WHERE status = 'sent'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : [])->fetchColumn();
            $stmt = $this->pdo->prepare("SELECT property_type, COUNT(*) as count FROM property_alert_subscriptions WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " GROUP BY property_type ORDER BY count DESC LIMIT 5");
            $stmt->execute($tid > 1 ? [$tid] : []);
            $stats['top_property_types'] = $stmt->fetchAll();
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }

    private function getTenantId(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }
}
