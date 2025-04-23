<?php
namespace App\Services;

use App\Models\User;
use App\Core\Database;

class AuthService {
    public function login(string $email, string $password): ?User {
        $user = User::findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }
        
        if (!$user->isActive()) {
            throw new \Exception('Account is not active');
        }
        
        $this->startSession($user);
        return $user;
    }
    
    public function register(array $userData): User {
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            if (User::findByEmail($userData['email'])) {
                throw new \Exception('Email already exists');
            }
            
            if (User::findByUsername($userData['username'])) {
                throw new \Exception('Username already exists');
            }
            
            $user = new User($userData);
            $user->setPassword($userData['password']);
            $user->save();
            
            $db->commit();
            return $user;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    
    public function logout(): void {
        session_destroy();
    }
    
    public function getCurrentUser(): ?User {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return User::find($_SESSION['user_id']);
    }
    
    private function startSession(User $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
    }
    
    public function requireAuth(): void {
        if (!$this->getCurrentUser()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public function requireRole(string $role): void {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            header('Location: /login.php');
            exit;
        }
        
        $hasRole = match($role) {
            'admin' => $user->isAdmin(),
            'associate' => $user->isAssociate(),
            'customer' => $user->isCustomer(),
            default => false
        };
        
        if (!$hasRole) {
            header('Location: /403.php');
            exit;
        }
    }
}