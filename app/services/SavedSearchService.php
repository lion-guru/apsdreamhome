<?php

namespace App\Services;

use PDO;

/**
 * Saved Searches Service
 * Save/load/share complex filter combinations for any entity
 */
class SavedSearchService
{
    private PDO $db;

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
    }

    public function save(int $userId, string $role, string $name, string $entityType, array $filters, ?string $description = null, bool $isFavorite = false, bool $isPublic = false): int
    {
        $stmt = $this->db->prepare("INSERT INTO saved_searches (user_id, user_role, name, description, entity_type, filters, is_favorite, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $name, $description, $entityType, json_encode($filters, JSON_UNESCAPED_UNICODE), $isFavorite ? 1 : 0, $isPublic ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM saved_searches WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $row ?: null;
    }

    public function update(int $id, int $userId, string $role, array $data): bool
    {
        $allowed = ['name', 'description', 'filters', 'is_favorite', 'is_public'];
        $sets = [];
        $vals = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = ?";
                $vals[] = $field === 'filters' ? json_encode($data[$field], JSON_UNESCAPED_UNICODE) : $data[$field];
            }
        }
        if (empty($sets)) return false;
        $vals[] = $id;
        $vals[] = $userId;
        $vals[] = $role;
        $stmt = $this->db->prepare("UPDATE saved_searches SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ? AND user_role = ?");
        $stmt->execute($vals);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("DELETE FROM saved_searches WHERE id = ? AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$id, $userId, $role]);
        return $stmt->rowCount() > 0;
    }

    public function toggleFavorite(int $id, int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("UPDATE saved_searches SET is_favorite = NOT is_favorite WHERE id = ? AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$id, $userId, $role]);
        return $stmt->rowCount() > 0;
    }

    public function recordUse(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE saved_searches SET use_count = use_count + 1, last_used_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }

    public function list(int $userId, string $role, string $entityType = '', bool $favoritesOnly = false): array
    {
        $sql = "SELECT * FROM saved_searches WHERE (user_id = ? AND user_role = ? OR is_public = 1)";
        $params = [$userId, $role];
        if ($entityType) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }
        if ($favoritesOnly) {
            $sql .= " AND is_favorite = 1";
        }
        $sql .= " ORDER BY is_favorite DESC, use_count DESC, last_used_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $rows;
    }

    public function recordHistory(int $userId, string $role, string $entityType, array $filters, ?int $resultsCount = null): void
    {
        $stmt = $this->db->prepare("INSERT INTO search_history (user_id, user_role, entity_type, filters, results_count, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $entityType, json_encode($filters, JSON_UNESCAPED_UNICODE), $resultsCount, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    public function getHistory(int $userId, string $role, string $entityType = '', int $limit = 20): array
    {
        $sql = "SELECT * FROM search_history WHERE user_id = ? AND user_role = ?";
        $params = [$userId, $role];
        if ($entityType) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['filters'] = json_decode($row['filters'], true) ?: [];
        }
        return $rows;
    }

    public function getStats(int $userId, string $role): array
    {
        $stmt = $this->db->prepare("SELECT entity_type, COUNT(*) as count, SUM(use_count) as total_uses, MAX(last_used_at) as last_used FROM saved_searches WHERE user_id = ? AND user_role = ? OR is_public = 1 GROUP BY entity_type");
        $stmt->execute([$userId, $role]);
        $byEntity = $stmt->fetchAll();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE user_id = ? AND user_role = ?");
        $stmt->execute([$userId, $role]);
        $mine = $stmt->fetch();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE is_public = 1");
        $stmt->execute();
        $public = $stmt->fetch();
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM saved_searches WHERE is_favorite = 1 AND (user_id = ? AND user_role = ? OR is_public = 1)");
        $stmt->execute([$userId, $role]);
        $fav = $stmt->fetch();
        return [
            'my_searches' => (int)($mine['total'] ?? 0),
            'public_searches' => (int)($public['total'] ?? 0),
            'favorites' => (int)($fav['total'] ?? 0),
            'by_entity' => $byEntity
        ];
    }
}
