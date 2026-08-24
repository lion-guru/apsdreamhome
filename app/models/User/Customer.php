<?php

namespace App\Models;

use App\Models\Model;
use App\Core\Database;
use PDO;

/**
 * Customer Model
 * Handles all customer-related database operations including properties, bookings, payments, and preferences
 */
class Customer extends Model
{
    protected static $table = 'users';
    protected static $primaryKey = 'id';
    protected $db;

    public function __construct(array $attributes = [])
    {
        $parent = get_parent_class($this);
        if ($parent && method_exists($parent, '__construct')) {
            parent::__construct($attributes);
        }
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Search users for AJAX/Select2
     */
    public function searchCustomers($search = '', $limit = 10, $offset = 0)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "SELECT u.id, u.name, u.email, u.phone 
                FROM users u
                WHERE u.role = 'customer' 
                AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)
                ORDER BY u.name ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $row['id'],
                'text' => $row['name'] . ' (' . ($row['phone'] ?? 'N/A') . ')'
            ];
        }

        // Get total count
        $sqlCount = "SELECT COUNT(*) as count 
                     FROM users u
                     WHERE u.role = 'customer' 
                     AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        $stmtCount->execute();
        $totalCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'items' => $items,
            'total' => $totalCount
        ];
    }

    /**
     * Get all users (used by Controller)
     */
    public function getAllCustomers($search = '')
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "SELECT u.*, cp.phone as profile_phone, cp.city, cp.state 
                FROM users u
                LEFT JOIN users cp ON u.id = cp.user_id
                WHERE u.role = 'customer'";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
            $params['search'] = "%$search%";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer by ID with complete details
     */
    public function getCustomerById($id)
    {
        try {
            $sql = "
                SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode, c.date_of_birth, c.occupation,
                       c.marital_status, c.anniversary_date, c.referral_source, c.created_at as customer_since,
                       COUNT(DISTINCT p.id) as total_properties_viewed,
                       COUNT(DISTINCT b.id) as total_bookings,
                       COUNT(DISTINCT pay.id) as total_payments,
                       COALESCE(SUM(CASE WHEN pay.status = 'completed' THEN pay.amount ELSE 0 END), 0) as total_spent,
                       COALESCE(AVG(pr.rating), 0) as avg_rating_given,
                       COUNT(DISTINCT pr.id) as total_reviews_given,
                       (SELECT COUNT(*) FROM customer_favorites cf WHERE cf.customer_id = u.id) as total_favorites,
                       (SELECT COUNT(*) FROM customer_alerts ca WHERE ca.customer_id = u.id AND ca.status = 'active') as active_alerts
                FROM {$this->table} u
                LEFT JOIN users c ON u.id = c.user_id
                LEFT JOIN property_views pv ON u.id = pv.customer_id
                LEFT JOIN properties p ON pv.property_id = p.id
                LEFT JOIN bookings b ON u.id = b.customer_id
                LEFT JOIN payments pay ON u.id = pay.user_id
                LEFT JOIN property_reviews pr ON u.id = pr.customer_id
                WHERE u.id = :id AND u.role = 'customer' AND u.status = 'active'
                GROUP BY u.id
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $db = Database::getInstance();
        $stmt = $db->query($sql, ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get users for admin with filters and pagination
     */
    public static function getAdminCustomers($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = ["u.role = 'customer'"];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'name', 'email', 'created_at', 'status'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY u.{$sort} {$order}";

            $sql = "SELECT u.*, cp.phone as profile_phone, cp.city 
                    FROM users u
                    LEFT JOIN users cp ON u.id = cp.user_id
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Admin users query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total users count for pagination
     */
    public static function getAdminTotalCustomers($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = ["role = 'customer'"];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM users {$where_clause}";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            error_log('Admin total users query error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get customer by email
     */
    public function getCustomerByEmail($email)
    {
        $sql = "
            SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode
            FROM {$this->table} u
            LEFT JOIN users c ON u.id = c.user_id
            WHERE u.email = :email AND u.role = 'customer' AND u.status = 'active'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate customer
     */
    public function authenticateCustomer($email, $password)
    {
        $customer = $this->getCustomerByEmail($email);

        if ($customer && password_verify($password, $customer['password'])) {
            return $customer;
        }

        return false;
    }

    /**
     * Create new customer
     */
    public function registerCustomer($data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Insert into users table with all profile fields that exist in schema
            $table = static::$table;
            $sql = "
                INSERT INTO {$table} (
                    name, email, password, phone, role, status, 
                    address, city, state, pincode, date_of_birth, occupation,
                    referral_code, created_at, updated_at
                ) VALUES (
                    :name, :email, :password, :phone, 'customer', 'active',
                    :address, :city, :state, :pincode, :date_of_birth, :occupation,
                    :referral_code, NOW(), NOW()
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'] ?? 'default123', PASSWORD_DEFAULT),
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'referral_code' => $data['referral_source'] ?? null,
            ]);

            $customerId = $this->db->lastInsertId();

            // Commit transaction
            $this->db->commit();

            return $customerId;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update customer profile
     */
    public function updateCustomerProfile($customerId, $data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update users table with all fields that exist in schema
            $userData = [];
            $userParams = ['id' => $customerId];

            $allowedFields = [
                'name', 'email', 'phone', 'address', 'city', 'state', 'pincode',
                'date_of_birth', 'occupation', 'referral_code'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $userData[] = "{$field} = :{$field}";
                    $userParams[$field] = $data[$field];
                }
            }

            // Map referral_source to referral_code
            if (isset($data['referral_source'])) {
                $userData[] = 'referral_code = :referral_code';
                $userParams['referral_code'] = $data['referral_source'];
            }

            if (!empty($userData)) {
                $userSql = "UPDATE {$this->table} SET " . implode(', ', $userData) . ", updated_at = NOW() WHERE id = :id";
                $userStmt = $this->db->prepare($userSql);
                $userStmt->execute($userParams);
            }

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get customer's favorite properties
     */
    public function getCustomerFavorites($customerId, $filters = [])
    {
        $conditions = ["cf.customer_id = :customer_id"];
        $params = ['customer_id' => $customerId];

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "p.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['min_price'])) {
            $conditions[] = "p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                   cf.created_at as favorited_at
            FROM customer_favorites cf
            JOIN properties p ON cf.property_id = p.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            {$whereClause}
            ORDER BY cf.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add property to favorites
     */
    public function addToFavorites($customerId, $propertyId)
    {
        $sql = "
            INSERT INTO customer_favorites (customer_id, property_id, created_at)
            VALUES (:customer_id, :property_id, NOW())
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId
        ]);
    }

    /**
     * Remove property from favorites
     */
    public function removeFromFavorites($customerId, $propertyId)
    {
        $sql = "
            DELETE FROM customer_favorites
            WHERE customer_id = :customer_id AND property_id = :property_id
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId
        ]);
    }

    /**
     * Get customer's property views history
     */
    public function getPropertyViews($customerId, $limit = 20)
    {
        $sql = "
            SELECT pv.*, p.title, p.price, p.address, p.city, p.state, p.bedrooms, p.bathrooms,
                   pt.name as property_type, pt.icon as property_type_icon,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   pv.viewed_at, pv.time_spent_seconds, pv.source
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE pv.customer_id = :customer_id
            ORDER BY pv.viewed_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'customer_id' => $customerId,
            'limit' => $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's bookings
     */
    public function getCustomerBookings($customerId, $filters = [])
    {
        $conditions = ["b.customer_id = :customer_id"];
        $params = ['customer_id' => $customerId];

        if (!empty($filters['status'])) {
            $conditions[] = "b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "b.booking_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "b.booking_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT b.*, p.title, p.price, p.address, p.city, p.state, p.bedrooms, p.bathrooms, p.area_sqft,
                   pt.name as property_type, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone, u.email as agent_email,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   b.created_at as booking_created_at
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            {$whereClause}
            ORDER BY b.booking_date DESC, b.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's payments
     */
    public function getCustomerPayments($customerId, $filters = [])
    {
        $conditions = ["pay.user_id = :customer_id"];
        $params = ['customer_id' => $customerId];

        if (!empty($filters['status'])) {
            $conditions[] = "pay.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = "pay.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "pay.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "pay.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['min_amount'])) {
            $conditions[] = "pay.amount >= :min_amount";
            $params['min_amount'] = $filters['min_amount'];
        }

        if (!empty($filters['max_amount'])) {
            $conditions[] = "pay.amount <= :max_amount";
            $params['max_amount'] = $filters['max_amount'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT pay.*, p.title as property_title, p.address as property_address, p.city, p.state,
                   pt.name as property_type, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone,
                   b.booking_date, b.status as booking_status,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   pay.created_at as payment_date
            FROM payments pay
            LEFT JOIN properties p ON pay.property_id = p.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN bookings b ON pay.booking_id = b.id
            {$whereClause}
            ORDER BY pay.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's reviews
     */
    public function getCustomerReviews($customerId, $filters = [])
    {
        $conditions = ["pr.customer_id = :customer_id"];
        $params = ['customer_id' => $customerId];

        if (!empty($filters['rating'])) {
            $conditions[] = "pr.rating = :rating";
            $params['rating'] = $filters['rating'];
        }

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "pr.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "pr.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT pr.*, p.title as property_title, p.address as property_address, p.city, p.state,
                   pt.name as property_type, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   pr.created_at as review_date
            FROM property_reviews pr
            JOIN properties p ON pr.property_id = p.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            {$whereClause}
            ORDER BY pr.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer's alerts/preferences
     */
    public function getCustomerAlerts($customerId, $filters = [])
    {
        $conditions = ["ca.customer_id = :customer_id"];
        $params = ['customer_id' => $customerId];

        if (!empty($filters['status'])) {
            $conditions[] = "ca.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = "ca.alert_type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "ca.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "ca.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        try {
            $sql = "
                SELECT ca.*, p.title as property_title, p.city, p.state, p.price,
                       pt.name as property_type,
                       ca.created_at as alert_created_at
                FROM customer_alerts ca
                LEFT JOIN properties p ON ca.property_id = p.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                {$whereClause}
                ORDER BY ca.created_at DESC
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create property alert
     */
    public function createPropertyAlert($customerId, $data)
    {
        try {
            $sql = "
                INSERT INTO customer_alerts (
                    customer_id, property_type_id, city, state, min_price, max_price,
                    min_bedrooms, max_bedrooms, alert_type, frequency, status, created_at, updated_at
                ) VALUES (
                    :customer_id, :property_type_id, :city, :state, :min_price, :max_price,
                    :min_bedrooms, :max_bedrooms, :alert_type, :frequency, 'active', NOW(), NOW()
                )
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_type_id' => $data['property_type_id'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'min_price' => $data['min_price'] ?? null,
            'max_price' => $data['max_price'] ?? null,
            'min_bedrooms' => $data['min_bedrooms'] ?? null,
            'max_bedrooms' => $data['max_bedrooms'] ?? null,
            'alert_type' => $data['alert_type'] ?? 'email',
            'frequency' => $data['frequency'] ?? 'daily'
        ]);
    }

    /**
     * Get customer's dashboard statistics
     */
    public function getCustomerStats($customerId)
    {
        $stats = [];

        // Total favorites
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM customer_favorites WHERE customer_id = :customer_id");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['total_favorites'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM bookings WHERE customer_id = :customer_id");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['total_bookings'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total payments
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM payments WHERE user_id = :customer_id");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['total_payments'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total spent
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE user_id = :customer_id AND status = 'completed'");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['total_spent'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        try {
            // Active alerts
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM customer_alerts WHERE customer_id = :customer_id AND status = 'active'");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute(['customer_id' => $customerId]);
        $stats['active_alerts'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Properties viewed this month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM property_views
            WHERE customer_id = :customer_id AND viewed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['properties_viewed_month'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Average rating given
        $stmt = $this->db->prepare("SELECT COALESCE(AVG(rating), 0) as avg FROM property_reviews WHERE customer_id = :customer_id");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['avg_rating_given'] = round((float)$stmt->fetch(PDO::FETCH_ASSOC)['avg'], 1);

        // Recent activities count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM (
                SELECT id FROM property_views WHERE customer_id = :customer_id AND viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                UNION ALL
                SELECT id FROM customer_favorites WHERE customer_id = :customer_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                UNION ALL
                SELECT id FROM bookings WHERE customer_id = :customer_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ) as recent_activities
        ");
        $stmt->execute(['customer_id' => $customerId]);
        $stats['recent_activities'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    /**
     * Get customer's recent activities
     */
    public function getCustomerActivities($customerId, $limit = 10)
    {
        $sql = "
            SELECT 'property_view' as activity_type, pv.viewed_at as activity_date,
                   p.title as property_title, p.city, p.state, pv.time_spent_seconds
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE pv.customer_id = :customer_id

            UNION ALL

            SELECT 'favorite_added' as activity_type, cf.created_at as activity_date,
                   p.title as property_title, p.city, p.state, NULL as time_spent_seconds
            FROM customer_favorites cf
            JOIN properties p ON cf.property_id = p.id
            WHERE cf.customer_id = :customer_id

            UNION ALL

            SELECT 'booking_made' as activity_type, b.created_at as activity_date,
                   p.title as property_title, p.city, p.state, NULL as time_spent_seconds
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            WHERE b.customer_id = :customer_id

            UNION ALL

            SELECT 'payment_made' as activity_type, pay.created_at as activity_date,
                   p.title as property_title, p.city, p.state, NULL as time_spent_seconds
            FROM payments pay
            JOIN properties p ON pay.property_id = p.id
            WHERE pay.user_id = :customer_id

            ORDER BY activity_date DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'customer_id' => $customerId,
            'limit' => $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get property recommendations for customer
     */
    public function getPropertyRecommendations($customerId, $limit = 6)
    {
        // Get customer's preferences from their activity
        $preferencesSql = "
            SELECT
                p.property_type_id,
                p.city,
                p.state,
                AVG(p.price) as avg_price_range,
                AVG(p.bedrooms) as avg_bedrooms,
                AVG(p.bathrooms) as avg_bathrooms
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE pv.customer_id = :customer_id
              AND pv.viewed_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY p.property_type_id, p.city, p.state
            ORDER BY COUNT(*) DESC
            LIMIT 3
        ";

        $stmt = $this->db->prepare($preferencesSql);
        $stmt->execute(['customer_id' => $customerId]);
        $preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($preferences)) {
            // Fallback to general recommendations
            $sql = "
                SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                       u.name as agent_name, u.phone as agent_phone,
                       (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                       (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                       (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews,
                       RAND() as random_order
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.status = 'available' AND p.featured = 1
                ORDER BY random_order
                LIMIT :limit
            ";
        } else {
            // Personalized recommendations based on preferences
            $conditions = [];
            $params = ['customer_id' => $customerId, 'limit' => $limit];

            foreach ($preferences as $index => $pref) {
                $conditions[] = "
                    (p.property_type_id = :type{$index}
                     AND p.city = :city{$index}
                     AND p.state = :state{$index}
                     AND p.price BETWEEN :min_price{$index} AND :max_price{$index})
                ";
                $params["type{$index}"] = $pref['property_type_id'];
                $params["city{$index}"] = $pref['city'];
                $params["state{$index}"] = $pref['state'];
                $params["min_price{$index}"] = max(0, $pref['avg_price_range'] * 0.7);
                $params["max_price{$index}"] = $pref['avg_price_range'] * 1.3;
            }

            $whereClause = "WHERE (" . implode(' OR ', $conditions) . ") AND p.status = 'available'";

            $sql = "
                SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                       u.name as agent_name, u.phone as agent_phone,
                       (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                       (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                       (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN users u ON p.created_by = u.id
                {$whereClause}
                ORDER BY
                    CASE
            ";

            foreach ($preferences as $index => $pref) {
                $sql .= "
                        WHEN (p.property_type_id = :type{$index}
                              AND p.city = :city{$index}
                              AND p.state = :state{$index}) THEN {$index}
                ";
            }

            $sql .= " ELSE 999 END, p.created_at DESC LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search properties for customer
     */
    public function searchProperties($customerId, $searchData)
    {
        $conditions = ["p.status = 'available'"];
        $params = [];

        // Basic search filters
        if (!empty($searchData['property_type'])) {
            $conditions[] = "p.property_type_id = :property_type";
            $params['property_type'] = $searchData['property_type'];
        }

        if (!empty($searchData['city'])) {
            $conditions[] = "p.city = :city";
            $params['city'] = $searchData['city'];
        }

        if (!empty($searchData['state'])) {
            $conditions[] = "p.state = :state";
            $params['state'] = $searchData['state'];
        }

        if (!empty($searchData['min_price'])) {
            $conditions[] = "p.price >= :min_price";
            $params['min_price'] = $searchData['min_price'];
        }

        if (!empty($searchData['max_price'])) {
            $conditions[] = "p.price <= :max_price";
            $params['max_price'] = $searchData['max_price'];
        }

        if (!empty($searchData['bedrooms'])) {
            $conditions[] = "p.bedrooms >= :bedrooms";
            $params['bedrooms'] = $searchData['bedrooms'];
        }

        if (!empty($searchData['bathrooms'])) {
            $conditions[] = "p.bathrooms >= :bathrooms";
            $params['bathrooms'] = $searchData['bathrooms'];
        }

        if (!empty($searchData['min_area'])) {
            $conditions[] = "p.area_sqft >= :min_area";
            $params['min_area'] = $searchData['min_area'];
        }

        if (!empty($searchData['max_area'])) {
            $conditions[] = "p.area_sqft <= :max_area";
            $params['max_area'] = $searchData['max_area'];
        }

        // Text search
        if (!empty($searchData['search'])) {
            $conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.address LIKE :search OR p.city LIKE :search)";
            $params['search'] = "%{$searchData['search']}%";
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $sql = "
            SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone, u.email as agent_email,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                   (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                   (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews,
                   (SELECT COUNT(*) FROM customer_favorites cf WHERE cf.property_id = p.id AND cf.customer_id = :customer_id) as is_favorited,
                   (SELECT COUNT(*) FROM property_views pv WHERE pv.property_id = p.id AND pv.customer_id = :customer_id) as has_viewed
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            {$whereClause}
            ORDER BY p.featured DESC, p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get property details with customer context
     */
    public function getPropertyDetails($propertyId, $customerId = null)
    {
        $sql = "
            SELECT p.*, pt.name as property_type_name, pt.icon as property_type_icon,
                   u.name as agent_name, u.phone as agent_phone, u.email as agent_email, u.profile_image as agent_image,
                   (SELECT GROUP_CONCAT(pi.image_path SEPARATOR ',') FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC) as all_images,
                   (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image,
                   (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                   (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                   (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews,
                   (SELECT COUNT(*) FROM customer_favorites cf WHERE cf.property_id = p.id AND cf.customer_id = :customer_id) as is_favorited,
                   (SELECT COUNT(*) FROM property_views pv WHERE pv.property_id = p.id AND pv.customer_id = :customer_id) as has_viewed
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = :property_id AND p.status = 'available'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'property_id' => $propertyId,
            'customer_id' => $customerId
        ]);

        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($property && $customerId) {
            // Track property view
            $this->trackPropertyView($customerId, $propertyId, $_SERVER['HTTP_REFERER'] ?? 'direct');
        }

        return $property;
    }

    /**
     * Track property view
     */
    public function trackPropertyView($customerId, $propertyId, $source = 'direct')
    {
        $sql = "
            INSERT INTO property_views (customer_id, property_id, view_duration, viewed_at)
            VALUES (:customer_id, :property_id, 0, NOW())
            ON DUPLICATE KEY UPDATE
                viewed_at = NOW(),
                view_duration = view_duration + 1
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId,
        ]);
    }

    /**
     * Submit property review
     */
    public function submitPropertyReview($customerId, $propertyId, $data)
    {
        $sql = "
            INSERT INTO property_reviews (
                customer_id, property_id, rating, review_text, anonymous, status, created_at, updated_at
            ) VALUES (
                :customer_id, :property_id, :rating, :review_text, :anonymous, 'pending', NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                review_text = VALUES(review_text),
                anonymous = VALUES(anonymous),
                updated_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId,
            'rating' => $data['rating'],
            'review_text' => $data['review_text'] ?? null,
            'anonymous' => $data['anonymous'] ?? 0
        ]);
    }

    /**
     * Get customer's EMI calculator history
     */
    public function getEMICalculatorHistory($customerId, $limit = 10)
    {
        try {
            $sql = "
                SELECT ech.*, p.title as property_title, p.price as property_price, p.city, p.state
                FROM emi_calculator_history ech
                LEFT JOIN properties p ON ech.property_id = p.id
                WHERE ech.customer_id = :customer_id
                ORDER BY ech.created_at DESC
                LIMIT :limit
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'customer_id' => $customerId,
            'limit' => $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save EMI calculation
     */
    public function saveEMICalculation($customerId, $propertyId, $data)
    {
        try {
            $sql = "
                INSERT INTO emi_calculator_history (
                    customer_id, property_id, loan_amount, interest_rate, loan_tenure,
                    monthly_emi, total_interest, total_payment, created_at
                ) VALUES (
                    :customer_id, :property_id, :loan_amount, :interest_rate, :loan_tenure,
                    :monthly_emi, :total_interest, :total_payment, NOW()
                )
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId,
            'loan_amount' => $data['loan_amount'],
            'interest_rate' => $data['interest_rate'],
            'loan_tenure' => $data['loan_tenure'],
            'monthly_emi' => $data['monthly_emi'],
            'total_interest' => $data['total_interest'],
            'total_payment' => $data['total_payment']
        ]);
    }

    /**
     * Get users for admin panel
     */
    public function getCustomersForAdmin($filters = [])
    {
        $conditions = ["u.role = 'customer' AND u.status = 'active'"];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['city'])) {
            $conditions[] = "c.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['state'])) {
            $conditions[] = "c.state = :state";
            $params['state'] = $filters['state'];
        }

        if (!empty($filters['registration_date_from'])) {
            $conditions[] = "u.created_at >= :registration_date_from";
            $params['registration_date_from'] = $filters['registration_date_from'];
        }

        if (!empty($filters['registration_date_to'])) {
            $conditions[] = "u.created_at <= :registration_date_to";
            $params['registration_date_to'] = $filters['registration_date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode, c.occupation,
                   COUNT(DISTINCT pv.id) as total_views,
                   COUNT(DISTINCT cf.id) as total_favorites,
                   COUNT(DISTINCT b.id) as total_bookings,
                   COALESCE(SUM(CASE WHEN pay.status = 'completed' THEN pay.amount ELSE 0 END), 0) as total_spent,
                   COUNT(DISTINCT pr.id) as total_reviews,
                   u.created_at as registration_date
            FROM {$this->table} u
            LEFT JOIN users c ON u.id = c.user_id
            LEFT JOIN property_views pv ON u.id = pv.customer_id
            LEFT JOIN customer_favorites cf ON u.id = cf.customer_id
            LEFT JOIN bookings b ON u.id = b.customer_id
            LEFT JOIN payments pay ON u.id = pay.user_id
            LEFT JOIN property_reviews pr ON u.id = pr.customer_id
            {$whereClause}
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Convert customer to associate
     */
    public function convertToAssociate($customerId, $sponsorId = null)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Get customer details
            $customer = $this->getCustomerById($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Check if customer is already an associate
            if (($customer['role'] ?? '') === 'associate') {
                throw new \Exception('Customer is already an associate');
            }

            // Generate unique associate code (stored in referral_code)
            $associateCode = $this->generateAssociateCode();

            // Update existing user record with associate data using columns that exist in users table
            $sql = "
                UPDATE {$this->table} SET 
                    role = 'associate',
                    sponsor_id = :sponsor_id,
                    referral_code = :referral_code,
                    mlm_rank = 'Associate',
                    commission_rate = 6.00,
                    mlm_target = 1000000.00,
                    current_level = 1,
                    mlm_position = 'none',
                    updated_at = NOW()
                WHERE id = :customer_id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'sponsor_id' => $sponsorId,
                'referral_code' => $associateCode,
                'customer_id' => $customerId
            ]);

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'associate_code' => $associateCode,
                'message' => 'Customer successfully converted to associate'
            ];
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique associate code
     */
    private function generateAssociateCode()
    {
        do {
            $code = 'APS' . strtoupper(substr(md5(uniqid()), 0, 8));

            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE associate_code = :code");
            $stmt->execute(['code' => $code]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } while ($result['count'] > 0);

        return $code;
    }

    /**
     * Get customer's potential associate benefits
     */
    public function getAssociateBenefits($customerId)
    {
        // Get customer's purchase history and activity
        $customerStats = $this->getCustomerStats($customerId);

        $benefits = [
            'current_level' => 'associate',
            'potential_earnings' => 0,
            'team_building_opportunity' => false,
            'rewards_eligible' => false,
            'referral_benefits' => []
        ];

        // Calculate potential earnings based on purchase history
        $totalSpent = $customerStats['total_spent'] ?? 0;
        $referralCount = $customerStats['total_bookings'] ?? 0;

        if ($totalSpent >= 100000) {
            $benefits['current_level'] = 'senior_associate';
            $benefits['potential_earnings'] = $totalSpent * 0.05; // 5% commission potential
        }

        if ($totalSpent >= 500000) {
            $benefits['current_level'] = 'bdm';
            $benefits['potential_earnings'] = $totalSpent * 0.10; // 10% commission potential
        }

        if ($totalSpent >= 1000000) {
            $benefits['current_level'] = 'sr_bdm';
            $benefits['potential_earnings'] = $totalSpent * 0.15; // 15% commission potential
        }

        // Team building opportunity
        if ($referralCount >= 3) {
            $benefits['team_building_opportunity'] = true;
            $benefits['potential_team_earnings'] = $referralCount * 10000; // Potential earnings from referrals
        }

        // Rewards eligibility
        if ($totalSpent >= 200000 || $referralCount >= 5) {
            $benefits['rewards_eligible'] = true;
            $benefits['rewards'] = [
                'Free Property Consultation',
                'Priority Customer Support',
                'Exclusive Property Deals'
            ];
        }

        // Referral benefits
        $benefits['referral_benefits'] = [
            'commission_rate' => $benefits['current_level'] === 'associate' ? '1%' : ($benefits['current_level'] === 'senior_associate' ? '1.5%' : '2%'),
            'monthly_earnings_potential' => round($benefits['potential_earnings'] / 12),
            'team_building_bonus' => $benefits['team_building_opportunity'] ? '₹10,000 per referral' : 'Not eligible yet'
        ];

        return $benefits;
    }

    /**
     * Get users who are potential users
     */
    public function getPotentialAssociates($filters = [])
    {
        $conditions = ["u.role = 'customer' AND u.status = 'active'"];
        $params = [];

        if (!empty($filters['min_spending'])) {
            $conditions[] = "COALESCE((SELECT SUM(amount) FROM payments WHERE user_id = u.id AND status = 'completed'), 0) >= :min_spending";
            $params['min_spending'] = $filters['min_spending'];
        }

        if (!empty($filters['min_bookings'])) {
            $conditions[] = "COALESCE((SELECT COUNT(*) FROM bookings WHERE user_id = u.id), 0) >= :min_bookings";
            $params['min_bookings'] = $filters['min_bookings'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "c.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['registration_date_from'])) {
            $conditions[] = "u.created_at >= :registration_date_from";
            $params['registration_date_from'] = $filters['registration_date_from'];
        }

        if (!empty($filters['registration_date_to'])) {
            $conditions[] = "u.created_at <= :registration_date_to";
            $params['registration_date_to'] = $filters['registration_date_to'];
        }

        $whereClause = "WHERE " . implode(' AND ', $conditions);

        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $limit = $filters['per_page'];

        $sql = "
            SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode, c.occupation,
                   COALESCE((SELECT SUM(amount) FROM payments WHERE user_id = u.id AND status = 'completed'), 0) as total_spent,
                   COALESCE((SELECT COUNT(*) FROM bookings WHERE user_id = u.id), 0) as total_bookings,
                   COALESCE((SELECT COUNT(*) FROM customer_favorites WHERE customer_id = u.id), 0) as total_favorites,
                   COALESCE((SELECT COUNT(*) FROM property_views WHERE customer_id = u.id), 0) as total_views,
                   u.created_at as registration_date
            FROM {$this->table} u
            LEFT JOIN users c ON u.id = c.user_id
            {$whereClause}
            ORDER BY total_spent DESC, total_bookings DESC
            LIMIT {$offset}, {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Send associate invitation to customer
     */
    public function sendAssociateInvitation($customerId, $sponsorId, $message = null)
    {
        // Check if customer is already an associate
        $existingAssociate = $this->db->prepare("SELECT associate_id FROM users WHERE user_id = :user_id");
        $existingAssociate->execute(['user_id' => $customerId]);
        if ($existingAssociate->fetch()) {
            return [
                'success' => false,
                'message' => 'Customer is already an associate'
            ];
        }

        // Create invitation record
        $sql = "
            INSERT INTO associate_invitations (
                customer_id, sponsor_id, invitation_message, status, sent_at, expires_at
            ) VALUES (
                :customer_id, :sponsor_id, :message, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)
            )
        ";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            'customer_id' => $customerId,
            'sponsor_id' => $sponsorId,
            'message' => $message
        ]);

        if ($success) {
            // Get customer email for notification
            $customer = $this->getCustomerById($customerId);
            if ($customer && $customer['email']) {
                // Send email invitation (you can implement email sending here)
                $this->sendInvitationEmail($customer['email'], $message);
            }

            return [
                'success' => true,
                'message' => 'Invitation sent successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to send invitation'
        ];
    }

    /**
     * Send invitation email (placeholder - implement actual email sending)
     */
    private function sendInvitationEmail($email, $message)
    {
        // This would integrate with your email system
        // For now, just log the email
        error_log("Associate invitation email sent to: $email");
    }

    /**
     * Get customer's associate invitations
     */
    public function getAssociateInvitations($customerId)
    {
        $sql = "
            SELECT ai.*, u.name as sponsor_name, u.email as sponsor_email,
                   a.associate_code as sponsor_code
            FROM associate_invitations ai
            JOIN users a ON ai.sponsor_id = a.associate_id
            JOIN users u ON a.user_id = u.id
            WHERE ai.customer_id = :customer_id AND ai.status = 'pending'
            AND ai.expires_at > NOW()
            ORDER BY ai.sent_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Accept associate invitation
     */
    public function acceptAssociateInvitation($invitationId)
    {
        // Get invitation details
        $invitationSql = "
            SELECT * FROM associate_invitations
            WHERE id = :invitation_id AND status = 'pending' AND expires_at > NOW()
        ";

        $invitationStmt = $this->db->prepare($invitationSql);
        $invitationStmt->execute(['invitation_id' => $invitationId]);
        $invitation = $invitationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$invitation) {
            return [
                'success' => false,
                'message' => 'Invalid or expired invitation'
            ];
        }

        // Convert customer to associate
        $conversionResult = $this->convertToAssociate($invitation['customer_id'], $invitation['sponsor_id']);

        if ($conversionResult['success']) {
            // Update invitation status
            $updateSql = "
                UPDATE associate_invitations
                SET status = 'accepted', accepted_at = NOW()
                WHERE id = :invitation_id
            ";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute(['invitation_id' => $invitationId]);

            return [
                'success' => true,
                'message' => 'Successfully joined as associate',
                'associate_code' => $conversionResult['associate_code']
            ];
        }

        return $conversionResult;
    }

    /**
     * Get EMI schedule for a booking
     */
    public function getEmiSchedule($bookingId)
    {
        try {
            // Get booking details first
            $bookingSql = "
                SELECT b.*, p.title as property_title, p.price as property_price,
                       u.name as customer_name, u.phone as customer_phone, u.email as customer_email
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.customer_id = u.id
                WHERE b.id = :booking_id
            ";
            $bookingStmt = $this->db->prepare($bookingSql);
            $bookingStmt->execute(['booking_id' => $bookingId]);
            $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                return [
                    'success' => false,
                    'message' => 'Booking not found'
                ];
            }

            // Get EMI installments
            $emiSql = "
                SELECT ei.*, b.customer_id, b.property_id,
                       DATEDIFF(ei.due_date, CURDATE()) as days_until_due,
                       CASE 
                           WHEN ei.status = 'paid' THEN 'paid'
                           WHEN ei.due_date < CURDATE() AND ei.status != 'paid' THEN 'overdue'
                           ELSE 'pending'
                       END as computed_status
                FROM emi_installments ei
                JOIN bookings b ON ei.booking_id = b.id
                WHERE ei.booking_id = :booking_id
                ORDER BY ei.emi_number ASC
            ";
            $emiStmt = $this->db->prepare($emiSql);
            $emiStmt->execute(['booking_id' => $bookingId]);
            $installments = $emiStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate summary
            $totalPaid = 0;
            $totalPending = 0;
            $overdueCount = 0;
            foreach ($installments as $emi) {
                if ($emi['status'] === 'paid') {
                    $totalPaid += (float)($emi['amount'] ?? 0);
                } else {
                    $totalPending += (float)($emi['amount'] ?? 0);
                    if ($emi['computed_status'] === 'overdue') {
                        $overdueCount++;
                    }
                }
            }

            return [
                'success' => true,
                'data' => [
                    'booking' => $booking,
                    'installments' => $installments,
                    'summary' => [
                        'total_installments' => count($installments),
                        'paid_installments' => count(array_filter($installments, fn($e) => $e['status'] === 'paid')),
                        'pending_installments' => count(array_filter($installments, fn($e) => $e['status'] !== 'paid')),
                        'overdue_count' => $overdueCount,
                        'total_paid' => $totalPaid,
                        'total_pending' => $totalPending,
                        'next_due_date' => $this->getNextDueDate($bookingId),
                    ]
                ]
            ];
        } catch (\Exception $e) {
            error_log("[Customer] getEmiSchedule exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch EMI schedule: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get next due date for a booking
     */
    private function getNextDueDate($bookingId)
    {
        $sql = "
            SELECT MIN(due_date) as next_due
            FROM emi_installments
            WHERE booking_id = :booking_id AND status != 'paid'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['next_due'] ?? null;
    }

    /**
     * Record EMI payment
     */
    public function recordEmiPayment($emiId, $amount, $method = 'Online')
    {
        try {
            // Get EMI details (real schema: emi_installments -> emi_plans)
            $emiSql = "
                SELECT ei.*, ep.customer_id, ep.property_id
                FROM emi_installments ei
                JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                WHERE ei.id = :emi_id
            ";
            $emiStmt = $this->db->prepare($emiSql);
            $emiStmt->execute(['emi_id' => $emiId]);
            $emi = $emiStmt->fetch(PDO::FETCH_ASSOC);

            if (!$emi) {
                return [
                    'success' => false,
                    'message' => 'EMI installment not found'
                ];
            }

            if (($emi['payment_status'] ?? '') === 'paid') {
                return [
                    'success' => false,
                    'message' => 'This EMI is already paid'
                ];
            }

            // Validate amount against installment due amount
            $dueAmount = (float)($emi['amount'] ?? 0);

            if ((float)$amount > $dueAmount + 0.01) { // Small tolerance for rounding
                return [
                    'success' => false,
                    'message' => 'Payment amount exceeds installment amount of ₹' . number_format($dueAmount, 2)
                ];
            }

            // Guard against dangling property FK (payments.property_id -> properties.id)
            $propertyId = $emi['property_id'] ?? null;
            if ($propertyId) {
                $chk = $this->db->prepare("SELECT id FROM properties WHERE id = :pid LIMIT 1");
                $chk->execute(['pid' => $propertyId]);
                if (!$chk->fetchColumn()) {
                    $propertyId = null; // FK is ON DELETE SET NULL — store NULL rather than dangling id
                }
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                $referenceNo = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

                // Record payment in payments table (real schema)
                $paymentSql = "
                    INSERT INTO payments (customer_id, user_id, property_id, emi_plan_id, emi_month,
                                          payment_type, amount, total_amount, gateway, status,
                                          transaction_id, reference_id, payment_date, payment_time, created_at)
                    VALUES (:customer_id, :user_id, :property_id, :emi_plan_id, :emi_month,
                           'emi', :amount, :total_amount, :gateway, 'completed',
                           :txn_id, :reference_no, CURDATE(), CURTIME(), NOW())
                ";
                $gatewayMap = ['Simulated' => 'cash', 'Online' => 'razorpay', 'UPI' => 'upi', 'Cash' => 'cash'];
                $gateway = $gatewayMap[$method] ?? 'cash';
                $paymentStmt = $this->db->prepare($paymentSql);
                $paymentStmt->execute([
                    'customer_id' => $emi['customer_id'],
                    'user_id' => $emi['customer_id'],
                    'property_id' => $propertyId,
                    'emi_plan_id' => $emi['emi_plan_id'],
                    'emi_month' => $emi['installment_number'] ?? null,
                    'amount' => $amount,
                    'total_amount' => $amount,
                    'gateway' => $gateway,
                    'txn_id' => $referenceNo,
                    'reference_no' => $referenceNo
                ]);
                $paymentId = (int)$this->db->lastInsertId();

                // Update EMI installment as paid (real columns)
                $updateEmiSql = "
                    UPDATE emi_installments 
                    SET payment_status = 'paid',
                        payment_date = CURDATE(),
                        payment_id = :payment_id,
                        updated_at = NOW()
                    WHERE id = :emi_id
                ";
                $updateStmt = $this->db->prepare($updateEmiSql);
                $updateStmt->execute([
                    'payment_id' => $paymentId,
                    'emi_id' => $emiId
                ]);

                // Update EMI plan status if all installments paid
                $this->updateEmiPlanStatus($emi['emi_plan_id']);

                $this->db->commit();

                return [
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'data' => [
                        'emi_id' => $emiId,
                        'amount_paid' => $amount,
                        'new_status' => 'paid',
                        'reference_no' => $referenceNo,
                        'remaining_balance' => 0
                    ]
                ];
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Throwable $e) {
            error_log("[Customer] recordEmiPayment exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update EMI plan status based on installment completion
     */
    private function updateEmiPlanStatus($emiPlanId)
    {
        $sql = "
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count
            FROM emi_installments
            WHERE emi_plan_id = :emi_plan_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['emi_plan_id' => $emiPlanId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && (int)$result['total'] > 0 && (int)$result['total'] === (int)$result['paid_count']) {
            // All installments paid - mark EMI plan completed
            $updateSql = "UPDATE emi_plans SET status = 'completed', updated_at = NOW() WHERE id = :emi_plan_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute(['emi_plan_id' => $emiPlanId]);
        }
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 1579 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//