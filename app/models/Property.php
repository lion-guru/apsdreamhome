<?php

namespace App\Models;

class Property extends Model {
    protected static string $table = 'properties';
    protected array $fillable = [
        'title',
        'description',
        'price',
        'location',
        'property_type',
        'bedrooms',
        'bathrooms',
        'area',
        'is_featured',
        'status',
        'image_path',
        'created_at',
        'updated_at'
    ];

    public function getFeaturedProperties($limit = 6) {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query(
                "SELECT * FROM properties
                 WHERE is_featured = 1
                 AND status = 'active'
                 ORDER BY created_at DESC
                 LIMIT ?",
                [$limit]
            );

            $results = $stmt->fetchAll();
            return array_map(fn($result) => new static($result), $results);
        } catch (\Exception $e) {
            error_log("Error in getFeaturedProperties: " . $e->getMessage());
            return [];
        }
    }

    public function getPropertyById($id) {
        try {
            return static::find($id);
        } catch (\Exception $e) {
            error_log("Error in getPropertyById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search properties with filters
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchProperties(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $params = [];
            $where = ["1=1"];

            // Keyword search (title, description, address)
            if (!empty($filters['keyword'])) {
                $where[] = "(title LIKE ? OR description LIKE ? OR address LIKE ?)";
                $keyword = "%{$filters['keyword']}%";
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
            }

            // Location search
            if (!empty($filters['location'])) {
                $where[] = "address LIKE ?";
                $params[] = "%{$filters['location']}%";
            }

            // Type filter
            if (!empty($filters['type'])) {
                $where[] = "type = ?";
                $params[] = $filters['type'];
            }

            // Price range
            if (!empty($filters['min_price'])) {
                $where[] = "price >= ?";
                $params[] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $where[] = "price <= ?";
                $params[] = $filters['max_price'];
            }

            // Bedrooms
            if (!empty($filters['bedrooms'])) {
                $where[] = "bedrooms >= ?";
                $params[] = $filters['bedrooms'];
            }

            // Bathrooms
            if (!empty($filters['bathrooms'])) {
                $where[] = "bathrooms >= ?";
                $params[] = $filters['bathrooms'];
            }

            // Build query
            $whereClause = implode(" AND ", $where);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM " . static::$table . " WHERE " . $whereClause;
            $stmt = $db->query($countSql, $params);
            $total = $stmt->fetch()['total'];

            // Get results
            $sql = "SELECT * FROM " . static::$table . " WHERE " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $db->query($sql, $params);
            $results = $stmt->fetchAll();

            return [
                'total' => $total,
                'properties' => $results
            ];

        } catch (\Exception $e) {
            error_log("Error in searchProperties: " . $e->getMessage());
            return ['total' => 0, 'properties' => []];
        }
    }
}
