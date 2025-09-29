<?php
/**
 * Script to create test data for MLM commission testing
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhomefinal',
    'user' => 'root',
    'pass' => ''
];

// Helper function to execute SQL with error handling
function executeSql($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        echo "SQL Error: " . $e->getMessage() . "\n";
        echo "SQL: $sql\n";
        throw $e;
    }
}

try {
    // Connect to database without transaction initially
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

    echo "=== MLM Test Data Setup ===\n";

    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Get or create the default commission plan
    $planId = $pdo->query("SELECT id FROM mlm_commission_plans LIMIT 1")->fetchColumn();
    
    if (!$planId) {
        echo "No commission plan found. Please run setup_mlm_commissions.php first.\n";
        exit(1);
    }

    echo "Using commission plan ID: $planId\n";

    // Create test users and associates
    $testUsers = [
        [
            'email' => 'test1@example.com',
            'first_name' => 'Test',
            'last_name' => 'User 1',
            'phone' => '9876543210',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'parent_id' => null // This will be the top-level user
        ],
        [
            'email' => 'test2@example.com',
            'first_name' => 'Test',
            'last_name' => 'User 2',
            'phone' => '9876543211',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'parent_id' => 1 // Child of user 1
        ],
        [
            'email' => 'test3@example.com',
            'first_name' => 'Test',
            'last_name' => 'User 3',
            'phone' => '9876543212',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'parent_id' => 2 // Child of user 2
        ]
    ];

    $userIds = [];
    $associateIds = [];

    // Start transaction for test data creation
    $pdo->beginTransaction();
    
    try {
        // Create test users and associates
        foreach ($testUsers as $index => $userData) {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            $userId = $stmt->fetchColumn();
            
            if (!$userId) {
                // Create user
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        email, first_name, last_name, password, phone, 
                        status, type, email_verified_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        'active', 'associate', NOW(), NOW(), NOW()
                    )
                &#x200B;");
                $stmt->execute([
                    $userData['email'],
                    $userData['first_name'],
                    $userData['last_name'],
                    $userData['password'],
                    $userData['phone']
                ]);
                $userId = $pdo->lastInsertId();
                echo "Created user: {$userData['email']} (ID: $userId)\n";
            } else {
                echo "User already exists: {$userData['email']} (ID: $userId)\n";
            }
            
            $userIds[] = $userId;
            
            // Create associate record
            $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $associateId = $stmt->fetchColumn();
            
            if (!$associateId) {
                $stmt = $pdo->prepare("
                    INSERT INTO associates (
                        user_id, status, commission_plan_id, current_level,
                        total_business, direct_business, team_business,
                        created_at, updated_at, parent_id
                    ) VALUES (
                        ?, 'active', ?, 1, 0, 0, 0, NOW(), NOW(), ?
                    )
                &#x200B;");
                $parentId = $userData['parent_id'] ? ($associateIds[$userData['parent_id'] - 1] ?? null) : null;
                $stmt->execute([$userId, $planId, $parentId]);
                $associateId = $pdo->lastInsertId();
                echo "Created associate record for user ID: $userId (Associate ID: $associateId)\n";
            } else {
                echo "Associate record already exists for user ID: $userId (Associate ID: $associateId)\n";
                
                // Update existing associate with parent_id
                $parentId = $userData['parent_id'] ? ($associateIds[$userData['parent_id'] - 1] ?? null) : null;
                $stmt = $pdo->prepare("
                    UPDATE associates 
                    SET parent_id = ?, 
                        commission_plan_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                &#x200B;");
                $stmt->execute([$parentId, $planId, $associateId]);
                echo "Updated associate record for ID: $associateId with parent ID: " . ($parentId ?? 'NULL') . "\n";
            }
            
            $associateIds[] = $associateId;
        }
        
        // Create a test transaction for commission testing
        $stmt = $pdo->query("SELECT id FROM users WHERE email = 'test1@example.com'");
        $testUserId = $stmt->fetchColumn();
        
        if ($testUserId) {
            $stmt = $pdo->query("SELECT id FROM associates WHERE user_id = $testUserId");
            $testAssociateId = $stmt->fetchColumn();
            
            if ($testAssociateId) {
                // Create a test transaction
                $pdo->exec("
                    INSERT INTO transactions (
                        user_id, amount, type, status, description, created_at, updated_at
                    ) VALUES (
                        $testUserId, 100000, 'booking', 'completed', 'Test transaction', NOW(), NOW()
                    )
                &#x200B;");
                $transactionId = $pdo->lastInsertId();
                
                // Link transaction to associate
                $pdo->exec("
                    INSERT INTO transaction_associates (transaction_id, associate_id, created_at)
                    VALUES ($transactionId, $testAssociateId, NOW())
                &#x200B;");
                
                echo "Created test transaction ID: $transactionId for associate ID: $testAssociateId\n";
                
                // Calculate commission
                $pdo->exec("CALL CalculateMLMCommission($transactionId, 100000)");
                echo "Calculated commissions for test transaction\n";
            }
        }
        
        // Commit the transaction
        $pdo->commit();
        echo "✅ Test data created successfully!\n";
        
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        throw $e;
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
