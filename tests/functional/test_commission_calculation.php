<?php
/**
 * Script to test MLM commission calculations
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

    echo "=== MLM Commission Calculation Test ===\n\n";

    // Get test associate (level 2)
    $stmt = $pdo->query("
        SELECT a.*, u.name, u.email 
        FROM associates a
        JOIN users u ON a.user_id = u.id
        WHERE u.email LIKE 'associate1@test.com'
        LIMIT 1
    ");
    $associate = $stmt->fetch();

    if (!$associate) {
        die("❌ Test associate not found. Please run create_test_associates.php first.\n");
    }

    echo "Testing with Associate:\n";
    echo "- Name: {$associate['name']}\n";
    echo "- ID: {$associate['id']}\n";
    echo "- Current Level: {$associate['current_level']}\n";
    echo "- Direct Business: ₹" . number_format($associate['direct_business'], 2) . "\n";
    echo "- Team Business: ₹" . number_format($associate['team_business'], 2) . "\n";
    echo "- Total Business: ₹" . number_format($associate['total_business'], 2) . "\n\n";

    // Get upline
    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email 
        FROM associates a
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$associate['parent_id']]);
    $upline = $stmt->fetch();

    if ($upline) {
        echo "Upline Details:\n";
        echo "- Name: {$upline['name']}\n";
        echo "- ID: {$upline['id']}\n";
        echo "- Current Level: {$upline['current_level']}\n";
        echo "- Direct Business: ₹" . number_format($upline['direct_business'], 2) . "\n";
        echo "- Team Business: ₹" . number_format($upline['team_business'], 2) . "\n";
        echo "- Total Business: ₹" . number_format($upline['total_business'], 2) . "\n\n";
    }

    // Get downline
    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email 
        FROM associates a
        JOIN users u ON a.user_id = u.id
        WHERE a.parent_id = ?
    ");
    $stmt->execute([$associate['id']]);
    $downline = $stmt->fetch();

    if ($downline) {
        echo "Downline Details:\n";
        echo "- Name: {$downline['name']}\n";
        echo "- ID: {$downline['id']}\n";
        echo "- Current Level: {$downline['current_level']}\n";
        echo "- Direct Business: ₹" . number_format($downline['direct_business'], 2) . "\n";
        echo "- Team Business: ₹" . number_format($downline['team_business'], 2) . "\n";
        echo "- Total Business: ₹" . number_format($downline['total_business'], 2) . "\n\n";
    }

    // Test commission calculation
    $testAmount = 100000; // ₹100,000 test transaction
    echo "=== Testing Commission Calculation ===\n";
    echo "Transaction Amount: ₹" . number_format($testAmount) . "\n";

    // Get expected commission percentage for the associate's level
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
        echo "Expected Commission for Level {$associate['current_level']} ({$level['direct_percentage']}%): ₹" . number_format($expectedCommission, 2) . "\n";
    }

    // Get expected commission percentage for upline (difference)
    if ($upline) {
        $stmt->execute([$upline['commission_plan_id'], $upline['current_level'], $testAmount]);
        $uplineLevel = $stmt->fetch();
        
        if ($uplineLevel && $uplineLevel['direct_percentage'] > $level['direct_percentage']) {
            $difference = $uplineLevel['direct_percentage'] - $level['direct_percentage'];
            $expectedUplineCommission = ($testAmount * $difference) / 100;
            echo "Expected Upline Difference Commission: {$difference}% = ₹" . number_format($expectedUplineCommission, 2) . "\n";
        }
    }

    // Now let's test the actual commission calculation
    echo "\n=== Running Actual Commission Calculation ===\n";
    
    // Ensure we're not in a transaction
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    
    // Start a new transaction
    $pdo->beginTransaction();
    
    try {
        // Insert test transaction
        $stmt = $pdo->prepare("\n            INSERT INTO transactions \n                (user_id, amount, type, description, date, created_at)\n            VALUES (?, ?, 'sale', 'Test commission calculation', CURDATE(), NOW())\n        ");
        $stmt->execute([$associate['user_id'], $testAmount]);
        $transactionId = $pdo->lastInsertId();
        
        if (!$transactionId) {
            throw new Exception("Failed to create test transaction");
        }
        
        echo "✅ Created test transaction #$transactionId for ₹" . number_format($testAmount) . "\n";
        
        // Create transaction_associates mapping if it doesn't exist
        echo "🔧 Setting up transaction_associates table...\n";
        $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_associates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_id INT NOT NULL,
                associate_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
                FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
            )");
            
            // Map the transaction to the associate
            $pdo->exec("INSERT INTO transaction_associates (transaction_id, associate_id) 
                        VALUES ($transactionId, {$associate['id']})");
            
            // Update the CalculateMLMCommission procedure to use transaction_associates
            $pdo->exec("DROP PROCEDURE IF EXISTS CalculateMLMCommission");
            
            $pdo->exec("
            CREATE PROCEDURE CalculateMLMCommission(
                IN p_transaction_id INT,
                IN p_amount DECIMAL(15,2)
            )
            BEGIN
                DECLARE v_associate_id INT;
                DECLARE v_plan_id INT;
                DECLARE v_level INT;
                DECLARE v_direct_percent DECIMAL(5,2);
                DECLARE v_parent_id INT;
                DECLARE v_parent_level INT;
                DECLARE v_parent_percent DECIMAL(5,2);
                DECLARE v_difference_percent DECIMAL(5,2);
                
                -- Get associate details from transaction_associates
                SELECT ta.associate_id, a.commission_plan_id, a.current_level, a.parent_id
                INTO v_associate_id, v_plan_id, v_level, v_parent_id
                FROM transaction_associates ta
                JOIN associates a ON ta.associate_id = a.id
                WHERE ta.transaction_id = p_transaction_id
                LIMIT 1;
                
                -- Get direct commission percentage based on business volume
                SELECT direct_percentage INTO v_direct_percent
                FROM mlm_commission_levels
                WHERE plan_id = v_plan_id 
                AND level = v_level
                AND (min_business IS NULL OR p_amount >= min_business)
                AND (max_business IS NULL OR p_amount <= max_business)
                ORDER BY level DESC
                LIMIT 1;
                
                -- If no specific level found, get default for the level
                IF v_direct_percent IS NULL THEN
                    SELECT direct_percentage INTO v_direct_percent
                    FROM mlm_commission_levels
                    WHERE plan_id = v_plan_id AND level = v_level
                    ORDER BY min_business
                    LIMIT 1;
                END IF;
                
                -- Insert direct commission
                INSERT INTO mlm_commissions (
                    user_id, transaction_id, commission_amount, 
                    commission_type, status, level, 
                    direct_percentage, is_direct, created_at,
                    commission_plan_id
                ) VALUES (
                    (SELECT user_id FROM associates WHERE id = v_associate_id),
                    p_transaction_id, 
                    (p_amount * IFNULL(v_direct_percent, 0) / 100),
                    'direct_commission', 'pending', v_level,
                    IFNULL(v_direct_percent, 0), 1, NOW(),
                    v_plan_id
                );
                
                -- Calculate difference for upline
                IF v_parent_id IS NOT NULL THEN
                    -- Get parent's level and percentage
                    SELECT a.current_level, cl.direct_percentage
                    INTO v_parent_level, v_parent_percent
                    FROM associates a
                    LEFT JOIN mlm_commission_levels cl ON cl.plan_id = a.commission_plan_id 
                        AND cl.level = a.current_level
                    WHERE a.id = v_parent_id;
                    
                    -- If parent has higher level, calculate difference
                    IF v_parent_level > v_level AND v_parent_percent > IFNULL(v_direct_percent, 0) THEN
                        SET v_difference_percent = v_parent_percent - IFNULL(v_direct_percent, 0);
                        
                        -- Insert difference commission for upline
                        INSERT INTO mlm_commissions (
                            user_id, transaction_id, commission_amount, 
                            commission_type, status, level, 
                            direct_percentage, difference_percentage,
                            upline_id, is_direct, created_at,
                            commission_plan_id
                        ) VALUES (
                            (SELECT user_id FROM associates WHERE id = v_parent_id),
                            p_transaction_id, 
                            (p_amount * v_difference_percent / 100),
                            'difference_commission', 'pending', v_parent_level,
                            v_parent_percent, v_difference_percent,
                            v_associate_id, 0, NOW(),
                            v_plan_id
                        );
                    END IF;
                END IF;
                
                -- Update associate's business volume
                UPDATE associates 
                SET direct_business = direct_business + p_amount,
                    total_business = direct_business + p_amount + IFNULL(team_business, 0)
                WHERE id = v_associate_id;
                
                -- Update team business for upline chain
                CALL UpdateTeamBusiness(v_associate_id, p_amount);
            END");
            
        // Now call the procedure
        echo "\n🔍 Running commission calculation...\n";
        
        // First, ensure the procedure exists
        $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Name = 'CalculateMLMCommission'");
        if ($stmt->rowCount() === 0) {
            throw new Exception("CalculateMLMCommission procedure not found in database");
        }
        
        // Explicitly map the transaction to the associate
        $stmt = $pdo->prepare("INSERT INTO transaction_associates (transaction_id, associate_id) VALUES (?, ?)");
        $stmt->execute([$transactionId, $associate['id']]);
        
        echo "✅ Mapped transaction #$transactionId to associate #{$associate['id']}\n";
        
        // Call the procedure with proper error handling
        try {
            $pdo->exec("CALL CalculateMLMCommission($transactionId, $testAmount)");
            echo "✅ Commission calculation completed\n";
        } catch (PDOException $e) {
            throw new Exception("Error executing CalculateMLMCommission: " . $e->getMessage());
        }
        
        // Get the calculated commission
        $stmt = $pdo->prepare("\n            SELECT mc.*, u.name as associate_name\n            FROM mlm_commissions mc\n            JOIN users u ON mc.user_id = u.id\n            WHERE mc.transaction_id = ?\n            ORDER BY mc.is_direct DESC, mc.id\n        ");
        $stmt->execute([$transactionId]);
        $commissions = $stmt->fetchAll();
        
        // Get transaction details for verification
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch();
        
        echo "\n=== Transaction Details ===\n";
        echo "- ID: {$transaction['id']}\n";
        echo "- Amount: ₹" . number_format($transaction['amount'], 2) . "\n";
        echo "- Type: {$transaction['type']}\n";
        echo "- Date: {$transaction['date']}\n";
        
        if (count($commissions) > 0) {
            echo "\n=== Commission Results ===\n";
            
            foreach ($commissions as $commission) {
                $type = $commission['is_direct'] ? 'Direct' : 'Upline Difference';
                echo "\n$type Commission for {$commission['associate_name']}:\n";
                echo "- Amount: ₹" . number_format($commission['commission_amount'], 2) . "\n";
                echo "- Percentage: {$commission['direct_percentage']}%\n";
                
                if (!$commission['is_direct']) {
                    echo "- Difference: {$commission['difference_percentage']}%\n";
                }
                
                echo "- Status: {$commission['status']}\n";
                echo "- Created: {$commission['created_at']}\n";
            }
        } else {
            echo "\n❌ No commissions were generated!\n";
        }
        
        // Verify business volume updates
        $stmt = $pdo->prepare("SELECT * FROM associates WHERE id = ?");
        $stmt->execute([$associate['id']]);
        $updatedAssociate = $stmt->fetch();
        
        echo "\n=== Business Volume Update ===\n";
        echo "- Direct Business: ₹" . number_format($updatedAssociate['direct_business'], 2) . "\n";
        echo "- Team Business: ₹" . number_format($updatedAssociate['team_business'], 2) . "\n";
        echo "- Total Business: ₹" . number_format($updatedAssociate['total_business'], 2) . "\n";
        
        // Commit the transaction to apply changes
        $pdo->commit();
        echo "\n✅ Test transaction committed successfully!\n";
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            try {
                $pdo->rollBack();
                echo "\n❌ Transaction rolled back due to error\n";
            } catch (Exception $rollbackEx) {
                echo "\n❌ Error during rollback: " . $rollbackEx->getMessage() . "\n";
            }
        }
        throw new Exception("Commission calculation failed: " . $e->getMessage());
    } finally {
        // Ensure we're not leaving any open transactions
        while ($pdo->inTransaction()) {
            try {
                $pdo->commit();
            } catch (Exception $e) {
                // If commit fails, try rollback
                try {
                    $pdo->rollBack();
                } catch (Exception $e) {
                    // If rollback also fails, we can't do much more
                    break;
                }
            }
        }
    }
    
    echo "\n=== Test Completed Successfully ===\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
