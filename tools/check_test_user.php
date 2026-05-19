<?php
// Check test user data in database
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking test user data...\n\n";

    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email, phone, status, user_type, role, created_at FROM users WHERE email = :e LIMIT 1");
    $stmt->execute([':e' => 'testuser@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "✅ User found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Phone: " . $user['phone'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "User Type: " . ($user['user_type'] ?? 'NULL') . "\n";
        echo "Role: " . ($user['role'] ?? 'NULL') . "\n";
        echo "Created At: " . $user['created_at'] . "\n";
        
        // Check password verification
        echo "\n🔐 Testing password verification...\n";
        $testPassword = 'Test@123';
        $stmt = $db->prepare("SELECT password FROM users WHERE email = :e LIMIT 1");
        $stmt->execute([':e' => 'testuser@example.com']);
        $hash = $stmt->fetchColumn();
        
        if ($hash) {
            if (password_verify($testPassword, $hash)) {
                echo "✅ Password verification PASSED for 'Test@123'\n";
            } else {
                echo "❌ Password verification FAILED for 'Test@123'\n";
                echo "Hash in database: " . substr($hash, 0, 20) . "...\n";
                
                // Test with other common passwords
                $altPasswords = ['123456', 'password', 'admin123', 'Test123'];
                foreach ($altPasswords as $alt) {
                    if (password_verify($alt, $hash)) {
                        echo "✅ Password matches: '$alt'\n";
                        break;
                    }
                }
            }
        } else {
            echo "❌ Password field is NULL or empty\n";
        }
        
        // Check if password column exists
        echo "\n🔍 Checking database schema...\n";
        $cols = $db->query("SHOW COLUMNS FROM users LIKE 'password'");
        if ($cols->rowCount() > 0) {
            echo "✅ 'password' column exists in users table\n";
        } else {
            echo "❌ 'password' column NOT FOUND in users table\n";
            $cols = $db->query("SHOW COLUMNS FROM users");
            echo "Available columns:\n";
            while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
                echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        }
        
    } else {
        echo "❌ User NOT found in database\n";
        echo "Attempting to create test user...\n";
        
        $hash = password_hash('Test@123', PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, status, user_type, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute(['Test User', 'testuser@example.com', '+9199999999', $hash, 'active', 'customer', 'user']);
            echo "✅ Test user created successfully\n";
        } catch (Exception $e) {
            echo "❌ Failed to create user: " . $e->getMessage() . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>