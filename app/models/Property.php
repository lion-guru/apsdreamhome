<?php

namespace App\Models;

class Property extends Model
{
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

    public static function getFeaturedProperties($limit = 6)
    {
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

    public function getPropertyById($id)
    {
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

            $sql = "SELECT * FROM properties WHERE " . implode(" AND ", $where) . " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            return array_map(fn($result) => new static($result), $results);
        } catch (\Exception $e) {
            error_log("Error in searchProperties: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get properties for admin with filters and pagination
     */
    public static function getAdminProperties($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.city LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "p.status = :status";
                $params['status'] = $filters['status'];
            }

            // Featured filter
            if (isset($filters['featured']) && $filters['featured'] !== '') {
                $where_conditions[] = "p.featured = :featured";
                $params['featured'] = (int)$filters['featured'];
            }

            // Type filter
            if (!empty($filters['type'])) {
                $where_conditions[] = "pt.type = :type";
                $params['type'] = $filters['type'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'title', 'price', 'created_at', 'status'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY p.{$sort} {$order}";

            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.status,
                    p.featured,
                    p.city,
                    p.created_at,
                    pt.type as property_type
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                {$where_clause}
                {$order_clause}
                LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':limit', (int)$filters['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)(($filters['page'] - 1) * $filters['per_page']), \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Admin properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total properties count for pagination
     */
    public static function getAdminTotalProperties($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.city LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "p.status = :status";
                $params['status'] = $filters['status'];
            }

            // Featured filter
            if (isset($filters['featured']) && $filters['featured'] !== '') {
                $where_conditions[] = "p.featured = :featured";
                $params['featured'] = (int)$filters['featured'];
            }

            // Type filter
            if (!empty($filters['type'])) {
                $where_conditions[] = "pt.type = :type";
                $params['type'] = $filters['type'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total 
                    FROM properties p 
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    {$where_clause}";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            error_log('Admin total properties query error: ' . $e->getMessage());
            return 0;
        }
    }
}
