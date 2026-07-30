<?php

namespace App\Services;

/**
 * Property Comparison Service
 * Save up to 4 properties for side-by-side comparison
 */
class PropertyComparisonService
{
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

    public function getOrCreateActive(int $userId = 0, string $sessionId = ''): array
    {
        $sql = "SELECT * FROM property_comparisons WHERE is_active = 1";
        $params = [];
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        } elseif ($sessionId) {
            $sql .= " AND session_id = ?";
            $params[] = $sessionId;
        } else {
            return ['id' => 0, 'property_ids' => '[]'];
        }
        $sql .= " ORDER BY updated_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row) return $row;
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("INSERT INTO property_comparisons (user_id, session_id, property_ids, share_token) VALUES (?, ?, '[]', ?)");
        $stmt->execute([$userId ?: null, $sessionId ?: null, $token]);
        return ['id' => (int)$this->pdo->lastInsertId(), 'user_id' => $userId, 'session_id' => $sessionId, 'property_ids' => '[]', 'share_token' => $token];
    }

    public function addProperty(int $compId, int $propertyId): array
    {
        $row = $this->getById($compId);
        if (!$row) return ['success' => false, 'error' => 'Comparison list not found'];
        $ids = json_decode($row['property_ids'] ?? '[]', true) ?: [];
        $ids = array_filter($ids, fn($x) => $x != $propertyId);
        $ids[] = $propertyId;
        if (count($ids) > 4) {
            return ['success' => false, 'error' => 'Maximum 4 properties allowed. Remove one first.'];
        }
        $stmt = $this->pdo->prepare("UPDATE property_comparisons SET property_ids = ? WHERE id = ?");
        $stmt->execute([json_encode(array_values($ids)), $compId]);
        return ['success' => true, 'count' => count($ids), 'ids' => array_values($ids)];
    }

    public function removeProperty(int $compId, int $propertyId): array
    {
        $row = $this->getById($compId);
        if (!$row) return ['success' => false, 'error' => 'Comparison list not found'];
        $ids = json_decode($row['property_ids'] ?? '[]', true) ?: [];
        $ids = array_values(array_filter($ids, fn($x) => $x != $propertyId));
        $stmt = $this->pdo->prepare("UPDATE property_comparisons SET property_ids = ? WHERE id = ?");
        $stmt->execute([json_encode($ids), $compId]);
        return ['success' => true, 'count' => count($ids), 'ids' => $ids];
    }

    public function clear(int $compId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE property_comparisons SET property_ids = '[]' WHERE id = ?");
        $stmt->execute([$compId]);
        return true;
    }

    public function getById(int $compId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM property_comparisons WHERE id = ?");
        $stmt->execute([$compId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM property_comparisons WHERE share_token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) {
            $this->pdo->prepare("UPDATE property_comparisons SET view_count = view_count + 1 WHERE id = ?")->execute([$row['id']]);
        }
        return $row ?: null;
    }

    public function getProperties(array $ids): array
    {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user_properties WHERE id IN ($placeholders) AND status = 'approved'");
            $stmt->execute(array_map('intval', $ids));
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getStats(): array
    {
        $stats = ['total_lists' => 0, 'total_views' => 0, 'avg_properties_per_list' => 0];
        try {
            $stats['total_lists'] = (int)$this->pdo->query("SELECT COUNT(*) FROM property_comparisons")->fetchColumn();
            $stats['total_views'] = (int)$this->pdo->query("SELECT COALESCE(SUM(view_count), 0) FROM property_comparisons")->fetchColumn();
            $stats['avg_properties_per_list'] = round((float)$this->pdo->query("SELECT AVG(JSON_LENGTH(property_ids)) FROM property_comparisons")->fetchColumn(), 1);
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        return $stats;
    }

    public function computeComparisonData(array $properties): array
    {
        if (empty($properties)) return [];
        $prices = array_column($properties, 'price');
        $areas = array_column($properties, 'area_sqft');
        $perSqft = [];
        foreach ($properties as $p) {
            if (!empty($p['price']) && !empty($p['area_sqft']) && $p['area_sqft'] > 0) {
                $perSqft[$p['id']] = round((float)$p['price'] / (float)$p['area_sqft'], 2);
            }
        }
        return [
            'cheapest' => !empty($prices) ? min($prices) : 0,
            'priciest' => !empty($prices) ? max($prices) : 0,
            'avg_price' => !empty($prices) ? round(array_sum($prices) / count($prices), 2) : 0,
            'largest' => !empty($areas) ? max($areas) : 0,
            'best_value_id' => !empty($perSqft) ? array_keys($perSqft, min($perSqft))[0] : null
        ];
    }
}
