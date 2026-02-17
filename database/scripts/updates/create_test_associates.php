<?php
/**
 * Script to create test associates for MLM commission testing
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => ''
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
        ]
    );

    // Start transaction
    $pdo->beginTransaction();

    // Get the first commission plan
    $plan = $pdo->query("SELECT id FROM mlm_commission_plans LIMIT 1")->fetch();
    
    if (!$plan) {
        die("No commission plan found. Please run update_mlm_tables.php first.\n");
    }
    
    $planId = $plan['id'];
    
    // Create test users if they don't exist
    $testUsers = [
        ['name' => 'Test Upline', 'email' => 'upline@test.com', 'phone' => '9000000001', 'password' => password_hash('password', PASSWORD_DEFAULT)],
        ['name' => 'Test Associate 1', 'email' => 'associate1@test.com', 'phone' => '9000000002', 'password' => password_hash('password', PASSWORD_DEFAULT)],
        ['name' => 'Test Associate 2', 'email' => 'associate2@test.com', 'phone' => '9000000003', 'password' => password_hash('password', PASSWORD_DEFAULT)],
    ];
    
    echo "Creating test users and associates...\n";
    
    $userIds = [];
    
    foreach ($testUsers as $userData) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$userData['email']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Create user
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'associate', 'active', NOW(), NOW())
            ");
            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $userData['phone'],
                $userData['password']
            ]);
            $userId = $pdo->lastInsertId();
            echo "✅ Created user: {$userData['name']} (ID: $userId)\n";
        } else {
            $userId = $user['id'];
            echo "ℹ️ User already exists: {$userData['name']} (ID: $userId)\n";
        }
        
        $userIds[] = $userId;
    }
    
    // Create associates with MLM structure
    // Upline (Level 1)
    $uplineId = $userIds[0];
    $associate1Id = $userIds[1];
    $associate2Id = $userIds[2];
    
    // Create or update upline
    $stmt = $pdo->prepare("
        INSERT INTO associates 
            (user_id, parent_id, commission_plan_id, current_level, 
             direct_business, team_business, total_business, status, created_at)
        VALUES (?, NULL, ?, 1, 100000, 0, 100000, 'active', NOW())
        ON DUPLICATE KEY UPDATE 
            commission_plan_id = VALUES(commission_plan_id),
            current_level = VALUES(current_level),
            direct_business = VALUES(direct_business),
            team_business = VALUES(team_business),
            total_business = VALUES(total_business)
    ");
    
    $stmt->execute([$uplineId, $planId]);
    $uplineAssociateId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM associates WHERE user_id = $uplineId")->fetchColumn();
    
    echo "\n✅ Created/Updated Upline: Test Upline (Associate ID: $uplineAssociateId)\n";
    
    // Create or update associate 1 (sponsored by upline)
    $stmt = $pdo->prepare("
        INSERT INTO associates 
            (user_id, parent_id, commission_plan_id, current_level, 
             direct_business, team_business, total_business, status, created_at)
        VALUES (?, ?, ?, 1, 50000, 0, 50000, 'active', NOW())
        ON DUPLICATE KEY UPDATE 
            parent_id = VALUES(parent_id),
            commission_plan_id = VALUES(commission_plan_id),
            current_level = VALUES(current_level),
            direct_business = VALUES(direct_business),
            team_business = VALUES(team_business),
            total_business = VALUES(total_business)
    ");
    
    $stmt->execute([$associate1Id, $uplineAssociateId, $planId]);
    $associate1AssociateId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM associates WHERE user_id = $associate1Id")->fetchColumn();
    
    echo "✅ Created/Updated Associate 1: Test Associate 1 (ID: $associate1AssociateId, Sponsor: $uplineAssociateId)\n";
    
    // Create or update associate 2 (sponsored by associate 1)
    $stmt = $pdo->prepare("
        INSERT INTO associates 
            (user_id, parent_id, commission_plan_id, current_level, 
             direct_business, team_business, total_business, status, created_at)
        VALUES (?, ?, ?, 1, 25000, 0, 25000, 'active', NOW())
        ON DUPLICATE KEY UPDATE 
            parent_id = VALUES(parent_id),
            commission_plan_id = VALUES(commission_plan_id),
            current_level = VALUES(current_level),
            direct_business = VALUES(direct_business),
            team_business = VALUES(team_business),
            total_business = VALUES(total_business)
    ");
    
    $stmt->execute([$associate2Id, $associate1AssociateId, $planId]);
    $associate2AssociateId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM associates WHERE user_id = $associate2Id")->fetchColumn();
    
    echo "✅ Created/Updated Associate 2: Test Associate 2 (ID: $associate2AssociateId, Sponsor: $associate1AssociateId)\n";
    
    // Update team business for the hierarchy
    // First, let's make sure the UpdateTeamBusiness procedure exists with the correct structure
    try {
        $pdo->exec("DROP PROCEDURE IF EXISTS UpdateTeamBusiness");
        
        $pdo->exec("
        CREATE PROCEDURE UpdateTeamBusiness(
            IN p_associate_id INT,
            IN p_amount DECIMAL(15,2)
        )
        BEGIN
            DECLARE v_parent_id INT;
            
            -- Get immediate parent
            SELECT parent_id INTO v_parent_id
            FROM associates
            WHERE id = p_associate_id;
            
            -- Recursively update team business up the chain
            WHILE v_parent_id IS NOT NULL DO
                UPDATE associates 
                SET team_business = IFNULL(team_business, 0) + p_amount,
                    total_business = IFNULL(direct_business, 0) + IFNULL(team_business, 0) + p_amount
                WHERE id = v_parent_id;
                
                -- Move up to next upline
                SELECT parent_id INTO v_parent_id
                FROM associates
                WHERE id = v_parent_id;
            END WHILE;
        END");
        
        // Now call the procedure
        $pdo->exec("CALL UpdateTeamBusiness($associate2AssociateId, 0)");
    } catch (PDOException $e) {
        echo "⚠️ Could not create/update UpdateTeamBusiness procedure: " . $e->getMessage() . "\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n✅ Test MLM structure created successfully!\n";
    echo "\nMLM Test Structure:\n";
    echo "Test Upline (ID: $uplineAssociateId)\n";
    echo "└── Test Associate 1 (ID: $associate1AssociateId)\n";
    echo "    └── Test Associate 2 (ID: $associate2AssociateId)\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage() . "\n");
}

// Now run the test commissions script
include 'test_mlm_commissions.php';
?>
