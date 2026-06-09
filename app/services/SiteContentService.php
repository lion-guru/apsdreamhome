<?php
namespace App\Services;

use App\Core\Database;

class SiteContentService
{
    private static ?self $instance = null;
    private $db;

    /** @var array In-memory cache: section => [key => value] */
    private static array $memCache = [];

    /** File cache directory (relative to project root) */
    private string $cacheDir;
    private int $cacheTtl = 300; // 5 minutes

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->cacheDir = dirname(__DIR__, 2) . '/storage/cache/site_content';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getPdo()
    {
        return $this->db->getPdo();
    }

    private function cachePath(string $section): string
    {
        return $this->cacheDir . '/' . md5($section) . '.cache';
    }

    private function readCache(string $section): ?array
    {
        $path = $this->cachePath($section);
        if (!file_exists($path)) return null;
        $age = time() - filemtime($path);
        if ($age > $this->cacheTtl) return null;
        $data = @unserialize(file_get_contents($path));
        return is_array($data) ? $data : null;
    }

    private function writeCache(string $section, array $data): void
    {
        @file_put_contents($this->cachePath($section), serialize($data));
    }

    private function clearCacheFor(string $section): void
    {
        unset(self::$memCache[$section]);
        $path = $this->cachePath($section);
        if (file_exists($path)) @unlink($path);
    }

    /**
     * Get all content for a section as key=>value associative array
     */
    public function getSection(string $section): array
    {
        if (isset(self::$memCache[$section])) return self::$memCache[$section];

        $cached = $this->readCache($section);
        if ($cached !== null) {
            self::$memCache[$section] = $cached;
            return $cached;
        }

        try {
            $rows = $this->getPdo()->prepare("SELECT content_key, content_value, content_type, content_group, sort_order FROM site_content WHERE section = ? AND is_active = 1 ORDER BY sort_order ASC");
            $rows->execute([$section]);
            $result = [];
            foreach ($rows->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $result[$row['content_key']] = $row['content_value'];
            }
            self::$memCache[$section] = $result;
            $this->writeCache($section, $result);
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
        $all = $this->getSection($section);
        return $all[$key] ?? $default;
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
            $ok = $stmt->execute([$value, $section, $key]);
            if ($ok) $this->clearCacheFor($section);
            return $ok;
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
            $this->clearCacheFor($section);
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
            $ok = $stmt->execute([
                $data['section'] ?? '',
                $data['content_key'] ?? '',
                $data['content_value'] ?? '',
                $data['content_type'] ?? 'text',
                $data['content_group'] ?? null,
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1
            ]);
            if ($ok) $this->clearCacheFor($data['section'] ?? '');
            return $ok;
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
            $ok = $stmt->execute([$section, $key]);
            if ($ok) $this->clearCacheFor($section);
            return $ok;
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
