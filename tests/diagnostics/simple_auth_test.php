<?php
/**
 * Simple Admin Authentication Test
 * Tests the admin login functionality directly with the database
 */

// Suppress session warnings for CLI
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 0);

echo "=== SIMPLE ADMIN AUTHENTICATION TEST ===\n\n";

// Include database configuration
try {
    require_once __DIR__ . '/includes/db_connection.php';
    echo "✅ Database connection loaded\n";
    
    // Test credentials
    $test_credentials = [
        ['username' => 'admin', 'password' => 'demo123'],
        ['username' => 'testadmin', 'password' => 'admin123'],
        ['username' => 'admin@apsdreamhome.com', 'password' => 'admin123']
    ];
    
    foreach ($test_credentials as $cred) {
        echo "\n--- Testing: {$cred['username']} / {$cred['password']} ---\n";
        
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT id, auser, apass, role, status FROM admin WHERE auser = ? AND status = 'active'");
        $stmt->execute([$cred['username']]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✅ Admin user found: {$admin['auser']}\n";
            echo "✅ Role: {$admin['role']}\n";
            echo "✅ Status: {$admin['status']}\n";
            
            // Test password verification
            if (password_verify($cred['password'], $admin['apass'])) {
                echo "✅ Password verification: SUCCESS\n";
                echo "🎉 LOGIN WOULD BE SUCCESSFUL!\n";
                break;
            } else {
                echo "❌ Password verification: FAILED\n";
                echo "⚠️  Password does not match\n";
            }
        } else {
            echo "❌ Admin user not found: {$cred['username']}\n";
        }
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>