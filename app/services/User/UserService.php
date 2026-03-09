<?php

namespace App\Services\User;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * User Service - APS Dream Home
 * User registration, management, and authentication
 * Custom MVC implementation without Laravel dependencies
 */
class UserService
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new LoggingService();
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new user
     */
    public function createUser($data)
    {
        try {
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, email, password, phone, role, status, created_at) 
                    VALUES (:name, :email, :password, :phone, :role, :status, NOW())";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role'] ?? 'user');
            $stmt->bindParam(':status', $data['status'] ?? 'active');
            
            $result = $stmt->execute();
            
            if ($result) {
                $userId = $this->database->lastInsertId();
                $this->logger->info("User created successfully with ID: " . $userId);
                return $userId;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        try {
            $sql = "UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        role = :role, 
                        status = :status, 
                        updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User updated successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updateUserPassword($id, $password)
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':password', $hashedPassword);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User password updated successfully for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating user password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User deleted successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user
     */
    public function authenticateUser($email, $password)
    {
        try {
            $user = $this->getUserByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->logger->info("User authenticated successfully: " . $email);
                return $user;
            }
            
            $this->logger->warning("Authentication failed for email: " . $email);
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error authenticating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        try {
            $sql = "SELECT * FROM users WHERE status = 'active' ORDER BY name";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting active users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        try {
            $sql = "SELECT * FROM users WHERE role = :role ORDER BY name";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting users by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus($id, $status)
    {
        try {
            $sql = "UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User status updated to {$status} for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating user status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     */
    public function emailExists($email)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (Exception $e) {
            $this->logger->error("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
                        COUNT(CASE WHEN role = 'user' THEN 1 END) as regular_users,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
                    FROM users";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logger->error("Error getting user statistics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateUserProfile($id, $data)
    {
        try {
            $sql = "UPDATE users SET 
                        name = :name, 
                        phone = :phone, 
                        updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User profile updated successfully for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user preferences
     */
    public function getUserPreferences($userId)
    {
        try {
            $sql = "SELECT * FROM user_preferences WHERE user_id = :user_id";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting user preferences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences($userId, $preferences)
    {
        try {
            foreach ($preferences as $key => $value) {
                $sql = "INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                        VALUES (:user_id, :preference_key, :preference_value, NOW())
                        ON DUPLICATE KEY UPDATE preference_value = :preference_value, updated_at = NOW()";
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':preference_key', $key);
                $stmt->bindParam(':preference_value', $value);
                $stmt->execute();
            }
            
            $this->logger->info("User preferences updated successfully for ID: " . $userId);
            return true;
        } catch (Exception $e) {
            $this->logger->error("Error updating user preferences: " . $e->getMessage());
            return false;
        }
    }
}
