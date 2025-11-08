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
    protected static string $table = 'users';
    protected $primaryKey = 'id';

    /**
     * Get customer by ID with complete details
     */
    public function getCustomerById($id)
    {
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
            LEFT JOIN customer_profiles c ON u.id = c.user_id
            LEFT JOIN property_views pv ON u.id = pv.customer_id
            LEFT JOIN properties p ON pv.property_id = p.id
            LEFT JOIN bookings b ON u.id = b.customer_id
            LEFT JOIN payments pay ON u.id = pay.user_id
            LEFT JOIN property_reviews pr ON u.id = pr.customer_id
            WHERE u.id = :id AND u.role = 'customer' AND u.status = 'active'
            GROUP BY u.id
        ";

        $db = Database::getInstance();
        $stmt = $db->query($sql, ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer by email
     */
    public function getCustomerByEmail($email)
    {
        $sql = "
            SELECT u.*, c.phone, c.address, c.city, c.state, c.pincode
            FROM {$this->table} u
            LEFT JOIN customer_profiles c ON u.id = c.user_id
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
    public function createCustomer($data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Insert into users table
            $sql = "
                INSERT INTO {$this->table} (
                    name, email, password, phone, role, status, email_verified, created_at, updated_at
                ) VALUES (
                    :name, :email, :password, :phone, 'customer', 'active', 0, NOW(), NOW()
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone']
            ]);

            $customerId = $this->db->lastInsertId();

            // Insert into customer_profiles table
            $profileSql = "
                INSERT INTO customer_profiles (
                    user_id, phone, address, city, state, pincode, date_of_birth,
                    occupation, marital_status, anniversary_date, referral_source, created_at, updated_at
                ) VALUES (
                    :user_id, :phone, :address, :city, :state, :pincode, :date_of_birth,
                    :occupation, :marital_status, :anniversary_date, :referral_source, NOW(), NOW()
                )
            ";

            $profileStmt = $this->db->prepare($profileSql);
            $profileStmt->execute([
                'user_id' => $customerId,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'anniversary_date' => $data['anniversary_date'] ?? null,
                'referral_source' => $data['referral_source'] ?? null
            ]);

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
            // Update users table
            $userData = [];
            $userParams = ['id' => $customerId];

            if (isset($data['name'])) {
                $userData[] = 'name = :name';
                $userParams['name'] = $data['name'];
            }

            if (isset($data['email'])) {
                $userData[] = 'email = :email';
                $userParams['email'] = $data['email'];
            }

            if (isset($data['phone'])) {
                $userData[] = 'phone = :phone';
                $userParams['phone'] = $data['phone'];
            }

            if (!empty($userData)) {
                $userSql = "UPDATE {$this->table} SET " . implode(', ', $userData) . ", updated_at = NOW() WHERE id = :id";
                $userStmt = $this->db->prepare($userSql);
                $userStmt->execute($userParams);
            }

            // Update customer_profiles table
            $profileData = [];
            $profileParams = ['user_id' => $customerId];

            $profileFields = [
                'phone', 'address', 'city', 'state', 'pincode', 'date_of_birth',
                'occupation', 'marital_status', 'anniversary_date', 'referral_source'
            ];

            foreach ($profileFields as $field) {
                if (isset($data[$field])) {
                    $profileData[] = "{$field} = :{$field}";
                    $profileParams[$field] = $data[$field];
                }
            }

            if (!empty($profileData)) {
                $profileSql = "
                    INSERT INTO customer_profiles (user_id, " . implode(', ', $profileFields) . ", created_at, updated_at)
                    VALUES (:user_id, " . implode(', ', array_map(function($field) { return ':' . $field; }, $profileFields)) . ", NOW(), NOW())
                    ON DUPLICATE KEY UPDATE " . implode(', ', $profileData) . ", updated_at = NOW()
                ";

                $profileStmt = $this->db->prepare($profileSql);
                $profileStmt->execute($profileParams);
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

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create property alert
     */
    public function createPropertyAlert($customerId, $data)
    {
        $sql = "
            INSERT INTO customer_alerts (
                customer_id, property_type_id, city, state, min_price, max_price,
                min_bedrooms, max_bedrooms, alert_type, frequency, status, created_at, updated_at
            ) VALUES (
                :customer_id, :property_type_id, :city, :state, :min_price, :max_price,
                :min_bedrooms, :max_bedrooms, :alert_type, :frequency, 'active', NOW(), NOW()
            )
        ";

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

        // Active alerts
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM customer_alerts WHERE customer_id = :customer_id AND status = 'active'");
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
            INSERT INTO property_views (customer_id, property_id, source, viewed_at, time_spent_seconds)
            VALUES (:customer_id, :property_id, :source, NOW(), 0)
            ON DUPLICATE KEY UPDATE
                source = VALUES(source),
                viewed_at = NOW(),
                time_spent_seconds = time_spent_seconds + 1
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $customerId,
            'property_id' => $propertyId,
            'source' => $source
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
        $sql = "
            SELECT ech.*, p.title as property_title, p.price as property_price, p.city, p.state
            FROM emi_calculator_history ech
            LEFT JOIN properties p ON ech.property_id = p.id
            WHERE ech.customer_id = :customer_id
            ORDER BY ech.created_at DESC
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
     * Save EMI calculation
     */
    public function saveEMICalculation($customerId, $propertyId, $data)
    {
        $sql = "
            INSERT INTO emi_calculator_history (
                customer_id, property_id, loan_amount, interest_rate, loan_tenure,
                monthly_emi, total_interest, total_payment, created_at
            ) VALUES (
                :customer_id, :property_id, :loan_amount, :interest_rate, :loan_tenure,
                :monthly_emi, :total_interest, :total_payment, NOW()
            )
        ";

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
     * Get customers for admin panel
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
            LEFT JOIN customer_profiles c ON u.id = c.user_id
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
            $existingAssociate = $this->db->prepare("SELECT associate_id FROM associates WHERE user_id = :user_id");
            $existingAssociate->execute(['user_id' => $customerId]);
            if ($existingAssociate->fetch()) {
                throw new \Exception('Customer is already an associate');
            }

            // Generate unique associate code
            $associateCode = $this->generateAssociateCode();

            // Create associate record
            $associateData = [
                'user_id' => $customerId,
                'sponsor_id' => $sponsorId,
                'associate_code' => $associateCode,
                'level' => 1,
                'status' => 'active',
                'joining_date' => date('Y-m-d H:i:s'),
                'kyc_status' => 'pending',
                'bank_details' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $sql = "
                INSERT INTO associates (
                    user_id, sponsor_id, associate_code, level, status, joining_date,
                    kyc_status, bank_details, created_at, updated_at
                ) VALUES (
                    :user_id, :sponsor_id, :associate_code, :level, :status, :joining_date,
                    :kyc_status, :bank_details, :created_at, :updated_at
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($associateData);

            // Update customer role to associate
            $updateSql = "UPDATE {$this->table} SET role = 'associate' WHERE id = :customer_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute(['customer_id' => $customerId]);

            // Create activity log
            $activitySql = "
                INSERT INTO associate_activities (associate_id, activity_type, description, created_at)
                VALUES (:associate_id, 'joined_as_associate', 'Converted from customer to associate', NOW())
            ";
            $activityStmt = $this->db->prepare($activitySql);
            $activityStmt->execute(['associate_id' => $this->db->lastInsertId()]);

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

            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM associates WHERE associate_code = :code");
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
            'current_level' => 'Bronze',
            'potential_earnings' => 0,
            'team_building_opportunity' => false,
            'rewards_eligible' => false,
            'referral_benefits' => []
        ];

        // Calculate potential earnings based on purchase history
        $totalSpent = $customerStats['total_spent'] ?? 0;
        $referralCount = $customerStats['total_bookings'] ?? 0;

        if ($totalSpent >= 100000) {
            $benefits['current_level'] = 'Silver';
            $benefits['potential_earnings'] = $totalSpent * 0.05; // 5% commission potential
        }

        if ($totalSpent >= 500000) {
            $benefits['current_level'] = 'Gold';
            $benefits['potential_earnings'] = $totalSpent * 0.10; // 10% commission potential
        }

        if ($totalSpent >= 1000000) {
            $benefits['current_level'] = 'Diamond';
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
            'commission_rate' => $benefits['current_level'] === 'Bronze' ? '5%' : ($benefits['current_level'] === 'Silver' ? '7%' : '10%'),
            'monthly_earnings_potential' => round($benefits['potential_earnings'] / 12),
            'team_building_bonus' => $benefits['team_building_opportunity'] ? '₹10,000 per referral' : 'Not eligible yet'
        ];

        return $benefits;
    }

    /**
     * Get customers who are potential associates
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
            LEFT JOIN customer_profiles c ON u.id = c.user_id
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
        $existingAssociate = $this->db->prepare("SELECT associate_id FROM associates WHERE user_id = :user_id");
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
            JOIN associates a ON ai.sponsor_id = a.associate_id
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
}
