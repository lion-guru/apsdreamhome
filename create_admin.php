<?php
/**
 * Admin User Creation Script
 * Creates an initial admin user for APS Dream Home system
 * Run this script once to set up admin access
 */

require_once __DIR__ . '/includes/db_connection.php';

class AdminUserCreator {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create admin user with secure credentials
     */
    public function createAdminUser() {
        // Admin credentials
        $admin_data = [
            'name' => 'System Administrator',
            'email' => 'admin@apsdreamhome.com',
            'phone' => '9876543210',
            'password' => 'Admin@123456', // Strong password
            'role' => 'admin',
            'status' => 'active'
        ];

        // Check if admin already exists
        if ($this->adminExists($admin_data['email'])) {
            echo "✅ Admin user already exists with email: {$admin_data['email']}\n";
            return true;
        }

        // Hash the password securely
        $hashed_password = password_hash($admin_data['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $result = $stmt->execute([
                $admin_data['name'],
                $admin_data['email'],
                $admin_data['phone'],
                $hashed_password,
                $admin_data['role'],
                $admin_data['status']
            ]);

            if ($result) {
                $user_id = $this->conn->lastInsertId();
                echo "✅ Admin user created successfully!\n";
                echo "📧 Email: {$admin_data['email']}\n";
                echo "🔐 Password: {$admin_data['password']}\n";
                echo "🔗 Admin URL: http://localhost/apsdreamhomefinal/admin\n";
                echo "🆔 User ID: {$user_id}\n";

                // Create admin role if not exists
                $this->createAdminRole();

                return true;
            } else {
                echo "❌ Failed to create admin user\n";
                return false;
            }

        } catch (PDOException $e) {
            echo "❌ Database error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Check if admin user already exists
     */
    private function adminExists($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0) > 0;

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create admin role and permissions
     */
    private function createAdminRole() {
        try {
            // Check if roles table exists and has admin role
            $stmt = $this->conn->query("SHOW TABLES LIKE 'roles'");
            if ($stmt->rowCount() > 0) {
                // Insert admin role if not exists
                $stmt = $this->conn->prepare("
                    INSERT IGNORE INTO roles (name, display_name, description, permissions, created_at)
                    VALUES ('admin', 'Administrator', 'Full system access', ?, NOW())
                ");

                $permissions = json_encode([
                    'dashboard' => ['view', 'manage'],
                    'properties' => ['view', 'create', 'edit', 'delete'],
                    'users' => ['view', 'create', 'edit', 'delete'],
                    'leads' => ['view', 'manage'],
                    'bookings' => ['view', 'manage'],
                    'analytics' => ['view'],
                    'settings' => ['view', 'edit'],
                    'system' => ['view', 'manage']
                ]);

                $stmt->execute([$permissions]);
                echo "✅ Admin role and permissions configured\n";
            }

        } catch (PDOException $e) {
            echo "⚠️  Could not configure admin role: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Test admin login functionality
     */
    public function testAdminLogin() {
        echo "\n🔐 Testing Admin Login...\n";

        // Simulate login process
        $email = 'admin@apsdreamhome.com';
        $password = 'Admin@123456';

        try {
            $stmt = $this->conn->prepare("
                SELECT id, name, email, role, status, password
                FROM users
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                echo "✅ Admin login test successful!\n";
                echo "👤 User: {$user['name']}\n";
                echo "🎭 Role: {$user['role']}\n";
                return true;
            } else {
                echo "❌ Admin login test failed\n";
                return false;
            }

        } catch (PDOException $e) {
            echo "❌ Login test error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Main execution
echo "🚀 APS Dream Home - Admin User Creation Script\n";
echo "==============================================\n\n";

// Check database connection
try {
    $conn = new PDO("mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $adminCreator = new AdminUserCreator($conn);

    // Create admin user
    if ($adminCreator->createAdminUser()) {
        // Test admin login
        $adminCreator->testAdminLogin();

        echo "\n🎉 Admin setup completed successfully!\n";
        echo "\n📋 Next Steps:\n";
        echo "1. Go to: http://localhost/apsdreamhomefinal/login\n";
        echo "2. Login with admin credentials\n";
        echo "3. Access admin panel: http://localhost/apsdreamhomefinal/admin\n";
        echo "4. Change admin password after first login\n";

    } else {
        echo "\n❌ Admin setup failed\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure:\n";
    echo "1. XAMPP is running\n";
    echo "2. Database 'apsdreamhome' exists\n";
    echo "3. Database user has proper permissions\n";
}

echo "\n";
?>
