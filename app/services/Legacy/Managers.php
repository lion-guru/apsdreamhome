<?php

namespace App\Services\Legacy;

/**
 * APS Dream Home - Property Management System
 * Enhanced property handling with advanced features
 */

class PropertyManager
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->config = AppConfig::getInstance();
    }

    /**
     * Get properties with advanced filtering
     */
    public function getProperties($filters = [], $limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, u.name as agent_name, u.email as agent_email, u.phone as agent_phone
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.status = 'active'";

        $params = [];
        $conditions = [];

        // Apply filters
        if (!empty($filters['city'])) {
            $conditions[] = "p.city = ?";
            $params[] = $filters['city'];
        }

        if (!empty($filters['property_type'])) {
            $conditions[] = "p.property_type = ?";
            $params[] = $filters['property_type'];
        }

        if (!empty($filters['min_price'])) {
            $conditions[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['bedrooms'])) {
            $conditions[] = "p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
        }

        if (!empty($filters['bathrooms'])) {
            $conditions[] = "p.bathrooms >= ?";
            $params[] = $filters['bathrooms'];
        }

        if (!empty($filters['area_min'])) {
            $conditions[] = "p.area >= ?";
            $params[] = $filters['area_min'];
        }

        if (!empty($filters['area_max'])) {
            $conditions[] = "p.area <= ?";
            $params[] = $filters['area_max'];
        }

        if (!empty($filters['furnished'])) {
            $conditions[] = "p.furnished = ?";
            $params[] = $filters['furnished'];
        }

        if (!empty($filters['parking'])) {
            $conditions[] = "p.parking = ?";
            $params[] = $filters['parking'];
        }

        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.locality LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY p.featured DESC, p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        try {
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Property query error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 6)
    {
        return $this->getProperties(['featured' => true], $limit);
    }

    /**
     * Get recent properties
     */
    public function getRecentProperties($limit = 8)
    {
        return $this->getProperties([], $limit);
    }

    /**
     * Get property by ID
     */
    public function getProperty($id)
    {
        $sql = "SELECT p.*, u.name as uname, u.email as agent_email, u.phone as agent_phone
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.id = ?";

        try {
            return $this->db->fetchOne($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get property error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search properties with scoring
     */
    public function searchPropertiesAdvanced($query, $filters = [])
    {
        $search_terms = explode(' ', $query);
        $properties = $this->getProperties($filters);

        $scored_properties = [];

        foreach ($properties as $property) {
            $score = 0;
            $searchable_text = strtolower($property['title'] . ' ' . $property['description'] . ' ' . $property['city'] . ' ' . $property['locality']);

            foreach ($search_terms as $term) {
                $term = trim($term);
                if (empty($term)) continue;

                // Exact matches get higher scores
                if (strpos($searchable_text, strtolower($term)) !== false) {
                    $score += 10;

                    // Title matches get bonus points
                    if (strpos(strtolower($property['title']), strtolower($term)) !== false) {
                        $score += 5;
                    }

                    // City matches get bonus points
                    if (strpos(strtolower($property['city']), strtolower($term)) !== false) {
                        $score += 3;
                    }
                }
            }

            // Apply filters as negative scores if not matched
            if (!empty($filters['city']) && $property['city'] !== $filters['city']) {
                $score -= 5;
            }

            if (!empty($filters['property_type']) && $property['property_type'] !== $filters['property_type']) {
                $score -= 3;
            }

            $property['search_score'] = $score;
            $scored_properties[] = $property;
        }

        // Sort by score descending
        usort($scored_properties, function ($a, $b) {
            return $b['search_score'] <=> $a['search_score'];
        });

        return $scored_properties;
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats()
    {
        $stats = [];

        try {
            // Total properties
            $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
            $stats['total_properties'] = $row['total'];

            // Properties by type
            $stats['by_type'] = $this->db->fetchAll("SELECT property_type, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY property_type");

            // Properties by city
            $stats['by_city'] = $this->db->fetchAll("SELECT city, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY city ORDER BY count DESC LIMIT 10");

            // Average price by city
            $stats['avg_price_by_city'] = $this->db->fetchAll("SELECT city, AVG(price) as avg_price FROM properties WHERE status = 'active' GROUP BY city ORDER BY avg_price DESC");

            // Price ranges
            $stats['price_ranges'] = $this->db->fetchOne("
                SELECT
                    COUNT(CASE WHEN price < 1000000 THEN 1 END) as under_10l,
                    COUNT(CASE WHEN price BETWEEN 1000000 AND 5000000 THEN 1 END) as 10l_50l,
                    COUNT(CASE WHEN price BETWEEN 5000000 AND 10000000 THEN 1 END) as 50l_1cr,
                    COUNT(CASE WHEN price > 10000000 THEN 1 END) as above_1cr
                FROM properties WHERE status = 'active'
            ");
        } catch (Exception $e) {
            error_log("Property stats error: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties($property_id, $limit = 4)
    {
        $property = $this->getProperty($property_id);
        if (!$property) return [];

        $filters = [
            'city' => $property['city'],
            'property_type' => $property['property_type']
        ];

        // Remove the current property from results
        $all_similar = $this->getProperties($filters, $limit + 1);
        return array_filter($all_similar, function ($prop) use ($property_id) {
            return $prop['id'] != $property_id;
        });
    }

    /**
     * Get properties by agent
     */
    public function getPropertiesByAgent($agent_id, $limit = null)
    {
        $sql = "SELECT * FROM properties WHERE agent_id = ? AND status = 'active' ORDER BY created_at DESC";

        $params = [$agent_id];
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        try {
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get properties by agent error: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * User Management System
 */
class UserManager
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->config = AppConfig::getInstance();
    }

    /**
     * Authenticate user
     */
    public function authenticate($email, $password)
    {
        try {
            // Map modern columns to legacy aliases for backward compatibility
            $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, 
                           password as upass, role as utype, status, created_at as join_date 
                    FROM users WHERE email = ? AND status = 'active'";
            $user = $this->db->fetchOne($sql, [$email]);

            if ($user && password_verify($password, $user['upass'])) {
                return $user;
            }

            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register new user
     */
    public function register($user_data)
    {
        try {
            // Hash password
            $hashed_password = password_hash($user_data['password'], PASSWORD_ARGON2ID);

            $role = $user_data['role'] ?? 'customer';
            if ($role == '4') $role = 'customer'; // Legacy mapping

            $status = 'active';
            $name = $user_data['first_name'] . ' ' . ($user_data['last_name'] ?? '');

            $sql = "INSERT INTO users (name, email, phone, password, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $success = $this->db->execute($sql, [
                $name,
                $user_data['email'],
                $user_data['phone'],
                $hashed_password,
                $role,
                $status
            ]);

            if ($success) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user profile
     */
    public function getUserProfile($user_id)
    {
        try {
            $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, 
                           password as upass, role as utype, status, created_at as join_date 
                    FROM users WHERE id = ?";
            return $this->db->fetchOne($sql, [$user_id]);
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $user_data)
    {
        try {
            $updates = [];
            $params = [];

            if (isset($user_data['name'])) {
                $updates[] = 'name = ?';
                $params[] = $user_data['name'];
            }

            if (isset($user_data['phone'])) {
                $updates[] = 'phone = ?';
                $params[] = $user_data['phone'];
            }

            if (isset($user_data['address'])) {
                $updates[] = 'address = ?';
                $params[] = $user_data['address'];
            }

            if (empty($updates)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $user_id;

            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Contact Management System
 */
class ContactManager
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->config = AppConfig::getInstance();
    }

    /**
     * Submit contact form
     */
    public function submitContact($contact_data)
    {
        try {
            $sql = "INSERT INTO contacts (name, email, phone, subject, message, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $success = $this->db->execute($sql, [
                $contact_data['name'],
                $contact_data['email'],
                $contact_data['phone'],
                $contact_data['subject'],
                $contact_data['message'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            if ($success) {
                // Send notification email
                $this->sendContactNotification($contact_data);
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Contact submission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send contact notification email
     */
    private function sendContactNotification($contact_data)
    {
        $to = config('admin_email');
        $subject = 'New Contact Form Submission - APS Dream Home';
        $message = "
        New contact form submission received:

        Name: {$contact_data['name']}
        Email: {$contact_data['email']}
        Phone: {$contact_data['phone']}
        Subject: {$contact_data['subject']}

        Message:
        {$contact_data['message']}

        IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
        Submitted: " . date('Y-m-d H:i:s');

        mail($to, $subject, $message, "From: noreply@apsdreamhome.com");
    }

    /**
     * Get recent contacts
     */
    public function getRecentContacts($limit = 10)
    {
        try {
            return $this->db->fetchAll("
                SELECT * FROM contacts
                ORDER BY created_at DESC
                LIMIT ?
            ", [$limit]);
        } catch (Exception $e) {
            error_log("Get recent contacts error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize managers
$property_manager = new PropertyManager();
$user_manager = new UserManager();
$contact_manager = new ContactManager();
