<?php
/**
 * Simplified script to create test users for MLM testing
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhomefinal',
    'user' => 'root',
    'pass' => ''
];

// Test users with hierarchy
$testUsers = [
    [
        'email' => 'associate1@test.com',
        'name' => 'Test Associate 1',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '9876543210',
        'parent_id' => null, // Top level
        'business_volume' => 100000
    ],
    [
        'email' => 'associate2@test.com',
        'name' => 'Test Associate 2',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '9876543211',
        'parent_id' => 1, // Child of associate 1
        'business_volume' => 50000
    ],
    [
        'email' => 'associate3@test.com',
        'name' => 'Test Associate 3',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'phone' => '9876543212',
        'parent_id' => 1, // Child of associate 1
        'business_volume' => 30000
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

    echo "=== Creating Test Users ===\n\n";

    // Get the default commission plan
    $plan = $pdo->query("SELECT id FROM mlm_commission_plans WHERE is_active = 1 LIMIT 1")->fetch();
    if (!$plan) {
        die("❌ No active commission plan found. Please run setup_mlm_commissions.php first.\n");
    }
    $planId = $plan['id'];
    echo "Using commission plan ID: $planId\n\n";

    // Start transaction
    $pdo->beginTransaction();

    $userIds = [];
    
    // First pass: Create users
    foreach ($testUsers as $index => $userData) {
        $email = $userData['email'];
        echo "Processing user: {$userData['name']} ($email)\n";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();
        
        if (!$userId) {
            // Create user
            $stmt = $pdo->prepare("
                INSERT INTO users (email, name, password, phone, status, type, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'active', 'associate', NOW(), NOW())
            &#x200B;");
            $stmt->execute([
                $email,
                $userData['name'],
                $userData['password'],
                $userData['phone']
            ]);
            $userId = $pdo->lastInsertId();
            echo "  ✅ Created user (ID: $userId)\n";
        } else {
            echo "  ℹ️ User already exists (ID: $userId)\n";
        }
        
        $userIds[$index + 1] = $userId;
    }
    
    // Second pass: Create associate records with proper parent-child relationships
    foreach ($testUsers as $index => $userData) {
        $userId = $userIds[$index + 1];
        $parentId = $userData['parent_id'] ? $userIds[$userData['parent_id']] : null;
        $directBusiness = $userData['business_volume'];
        
        // Check if associate record exists
        $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ?");
        $stmt->execute([$userId]);
        $associateId = $stmt->fetchColumn();
        
        if ($associateId) {
            // Update existing associate
            $stmt = $pdo->prepare("
                UPDATE associates 
                SET parent_id = ?, 
                    commission_plan_id = ?,
                    direct_business = ?,
                    total_business = ?,
                    updated_at = NOW()
                WHERE id = ?
            &#x200B;");
            $stmt->execute([$parentId, $planId, $directBusiness, $directBusiness, $associateId]);
            echo "  ✅ Updated associate record (ID: $associateId)\n";
        } else {
            // Create new associate
            $stmt = $pdo->prepare("
                INSERT INTO associates 
                (user_id, status, commission_plan_id, current_level, 
                 total_business, direct_business, team_business, parent_id,
                 created_at, updated_at)
                VALUES (?, 'active', ?, 1, ?, ?, 0, ?, NOW(), NOW())
            &#x200B;");
            $stmt->execute([
                $userId,
                $planId,
                $directBusiness,
                $directBusiness,
                $parentId
            ]);
            $associateId = $pdo->lastInsertId();
            echo "  ✅ Created associate record (ID: $associateId)\n";
        }
        
        // Update team business for upline
        if ($parentId) {
            $pdo->exec("CALL UpdateTeamBusiness($associateId, $directBusiness)");
            echo "  ✅ Updated team business for upline\n";
        }
        
        // Create test transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (user_id, amount, type, status, description, created_at, updated_at)
            VALUES (?, ?, 'booking', 'completed', 'Test transaction', NOW(), NOW())
        &#x200B;");
        $stmt->execute([$userId, $directBusiness]);
        $transactionId = $pdo->lastInsertId();
        
        // Link transaction to associate
        $pdo->prepare("
            INSERT INTO transaction_associates (transaction_id, associate_id, created_at)
            VALUES (?, ?, NOW())
        &#x200B;")->execute([$transactionId, $associateId]);
        
        // Calculate commission
        $pdo->exec("CALL CalculateMLMCommission($transactionId, $directBusiness)");
        
        echo "  ✅ Created transaction #$transactionId (Amount: ₹$directBusiness)\n\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Test users and transactions created successfully!\n";
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    die("❌ Error: " . $e->getMessage() . "\n");
}
