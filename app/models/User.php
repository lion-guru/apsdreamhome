<?php
namespace App\Models;

class User extends Model {
    protected static string $table = 'users';
    protected array $fillable = [
        'username',
        'email',
        'password',
        'mobile',
        'role',
        'status',
        'google_id',
        'created_at',
        'updated_at'
    ];

    public static function findByEmail(string $email) {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        $result = $stmt->fetch();
        return $result ? new static($result) : null;
    }

    public static function findByUsername(string $username) {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
        $result = $stmt->fetch();
        return $result ? new static($result) : null;
    }

    public function setPassword(string $password): void {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->attributes['password']);
    }

    public function isActive(): bool {
        return $this->attributes['status'] === 'active';
    }

    public function isAdmin(): bool {
        return $this->attributes['role'] === 'admin';
    }

    public function isAssociate(): bool {
        return $this->attributes['role'] === 'associate';
    }

    public function isCustomer(): bool {
        return $this->attributes['role'] === 'customer';
    }
}