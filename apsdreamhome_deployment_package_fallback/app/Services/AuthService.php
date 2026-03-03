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
     * Logout user
     */
    public function logout(): void {
        session_unset();
        session_destroy();
    }
}