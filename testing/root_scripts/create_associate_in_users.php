<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n\n";
    
    // First, delete any existing associate user with this email
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ? AND user_type = 'associate'");
    $stmt->execute(['associate@apsdreamhome.com']);
    echo "Cleaned up any existing associate user with this email.\n\n";
    
    // Create associate user in users table
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    $associate_id = 'ASC' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $referral_code = strtoupper(substr('Test Associate', 0, 3)) . date('ymd') . rand(100, 999);
    
    $sql = "INSERT INTO users (customer_id, name, email, phone, password, referral_code, user_type, role, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    $name = 'Test Associate';
    $email = 'associate@apsdreamhome.com';
    $phone = '9876543210';
    $user_type = 'associate';
    $role = 'associate';
    $status = 'active';
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    
    $stmt->execute([
        $associate_id, 
        $name, 
        $email, 
        $phone, 
        $hashedPassword, 
        $referral_code, 
        $user_type, 
        $role, 
        $status, 
        $created_at, 
        $updated_at
    ]);
    
    echo "Associate user created successfully in users table!\n\n";
    echo "Login credentials:\n";
    echo "Email: associate@apsdreamhome.com\n";
    echo "Password: password123\n";
    echo "Associate ID: $associate_id\n";
    echo "Referral Code: $referral_code\n\n";
    
    // Create wallet entry for associate
    $userId = $pdo->lastInsertId();
    
    $sql = "INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, total_transferred_to_emi, 
            referral_earnings, commission_earnings, bonus_earnings, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,
        0.00,
        0.00,
        0.00,
        0.00,
        0.00,
        0.00,
        0.00,
        'active',
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    echo "Wallet entry created for associate user.\n\n";
    
    // Verify the associate was created
    $stmt = $pdo->prepare("SELECT id, customer_id, name, email, user_type, role, status FROM users WHERE email = ? AND user_type = 'associate' LIMIT 1");
    $stmt->execute(['associate@apsdreamhome.com']);
    $associate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($associate) {
        echo "Associate user verified:\n";
        echo "ID: " . $associate['id'] . "\n";
        echo "Customer ID: " . $associate['customer_id'] . "\n";
        echo "Name: " . $associate['name'] . "\n";
        echo "Email: " . $associate['email'] . "\n";
        echo "User Type: " . $associate['user_type'] . "\n";
        echo "Role: " . $associate['role'] . "\n";
        echo "Status: " . $associate['status'] . "\n";
        
        // Test password verification
        $password = 'password123';
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'associate' LIMIT 1");
        $stmt->execute(['associate@apsdreamhome.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            echo "\nPassword verification: SUCCESS\n";
        } else {
            echo "\nPassword verification: FAILED\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
