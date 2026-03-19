<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom UserManager Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class UserManager
{
    private $db;
    private $loggingService;
    private $authService;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->loggingService = new LoggingService();
        $this->authService = new AuthenticationService();
    }
    
    /**
     * Create new user
     */
    public function createUser(array $userData): array
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst($field) . ' is required',
                        'error_code' => 'MISSING_FIELD'
                    ];
                }
            }
            
            // Validate email
            $email = CoreFunctionsServiceCustom::validateInput($userData['email'], 'email');
            if (!$email) {
                return [
                    'success' => false,
                    'message' => 'Invalid email address',
                    'error_code' => 'INVALID_EMAIL'
                ];
            }
            
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email already registered',
                    'error_code' => 'EMAIL_EXISTS'
                ];
            }
            
            // Validate password
            $password = $userData['password'];
            if (strlen($password) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 8 characters',
                    'error_code' => 'WEAK_PASSWORD'
                ];
            }
            
            // Validate role
            $validRoles = ['admin', 'manager', 'associate', 'customer'];
            if (!in_array($userData['role'], $validRoles)) {
                return [
                    'success' => false,
                    'message' => 'Invalid user role',
                    'error_code' => 'INVALID_ROLE'
                ];
            }
            
            // Hash password
            $hashedPassword = CoreFunctionsServiceCustom::hashPassword($password);
            
            // Generate unique ID
            $uniqueId = $this->generateUniqueId();
            
            // Create user
            $sql = "INSERT INTO users (unique_id, name, email, password, role, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $uniqueId,
                CoreFunctionsServiceCustom::validateInput($userData['name'], 'string'),
                $email,
                $hashedPassword,
                $userData['role']
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create user');
            }
            
            $userId = $this->db->lastInsertId();
            
            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_created', [
                'new_user_id' => $userId,
                'email' => $email,
                'role' => $userData['role']
            ]);
            
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId,
                'unique_id' => $uniqueId
            ];
            
        } catch (Exception $e) {
            $this->loggingService->error("User creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create user',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Update user
     */
    public function updateUser(int $userId, array $userData): array
    {
        try {
            // Check if user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }
            
            // Build update query
            $updateFields = [];
            $updateValues = [];
            
            // Update name if provided
            if (isset($userData['name']) && !empty($userData['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($userData['name'], 'string');
            }
            
            // Update email if provided
            if (isset($userData['email']) && !empty($userData['email'])) {
                $email = CoreFunctionsServiceCustom::validateInput($userData['email'], 'email');
                if (!$email) {
                    return [
                        'success' => false,
                        'message' => 'Invalid email address',
                        'error_code' => 'INVALID_EMAIL'
                    ];
                }
                
                // Check if email already exists for another user
                $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    return [
                        'success' => false,
                        'message' => 'Email already exists',
                        'error_code' => 'EMAIL_EXISTS'
                    ];
                }
                
                $updateFields[] = "email = ?";
                $updateValues[] = $email;
            }
            
            // Update role if provided
            if (isset($userData['role']) && !empty($userData['role'])) {
                $validRoles = ['admin', 'manager', 'associate', 'customer'];
                if (!in_array($userData['role'], $validRoles)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid user role',
                        'error_code' => 'INVALID_ROLE'
                    ];
                }
                
                $updateFields[] = "role = ?";
                $updateValues[] = $userData['role'];
            }
            
            // Update status if provided
            if (isset($userData['status']) && !empty($userData['status'])) {
                $validStatuses = ['active', 'inactive', 'suspended'];
                if (!in_array($userData['status'], $validStatuses)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid user status',
                        'error_code' => 'INVALID_STATUS'
                    ];
                }
                
                $updateFields[] = "status = ?";
                $updateValues[] = $userData['status'];
            }
            
            // Update phone if provided
            if (isset($userData['phone'])) {
                $phone = CoreFunctionsServiceCustom::validateInput($userData['phone'], 'phone');
                if ($phone === false) {
                    return [
                        'success' => false,
                        'message' => 'Invalid phone number',
                        'error_code' => 'INVALID_PHONE'
                    ];
                }
                
                $updateFields[] = "phone = ?";
                $updateValues[] = $phone;
            }
            
            // Add updated_at timestamp
            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $userId; // For WHERE clause
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update',
                    'error_code' => 'NO_FIELDS'
                ];
            }
            
            // Execute update
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);
            
            if (!$result) {
                throw new Exception('Failed to update user');
            }
            
            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_updated', [
                'updated_user_id' => $userId,
                'changes' => $userData
            ]);
            
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->loggingService->error("User update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update user',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser(int $userId): array
    {
        try {
            // Check if user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }
            
            // Prevent deletion of admin users (safety check)
            if ($user['role'] === 'admin') {
                return [
                    'success' => false,
                    'message' => 'Cannot delete admin users',
                    'error_code' => 'CANNOT_DELETE_ADMIN'
                ];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Delete user's related records (optional - depends on requirements)
                // For now, just mark as deleted
                $sql = "UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_deleted', [
                    'deleted_user_id' => $userId,
                    'user_email' => $user['email']
                ]);
                
                // Commit transaction
                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->loggingService->error("User deletion error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete user',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array
    {
        try {
            $sql = "SELECT id, unique_id, name, email, phone, role, status, created_at, updated_at 
                    FROM users WHERE id = ? AND status != 'deleted'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
            
        } catch (Exception $e) {
            $this->loggingService->error("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, unique_id, name, email, phone, role, status, created_at, updated_at 
                    FROM users WHERE email = ? AND status != 'deleted'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch() ?: null;
            
        } catch (Exception $e) {
            $this->loggingService->error("Get user by email error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all users with pagination and filtering
     */
    public function getUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Build query
            $sql = "SELECT id, unique_id, name, email, phone, role, status, created_at, updated_at 
                    FROM users WHERE status != 'deleted'";
            $params = [];
            
            // Apply filters
            if (!empty($filters['search'])) {
                $sql .= " AND (name LIKE ? OR email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['role'])) {
                $sql .= " AND role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            // Count total
            $countSql = str_replace("SELECT id, unique_id, name, email, phone, role, status, created_at, updated_at", 
                                   "SELECT COUNT(*) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // Add ordering and pagination
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
            
        } catch (Exception $e) {
            $this->loggingService->error("Get users error: " . $e->getMessage());
            return [
                'users' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => 0
            ];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        try {
            $stats = [];
            
            // Total users by role
            $roleSql = "SELECT role, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY role";
            $roleStmt = $this->db->prepare($roleSql);
            $roleStmt->execute();
            
            $stats['by_role'] = [];
            foreach ($roleStmt->fetchAll() as $row) {
                $stats['by_role'][$row['role']] = (int)$row['count'];
            }
            
            // Users by status
            $statusSql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
            $statusStmt = $this->db->prepare($statusSql);
            $statusStmt->execute();
            
            $stats['by_status'] = [];
            foreach ($statusStmt->fetchAll() as $row) {
                $stats['by_status'][$row['status']] = (int)$row['count'];
            }
            
            // Recent registrations
            $recentSql = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $recentStmt = $this->db->prepare($recentSql);
            $recentStmt->execute();
            $stats['recent_registrations'] = (int)$recentStmt->fetch()['count'];
            
            // Total active users
            $totalSql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
            $totalStmt = $this->db->prepare($totalSql);
            $totalStmt->execute();
            $stats['total_active'] = (int)$totalStmt->fetch()['count'];
            
            return $stats;
            
        } catch (Exception $e) {
            $this->loggingService->error("Get user statistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Change user password (admin function)
     */
    public function changeUserPassword(int $userId, string $newPassword): array
    {
        try {
            // Check if user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ];
            }
            
            // Validate new password
            if (strlen($newPassword) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 8 characters',
                    'error_code' => 'WEAK_PASSWORD'
                ];
            }
            
            // Hash new password
            $hashedPassword = CoreFunctionsServiceCustom::hashPassword($newPassword);
            
            // Update password
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if (!$result) {
                throw new Exception('Failed to update password');
            }
            
            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'password_changed', [
                'target_user_id' => $userId
            ]);
            
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
            
        } catch (Exception $e) {
            $this->loggingService->error("Change password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to change password',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
    
    /**
     * Generate unique user ID
     */
    private function generateUniqueId(): string
    {
        do {
            $uniqueId = 'USR' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            
            $sql = "SELECT id FROM users WHERE unique_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$uniqueId]);
        } while ($stmt->fetch());
        
        return $uniqueId;
    }
    
    /**
     * Bulk user operations
     */
    public function bulkOperation(array $userIds, string $operation, array $data = []): array
    {
        try {
            if (empty($userIds)) {
                return [
                    'success' => false,
                    'message' => 'No users selected',
                    'error_code' => 'NO_USERS'
                ];
            }
            
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            
            switch ($operation) {
                case 'activate':
                    $sql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id IN ($placeholders)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($userIds);
                    $message = 'Users activated successfully';
                    break;
                    
                case 'deactivate':
                    $sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id IN ($placeholders)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($userIds);
                    $message = 'Users deactivated successfully';
                    break;
                    
                case 'delete':
                    $sql = "UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id IN ($placeholders) AND role != 'admin'";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($userIds);
                    $message = 'Users deleted successfully';
                    break;
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Invalid operation',
                        'error_code' => 'INVALID_OPERATION'
                    ];
            }
            
            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'bulk_user_operation', [
                'operation' => $operation,
                'user_ids' => $userIds
            ]);
            
            return [
                'success' => true,
                'message' => $message,
                'affected' => $stmt->rowCount()
            ];
            
        } catch (Exception $e) {
            $this->loggingService->error("Bulk operation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bulk operation failed',
                'error_code' => 'SYSTEM_ERROR'
            ];
        }
    }
}