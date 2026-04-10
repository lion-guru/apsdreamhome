<?php
// Create test associate user
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if associate already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['testassociate@example.com']);
    
    if ($stmt->fetchColumn()) {
        echo "Test associate already exists.\n";
    } else {
        // Generate associate_id and referral code
        $associateId = 'AST' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $referralCode = strtoupper(substr('Test Associate', 0, 3)) . date('ymd') . rand(100, 999);
        $password = password_hash('Test@123', PASSWORD_DEFAULT);

        // Insert associate
        $stmt = $db->prepare("INSERT INTO users (customer_id, name, email, phone, password, referral_code, user_type, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $associateId,
            'Test Associate',
            'testassociate@example.com',
            '9876543210',
            $password,
            $referralCode,
            'associate',
            'associate',
            'active'
        ]);

        $userId = $db->lastInsertId();

        // Create wallet entry
        $stmt = $db->prepare("INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, status, created_at) VALUES (?, 0, 0, 0, 'active', NOW())");
        $stmt->execute([$userId]);

        echo "Test associate created successfully!\n";
        echo "Email: testassociate@example.com\n";
        echo "Password: Test@123\n";
        echo "Associate ID: $associateId\n";
        echo "Referral Code: $referralCode\n";
    }

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
