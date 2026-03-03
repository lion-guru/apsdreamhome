<?php

namespace App\Models;

class User extends Model
{
    protected static $table = 'users';
    protected array $fillable = [
        'username',
        'email',
        'password',
        'mobile',
        'role',
        'status',
        'email_verified_at',
        'google_id',
        'created_at',
        'updated_at'
    ];

    protected $dates = ['email_verified_at'];

    public static function findByEmail(string $email)
    {
        $db = \App\Core\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    public static function findByUsername(string $username)
    {
        $db = \App\Core\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    public function setPassword(string $password): void
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password']);
    }

    public function isActive(): bool
    {
        return $this->attributes['status'] === 'active' && $this->hasVerifiedEmail();
    }

    /**
     * Check if the user has verified their email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->status = 'active';
        return $this->save();
    }

    public function isAdmin(): bool
    {
        return $this->attributes['role'] === 'admin';
    }

    public function isAssociate(): bool
    {
        return $this->attributes['role'] === 'associate';
    }

    public function isCustomer(): bool
    {
        return $this->attributes['role'] === 'customer';
    }

    /**
     * Get users for admin with filters and pagination
     */
    public static function getAdminUsers($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Role filter
            if (!empty($filters['role'])) {
                $where_conditions[] = "u.role = :role";
                $params['role'] = $filters['role'];
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'name', 'email', 'created_at', 'status'];
            $sort = in_array($filters['sort'] ?? '', $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY u.{$sort} {$order}";

            $sql = "
                SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.phone,
                    u.role,
                    u.status,
                    u.created_at,
                    u.last_login,
                    (SELECT COUNT(*) FROM properties p WHERE p.created_by = u.id) as properties_count
                FROM users u
                {$where_clause}
                {$order_clause}
                LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':limit', (int)$filters['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)(($filters['page'] - 1) * $filters['per_page']), \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Admin users query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total users count for pagination
     */
    public static function getAdminTotalUsers($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            // Role filter
            if (!empty($filters['role'])) {
                $where_conditions[] = "u.role = :role";
                $params['role'] = $filters['role'];
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = :status";
                $params['status'] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM users u {$where_clause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (\Exception $e) {
            error_log('Admin total users query error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active agents for dropdowns
     */
    public static function getActiveAgents()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Active agents query error: ' . $e->getMessage());
            return [];
        }
    }
}
