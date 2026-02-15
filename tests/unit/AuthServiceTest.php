<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;

class AuthServiceTest extends TestCase {
    private $authService;

    protected function setUp(): void {
        $this->authService = new AuthService();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = []; // Clear session before each test
    }

    public function testIsLoggedInReturnsFalseWhenNotLoggedIn() {
        $this->assertFalse($this->authService->isLoggedIn());
    }

    public function testIsLoggedInReturnsTrueWhenLoggedIn() {
        $_SESSION['auser'] = 'admin';
        $this->assertTrue($this->authService->isLoggedIn());
    }

    public function testIsAdminReturnsFalseWhenNotAdmin() {
        $_SESSION['role'] = 'user';
        $this->assertFalse($this->authService->isAdmin());
    }

    public function testIsAdminReturnsTrueWhenAdmin() {
        $_SESSION['role'] = 'admin';
        $this->assertTrue($this->authService->isAdmin());
    }

    public function testLogoutClearsSession() {
        $_SESSION['auser'] = 'admin';
        $this->authService->logout();
        $this->assertEmpty($_SESSION);
    }
}
