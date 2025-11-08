<?php
/**
 * Script to set up test users with MLM hierarchy
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => ''
];

// Test user data
$testUsers = [
    [
        'email' => 'associate1@test.com',
        'name' => 'Test Associate 1',
        'password' => 'password123',
        'phone' => '9876543210',
        'parent_id' => null, // Top level
        'business_volume' => 100000
    ],
    [
        'email' => 'associate2@test.com',
        'name' => 'Test Associate 2',
        'password' => 'password123',
        'phone' => '9876543211',
        'parent_id' => 1, // Child of associate 1
        'business_volume' => 50000
    ],
    [
        'email' => 'associate3@test.com',
        'name' => 'Test Associate 3',
        'password' => 'password123',
        'phone' => '9876543212',
        'parent_id' => 1, // Child of associate 1
        'business_volume' => 30000
    ],
    [
        'email' => 'associate4@test.com',
        'name' => 'Test Associate 4',
        'password' => 'password123',
        'phone' => '9876543213',
        'parent_id' => 2, // Child of associate 2
        'business_volume' => 20000
    ]
];

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "=== Setting Up MLM Test Users ===\n\n";

    // Get the default commission plan
    $plan = $pdo->query("SELECT id FROM mlm_commission_plans WHERE is_active = 1 LIMIT 1")->fetch();
    if (!$plan) {
        die("❌ No active commission plan found. Please run setup_mlm_commissions.php first.\n");
    }
    $planId = $plan['id'];
    echo "Using commission plan ID: $planId\n\n";

    // Start transaction
    $pdo->beginTransaction();

    // Create or update test users
    $userIds = [];
    foreach ($testUsers as $index => $userData) {
        echo "Processing user: {$userData['name']} ({$userData['email']})\n";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$userData['email']]);
        $userId = $stmt->fetchColumn();
        
        if (!$userId) {
            // Create user
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (email, name, password, phone, status, type, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'active', 'associate', NOW(), NOW())
            &#x200B;");
            $stmt->execute([
                $userData['email'],
                $userData['name'],
                $hashedPassword,
                $userData['phone']
            ]);
            $userId = $pdo->lastInsertId();
            echo "  ✅ Created user (ID: $userId)\n";
        } else {
            echo "  ℹ️ User already exists (ID: $userId)\n";
        }
        
        $userIds[$index + 1] = $userId; // Store with 1-based index to match parent_id
        
        // Create or update associate record
        $stmt = $pdo->prepare("\n            INSERT INTO associates \n                (user_id, status, commission_plan_id, current_level, \n                 total_business, direct_business, team_business, parent_id,\n                 created_at, updated_at)\n            VALUES (?, 'active', ?, 1, ?, ?, 0, ?, NOW(), NOW())\n            ON DUPLICATE KEY UPDATE\n                status = 'active',\n                commission_plan_id = ?,\n                parent_id = ?,\n                updated_at = NOW()");
        
        $parentId = null;
        if ($userData['parent_id'] !== null && isset($userIds[$userData['parent_id']])) {
            $parentId = $userIds[$userData['parent_id']];
        }
        $directBusiness = $userData['business_volume'];
        
        $params = [
            $userId, // user_id
            $planId, // commission_plan_id
            $directBusiness, // total_business
            $directBusiness, // direct_business
            $parentId, // parent_id
            $planId, // on duplicate: commission_plan_id
            $parentId  // on duplicate: parent_id
        ];
        
        // Convert null to NULL for SQL
        foreach ($params as &$param) {
            if ($param === null) {
                $param = 'NULL';
            } else if (is_numeric($param)) {
                $param = (string)$param;
            }
        }
        
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            $associateId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM associates WHERE user_id = $userId")->fetchColumn();
            echo "  ✅ Updated associate record (ID: $associateId)\n";
            
            // Update team business for upline
            if ($parentId) {
                $pdo->exec("CALL UpdateTeamBusiness($associateId, $directBusiness)");
                echo "  ✅ Updated team business for upline\n";
            }
        }
        
        echo "\n";
    }
    
    // Create test transactions
    echo "Creating test transactions...\n";
    foreach ($testUsers as $index => $userData) {
        $userId = $userIds[$index + 1];
        $amount = $userData['business_volume'];
        
        // Create transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
                (user_id, amount, type, status, description, created_at, updated_at)
            VALUES (?, ?, 'booking', 'completed', 'Test transaction', NOW(), NOW())
        &#x200B;");
        $stmt->execute([$userId, $amount]);
        $transactionId = $pdo->lastInsertId();
        
        // Link to associate
        $associateId = $pdo->query("SELECT id FROM associates WHERE user_id = $userId")->fetchColumn();
        $pdo->prepare("
            INSERT INTO transaction_associates (transaction_id, associate_id, created_at)
            VALUES (?, ?, NOW())
        &#x200B;")->execute([$transactionId, $associateId]);
        
        // Calculate commission
        $pdo->exec("CALL CalculateMLMCommission($transactionId, $amount)");
        
        echo "  ✅ Created transaction #$transactionId for user $userId (Amount: ₹$amount)\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Test users and transactions created successfully!\n";
    echo "You can now test the MLM commission calculations.\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
