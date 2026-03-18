<?php

namespace App\Models;

use Exception;

/**
 * Admin Model
 * Handles admin-related database operations
 */
class Admin extends Model
{
    protected static $table = 'admins';
    protected static $primaryKey = 'id';

    // Instance properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $status;
    public $last_login;
    public $created_at;
    public $updated_at;

    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Find admin by username or email
     */
    public static function findByUsernameOrEmail($identifier)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " 
                    WHERE username = ? OR email = ? 
                    LIMIT 1";
            
            $result = static::getDb()->fetch($sql, [$identifier, $identifier]);
            
            if ($result) {
                return new self($result);
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Admin findByUsernameOrEmail error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find admin by ID
     */
    public static function findById($id)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " 
                    WHERE id = ? 
                    LIMIT 1";
            
            $result = static::getDb()->fetch($sql, [$id]);
            
            if ($result) {
                return new self($result);
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Admin findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update last login
     */
    public function updateLastLogin()
    {
        try {
            return static::update($this->id, [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Admin updateLastLogin error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Check if admin is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Get admin role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Check if admin has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Create new admin
     */
    public static function create(array $data)
    {
        try {
            $adminData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'] ?? 'admin',
                'status' => $data['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return static::insert($adminData);
        } catch (Exception $e) {
            error_log("Admin create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update admin
     */
    public function update(array $data)
    {
        try {
            $updateData = [];
            
            if (isset($data['username'])) {
                $updateData['username'] = $data['username'];
            }
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            if (isset($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if (isset($data['role'])) {
                $updateData['role'] = $data['role'];
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            return static::update($this->id, $updateData);
        } catch (Exception $e) {
            error_log("Admin update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all admins
     */
    public static function getAll()
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " 
                    ORDER BY created_at DESC";
            
            $results = static::getDb()->fetchAll($sql);
            
            $admins = [];
            foreach ($results as $result) {
                $admins[] = new self($result);
            }
            
            return $admins;
        } catch (Exception $e) {
            error_log("Admin getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get admin statistics
     */
    public static function getStats()
    {
        try {
            $stats = [];

            // Total admins
            $totalResult = static::getDb()->fetch("SELECT COUNT(*) as total FROM " . static::$table);
            $stats['total'] = $totalResult['total'] ?? 0;

            // Active admins
            $activeResult = static::getDb()->fetch("SELECT COUNT(*) as count FROM " . static::$table . " WHERE status = 'active'");
            $stats['active'] = $activeResult['count'] ?? 0;

            // Admins by role
            $roleResults = static::getDb()->fetchAll("SELECT role, COUNT(*) as count FROM " . static::$table . " GROUP BY role");
            $stats['by_role'] = [];
            foreach ($roleResults as $result) {
                $stats['by_role'][$result['role']] = $result['count'];
            }

            return $stats;
        } catch (Exception $e) {
            error_log("Admin getStats error: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'by_role' => []
            ];
        }
    }

    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'last_login' => $this->last_login,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
