<?php
/**
 * User Authentication and Management System
 * Complete user registration, login, and profile management
 */

class AuthManager {
    private $conn;
    private $logger;
    private $propertyAI;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->propertyAI = new PropertyAI($conn);
    }

    /**
     * User registration
     */
    public function register($data) {
        // Validate input
        if (!$this->validateRegistrationData($data)) {
            return ['success' => false, 'message' => 'Invalid registration data'];
        }

        // Check if user already exists
        if ($this->userExists($data['email'], $data['username'])) {
            return ['success' => false, 'message' => 'User already exists with this email or username'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO users (username, email, password, full_name, phone, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $role = $data['role'] ?? 'user';
        $status = 'active';

        $stmt->bind_param("sssssss",
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'],
            $data['phone'],
            $role,
            $status
        );

        $result = $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            // Send welcome email
            $this->sendWelcomeEmail($data['email'], $data['full_name']);

            // Start session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $data['username'];
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['role'] = $role;

            if ($this->logger) {
                $this->logger->log("User registered: {$data['username']} (ID: $userId)", 'info', 'auth');
            }

            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    /**
     * User login
     */
    public function login($email, $password) {
        $sql = "SELECT id, username, password, full_name, role, status, email_verified
                FROM users WHERE email = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is ' . $user['status']];
        }

        // Start session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        // Update last login
        $this->updateLastLogin($user['id']);

        if ($this->logger) {
            $this->logger->log("User logged in: {$user['username']} (ID: {$user['id']})", 'info', 'auth');
        }

        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }

    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($email, $name) {
        $emailManager = new EmailTemplateManager($this->conn, $this->logger);
        $emailManager->sendTemplateEmail('welcome_user', ['name' => $name], $email, $name);
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
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
    private function userExists($email, $username) {
        $sql = "SELECT id FROM users WHERE email = ? OR username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /**
     * Update last login time
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Logout user
     */
    public function logout() {
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
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $sql = "SELECT id, username, email, full_name, phone, role, status, email_verified, profile_image
                FROM users WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $data['full_name'], $data['phone'], $userId);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // Update session data
            $_SESSION['full_name'] = $data['full_name'];

            if ($this->logger) {
                $this->logger->log("User profile updated: ID $userId", 'info', 'auth');
            }
        }

        return $result;
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get current password hash
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userId);

        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Password changed for user ID: $userId", 'info', 'auth');
        }

        return $result ?
            ['success' => true, 'message' => 'Password changed successfully'] :
            ['success' => false, 'message' => 'Failed to change password'];
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $sql = "SELECT id, username, email, full_name, phone, role, status, email_verified, created_at
                FROM users WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    /**
     * Get users with pagination
     */
    public function getUsers($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT id, username, email, full_name, phone, role, status, created_at
                FROM users WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $sql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $sql .= " ORDER BY created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();

        return $users;
    }
}
?>
