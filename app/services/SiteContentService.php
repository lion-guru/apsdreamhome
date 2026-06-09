<?php
namespace App\Services;

use App\Core\Database;

class SiteContentService
{
    private static ?self $instance = null;
    private $db;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getPdo()
    {
        return $this->db->getPdo();
    }

    /**
     * Get all content for a section as key=>value associative array
     */
    public function getSection(string $section): array
    {
        try {
            $rows = $this->getPdo()->prepare("SELECT content_key, content_value, content_type, content_group, sort_order FROM site_content WHERE section = ? AND is_active = 1 ORDER BY sort_order ASC");
            $rows->execute([$section]);
            $result = [];
            foreach ($rows->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $result[$row['content_key']] = $row['content_value'];
            }
            return $result;
        } catch (\Exception $e) {
            error_log('SiteContentService::getSection error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single content value by section + key
     */
    public function get(string $section, string $key, string $default = ''): string
    {
        try {
            $row = $this->getPdo()->prepare("SELECT content_value FROM site_content WHERE section = ? AND content_key = ? AND is_active = 1");
            $row->execute([$section, $key]);
            $data = $row->fetch(\PDO::FETCH_ASSOC);
            return $data ? ($data['content_value'] ?? $default) : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get all content grouped by content_group for a section
     */
    public function getGrouped(string $section): array
    {
        try {
            $rows = $this->getPdo()->prepare("SELECT content_key, content_value, content_type, content_group, sort_order FROM site_content WHERE section = ? AND is_active = 1 ORDER BY content_group ASC, sort_order ASC");
            $rows->execute([$section]);
            $result = [];
            foreach ($rows->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $group = $row['content_group'] ?? 'default';
                $result[$group][$row['content_key']] = $row['content_value'];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all content for a section with full metadata (for admin editing)
     */
    public function getFullSection(string $section): array
    {
        try {
            $rows = $this->getPdo()->prepare("SELECT * FROM site_content WHERE section = ? ORDER BY content_group ASC, sort_order ASC");
            $rows->execute([$section]);
            return $rows->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Update a single content value
     */
    public function update(string $section, string $key, string $value): bool
    {
        try {
            $stmt = $this->getPdo()->prepare("UPDATE site_content SET content_value = ?, updated_at = NOW() WHERE section = ? AND content_key = ?");
            return $stmt->execute([$value, $section, $key]);
        } catch (\Exception $e) {
            error_log('SiteContentService::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk update multiple values for a section
     */
    public function bulkUpdate(string $section, array $data): bool
    {
        try {
            $pdo = $this->getPdo();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE site_content SET content_value = ?, updated_at = NOW() WHERE section = ? AND content_key = ?");
            foreach ($data as $key => $value) {
                $stmt->execute([$value, $section, $key]);
            }
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->getPdo()->rollBack();
            error_log('SiteContentService::bulkUpdate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert new content entry
     */
    public function create(array $data): bool
    {
        try {
            $stmt = $this->getPdo()->prepare("INSERT INTO site_content (section, content_key, content_value, content_type, content_group, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['section'] ?? '',
                $data['content_key'] ?? '',
                $data['content_value'] ?? '',
                $data['content_type'] ?? 'text',
                $data['content_group'] ?? null,
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1
            ]);
        } catch (\Exception $e) {
            error_log('SiteContentService::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete content entry
     */
    public function delete(string $section, string $key): bool
    {
        try {
            $stmt = $this->getPdo()->prepare("DELETE FROM site_content WHERE section = ? AND content_key = ?");
            return $stmt->execute([$section, $key]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all sections
     */
    public function getSections(): array
    {
        try {
            $rows = $this->getPdo()->query("SELECT DISTINCT section, COUNT(*) as item_count FROM site_content GROUP BY section ORDER BY section");
            return $rows->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
