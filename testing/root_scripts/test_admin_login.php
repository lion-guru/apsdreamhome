<?php
/**
 * Admin Login Test & Fix Script
 * Check if admin user exists and create/fix if needed
 */
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Database\Database;

echo "=== ADMIN LOGIN DIAGNOSTIC ===\n\n";

try {
    $db = Database::getInstance();
    
    // Check admin_users table
    echo "1. Checking admin_users table:\n";
    $admins = $db->fetchAll("SELECT id, username, email, role, password_hash FROM admin_users LIMIT 5");
    if (empty($admins)) {
        echo "   ❌ No admin users found!\n";
    } else {
        foreach ($admins as $admin) {
            echo "   ✅ ID: {$admin['id']}, User: {$admin['username']}, Email: {$admin['email']}, Role: {$admin['role']}\n";
        }
    }
    
    // Check users table for admin roles
    echo "\n2. Checking users table for admin roles:\n";
    $users = $db->fetchAll("SELECT id, name, email, role, password FROM users WHERE role IN ('admin', 'super_admin') LIMIT 5");
    if (empty($users)) {
        echo "   ❌ No admin users in users table!\n";
    } else {
        foreach ($users as $user) {
            $hasPass = !empty($user['password']) ? 'Yes' : 'No';
            echo "   ✅ ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}, HasPass: $hasPass\n";
        }
    }
    
    // Test password verification
    echo "\n3. Testing password 'admin123':\n";
    $testHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "   Hash for 'admin123': $testHash\n";
    echo "   Verification: " . (password_verify('admin123', $testHash) ? '✅ PASS' : '❌ FAIL') . "\n";
    
    // Check if specific user exists
    echo "\n4. Checking for admin@apsdreamhome.com:\n";
    $specific = $db->fetchOne("SELECT * FROM admin_users WHERE email = ?", ['admin@apsdreamhome.com']);
    if ($specific) {
        echo "   ✅ Found in admin_users!\n";
        $passOk = password_verify('admin123', $specific['password_hash'] ?? '');
        echo "   Password verify: " . ($passOk ? '✅ PASS' : '❌ FAIL - Wrong password') . "\n";
    } else {
        echo "   ❌ Not found in admin_users\n";
        
        $specific2 = $db->fetchOne("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'super_admin')", ['admin@apsdreamhome.com']);
        if ($specific2) {
            echo "   ✅ Found in users table!\n";
            $passOk = password_verify('admin123', $specific2['password'] ?? '');
            echo "   Password verify: " . ($passOk ? '✅ PASS' : '❌ FAIL - Wrong password') . "\n";
        } else {
            echo "   ❌ Not found in users table either\n";
        }
    }
    
    // Create admin user if missing
    echo "\n5. Creating admin user if missing...\n";
    
    // Check tables exist
    $tables = $db->fetchAll("SHOW TABLES LIKE 'admin_users'");
    if (empty($tables)) {
        echo "   Creating admin_users table...\n";
        $db->query("CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'super_admin') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "   ✅ Table created\n";
    }
    
    // Check if admin@apsdreamhome.com exists
    $exists = $db->fetchOne("SELECT id FROM admin_users WHERE email = ?", ['admin@apsdreamhome.com']);
    if (!$exists) {
        echo "   Creating admin user...\n";
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("INSERT INTO admin_users (username, email, password_hash, role, status) 
                   VALUES (?, ?, ?, ?, ?)", 
                   ['admin', 'admin@apsdreamhome.com', $hash, 'super_admin', 'active']);
        echo "   ✅ Admin user created: admin@apsdreamhome.com / admin123\n";
    } else {
        echo "   Admin user exists, resetting password...\n";
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("UPDATE admin_users SET password_hash = ? WHERE email = ?", [$hash, 'admin@apsdreamhome.com']);
        echo "   ✅ Password reset to: admin123\n";
    }
    
    echo "\n=== LOGIN TEST ===\n";
    echo "Email: admin@apsdreamhome.com\n";
    echo "Password: admin123\n";
    echo "Captcha: 7 + 9 = 16\n";
    echo "URL: /admin/login\n";
    echo "\n✅ Admin login should work now!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
