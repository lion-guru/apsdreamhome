<?php
/**
 * APS Dream Home - Property Management System
 * Enhanced property handling with advanced features
 */

class PropertyManager {
    private $conn;
    private $config;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->config = AppConfig::getInstance();
    }

    /**
     * Get properties with advanced filtering
     */
    public function getProperties($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email as agent_email, u.phone as agent_phone
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
            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Property query error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 6) {
        return $this->getProperties(['featured' => true], $limit);
    }

    /**
     * Get recent properties
     */
    public function getRecentProperties($limit = 8) {
        return $this->getProperties([], $limit);
    }

    /**
     * Get property by ID
     */
    public function getProperty($id) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email as agent_email, u.phone as agent_phone
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.id = ?";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get property error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search properties with scoring
     */
    public function searchPropertiesAdvanced($query, $filters = []) {
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
        usort($scored_properties, function($a, $b) {
            return $b['search_score'] <=> $a['search_score'];
        });

        return $scored_properties;
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats() {
        $stats = [];

        try {
            // Total properties
            $result = $this->conn->query("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
            $stats['total_properties'] = $result->fetch_assoc()['total'];

            // Properties by type
            $result = $this->conn->query("SELECT property_type, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY property_type");
            $stats['by_type'] = $result->fetch_all(MYSQLI_ASSOC);

            // Properties by city
            $result = $this->conn->query("SELECT city, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY city ORDER BY count DESC LIMIT 10");
            $stats['by_city'] = $result->fetch_all(MYSQLI_ASSOC);

            // Average price by city
            $result = $this->conn->query("SELECT city, AVG(price) as avg_price FROM properties WHERE status = 'active' GROUP BY city ORDER BY avg_price DESC");
            $stats['avg_price_by_city'] = $result->fetch_all(MYSQLI_ASSOC);

            // Price ranges
            $result = $this->conn->query("
                SELECT
                    COUNT(CASE WHEN price < 1000000 THEN 1 END) as under_10l,
                    COUNT(CASE WHEN price BETWEEN 1000000 AND 5000000 THEN 1 END) as 10l_50l,
                    COUNT(CASE WHEN price BETWEEN 5000000 AND 10000000 THEN 1 END) as 50l_1cr,
                    COUNT(CASE WHEN price > 10000000 THEN 1 END) as above_1cr
                FROM properties WHERE status = 'active'
            ");
            $stats['price_ranges'] = $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Property stats error: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties($property_id, $limit = 4) {
        $property = $this->getProperty($property_id);
        if (!$property) return [];

        $filters = [
            'city' => $property['city'],
            'property_type' => $property['property_type']
        ];

        // Remove the current property from results
        $all_similar = $this->getProperties($filters, $limit + 1);
        return array_filter($all_similar, function($prop) use ($property_id) {
            return $prop['id'] != $property_id;
        });
    }

    /**
     * Get properties by agent
     */
    public function getPropertiesByAgent($agent_id, $limit = null) {
        $sql = "SELECT * FROM properties WHERE agent_id = ? AND status = 'active' ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ?";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            if ($limit) {
                $stmt->bind_param('ii', $agent_id, $limit);
            } else {
                $stmt->bind_param('i', $agent_id);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get properties by agent error: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * User Management System
 */
class UserManager {
    private $conn;
    private $config;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->config = AppConfig::getInstance();
    }

    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
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
    public function register($user_data) {
        try {
            // Hash password
            $hashed_password = password_hash($user_data['password'], PASSWORD_ARGON2ID);

            $stmt = $this->conn->prepare("
                INSERT INTO users (first_name, last_name, email, phone, password, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $role = $user_data['role'] ?? 'customer';
            $status = 'active';

            $stmt->bind_param('sssssss',
                $user_data['first_name'],
                $user_data['last_name'],
                $user_data['email'],
                $user_data['phone'],
                $hashed_password,
                $role,
                $status
            );

            if ($stmt->execute()) {
                return $this->conn->insert_id;
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
    public function getUserProfile($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get user profile error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $user_data) {
        try {
            $updates = [];
            $params = [];
            $types = '';

            if (isset($user_data['first_name'])) {
                $updates[] = 'first_name = ?';
                $params[] = $user_data['first_name'];
                $types .= 's';
            }

            if (isset($user_data['last_name'])) {
                $updates[] = 'last_name = ?';
                $params[] = $user_data['last_name'];
                $types .= 's';
            }

            if (isset($user_data['phone'])) {
                $updates[] = 'phone = ?';
                $params[] = $user_data['phone'];
                $types .= 's';
            }

            if (isset($user_data['address'])) {
                $updates[] = 'address = ?';
                $params[] = $user_data['address'];
                $types .= 's';
            }

            if (empty($updates)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $user_id;
            $types .= 'i';

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Contact Management System
 */
class ContactManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Submit contact form
     */
    public function submitContact($contact_data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO contacts (name, email, phone, subject, message, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->bind_param('sssssss',
                $contact_data['name'],
                $contact_data['email'],
                $contact_data['phone'],
                $contact_data['subject'],
                $contact_data['message'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            if ($stmt->execute()) {
                // Send notification email
                $this->sendContactNotification($contact_data);
                return $this->conn->insert_id;
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
    private function sendContactNotification($contact_data) {
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

        IP Address: {$_SERVER['REMOTE_ADDR']}
        Submitted: " . date('Y-m-d H:i:s');

        mail($to, $subject, $message, "From: noreply@apsdreamhome.com");
    }

    /**
     * Get recent contacts
     */
    public function getRecentContacts($limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM contacts
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get recent contacts error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize managers
$property_manager = new PropertyManager($db_connection);
$user_manager = new UserManager($db_connection);
$contact_manager = new ContactManager($db_connection);
?>
