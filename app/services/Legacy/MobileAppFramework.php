<?php

namespace App\Services\Legacy;

/**
 * Mobile App Framework - APS Dream Homes
 * RESTful API for mobile applications
 */

class MobileAppFramework
{
    private $db;
    private $apiVersion = "v1";

    public function __construct()
    {
        $this->db = \App\Core\App::database();
        $this->initMobileAPI();
    }

    /**
     * Initialize mobile API
     */
    private function initMobileAPI()
    {
        // Create mobile API tables
        $this->createMobileTables();

        // Setup API endpoints
        $this->setupAPIEndpoints();
    }

    /**
     * Create mobile API tables
     */
    private function createMobileTables()
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS mobile_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT(50),
                device_id VARCHAR(255),
                device_type VARCHAR(50),
                app_version VARCHAR(50),
                push_token VARCHAR(500),
                last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_device (device_id),
                CONSTRAINT fk_mobile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS mobile_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT(50),
                session_token VARCHAR(500),
                device_id VARCHAR(255),
                expires_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token (session_token),
                INDEX idx_user (user_id),
                CONSTRAINT fk_mobile_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS mobile_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT(50),
                title VARCHAR(200),
                message TEXT,
                type VARCHAR(50),
                data JSON,
                sent BOOLEAN DEFAULT 0,
                sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_type (type),
                CONSTRAINT fk_mobile_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Setup API endpoints
     */
    private function setupAPIEndpoints()
    {
        // This would setup routes for mobile API
        // For now, we'll create the API structure
    }

    /**
     * Authenticate mobile user
     */
    public function authenticateUser($email, $password, $deviceId)
    {
        $sql = "SELECT id, password FROM users WHERE email = ? AND status = 'active'";
        $user = $this->db->fetch($sql, [$email]);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                return $this->createMobileSession($user['id'], $deviceId);
            }
        }

        return false;
    }

    /**
     * Create mobile session
     */
    private function createMobileSession($userId, $deviceId)
    {
        $sessionToken = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        $expiresAt = \date('Y-m-d H:i:s', \strtotime('+30 days'));

        $sql = "INSERT INTO mobile_sessions (user_id, session_token, device_id, expires_at) VALUES (?, ?, ?, ?)";
        $this->db->execute($sql, [$userId, $sessionToken, $deviceId, $expiresAt]);

        return [
            'token' => $sessionToken,
            'expires_at' => $expiresAt,
            'user_id' => $userId
        ];
    }

    /**
     * Get properties for mobile
     */
    public function getMobileProperties($filters = [])
    {
        $sql = "SELECT p.*, pi.image_url FROM properties p
                LEFT JOIN property_images pi ON p.id = pi.property_id
                WHERE p.status = 'available'";

        $params = [];

        if (!empty($filters['property_type'])) {
            $sql .= " AND p.property_type = ?";
            $params[] = $filters['property_type'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }
}

// Initialize mobile framework
$mobileFramework = new MobileAppFramework();
