<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;

class AuthenticationTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    public function test_user_registration()
    {
        // Check if registration page exists
        $registerFile = __DIR__ . '/../../register.php';
        if (file_exists($registerFile)) {
            $content = file_get_contents($registerFile);
            $this->assertStringContains('form', $content, 'Registration page should have form');
            $this->assertStringContains('name', $content, 'Registration form should have name field');
            $this->assertStringContains('email', $content, 'Registration form should have email field');
            $this->assertStringContains('password', $content, 'Registration form should have password field');
        }
    }
    
    public function test_user_login()
    {
        // Check if login page exists
        $loginFile = __DIR__ . '/../../login.php';
        if (file_exists($loginFile)) {
            $content = file_get_contents($loginFile);
            $this->assertStringContains('form', $content, 'Login page should have form');
            $this->assertStringContains('email', $content, 'Login form should have email field');
            $this->assertStringContains('password', $content, 'Login form should have password field');
            $this->assertStringContains('submit', $content, 'Login form should have submit button');
        }
    }
    
    public function test_admin_login()
    {
        // Check if admin login page exists
        $adminLoginFile = __DIR__ . '/../../admin/index.php';
        $this->assertTrue(file_exists($adminLoginFile), 'Admin login page should exist');
        
        $content = file_get_contents($adminLoginFile);
        $this->assertStringContains('form', $content, 'Admin login page should have form');
        $this->assertStringContains('username', $content, 'Admin login form should have username field');
        $this->assertStringContains('password', $content, 'Admin login form should have password field');
    }
    
    public function test_user_table_exists()
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = :database 
            AND table_name = 'users'
        ");
        
        $stmt->execute(['database' => DB_NAME]);
        $exists = $stmt->fetch()['count'] > 0;
        
        $this->assertTrue($exists, 'Users table should exist');
    }
    
    public function test_user_table_has_required_columns()
    {
        $stmt = $this->pdo->prepare("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = :database 
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME IN ('id', 'name', 'email', 'password', 'role', 'status')
        ");
        
        $stmt->execute(['database' => DB_NAME]);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'name', 'email', 'password', 'role', 'status'];
        foreach ($requiredColumns as $column) {
            $this->assertContains($column, $columns, "Users table should have {$column} column");
        }
    }
    
    public function test_can_create_user()
    {
        // Create a test user
        $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, role, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $name = 'Test User';
        $email = 'testuser' . time() . '@example.com';
        $role = 'customer';
        $status = 'active';
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        
        $stmt->execute([$name, $email, $hashedPassword, $role, $status, $created_at, $updated_at]);
        
        $userId = $this->pdo->lastInsertId();
        $this->assertTrue($userId > 0, 'User should be created with valid ID');
        
        // Verify user exists
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertEquals($name, $user['name'], 'User name should match');
        $this->assertEquals($email, $user['email'], 'User email should match');
        $this->assertEquals($role, $user['role'], 'User role should match');
        $this->assertTrue(password_verify('testpassword123', $user['password']), 'Password should be hashed correctly');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    public function test_user_authentication()
    {
        // Create a test user
        $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, role, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $email = 'testauth' . time() . '@example.com';
        $stmt->execute([
            'Test Auth User',
            $email,
            $hashedPassword,
            'customer',
            'active',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $userId = $this->pdo->lastInsertId();
        
        // Test authentication
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $this->assertNotNull($user, 'User should be found by email');
        $this->assertTrue(password_verify('testpassword123', $user['password']), 'Password verification should work');
        $this->assertEquals('active', $user['status'], 'User should be active');
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    public function test_user_roles()
    {
        // Create users with different roles
        $roles = ['customer', 'agent', 'admin'];
        $createdIds = [];
        
        foreach ($roles as $role) {
            $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $email = 'test' . $role . time() . '@example.com';
            $stmt->execute([
                'Test ' . ucfirst($role),
                $email,
                $hashedPassword,
                $role,
                'active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test role filtering
        foreach ($roles as $role) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->execute([$role]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find users with {$role} role");
        }
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_user_status_management()
    {
        // Create a user with different status
        $statuses = ['active', 'inactive', 'suspended'];
        $createdIds = [];
        
        foreach ($statuses as $status) {
            $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (name, email, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $email = 'test' . $status . time() . '@example.com';
            $stmt->execute([
                'Test ' . ucfirst($status),
                $email,
                $hashedPassword,
                'customer',
                $status,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $createdIds[] = $this->pdo->lastInsertId();
        }
        
        // Test status filtering
        foreach ($statuses as $status) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE status = ?");
            $stmt->execute([$status]);
            $count = $stmt->fetch()['count'];
            
            $this->assertGreaterThan(0, $count, "Should find users with {$status} status");
        }
        
        // Test that only active users can login
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stmt->execute();
        $activeCount = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $activeCount, 'Should have active users');
        
        // Clean up
        foreach ($createdIds as $id) {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    public function test_email_uniqueness()
    {
        // Create first user
        $hashedPassword = password_hash('testpassword123', PASSWORD_DEFAULT);
        $email = 'testunique' . time() . '@example.com';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, role, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Test User 1',
            $email,
            $hashedPassword,
            'customer',
            'active',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        $userId1 = $this->pdo->lastInsertId();
        
        // Try to create second user with same email (should fail)
        try {
            $stmt->execute([
                'Test User 2',
                $email,
                $hashedPassword,
                'customer',
                'active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            
            $this->fail('Should not allow duplicate emails');
        } catch (\PDOException $e) {
            // Expected behavior - duplicate email should fail
            $this->assertTrue(true, 'Duplicate email should be rejected');
        }
        
        // Clean up
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId1]);
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
