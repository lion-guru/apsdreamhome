<?php

namespace App\Models;

use App\Models\Model;

/**
 * Authentication Model
 * Handles authentication-related data operations
 */
class Auth extends Model
{
    protected static $table = 'users';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'last_login',
        'created_at',
        'updated_at'
    ];

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?Auth
    {
        return self::where('email', $email)
                   ->where('status', '!=', 'deleted')
                   ->first();
    }

    /**
     * Find user by remember token
     */
    public static function findByRememberToken(string $token): ?Auth
    {
        $sql = "SELECT u.* FROM " . static::$table . " u
                JOIN remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status != 'deleted'";
        
        $result = self::raw($sql, [$token]);
        return $result ? new self($result[0]) : null;
    }

    /**
     * Get user by ID with role
     */
    public static function findByIdWithRole(int $id): ?Auth
    {
        return self::where('id', $id)
                   ->where('status', '!=', 'deleted')
                   ->first();
    }

    /**
     * Update last login
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Update password
     */
    public function updatePassword(string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->update([
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is locked
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Lock user account
     */
    public function lock(): bool
    {
        return $this->update(['status' => 'locked']);
    }

    /**
     * Unlock user account
     */
    public function unlock(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Get user's full name
     */
    public function getFullName(): string
    {
        return $this->name ?? '';
    }

    /**
     * Get user's role label
     */
    public function getRoleLabel(): string
    {
        $labels = [
            'admin' => 'Administrator',
            'agent' => 'Agent',
            'customer' => 'Customer',
            'associate' => 'Associate'
        ];

        return $labels[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Get user's status label
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'locked' => 'Locked',
            'deleted' => 'Deleted'
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted last login
     */
    public function getFormattedLastLogin(): string
    {
        if (!$this->last_login) {
            return 'Never';
        }

        return date('M j, Y H:i', strtotime($this->last_login));
    }

    /**
     * Get registration date
     */
    public function getRegistrationDate(): string
    {
        return date('M j, Y', strtotime($this->created_at));
    }

    /**
     * Get user statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Total users
        $stats['total'] = self::count();

        // Users by role
        $roleStats = self::raw("
            SELECT role, COUNT(*) as count 
            FROM " . static::$table . " 
            WHERE status != 'deleted'
            GROUP BY role
        ");

        $stats['by_role'] = [];
        foreach ($roleStats as $stat) {
            $stats['by_role'][$stat['role']] = $stat['count'];
        }

        // Users by status
        $statusStats = self::raw("
            SELECT status, COUNT(*) as count 
            FROM " . static::$table . " 
            GROUP BY status
        ");

        $stats['by_status'] = [];
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }

        // Recent registrations
        $stats['recent'] = self::where('status', '!=', 'deleted')
                              ->orderBy('created_at', 'desc')
                              ->limit(10)
                              ->get();

        // Active users (logged in last 30 days)
        $stats['active_30_days'] = self::raw("
            SELECT COUNT(*) as count 
            FROM " . static::$table . " 
            WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")[0]['count'] ?? 0;

        return $stats;
    }

    /**
     * Search users
     */
    public static function search(string $term): array
    {
        return self::where('name', 'LIKE', "%{$term}%")
                   ->orWhere('email', 'LIKE', "%{$term}%")
                   ->where('status', '!=', 'deleted')
                   ->orderBy('name', 'asc')
                   ->get();
    }

    /**
     * Get users by role
     */
    public static function getByRole(string $role): array
    {
        return self::where('role', $role)
                   ->where('status', 'active')
                   ->orderBy('name', 'asc')
                   ->get();
    }

    /**
     * Get users by status
     */
    public static function getByStatus(string $status): array
    {
        return self::where('status', $status)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Create password reset token
     */
    public static function createPasswordReset(string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $sql = "INSERT INTO password_resets (email, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()";
        
        self::execute($sql, [$email, $token, $expires, $token, $expires]);

        return $token;
    }

    /**
     * Validate password reset token
     */
    public static function validatePasswordReset(string $token): ?array
    {
        $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
        $result = self::raw($sql, [$token]);
        
        return $result ? $result[0] : null;
    }

    /**
     * Delete password reset token
     */
    public static function deletePasswordReset(string $token): void
    {
        $sql = "DELETE FROM password_resets WHERE token = ?";
        self::execute($sql, [$token]);
    }

    /**
     * Record login attempt
     */
    public static function recordLoginAttempt(string $email, bool $success): void
    {
        $sql = "INSERT INTO login_attempts (email, success, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        self::execute($sql, [
            $email,
            $success ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Check rate limit for login attempts
     */
    public static function checkRateLimit(string $email, int $maxAttempts = 5, int $windowMinutes = 15): bool
    {
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE email = ? AND success = 0 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $attempts = self::raw($sql, [$email, $windowMinutes])[0]['attempts'] ?? 0;
        
        return $attempts < $maxAttempts;
    }

    /**
     * Get failed login attempts count
     */
    public static function getFailedAttempts(string $email, int $minutes = 15): int
    {
        $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
                WHERE email = ? AND success = 0 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        return self::raw($sql, [$email, $minutes])[0]['attempts'] ?? 0;
    }

    /**
     * Create remember token
     */
    public function createRememberToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days

        $sql = "INSERT INTO remember_tokens (user_id, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()";
        
        self::execute($sql, [$this->id, $token, $expires, $token, $expires]);

        return $token;
    }

    /**
     * Delete remember tokens
     */
    public function deleteRememberTokens(): void
    {
        $sql = "DELETE FROM remember_tokens WHERE user_id = ?";
        self::execute($sql, [$this->id]);
    }

    /**
     * Get user's recent activity
     */
    public function getRecentActivity(int $limit = 10): array
    {
        $sql = "SELECT * FROM user_activity 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return self::raw($sql, [$this->id, $limit]);
    }

    /**
     * Log user activity
     */
    public function logActivity(string $action, array $data = []): void
    {
        $sql = "INSERT INTO user_activity (user_id, action, activity_data, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        self::execute($sql, [$this->id, $action, json_encode($data)]);
    }

    /**
     * Sanitize user data for output
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        unset($data['password']);
        return $data;
    }
}
