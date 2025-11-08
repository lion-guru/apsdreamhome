<?php
/**
 * Test Script for MLM Commission Structure
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

    // Test 1: Verify commission plan exists
    echo "\n=== Testing Commission Plan ===\n";
    $stmt = $pdo->query("SELECT * FROM mlm_commission_plans");
    $plan = $stmt->fetch();
    
    if ($plan) {
        echo "✅ Commission Plan Found: {$plan['name']} (ID: {$plan['id']})\n";
        
        // Test 2: Verify commission levels
        $stmt = $pdo->query("SELECT * FROM mlm_commission_levels WHERE plan_id = {$plan['id']} ORDER BY level");
        $levels = $stmt->fetchAll();
        
        echo "\n=== Commission Levels ===\n";
        echo str_pad("Level", 8) . str_pad("Min", 15) . str_pad("Max", 15) . "Commission%\n";
        echo str_repeat("-", 45) . "\n";
        
        foreach ($levels as $level) {
            $min = $level['min_business'] ?? '0';
            $max = $level['max_business'] ?? '∞';
            echo str_pad($level['level'], 8) . 
                 str_pad(number_format($min), 15) . 
                 str_pad($max == '∞' ? $max : number_format($max), 15) . 
                 $level['direct_percentage'] . "%\n";
        }
    } else {
        echo "❌ No commission plan found!\n";
    }
    
    // Test 3: Check if associates have been assigned to the plan
    echo "\n=== Associates Assignment ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN commission_plan_id IS NOT NULL THEN 1 ELSE 0 END) as assigned FROM associates");
    $result = $stmt->fetch();
    
    echo "Total Associates: {$result['total']}\n";
    echo "Assigned to Plan: {$result['assigned']} (" . round(($result['assigned'] / max(1, $result['total'])) * 100) . "%)\n";
    
    if ($result['assigned'] > 0) {
        // Test 4: Show sample associate with their plan
        $stmt = $pdo->query("
            SELECT a.id, u.name, a.commission_plan_id, a.current_level, 
                   a.direct_business, a.team_business, a.total_business
            FROM associates a
            JOIN users u ON a.user_id = u.id
            WHERE a.commission_plan_id IS NOT NULL
            LIMIT 3
        ");
        
        echo "\n=== Sample Associates ===\n";
        while ($associate = $stmt->fetch()) {
            echo "\nAssociate: {$associate['name']} (ID: {$associate['id']})\n";
            echo "Level: {$associate['current_level']}\n";
            echo "Direct Business: ₹" . number_format($associate['direct_business'], 2) . "\n";
            echo "Team Business: ₹" . number_format($associate['team_business'], 2) . "\n";
            echo "Total Business: ₹" . number_format($associate['total_business'], 2) . "\n";
        }
    }
    
    // Test 5: Test commission calculation with a sample transaction
    echo "\n=== Testing Commission Calculation ===\n";
    
    // Get a random associate
    $stmt = $pdo->query("
        SELECT a.id as associate_id, a.user_id, a.commission_plan_id, a.current_level
        FROM associates a
        WHERE a.commission_plan_id IS NOT NULL
        LIMIT 1
    ");
    $associate = $stmt->fetch();
    
    if ($associate) {
        $testAmount = 75000; // ₹75,000 test transaction
        
        echo "Testing with Associate ID: {$associate['associate_id']}\n";
        echo "Current Level: {$associate['current_level']}\n";
        echo "Test Transaction Amount: ₹" . number_format($testAmount) . "\n\n";
        
        // Get expected commission percentage
        $stmt = $pdo->prepare("
            SELECT direct_percentage 
            FROM mlm_commission_levels 
            WHERE plan_id = ? AND level = ?
            AND (? BETWEEN min_business AND COALESCE(max_business, 999999999))
        ");
        $stmt->execute([$associate['commission_plan_id'], $associate['current_level'], $testAmount]);
        $level = $stmt->fetch();
        
        if ($level) {
            $expectedCommission = ($testAmount * $level['direct_percentage']) / 100;
            echo "Expected Commission ({$level['direct_percentage']}% of ₹" . number_format($testAmount) . "): ₹" . number_format($expectedCommission, 2) . "\n";
            
            // Now let's test the actual calculation
            try {
                // Create a test transaction
                $pdo->beginTransaction();
                
                // Insert test transaction
                $pdo->exec("INSERT INTO transactions (associate_id, amount, type, status, created_at) 
                           VALUES ({$associate['associate_id']}, $testAmount, 'sale', 'completed', NOW())");
                
                $transactionId = $pdo->lastInsertId();
                
                // Calculate commission
                $pdo->exec("CALL CalculateMLMCommission($transactionId, $testAmount)");
                
                // Get the calculated commission
                $stmt = $pdo->query("
                    SELECT * FROM mlm_commissions 
                    WHERE transaction_id = $transactionId
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $commission = $stmt->fetch();
                
                if ($commission) {
                    echo "✅ Commission Calculated: ₹" . number_format($commission['commission_amount'], 2) . "\n";
                    echo "   Type: " . ucfirst(str_replace('_', ' ', $commission['commission_type'])) . "\n";
                    echo "   Status: " . ucfirst($commission['status']) . "\n";
                    
                    // Check if difference commission was calculated for upline
                    if ($commission['is_direct']) {
                        $stmt = $pdo->query("
                            SELECT * FROM mlm_commissions 
                            WHERE transaction_id = $transactionId
                            AND is_direct = 0
                            LIMIT 1
                        ");
                        $diffCommission = $stmt->fetch();
                        
                        if ($diffCommission) {
                            echo "\n🔹 Upline Difference Commission: ₹" . number_format($diffCommission['commission_amount'], 2) . "\n";
                            echo "   Upline Commission: {$diffCommission['direct_percentage']}%\n";
                            echo "   Associate Commission: {$commission['direct_percentage']}%\n";
                            echo "   Difference: {$diffCommission['difference_percentage']}%\n";
                        }
                    }
                } else {
                    echo "❌ No commission was calculated!\n";
                }
                
                // Rollback the test transaction
                $pdo->rollBack();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "❌ Error testing commission calculation: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "❌ Could not determine commission rate for level {$associate['current_level']}\n";
        }
    } else {
        echo "❌ No associates found with a commission plan!\n";
    }
    
    echo "\n✅ MLM Commission System Tests Completed!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
