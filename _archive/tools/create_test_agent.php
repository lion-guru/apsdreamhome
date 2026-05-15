<?php
// Create test agent user
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if agent already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['testagent@example.com']);
    
    if ($stmt->fetchColumn()) {
        echo "Test agent already exists.\n";
    } else {
        // Generate agent_id and referral code
        $agentId = 'AGT' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $referralCode = strtoupper(substr('Test Agent', 0, 3)) . date('ymd') . rand(100, 999);
        $password = password_hash('Test@123', PASSWORD_DEFAULT);

        // Insert agent
        $stmt = $db->prepare("INSERT INTO users (customer_id, name, email, phone, password, referral_code, user_type, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $agentId,
            'Test Agent',
            'testagent@example.com',
            '9876543211',
            $password,
            $referralCode,
            'agent',
            'agent',
            'active'
        ]);

        $userId = $db->lastInsertId();

        // Create wallet entry
        $stmt = $db->prepare("INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, status, created_at) VALUES (?, 0, 0, 0, 'active', NOW())");
        $stmt->execute([$userId]);

        echo "Test agent created successfully!\n";
        echo "Email: testagent@example.com\n";
        echo "Password: Test@123\n";
        echo "Agent ID: $agentId\n";
        echo "Referral Code: $referralCode\n";
    }

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
