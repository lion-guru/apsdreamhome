<?php

namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected static $table = 'users';
    protected $fillable = [
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

    public static function findByEmail(string $email) {
        $db = \App\Models\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    public static function findByUsername(string $username) {
        $db = \App\Models\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    public function setPassword(string $password): void {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->attributes['password']);
    }

    public function isActive(): bool {
        return $this->attributes['status'] === 'active' && $this->hasVerifiedEmail();
    }

    /**
     * Check if the user has verified their email
     */
    public function hasVerifiedEmail(): bool {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified
     */
    public function markEmailAsVerified(): bool {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->status = 'active';
        return $this->save();
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