<?php

namespace App\Services\Legacy;

/**
 * User Authentication and Management System
 * Complete user registration, login, and profile management
 */

class AuthManager
{
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_ASSOCIATE = 'associate';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_GUEST = 'guest';

    private $db;
    private $logger;
    private $propertyAI;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->propertyAI = new PropertyAI($this->db);
    }

    /**
     * User registration
     */
    public function register($data)
    {
        // Validate input
        if (!$this->validateRegistrationData($data)) {
            return ['success' => false, 'message' => 'Invalid registration data'];
        }

        // Check if user already exists
        if ($this->userExists($data['email'], $data['username'] ?? $data['full_name'])) {
            return ['success' => false, 'message' => 'User already exists with this email or name'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO users (name, email, password, role, phone, status)
                VALUES (?, ?, ?, ?, ?, 'active')";

        $role = $data['job_role'] ?? 'associate'; // Default to Associate
        // Ensure role is valid enum value
        if (!in_array($role, ['admin', 'associate', 'customer', 'manager', 'employee'])) {
            $role = 'associate';
        }

        try {
            $name = $data['full_name'] ?? $data['username'];

            $this->db->execute($sql, [
                $name,
                $data['email'],
                $hashedPassword,
                $role,
                $data['phone']
            ]);

            $userId = $this->db->lastInsertId();

            // Send welcome email
            $this->sendWelcomeEmail($data['email'], $name);

            // Start session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $name;
            $_SESSION['full_name'] = $name;
            $_SESSION['role'] = $role;

            if ($this->logger) {
                $this->logger->log("User registered: {$name} (ID: $userId)", 'info', 'auth');
            }

            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Registration failed: " . $e->getMessage(), 'error', 'auth');
            }
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * User login
     */
    public function login($email, $password)
    {
        $sql = "SELECT id, name, password, role, role as job_role
                FROM users WHERE email = ?";

        try {
            $user = $this->db->fetchOne($sql, [$email]);

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['full_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['job_role'] = $user['job_role'];
                
                // Log login
                if ($this->logger) {
                    $this->logger->log("User logged in: {$user['name']} (ID: {$user['id']})", 'info', 'auth');
                }

                return ['success' => true, 'user' => $user];
            }

            return ['success' => false, 'message' => 'Invalid credentials'];
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Login failed: " . $e->getMessage(), 'error', 'auth');
            }
            return ['success' => false, 'message' => 'Login failed'];
        }
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($email, $name)
    {
        $emailManager = new EmailService();
        $emailManager->send('welcome_user', ['name' => $name], $email, $name);
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData($data)
    {
        if (empty($data['username']) || strlen($data['username']) < 3) {
            return false;
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            return false;
        }

        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            return false;
        }

        return true;
    }

    /**
     * Check if user exists
     */
    private function userExists($email, $name)
    {
        $sql = "SELECT id as uid FROM users WHERE email = ? OR name = ?";
        try {
            $user = $this->db->fetch($sql, [$email, $name]);
            return !empty($user);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update last login time
     */
    private function updateLastLogin($userId)
    {
        // user table does not have updated_at or last_login column in the export
        // skipping for now to avoid errors, or could use join_date if needed
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'unknown';

        // Clear session
        session_unset();
        session_destroy();

        if ($this->logger && $userId) {
            $this->logger->log("User logged out: $username (ID: $userId)", 'info', 'auth');
        }

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Get current user
     */
    public function getCurrentUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, role as utype, NULL as uimage, role as job_role
                FROM users WHERE id = ?";

        try {
            return $this->db->fetch($sql, [$_SESSION['user_id']]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        $sql = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
        try {
            $this->db->execute($sql, [$data['full_name'], $data['phone'], $userId]);

            // Update session data
            $_SESSION['full_name'] = $data['full_name'];

            if ($this->logger) {
                $this->logger->log("User profile updated: ID $userId", 'info', 'auth');
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Get current password hash
        $sql = "SELECT password as upass FROM users WHERE id = ?";
        try {
            $user = $this->db->fetch($sql, [$userId]);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['upass'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $this->db->execute($sql, [$hashedPassword, $userId]);

            if ($this->logger) {
                $this->logger->log("Password changed for user ID: $userId", 'info', 'auth');
            }

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to change password: ' . $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId)
    {
        $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, role as utype, created_at as join_date
                FROM users WHERE id = ?";

        try {
            return $this->db->fetch($sql, [$userId]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, role as utype, created_at as join_date, password as upass FROM users WHERE email = ?";

        try {
            return $this->db->fetch($sql, [$email]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get users with pagination
     */
    public function getUsers($filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT id as uid, name as uname, email as uemail, phone as uphone, role as utype, created_at as join_date
                FROM users WHERE 1=1";

        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND utype = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (uname LIKE ? OR uemail LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY join_date DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = (int)$offset;
        }

        try {
            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            return [];
        }
    }
}
