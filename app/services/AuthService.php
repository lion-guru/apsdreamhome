<?php
namespace App\Services;

class AuthService {
    /**
     * Check if user is logged in (simple session check)
     */
    public function isLoggedIn(): bool {
        return isset($_SESSION['auser']);
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Simple authentication for demo purposes
     */
    public function authenticate(string $email, string $password): bool {
        // Demo users for testing
        $demo_users = [
            'admin@apsdreamhome.com' => ['password' => 'admin123', 'role' => 'admin'],
            'rajesh@apsdreamhome.com' => ['password' => 'agent123', 'role' => 'agent'],
            'amit@example.com' => ['password' => 'customer123', 'role' => 'customer']
        ];

        if (isset($demo_users[$email]) && $demo_users[$email]['password'] === $password) {
            $_SESSION['auser'] = $demo_users[$email]['role'] === 'admin' ? 'Administrator' : ucfirst($demo_users[$email]['role']);
            $_SESSION['user_id'] = array_search($email, array_keys($demo_users)) + 1;
            $_SESSION['role'] = $demo_users[$email]['role'];
            $_SESSION['email'] = $email;
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout(): void {
        session_unset();
        session_destroy();
    }
}