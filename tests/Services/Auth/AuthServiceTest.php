<?php

namespace Tests\Services\Auth;

use PHPUnit\Framework\TestCase;
use App\Services\Auth\AuthService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->authService = new AuthService($this->db, $this->logger);
    }

    public function testLoginSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = [
            'id' => 1,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($user);

        $result = $this->authService->login($email, $password);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful', $result['message']);
        $this->assertArrayHasKey('user', $result);
    }

    public function testLoginFailureInvalidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->authService->login($email, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
    }

    public function testRegisterSuccess(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '1234567890'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null); // Email not exists

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->authService->register($userData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Registration successful', $result['message']);
    }

    public function testRegisterEmailExists(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['id' => 1]); // Email exists

        $result = $this->authService->register($userData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Email already exists', $result['message']);
    }

    public function testLogout(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = 'test@example.com';

        $result = $this->authService->logout();

        $this->assertTrue($result['success']);
        $this->assertEquals('Logout successful', $result['message']);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testForgotPassword(): void
    {
        $email = 'test@example.com';
        $user = ['id' => 1, 'email' => $email];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($user);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->authService->forgotPassword($email);

        $this->assertTrue($result['success']);
        $this->assertEquals('Password reset link sent', $result['message']);
    }

    public function testResetPassword(): void
    {
        $token = 'valid_token';
        $newPassword = 'newpassword123';
        $resetData = ['user_id' => 1, 'expires_at' => date('Y-m-d H:i:s', time() + 3600)];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($resetData);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->authService->resetPassword($token, $newPassword);

        $this->assertTrue($result['success']);
        $this->assertEquals('Password reset successful', $result['message']);
    }

    public function testCheckRole(): void
    {
        $_SESSION['user_role'] = 'admin';

        $result = $this->authService->checkRole('admin');

        $this->assertTrue($result);
    }

    public function testCheckRoleFailure(): void
    {
        $_SESSION['user_role'] = 'user';

        $result = $this->authService->checkRole('admin');

        $this->assertFalse($result);
    }

    public function testGetLoginStats(): void
    {
        $stats = [
            'total_logins' => 100,
            'successful_logins' => 90,
            'failed_logins' => 10,
            'unique_users' => 50
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($stats);

        $result = $this->authService->getLoginStats();

        $this->assertEquals($stats, $result);
    }
}
