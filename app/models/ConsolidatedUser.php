<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * Consolidated User Model
 * Unifies functionality from multiple legacy User implementations:
 * - app/models/User.php (modern)
 * - src/Models/User.php (legacy)
 * - includes/classes/User.php (legacy)
 * - includes/managers.php UserManager (legacy)
 */
class ConsolidatedUser extends UnifiedModel
{
    protected static $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'type',
        'status',
        'profile_picture',
        'api_access',
        'api_rate_limit',
        'created_at',
        'updated_at'
    ];

    protected array $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Legacy manager instances
     */
    public static $userManager = null;

    /**
     * Initialize legacy managers if available
     */
    protected static function initLegacyManagers()
    {
        if (self::$userManager === null) {
            try {
                if (class_exists('UserManager')) {
                    global $db;
                    self::$userManager = new \UserManager($db);
                }
            } catch (\Exception $e) {
                // Legacy manager not available, will use modern methods
                self::$userManager = false;
            }
        }
        return self::$userManager;
    }

    /**
     * Map legacy field names to modern field names
     */
    protected static function mapLegacyFields($data)
    {
        $mapped = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'user_id':
                    $mapped['id'] = $value;
                    break;
                case 'full_name':
                    $mapped['name'] = $value;
                    break;
                case 'user_type':
                    $mapped['type'] = $value;
                    break;
                case 'user_role':
                    $mapped['type'] = $value;
                    break;
                case 'role':
                    $mapped['type'] = $value;
                    break;
                case 'profile_image':
                    $mapped['profile_picture'] = $value;
                    break;
                case 'is_verified':
                    // No direct mapping for email_verified in current schema
                    break;
                default:
                    $mapped[$key] = $value;
            }
        }
        return $mapped;
    }

    /**
     * Find user by ID (legacy method)
     */
    public static function findLegacy($id)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getUserProfile')) {
            $userData = $manager->getUserProfile($id);
            if ($userData) {
                $userData = self::mapLegacyFields($userData);
                return new static($userData);
            }
        }
        return self::find($id);
    }

    /**
     * Get all users (legacy method)
     */
    public static function allLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'getAllUsers')) {
            $users = $manager->getAllUsers();
            return array_map(function ($userData) {
                $userData = self::mapLegacyFields($userData);
                return new static($userData);
            }, $users);
        }
        return self::all();
    }

    /**
     * Save user (legacy method)
     */
    public function saveLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'updateProfile')) {
            $data = $this->toArray();
            $data['user_id'] = $this->id;
            return $manager->updateProfile($data);
        }
        return $this->save();
    }

    /**
     * Delete user (legacy method)
     */
    public function deleteLegacy()
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'deleteUser')) {
            return $manager->deleteUser($this->id);
        }
        return $this->delete();
    }

    /**
     * Authenticate user (consolidated method)
     */
    public static function authenticate($email, $password)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'authenticate')) {
            $userData = $manager->authenticate($email, $password);
            if ($userData) {
                $userData = self::mapLegacyFields($userData);
                return new static($userData);
            }
        }

        // Fallback to modern method
        $user = self::whereStatic('email', $email)->first();
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    /**
     * Register new user (consolidated method)
     */
    public static function register($data)
    {
        $manager = self::initLegacyManagers();
        if ($manager && method_exists($manager, 'register')) {
            $userId = $manager->register($data);
            if ($userId) {
                return self::find($userId);
            }
        }

        // Fallback to modern method
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return self::create($data);
    }

    /**
     * Get full name
     */
    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display name
     */
    public function getDisplayName()
    {
        $fullName = $this->getFullName();
        return $fullName ?: $this->username ?: $this->email;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is associate
     */
    public function isAssociate()
    {
        return $this->role === 'associate';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail()
    {
        return (bool) $this->email_verified;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified()
    {
        $this->email_verified = true;
        $this->save();
    }

    /**
     * Set password (handles hashing)
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Get user by email
     */
    public static function findByEmail($email)
    {
        return self::whereStatic('email', $email)->first();
    }

    /**
     * Get user by username
     */
    public static function findByUsername($username)
    {
        return self::whereStatic('username', $username)->first();
    }

    /**
     * Get active users
     */
    public static function getActiveUsers()
    {
        return self::whereStatic('status', 'active')->get();
    }

    /**
     * Get users by role
     */
    public static function getUsersByRole($role)
    {
        return self::whereStatic('role', $role)->get();
    }
}
