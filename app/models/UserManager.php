<?php

namespace App\Models;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * User Management Class
 * Handles all user-related operations
 */
class UserManager
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
    }

    /**
     * Get all users with optional filtering
     * @param array $filters Optional filters (role, status, search, etc.)
     * @return array Users data
     */
    public function getAll($filters = [])
    {
        try {
            $sql = "SELECT u.*, 
                        (SELECT COUNT(*) FROM properties WHERE user_id = u.id) as property_count,
                        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count
                    FROM users u 
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($filters['role'])) {
                $sql .= " AND u.role = ?";
                $params[] = $filters['role'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND u.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY u.created_at DESC";

            $users = $this->db->fetchAll($sql, $params);

            return [
                'success' => true,
                'users' => $users,
                'total' => count($users)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch users: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return array User data
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT u.*, 
                        (SELECT COUNT(*) FROM properties WHERE user_id = u.id) as property_count,
                        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count
                    FROM users u 
                    WHERE u.id = ?";

            $user = $this->db->fetch($sql, [$id]);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Remove sensitive data
            unset($user['password']);
            unset($user['remember_token']);

            return [
                'success' => true,
                'user' => $user
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new user
     * @param array $data User data
     * @return array Creation result
     */
    public function create($data)
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'error' => "Field {$field} is required"
                    ];
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Invalid email format'
                ];
            }

            // Validate password strength
            if (strlen($data['password']) < 8) {
                return [
                    'success' => false,
                    'error' => 'Password must be at least 8 characters long'
                ];
            }

            // Check if email already exists
            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
            if ($existing) {
                return [
                    'success' => false,
                    'error' => 'Email already exists'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID);

            // Set default values
            $data['role'] = $data['role'] ?? 'user';
            $data['status'] = $data['status'] ?? 'active';
            $data['created_at'] = date('Y-m-d H:i:s');

            $userId = $this->db->insert(
                "INSERT INTO users (name, email, password, phone, role, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['name'],
                    $data['email'],
                    $hashedPassword,
                    $data['phone'] ?? null,
                    $data['role'],
                    $data['status'],
                    $data['created_at']
                ]
            );

            if ($userId) {
                return [
                    'success' => true,
                    'user_id' => $userId,
                    'message' => 'User created successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create user'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update user
     * @param int $id User ID
     * @param array $data Updated data
     * @return array Update result
     */
    public function update($id, $data)
    {
        try {
            // Check if user exists
            $existing = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Validate email format if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Invalid email format'
                ];
            }

            // Build update query
            $updateFields = [];
            $params = [];

            $allowedFields = ['name', 'email', 'phone', 'role', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    // Hash password if being updated
                    if ($field === 'password') {
                        $updateFields[] = "password = ?";
                        $params[] = password_hash($data[$field], PASSWORD_ARGON2ID);
                    } else {
                        $updateFields[] = "{$field} = ?";
                        $params[] = $data[$field];
                    }
                }
            }

            if (!empty($updateFields)) {
                $updateFields[] = "updated_at = ?";
                $params[] = date('Y-m-d H:i:s');
                $params[] = $id;

                $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $result = $this->db->query($sql, $params);

                if ($result) {
                    return [
                        'success' => true,
                        'message' => 'User updated successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to update user'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete user
     * @param int $id User ID
     * @return array Deletion result
     */
    public function delete($id)
    {
        try {
            // Check if user exists
            $existing = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Delete related records first
            $this->db->query("DELETE FROM user_sessions WHERE user_id = ?", [$id]);
            $this->db->query("DELETE FROM activity_log WHERE user_id = ?", [$id]);

            // Delete user
            $result = $this->db->query("DELETE FROM users WHERE id = ?", [$id]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to delete user'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user statistics
     * @return array Statistics data
     */
    public function getStatistics()
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
                COUNT(CASE WHEN role = 'manager' THEN 1 END) as managers,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as users,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users,
                COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users
                FROM users";

            $stats = $this->db->fetch($sql);

            return [
                'success' => true,
                'statistics' => [
                    'total' => $stats['total'] ?? 0,
                    'active' => $stats['active'] ?? 0,
                    'by_role' => [
                        'admin' => $stats['admins'] ?? 0,
                        'manager' => $stats['managers'] ?? 0,
                        'user' => $stats['users'] ?? 0
                    ],
                    'activity' => [
                        'new_users' => $stats['new_users'] ?? 0,
                        'active_users' => $stats['active_users'] ?? 0
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Change user status
     * @param int $id User ID
     * @param string $status New status
     * @return array Status change result
     */
    public function changeStatus($id, $status)
    {
        try {
            $allowedStatuses = ['active', 'inactive', 'suspended', 'banned'];
            if (!in_array($status, $allowedStatuses)) {
                return [
                    'success' => false,
                    'error' => 'Invalid status'
                ];
            }

            $result = $this->db->query(
                "UPDATE users SET status = ?, updated_at = ? WHERE id = ?",
                [$status, date('Y-m-d H:i:s'), $id]
            );

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'User status changed successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to change user status'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to change user status: ' . $e->getMessage()
            ];
        }
    }
}
