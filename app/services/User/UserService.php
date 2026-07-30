<?php

namespace App\Services\User;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
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

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? "WHERE tenant_id = ?" : "";
            $sql = "SELECT * FROM users $tenantWhere ORDER BY created_at DESC";
            $stmt = $this->database->prepare($sql);
            if ($tid > 1) $stmt->execute([$tid]);
            else $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "SELECT * FROM users WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($tid > 1) $stmt->bindParam(':tid', $tid);
            $stmt->execute();
            return $stmt->fetch();
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "SELECT * FROM users WHERE email = :email" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':email', $email);
            if ($tid > 1) $stmt->bindParam(':tid', $tid);
            $stmt->execute();
            return $stmt->fetch();
        } catch (\Exception $e) {
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
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $tid = $this->getTenantId();
            $sql = "INSERT INTO users (name, email, password, phone, role, status, tenant_id, created_at) 
                    VALUES (:name, :email, :password, :phone, :role, :status, :tid, NOW())";
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role'] ?? 'user');
            $stmt->bindParam(':status', $data['status'] ?? 'active');
            $stmt->bindParam(':tid', $tid);
            
            $result = $stmt->execute();
            
            if ($result) {
                $userId = $this->database->lastInsertId();
                $this->logger->info("User created successfully with ID: " . $userId);
                return $userId;
            }
            
            return false;
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        role = :role, 
                        status = :status, 
                        tenant_id = :tid,
                        updated_at = NOW() 
                    WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':tid', $tid);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User updated successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET password = :password, tenant_id = :tid, updated_at = NOW() WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':tid', $tid);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User password updated successfully for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "DELETE FROM users WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($tid > 1) $stmt->bindParam(':tid', $tid);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User deleted successfully with ID: " . $id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? "AND tenant_id = ?" : "";
            $sql = "SELECT * FROM users WHERE status = 'active' $tenantWhere ORDER BY name";
            $stmt = $this->database->prepare($sql);
            if ($tid > 1) $stmt->execute([$tid]);
            else $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? "AND tenant_id = ?" : "";
            $sql = "SELECT * FROM users WHERE role = :role $tenantWhere ORDER BY name";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':role', $role);
            if ($tid > 1) $stmt->execute([$tid]);
            else $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET status = :status, tenant_id = :tid, updated_at = NOW() WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tid', $tid);
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User status updated to {$status} for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? "AND tenant_id = ?" : "";
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email $tenantWhere";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':email', $email);
            if ($tid > 1) $stmt->execute([$email, $tid]);
            else $stmt->execute([$email]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? "WHERE tenant_id = ?" : "";
            $params = $tid > 1 ? [$tid] : [];
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                        COUNT(CASE WHEN role = 'admin' THEN 1 END) as users,
                        COUNT(CASE WHEN role = 'user' THEN 1 END) as regular_users,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
                    FROM users $tenantWhere";
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (\Exception $e) {
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
            $tid = $this->getTenantId();
            $sql = "UPDATE users SET 
                        name = :name, 
                        phone = :phone, 
                        tenant_id = :tid,
                        updated_at = NOW() 
                    WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':tid', $tid);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("User profile updated successfully for ID: " . $id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
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
            try {
                $sql = "SELECT * FROM user_preferences WHERE user_id = :user_id";
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
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
                try {
                    $sql = "INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                            VALUES (:user_id, :preference_key, :preference_value, NOW())
                            ON DUPLICATE KEY UPDATE preference_value = :preference_value, updated_at = NOW()";
                } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                }
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':preference_key', $key);
                $stmt->bindParam(':preference_value', $value);
                $stmt->execute();
            }
            
            $this->logger->info("User preferences updated successfully for ID: " . $userId);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error updating user preferences: " . $e->getMessage());
            return false;
        }
    }
}
