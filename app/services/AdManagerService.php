<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class AdManagerService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS ad_placements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                slot_key VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                image_url VARCHAR(500),
                link_url VARCHAR(500),
                html_code TEXT,
                slot_type ENUM('banner','sidebar','inline','popup','footer') DEFAULT 'banner',
                status ENUM('active','inactive') DEFAULT 'active',
                sort_order INT DEFAULT 0,
                views INT DEFAULT 0,
                clicks INT DEFAULT 0,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slot_key (slot_key),
                INDEX idx_status (status),
                INDEX idx_tenant (tenant_id),
                UNIQUE KEY uk_slot_tenant (slot_key, tenant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
    }

    public function getSlot(string $slotKey): ?array
    {
        try {
            $sql = "SELECT * FROM ad_placements WHERE slot_key = ? AND status = 'active'" . $this->tenantSql() . " LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$slotKey]);
            $ad = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($ad) {
                $this->incrementViews($ad['id']);
                return $ad;
            }
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
        return null;
    }

    public function getSlotsByType(string $type): array
    {
        try {
            $sql = "SELECT * FROM ad_placements WHERE slot_type = ? AND status = 'active'" . $this->tenantSql() . " ORDER BY sort_order ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllSlots(): array
    {
        try {
            $sql = "SELECT * FROM ad_placements" . $this->tenantSql() . " ORDER BY slot_type, sort_order ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function upsertSlot(array $data): bool
    {
        try {
            $insertData = $this->tenantInsertData();
            if (!empty($data['id'])) {
                $sql = "UPDATE ad_placements SET slot_key=?, title=?, content=?, image_url=?, link_url=?, html_code=?, slot_type=?, status=?, sort_order=?" . $this->tenantSql() . " WHERE id=?";
                $params = [$data['slot_key'], $data['title'], $data['content'] ?? '', $data['image_url'] ?? '', $data['link_url'] ?? '', $data['html_code'] ?? '', $data['slot_type'] ?? 'banner', $data['status'] ?? 'active', (int)($data['sort_order'] ?? 0)];
                if ($this->tenantId() > 1) $params[] = $this->tenantId();
                $params[] = $data['id'];
                return $this->db->prepare($sql)->execute($params);
            } else {
                $columns = ['slot_key', 'title', 'content', 'image_url', 'link_url', 'html_code', 'slot_type', 'status', 'sort_order'];
                $placeholders = ['?', '?', '?', '?', '?', '?', '?', '?', '?'];
                if (!empty($insertData)) {
                    $columns = array_merge($columns, array_keys($insertData));
                    $placeholders = array_merge($placeholders, array_fill(0, count($insertData), '?'));
                }
                $sql = "INSERT INTO ad_placements (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
                $params = [$data['slot_key'], $data['title'], $data['content'] ?? '', $data['image_url'] ?? '', $data['link_url'] ?? '', $data['html_code'] ?? '', $data['slot_type'] ?? 'banner', $data['status'] ?? 'active', (int)($data['sort_order'] ?? 0)];
                if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
                return $this->db->prepare($sql)->execute($params);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteSlot(int $id): bool
    {
        try {
            $sql = "DELETE FROM ad_placements WHERE id = ?" . $this->tenantSql();
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            return $this->db->prepare($sql)->execute($params);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function incrementViews(int $id): void
    {
        try {
            $sql = "UPDATE ad_placements SET views = views + 1 WHERE id = ?" . $this->tenantSql();
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $this->db->prepare($sql)->execute($params);
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
    }

    public function incrementClicks(int $id): void
    {
        try {
            $sql = "UPDATE ad_placements SET clicks = clicks + 1 WHERE id = ?" . $this->tenantSql();
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $this->db->prepare($sql)->execute($params);
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
    }

    public function renderSlot(string $slotKey): string
    {
        $ad = $this->getSlot($slotKey);
        if (!$ad) return '';

        if (!empty($ad['html_code'])) return $ad['html_code'];

        $html = '<div class="ad-slot ad-' . htmlspecialchars($slotKey) . '">';
        if (!empty($ad['image_url']) && !empty($ad['link_url'])) {
            $html .= '<a href="' . BASE_URL . '/ad-click/' . $ad['id'] . '" target="_blank" rel="sponsored">';
            $html .= '<img loading="lazy" src="' . htmlspecialchars($ad['image_url']) . '" alt="' . htmlspecialchars($ad['title']) . '" class="img-fluid">';
            $html .= '</a>';
        } elseif (!empty($ad['content'])) {
            $html .= htmlspecialchars($ad['content']);
        }
        $html .= '</div>';
        return $html;
    }
}
