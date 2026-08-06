<?php
/**
 * Search Helper
 * 
 * Full-text search for properties, leads, plots
 * Supports filtering and sorting
 */

namespace App\Helpers;

use App\Core\Database\Database;

class Search
{
    /**
     * Search properties with filters
     */
    public static function properties(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = ["p.status = 'available'"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['colony_id'])) {
            $where[] = "p.colony_id = ?";
            $params[] = (int) $filters['colony_id'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = (float) $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = (float) $filters['max_price'];
        }

        if (!empty($filters['property_type'])) {
            $where[] = "p.property_type = ?";
            $params[] = $filters['property_type'];
        }

        if (!empty($filters['min_area'])) {
            $where[] = "p.area >= ?";
            $params[] = (float) $filters['min_area'];
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = self::getOrderBy($filters['sort'] ?? 'newest');

        $sql = "SELECT p.*, c.name as colony_name 
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy}";

        return $db->fetchAll($sql, $params) ?? [];
    }

    /**
     * Search leads with filters
     */
    public static function leads(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['status'])) {
            $where[] = "l.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['source'])) {
            $where[] = "l.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[] = "l.assigned_to = ?";
            $params[] = (int) $filters['assigned_to'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = !empty($filters['sort']) ? self::getLeadOrderBy($filters['sort']) : "l.created_at DESC";

        $sql = "SELECT l.*, u.name as assigned_name 
                FROM leads l 
                LEFT JOIN users u ON l.assigned_to = u.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy}";

        return $db->fetchAll($sql, $params) ?? [];
    }

    /**
     * Search plots with filters
     */
    public static function plots(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(p.plot_number LIKE ? OR c.name LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['colony_id'])) {
            $where[] = "p.colony_id = ?";
            $params[] = (int) $filters['colony_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['min_size'])) {
            $where[] = "p.area >= ?";
            $params[] = (float) $filters['min_size'];
        }

        if (!empty($filters['max_size'])) {
            $where[] = "p.area <= ?";
            $params[] = (float) $filters['max_size'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT p.*, c.name as colony_name 
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                WHERE {$whereClause} 
                ORDER BY p.plot_number ASC";

        return $db->fetchAll($sql, $params) ?? [];
    }

    /**
     * Search users with filters
     */
    public static function users(array $filters = []): array
    {
        $db = Database::getInstance();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['role'])) {
            $where[] = "u.role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $where[] = "u.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT u.* FROM users u WHERE {$whereClause} ORDER BY u.name ASC";

        return $db->fetchAll($sql, $params) ?? [];
    }

    /**
     * Get order by clause for properties
     */
    private static function getOrderBy(string $sort): string
    {
        return match ($sort) {
            'price_low' => 'p.price ASC',
            'price_high' => 'p.price DESC',
            'area_large' => 'p.area DESC',
            'area_small' => 'p.area ASC',
            'oldest' => 'p.created_at ASC',
            default => 'p.created_at DESC',
        };
    }

    /**
     * Get order by clause for leads
     */
    private static function getLeadOrderBy(string $sort): string
    {
        return match ($sort) {
            'name_asc' => 'l.name ASC',
            'name_desc' => 'l.name DESC',
            'oldest' => 'l.created_at ASC',
            'score_high' => 'l.lead_score DESC',
            default => 'l.created_at DESC',
        };
    }
}
