<?php

/**
 * User Service
 * Handles all user-related business logic
 */

namespace App\Services\Business;

use App\Core\Database;
use App\Core\Security\CSRFProtection;
use App\Core\Security\InputValidation;
use App\Core\Security\PasswordManager;
use App\Core\Session\SessionManager;
use App\Core\Logger\Logger;

class UserService
{
    private $db;
    private $csrfProtection;
    private $inputValidation;
    private $passwordManager;
    private $sessionManager;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->csrfProtection = new CSRFProtection();
        $this->inputValidation = new InputValidation();
        $this->passwordManager = new PasswordManager();
        $this->sessionManager = SessionManager::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $limit = 10, $search = '', $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT 
                u.id, u.name, u.email, u.phone, u.role, u.status,
                u.registered_date, u.last_login, u.location,
                COUNT(p.id) as properties_viewed,
                COUNT(e.id) as enquiries_sent,
                u.profile_image
                FROM users u
                LEFT JOIN property_views pv ON u.id = pv.user_id
                LEFT JOIN properties p ON pv.property_id = p.id
                LEFT JOIN enquiries e ON u.id = e.user_id
                WHERE 1=1";
            
            $params = [];
            
            // Search functionality
            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.location LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Apply filters
            if (!empty($filters['role'])) {
                $sql .= " AND u.role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND u.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['location'])) {
                $sql .= " AND u.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
            
            $sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.role, u.status,
                      u.registered_date, u.last_login, u.location, u.profile_image
                      ORDER BY u.registered_date DESC
                      LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $users = $this->db->fetchAll($sql, $params);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(DISTINCT u.id) as total
                         FROM users u
                         WHERE 1=1";
            
            $countParams = [];
            
            if (!empty($search)) {
                $countSql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.location LIKE ?)";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }
            
            if (!empty($filters['role'])) {
                $countSql .= " AND u.role = ?";
                $countParams[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $countSql .= " AND u.status = ?";
                $countParams[] = $filters['status'];
            }
            
            if (!empty($filters['location'])) {
                $countSql .= " AND u.location LIKE ?";
                $countParams[] = '%' . $filters['location'] . '%';
            }
            
            $totalResult = $this->db->fetch($countSql, $countParams);
            $total = $totalResult['total'] ?? 0;
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            $this->logger->error("UserService::getAllUsers - Error: " . $e->getMessage());
            return [
                'users' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => 0
            ];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        try {
            $sql = "SELECT 
                u.*, COUNT(pv.id) as properties_viewed,
                COUNT(e.id) as enquiries_sent,
                COUNT(f.id) as favorite_properties
                FROM users u
                LEFT JOIN property_views pv ON u.id = pv.user_id
                LEFT JOIN properties p ON pv.property_id = p.id
                LEFT JOIN enquiries e ON u.id = e.user_id
                LEFT JOIN favorites f ON u.id = f.user_id
                WHERE u.id = ?
                GROUP BY u.id";
            
            return $this->db->fetch($sql, [$id]);
            
        } catch (Exception $e) {
            $this->logger->error("UserService::getUserById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new user
     */
    public function createUser($data)
    {
        try {
            // Validate input data
            $validationRules = [
                'name' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'type' => 'email'],
                'phone' => ['required' => true, 'type' => 'string', 'min' => 10, 'max' => 15],
                'password' => ['required' => true, 'type' => 'string', 'min' => 8],
                'role' => ['required' => true, 'type' => 'string', 'in' => ['client', 'associate', 'admin']],
                'address' => ['required' => true, 'type' => 'string', 'min' => 5, 'max' => 255],
                'city' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 50],
                'state' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 50],
                'pincode' => ['required' => true, 'type' => 'string', 'min' => 6, 'max' => 6]
            ];

            $validation = $this->inputValidation->validate($data, $validationRules);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Check if email already exists
            $existingEmail = $this->db->fetch(
                "SELECT id FROM users WHERE email = ?",
                [$data['email']]
            );
            
            if ($existingEmail) {
                return [
                    'success' => false,
                    'errors' => ['email' => 'Email already exists']
                ];
            }

            // Hash password
            $hashedPassword = $this->passwordManager->hash($data['password']);

            // Generate user ID
            $userId = 'USR' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Insert user
            $sql = "INSERT INTO users (
                id, name, email, phone, password, role,
                address, city, state, pincode, status,
                registered_date, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW(), NOW())";

            $params = [
                $userId,
                $data['name'],
                $data['email'],
                $data['phone'],
                $hashedPassword,
                $data['role'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode']
            ];

            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('user_created', $userId, 'New user created: ' . $data['name']);

            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("UserService::createUser - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create user']
            ];
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        try {
            // Validate input data
            $validationRules = [
                'name' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'type' => 'email'],
                'phone' => ['required' => true, 'type' => 'string', 'min' => 10, 'max' => 15],
                'role' => ['required' => true, 'type' => 'string', 'in' => ['client', 'associate', 'admin']],
                'address' => ['required' => true, 'type' => 'string', 'min' => 5, 'max' => 255],
                'city' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 50],
                'state' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 50],
                'pincode' => ['required' => true, 'type' => 'string', 'min' => 6, 'max' => 6]
            ];

            $validation = $this->inputValidation->validate($data, $validationRules);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Check if email already exists (excluding current user)
            $existingEmail = $this->db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$data['email'], $id]
            );
            
            if ($existingEmail) {
                return [
                    'success' => false,
                    'errors' => ['email' => 'Email already exists']
                ];
            }

            // Update user
            $sql = "UPDATE users SET 
                name = ?, email = ?, phone = ?, role = ?,
                address = ?, city = ?, state = ?, pincode = ?,
                updated_at = NOW()
                WHERE id = ?";

            $params = [
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['role'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode'],
                $id
            ];

            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('user_updated', $id, 'User updated: ' . $data['name']);

            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("UserService::updateUser - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to update user']
            ];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            // Check if user has properties or enquiries
            $userStats = $this->db->fetch(
                "SELECT 
                    (SELECT COUNT(*) FROM properties WHERE user_id = ?) as property_count,
                    (SELECT COUNT(*) FROM enquiries WHERE user_id = ?) as enquiry_count",
                [$id, $id]
            );

            if ($userStats['property_count'] > 0 || $userStats['enquiry_count'] > 0) {
                return [
                    'success' => false,
                    'errors' => ['general' => 'Cannot delete user with active properties or enquiries']
                ];
            }

            // Delete user
            $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity('user_deleted', $id, 'User deleted');

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("UserService::deleteUser - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to delete user']
            ];
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus($id, $status)
    {
        try {
            $validStatuses = ['active', 'inactive', 'suspended'];
            
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['status' => 'Invalid status']
                ];
            }

            $this->db->execute(
                "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
                [$status, $id]
            );

            // Log activity
            $this->logActivity('user_status_updated', $id, 'User status updated to: ' . $status);

            return [
                'success' => true,
                'message' => 'User status updated successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("UserService::updateUserStatus - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to update user status']
            ];
        }
    }

    /**
     * Reset user password
     */
    public function resetUserPassword($id)
    {
        try {
            // Get user info
            $user = $this->db->fetch("SELECT email, name FROM users WHERE id = ?", [$id]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['general' => 'User not found']
                ];
            }

            // Generate new password
            $newPassword = $this->generateRandomPassword();
            $hashedPassword = $this->passwordManager->hash($newPassword);

            // Update password
            $this->db->execute(
                "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
                [$hashedPassword, $id]
            );

            // Log activity
            $this->logActivity('password_reset', $id, 'Password reset for user: ' . $user['name']);

            // In real application, send email with new password
            // For now, return the password
            return [
                'success' => true,
                'new_password' => $newPassword,
                'message' => 'Password reset successfully'
            ];

        } catch (Exception $e) {
            $this->logger->error("UserService::resetUserPassword - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to reset password']
            ];
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
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
                SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as client_users,
                SUM(CASE WHEN role = 'associate' THEN 1 ELSE 0 END) as associate_users,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
                COUNT(CASE WHEN registered_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week,
                COUNT(CASE WHEN registered_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_month
                FROM users";
            
            return $this->db->fetch($sql);

        } catch (Exception $e) {
            $this->logger->error("UserService::getUserStatistics - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user activity
     */
    public function getUserActivity($id, $limit = 50)
    {
        try {
            $sql = "SELECT 
                action, details, ip_address, user_agent, created_at
                FROM activity_log
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";
            
            return $this->db->fetchAll($sql, [$id, $limit]);

        } catch (Exception $e) {
            $this->logger->error("UserService::getUserActivity - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search users
     */
    public function searchUsers($criteria)
    {
        try {
            $sql = "SELECT 
                u.id, u.name, u.email, u.phone, u.role, u.status,
                u.city, u.state, u.registered_date, u.last_login
                FROM users u
                WHERE 1=1";
            
            $params = [];
            
            // Name search
            if (!empty($criteria['name'])) {
                $sql .= " AND u.name LIKE ?";
                $params[] = '%' . $criteria['name'] . '%';
            }
            
            // Email search
            if (!empty($criteria['email'])) {
                $sql .= " AND u.email LIKE ?";
                $params[] = '%' . $criteria['email'] . '%';
            }
            
            // Phone search
            if (!empty($criteria['phone'])) {
                $sql .= " AND u.phone LIKE ?";
                $params[] = '%' . $criteria['phone'] . '%';
            }
            
            // Location search
            if (!empty($criteria['location'])) {
                $sql .= " AND u.location LIKE ?";
                $params[] = '%' . $criteria['location'] . '%';
            }
            
            // Role filter
            if (!empty($criteria['role'])) {
                $sql .= " AND u.role = ?";
                $params[] = $criteria['role'];
            }
            
            // Status filter
            if (!empty($criteria['status'])) {
                $sql .= " AND u.status = ?";
                $params[] = $criteria['status'];
            }
            
            $sql .= " ORDER BY u.name";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("UserService::searchUsers - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Log activity
     */
    private function logActivity($action, $userId, $details = '')
    {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $userId,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            
            $this->db->execute($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("UserService::logActivity - Error: " . $e->getMessage());
        }
    }
}
