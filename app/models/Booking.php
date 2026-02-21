<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Model;
use PDO;

class Booking extends Model
{
    protected static $table = 'bookings';
    protected static $primaryKey = 'id';

    /**
     * Get bookings for admin with filters and pagination
     */
    public static function getAdminBookings($filters)
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(booking_number LIKE :search_booking OR customer_id IN (SELECT id FROM users WHERE role = 'customer' AND name LIKE :search_customer))";
                $term = '%' . $filters['search'] . '%';
                $params['search_booking'] = $term;
                $params['search_customer'] = $term;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $allowed_sorts = ['id', 'booking_number', 'booking_date', 'status'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'booking_date';
            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

            $limit = (int)($filters['per_page'] ?? 10);
            $offset = (int)((($filters['page'] ?? 1) - 1) * $limit);

            $sql = "SELECT b.*, u.name as customer_name, p.title as property_title 
                    FROM bookings b 
                    LEFT JOIN users u ON b.customer_id = u.id 
                    LEFT JOIN properties p ON b.property_id = p.id 
                    {$where_clause} 
                    ORDER BY b.{$sort} {$order} 
                    LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Admin bookings query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total bookings count for pagination
     */
    public static function getAdminTotalBookings($filters)
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(booking_number LIKE :search_booking OR customer_id IN (SELECT id FROM users WHERE role = 'customer' AND name LIKE :search_customer))";
                $term = '%' . $filters['search'] . '%';
                $params['search_booking'] = $term;
                $params['search_customer'] = $term;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT COUNT(*) FROM bookings {$where_clause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Admin total bookings query error: ' . $e->getMessage());
            return 0;
        }
    }
}
