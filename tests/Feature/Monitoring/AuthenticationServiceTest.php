<?php

namespace Tests\Feature\Custom;

use App\Services\Custom\AuthenticationService;
use App\Controllers\Custom\AuthenticationController;
use PHPUnit\Framework\TestCase;

/**
 * Custom Authentication Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class AuthenticationServiceTest extends TestCase
{
    private $authService;
    private $controller;
    
    protected function setUp(): void
    {
        // Initialize custom database and logger for testing
        $this->authService = new AuthenticationService();
        $this->controller = new AuthenticationController();
    }
    
    /** @test */
    public function it_can_authenticate_valid_user()
    {
        // Create test user first
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $registerResult = $this->authService->register($userData);
        $this->assertTrue($registerResult['success']);
        
        // Test login
        $loginResult = $this->authService->login('test@example.com', 'password123');
        
        $this->assertTrue($loginResult['success']);
        $this->assertArrayHasKey('user', $loginResult);
        $this->assertEquals('test@example.com', $loginResult['user']['email']);
        $this->assertArrayNotHasKey('password', $loginResult['user']);
    }
    
    /** @test */
    public function it_rejects_invalid_credentials()
    {
        $loginResult = $this->authService->login('nonexistent@example.com', 'wrongpassword');
        
        $this->assertFalse($loginResult['success']);
        $this->assertEquals('INVALID_CREDENTIALS', $loginResult['code']);
    }
    
    /** @test */
    public function it_validates_email_format()
    {
        $loginResult = $this->authService->login('invalid-email', 'password123');
        
        $this->assertFalse($loginResult['success']);
        $this->assertEquals('INVALID_EMAIL', $loginResult['code']);
    }
    
    /** @test */
    public function it_handles_empty_password()
    {
        $loginResult = $this->authService->login('test@example.com', '');
        
        $this->assertFalse($loginResult['success']);
        $this->assertEquals('PASSWORD_REQUIRED', $loginResult['code']);
    }
    
    /** @test */
    public function it_registers_new_user_successfully()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('newuser@example.com', $result['user']['email']);
        $this->assertEquals('New User', $result['user']['name']);
        $this->assertEquals('user', $result['user']['role']);
    }
    
    /** @test */
    public function it_rejects_duplicate_email_registration()
    {
        // Create first user
        $userData1 = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $result1 = $this->authService->register($userData1);
        $this->assertTrue($result1['success']);
        
        // Try to create second user with same email
        $userData2 = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $result2 = $this->authService->register($userData2);
        $this->assertFalse($result2['success']);
        $this->assertEquals('EMAIL_EXISTS', $result2['code']);
    }
    
    /** @test */
    public function it_validates_password_length()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'shortpass@example.com',
            'password' => 'short', // Less than 8 characters
            'role' => 'user'
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('PASSWORD_TOO_SHORT', $result['code']);
    }
    
    /** @test */
    public function it_validates_required_fields()
    {
        // Test missing name
        $userData1 = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $result1 = $this->authService->register($userData1);
        $this->assertFalse($result1['success']);
        $this->assertEquals('MISSING_FIELD', $result1['code']);
        
        // Test missing email
        $userData2 = [
            'name' => 'Test User',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $result2 = $this->authService->register($userData2);
        $this->assertFalse($result2['success']);
        $this->assertEquals('MISSING_FIELD', $result2['code']);
    }
    
    /** @test */
    public function it_can_check_authentication_status()
    {
        // Should be false initially
        $this->assertFalse($this->authService->isAuthenticated());
        
        // Create and login user
        $userData = [
            'name' => 'Auth Test User',
            'email' => 'authtest@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $this->authService->register($userData);
        $this->authService->login('authtest@example.com', 'password123');
        
        // Should be true after login
        $this->assertTrue($this->authService->isAuthenticated());
    }
    
    /** @test */
    public function it_can_get_current_user()
    {
        // Should be null initially
        $this->assertNull($this->authService->getCurrentUser());
        
        // Create and login user
        $userData = [
            'name' => 'Current User Test',
            'email' => 'currentuser@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $this->authService->register($userData);
        $this->authService->login('currentuser@example.com', 'password123');
        
        $currentUser = $this->authService->getCurrentUser();
        
        $this->assertNotNull($currentUser);
        $this->assertEquals('currentuser@example.com', $currentUser['email']);
        $this->assertEquals('Current User Test', $currentUser['name']);
    }
    
    /** @test */
    public function it_can_get_user_role()
    {
        // Should be null initially
        $this->assertNull($this->authService->getUserRole());
        
        // Create and login admin user
        $userData = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];
        
        $this->authService->register($userData);
        $this->authService->login('admin@example.com', 'password123');
        
        $this->assertEquals('admin', $this->authService->getUserRole());
    }
    
    /** @test */
    public function it_can_check_permissions()
    {
        // Create and login admin user
        $userData = [
            'name' => 'Permission Test User',
            'email' => 'permission@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];
        
        $this->authService->register($userData);
        $this->authService->login('permission@example.com', 'password123');
        
        // Admin should have all permissions
        $this->assertTrue($this->authService->hasPermission('any_permission'));
        $this->assertTrue($this->authService->hasPermission('manage_users'));
        $this->assertTrue($this->authService->hasPermission('view_reports'));
    }
    
    /** @test */
    public function it_handles_user_logout()
    {
        // Create and login user
        $userData = [
            'name' => 'Logout Test User',
            'email' => 'logout@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $this->authService->register($userData);
        $this->authService->login('logout@example.com', 'password123');
        
        // Should be authenticated
        $this->assertTrue($this->authService->isAuthenticated());
        
        // Logout
        $logoutResult = $this->authService->logout();
        
        $this->assertTrue($logoutResult['success']);
        
        // Should not be authenticated after logout
        $this->assertFalse($this->authService->isAuthenticated());
    }
    
    /** @test */
    public function it_can_change_password()
    {
        // Create and login user
        $userData = [
            'name' => 'Password Change User',
            'email' => 'passwordchange@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $registerResult = $this->authService->register($userData);
        $this->assertTrue($registerResult['success']);
        
        $userId = $registerResult['user']['id'];
        
        // Change password
        $changeResult = $this->authService->changePassword($userId, 'password123', 'newpassword123');
        
        $this->assertTrue($changeResult['success']);
        
        // Should be able to login with new password
        $loginResult = $this->authService->login('passwordchange@example.com', 'newpassword123');
        $this->assertTrue($loginResult['success']);
        
        // Should not be able to login with old password
        $loginResultOld = $this->authService->login('passwordchange@example.com', 'password123');
        $this->assertFalse($loginResultOld['success']);
    }
    
    /** @test */
    public function it_rejects_wrong_current_password()
    {
        // Create user
        $userData = [
            'name' => 'Wrong Password User',
            'email' => 'wrongpass@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $registerResult = $this->authService->register($userData);
        $this->assertTrue($registerResult['success']);
        
        $userId = $registerResult['user']['id'];
        
        // Try to change with wrong current password
        $changeResult = $this->authService->changePassword($userId, 'wrongpassword', 'newpassword123');
        
        $this->assertFalse($changeResult['success']);
        $this->assertEquals('INVALID_CURRENT_PASSWORD', $changeResult['code']);
    }
    
    /** @test */
    public function it_can_reset_password()
    {
        // Create user
        $userData = [
            'name' => 'Reset Password User',
            'email' => 'reset@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $this->authService->register($userData);
        
        // Request password reset
        $resetResult = $this->authService->resetPassword('reset@example.com');
        
        $this->assertTrue($resetResult['success']);
        $this->assertEquals('Password reset link sent to your email', $resetResult['message']);
    }
    
    /** @test */
    public function it_handles_nonexistent_email_reset()
    {
        $resetResult = $this->authService->resetPassword('nonexistent@example.com');
        
        $this->assertFalse($resetResult['success']);
        $this->assertEquals('EMAIL_NOT_FOUND', $resetResult['code']);
    }
    
    /** @test */
    public function it_validates_user_roles()
    {
        $userData = [
            'name' => 'Invalid Role User',
            'email' => 'invalidrole@example.com',
            'password' => 'password123',
            'role' => 'invalid_role'
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('INVALID_ROLE', $result['code']);
    }
    
    /** @test */
    public function it_handles_inactive_accounts()
    {
        // Create user
        $userData = [
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];
        
        $registerResult = $this->authService->register($userData);
        $this->assertTrue($registerResult['success']);
        
        // Manually set user to inactive (simulating admin action)
        $database = \App\Core\Database::getInstance();
        $database->update('users', 
            ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$registerResult['user']['id']]
        );
        
        // Try to login
        $loginResult = $this->authService->login('inactive@example.com', 'password123');
        
        $this->assertFalse($loginResult['success']);
        $this->assertEquals('ACCOUNT_INACTIVE', $loginResult['code']);
    }
    
    /** @test */
    public function it_handles_rate_limiting()
    {
        // Try multiple failed logins to trigger rate limiting
        for ($i = 0; $i < 6; $i++) {
            $this->authService->login('ratelimit@example.com', 'wrongpassword');
        }
        
        // Should be rate limited
        $loginResult = $this->authService->login('ratelimit@example.com', 'password123');
        
        $this->assertFalse($loginResult['success']);
        $this->assertEquals('RATE_LIMITED', $loginResult['code']);
        $this->assertTrue($loginResult['locked']);
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        $database = \App\Core\Database::getInstance();
        
        // Clean up test users
        $testEmails = [
            'test@example.com',
            'newuser@example.com',
            'duplicate@example.com',
            'authtest@example.com',
            'currentuser@example.com',
            'admin@example.com',
            'permission@example.com',
            'logout@example.com',
            'passwordchange@example.com',
            'wrongpass@example.com',
            'reset@example.com',
            'invalidrole@example.com',
            'inactive@example.com',
            'ratelimit@example.com'
        ];
        
        foreach ($testEmails as $email) {
            $database->query("DELETE FROM users WHERE email = ?", [$email]);
        }
        
        // Clean up login attempts and logs
        $database->query("DELETE FROM login_attempts WHERE email LIKE ?", ['%example.com%']);
        $database->query("DELETE FROM login_logs WHERE email LIKE ?", ['%example.com%']);
        
        parent::tearDown();
    }
}
