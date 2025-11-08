<?php

namespace App\Core;

use App\Models\User;

class Auth {
    /**
     * @var User The authenticated user instance
     */
    protected $user;
    
    /**
     * Check if a user is authenticated
     */
    public function check(): bool {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get the authenticated user
     */
    public function user() {
        if ($this->user) {
            return $this->user;
        }
        
        if (!$this->check()) {
            return null;
        }
        
        $userModel = new User();
        $this->user = $userModel->find($_SESSION['user_id']);
        
        return $this->user;
    }
    
    /**
     * Get the authenticated user's ID
     */
    public function id() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Attempt to authenticate a user
     */
    public function attempt(array $credentials): bool {
        $userModel = new User();
        
        $user = $userModel->where('email', $credentials['email'])->first();
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($credentials['password'], $user->password)) {
            return false;
        }
        
        // Set the user session
        $this->login($user);
        
        return true;
    }
    
    /**
     * Log a user in
     */
    public function login($user) {
        if (is_numeric($user)) {
            $userModel = new User();
            $user = $userModel->find($user);
        }
        
        if (!$user) {
            return false;
        }
        
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role ?? 'user';
        
        $this->user = $user;
        
        return true;
    }
    
    /**
     * Log the user out
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        $this->user = null;
        
        return true;
    }
    
    /**
     * Check if the authenticated user is an admin
     */
    public function isAdmin(): bool {
        if (!$this->check()) {
            return false;
        }
        
        $user = $this->user();
        
        return $user && ($user->role === 'admin' || $user->role === 'superadmin');
    }
    
    /**
     * Check if the authenticated user has a specific role
     */
    public function hasRole(string $role): bool {
        if (!$this->check()) {
            return false;
        }
        
        $user = $this->user();
        
        return $user && $user->role === $role;
    }
    
    /**
     * Check if the authenticated user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool {
        if (!$this->check()) {
            return false;
        }
        
        $user = $this->user();
        
        return $user && in_array($user->role, $roles, true);
    }
    
    /**
     * Register a new user
     */
    public function register(array $data) {
        $userModel = new User();
        
        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        // Create the user
        $userId = $userModel->create($data);
        
        if (!$userId) {
            return false;
        }
        
        // Log the user in
        return $this->login($userId);
    }
}
