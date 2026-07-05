<?php

namespace App\Services;

use App\Core\Database\Database;

class AdManagerService
{
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
                slot_key VARCHAR(50) NOT NULL UNIQUE,
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slot_key (slot_key),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
    }

    public function getSlot(string $slotKey): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM ad_placements WHERE slot_key = ? AND status = 'active' LIMIT 1");
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
            $stmt = $this->db->prepare("SELECT * FROM ad_placements WHERE slot_type = ? AND status = 'active' ORDER BY sort_order ASC");
            $stmt->execute([$type]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllSlots(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM ad_placements ORDER BY slot_type, sort_order ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function upsertSlot(array $data): bool
    {
        try {
            if (!empty($data['id'])) {
                $stmt = $this->db->prepare("UPDATE ad_placements SET slot_key=?, title=?, content=?, image_url=?, link_url=?, html_code=?, slot_type=?, status=?, sort_order=? WHERE id=?");
                return $stmt->execute([$data['slot_key'], $data['title'], $data['content'] ?? '', $data['image_url'] ?? '', $data['link_url'] ?? '', $data['html_code'] ?? '', $data['slot_type'] ?? 'banner', $data['status'] ?? 'active', (int)($data['sort_order'] ?? 0), $data['id']]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO ad_placements (slot_key, title, content, image_url, link_url, html_code, slot_type, status, sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
                return $stmt->execute([$data['slot_key'], $data['title'], $data['content'] ?? '', $data['image_url'] ?? '', $data['link_url'] ?? '', $data['html_code'] ?? '', $data['slot_type'] ?? 'banner', $data['status'] ?? 'active', (int)($data['sort_order'] ?? 0)]);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteSlot(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ad_placements WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function incrementViews(int $id): void
    {
        try {
            $this->db->prepare("UPDATE ad_placements SET views = views + 1 WHERE id = ?")->execute([$id]);
        } catch (\Exception $e) { error_log('AdManagerService exception: ' . $e->getMessage()); }
    }

    public function incrementClicks(int $id): void
    {
        try {
            $this->db->prepare("UPDATE ad_placements SET clicks = clicks + 1 WHERE id = ?")->execute([$id]);
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
