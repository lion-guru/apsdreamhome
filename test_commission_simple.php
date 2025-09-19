<?php
/**
 * Simple test script for MLM commission calculation
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhomefinal',
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

    echo "=== MLM Commission Simple Test ===\n\n";

    // Get associate details with proper error handling
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name, u.email, u.phone 
            FROM associates a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = 1
        ");
        $stmt->execute();
        $associate = $stmt->fetch();
        
        if (!$associate) {
            throw new Exception("Test associate not found. Please run setup_mlm_commissions.php first.");
        }
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "TESTING COMMISSION CALCULATION\n";
        echo str_repeat("=", 70) . "\n\n";
        
        echo str_pad("ASSOCIATE DETAILS", 30, " ", STR_PAD_RIGHT) . "\n";
        echo str_repeat("-", 70) . "\n";
        echo str_pad("Name:", 20) . $associate['name'] . "\n";
        echo str_pad("Associate ID:", 20) . $associate['id'] . "\n";
        echo str_pad("User ID:", 20) . $associate['user_id'] . "\n";
        echo str_pad("Current Level:", 20) . $associate['current_level'] . "\n";
        echo str_pad("Total Business:", 20) . "₹" . number_format($associate['total_business'], 2) . "\n";
        echo str_pad("Join Date:", 20) . $associate['join_date'] . "\n";
        
        // Get commission level details
        $levelStmt = $pdo->prepare("
            SELECT * FROM mlm_commission_levels 
            WHERE level = ? AND plan_id = 1
        ");
        $levelStmt->execute([$associate['current_level']]);
        $levelInfo = $levelStmt->fetch();
        
        if ($levelInfo) {
            echo "\n" . str_pad("CURRENT COMMISSION LEVEL", 30, " ", STR_PAD_RIGHT) . "\n";
            echo str_repeat("-", 70) . "\n";
            echo str_pad("Level:", 20) . $levelInfo['level'] . "\n";
            echo str_pad("Direct %:", 20) . $levelInfo['direct_percentage'] . "%\n";
            echo str_pad("Business Range:", 20) . "₹" . number_format($levelInfo['min_business'], 2);
            echo " to " . ($levelInfo['max_business'] ? "₹" . number_format($levelInfo['max_business'], 2) : "∞") . "\n\n";
        }
        
        // Get upline (sponsor) details
        if ($associate['parent_id']) {
            $uplineStmt = $pdo->prepare("
                SELECT a.*, u.name, u.email, u.phone 
                FROM associates a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = ?
            ");
            $uplineStmt->execute([$associate['parent_id']]);
            $upline = $uplineStmt->fetch();
            
            if ($upline) {
                echo str_pad("UPLINE DETAILS", 30, " ", STR_PAD_RIGHT) . "\n";
                echo str_repeat("-", 70) . "\n";
                echo str_pad("Name:", 20) . $upline['name'] . "\n";
                echo str_pad("Associate ID:", 20) . $upline['id'] . "\n";
                echo str_pad("Current Level:", 20) . $upline['current_level'] . "\n";
                echo str_pad("Total Business:", 20) . "₹" . number_format($upline['total_business'], 2) . "\n";
                
                // Get upline's commission level
                $uplineLevelStmt = $pdo->prepare("
                    SELECT * FROM mlm_commission_levels 
                    WHERE level = ? AND plan_id = 1
                ");
                $uplineLevelStmt->execute([$upline['current_level']]);
                $uplineLevelInfo = $uplineLevelStmt->fetch();
                
                if ($uplineLevelInfo) {
                    echo "\n" . str_pad("UPLINE COMMISSION LEVEL", 30, " ", STR_PAD_RIGHT) . "\n";
                    echo str_repeat("-", 70) . "\n";
                    echo str_pad("Level:", 20) . $uplineLevelInfo['level'] . "\n";
                    echo str_pad("Direct %:", 20) . $uplineLevelInfo['direct_percentage'] . "%\n";
                    echo str_pad("Business Range:", 20) . "₹" . number_format($uplineLevelInfo['min_business'], 2);
                    echo " to " . ($uplineLevelInfo['max_business'] ? "₹" . number_format($uplineLevelInfo['max_business'], 2) : "∞") . "\n\n";
                }
            }
        } else {
            echo "\n" . str_pad("UPLINE DETAILS", 30, " ", STR_PAD_RIGHT) . "\n";
            echo str_repeat("-", 70) . "\n";
            echo "No upline (this is a top-level associate)\n\n";
        }
        
    } catch (Exception $e) {
        die("❌ Error: " . $e->getMessage() . "\n");
    }

    // Test transaction amount
    $testAmount = 100000; // ₹100,000 test transaction
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "TEST TRANSACTION\n";
    echo str_repeat("=", 70) . "\n\n";
    
    echo str_pad("Transaction Amount:", 30) . "₹" . number_format($testAmount, 2) . "\n\n";
    
    // Calculate expected commissions
    echo str_pad("EXPECTED COMMISSIONS", 30, " ", STR_PAD_RIGHT) . "\n";
    echo str_repeat("-", 70) . "\n";
    
    // Direct commission for the associate
    $directCommission = ($testAmount * $levelInfo['direct_percentage']) / 100;
    echo str_pad("Direct Commission:", 25) . "\n";
    echo str_pad("-", 25, " ") . "• " . number_format($levelInfo['direct_percentage'], 2) . "% of ₹" . number_format($testAmount, 2) . " = ";
    echo "₹" . number_format($directCommission, 2) . "\n";
    
    // If there's an upline, calculate difference commission
    if (isset($upline)) {
        // Get upline's commission level
        $uplineLevelStmt = $pdo->prepare("
            SELECT * FROM mlm_commission_levels 
            WHERE level = ? AND plan_id = 1
        ");
        $uplineLevelStmt->execute([$upline['current_level']]);
        $uplineLevelInfo = $uplineLevelStmt->fetch();
        
        if ($uplineLevelInfo && $uplineLevelInfo['direct_percentage'] > $levelInfo['direct_percentage']) {
            $uplineCommission = ($testAmount * $uplineLevelInfo['direct_percentage']) / 100;
            $differenceCommission = $uplineCommission - $directCommission;
            
            echo "\n" . str_pad("UPLINE DIFFERENCE COMMISSION", 30, " ", STR_PAD_RIGHT) . "\n";
            echo str_repeat("-", 70) . "\n";
            
            echo "Upline: {$upline['name']} (Level {$upline['current_level']})\n";
            echo str_pad("-", 10, " ") . "• Upline Rate: " . $uplineLevelInfo['direct_percentage'] . "%\n";
            echo str_pad("-", 10, " ") . "• Associate Rate: " . $levelInfo['direct_percentage'] . "%\n";
            echo str_pad("-", 10, " ") . "• Difference: " . ($uplineLevelInfo['direct_percentage'] - $levelInfo['direct_percentage']) . "%\n";
            echo str_repeat(" ", 10) . "  (" . $uplineLevelInfo['direct_percentage'] . "% - " . $levelInfo['direct_percentage'] . "% = " . 
                 ($uplineLevelInfo['direct_percentage'] - $levelInfo['direct_percentage']) . "%)\n";
            echo "\n" . str_pad("Difference Commission:", 25) . "\n";
            echo str_pad("-", 25, " ") . "• " . ($uplineLevelInfo['direct_percentage'] - $levelInfo['direct_percentage']) . "% of ₹" . number_format($testAmount, 2) . " = ";
            echo "₹" . number_format($differenceCommission, 2) . "\n";
            
            echo "\n" . str_pad("TOTAL UPLINE COMMISSION:", 30) . "₹" . number_format($uplineCommission, 2) . "\n";
            echo str_repeat(" ", 30) . "({$uplineLevelInfo['direct_percentage']}% of ₹" . number_format($testAmount, 2) . ")\n\n";
        } else if ($uplineLevelInfo) {
            echo "\n" . str_pad("UPLINE COMMISSION", 30, " ", STR_PAD_RIGHT) . "\n";
            echo str_repeat("-", 70) . "\n";
            echo "Upline: {$upline['name']} (Level {$upline['current_level']})\n";
            echo "No difference commission - Upline's rate (" . $uplineLevelInfo['direct_percentage'] . "%) is not higher than associate's rate (" . $levelInfo['direct_percentage'] . "%)\n\n";
        }
    } else {
        echo "\n" . str_pad("UPLINE COMMISSION", 30, " ", STR_PAD_RIGHT) . "\n";
        echo str_repeat("-", 70) . "\n";
        echo "No upline - This is a top-level associate\n\n";
    }

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

    // Test amount
    $testAmount = 100000; // ₹100,000 test transaction
    echo "\n=== Test Transaction ===\n";
    echo "- Amount: ₹" . number_format($testAmount, 2) . "\n\n";

    // Get commission levels for the associate
    $stmt = $pdo->prepare("
        SELECT * FROM mlm_commission_levels 
        WHERE plan_id = ? AND level = ?
        ORDER BY min_business
    ");
    $stmt->execute([$associate['commission_plan_id'], $associate['current_level']]);
    $levels = $stmt->fetchAll();

    if (empty($levels)) {
        die("❌ No commission levels found for plan ID {$associate['commission_plan_id']} and level {$associate['current_level']}\n");
    }

    echo "\n=== Commission Levels ===\n";
    foreach ($levels as $level) {
        echo "- Level {$level['level']}: {$level['direct_percentage']}% direct commission\n";
        echo "  Business Range: ₹" . number_format($level['min_business'] ?? 0, 2) . " to " . 
             (isset($level['max_business']) ? "₹" . number_format($level['max_business'], 2) : "∞") . "\n";
    }

    // Calculate expected commission
    $directCommission = ($testAmount * $levels[0]['direct_percentage']) / 100;
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EXPECTED COMMISSION CALCULATION\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "Associate: {$associate['name']} (Level {$associate['current_level']})\n";
    echo "Commission Rate: {$levels[0]['direct_percentage']}%\n";
    echo "Transaction Amount: ₹" . number_format($testAmount, 2) . "\n";
    echo str_repeat("-", 50) . "\n";
    echo "DIRECT COMMISSION: ₹" . number_format($directCommission, 2) . "\n\n";

    // If there's an upline, calculate difference commission
    if ($upline) {
        $stmt->execute([$upline['commission_plan_id'], $upline['current_level']]);
        $uplineLevels = $stmt->fetchAll();
        
        if (!empty($uplineLevels)) {
            $uplineCommission = ($testAmount * $uplineLevels[0]['direct_percentage']) / 100;
            $differenceCommission = $uplineCommission - $directCommission;
            
            if ($differenceCommission > 0) {
                $differencePercent = $uplineLevels[0]['direct_percentage'] - $levels[0]['direct_percentage'];
                echo "\nUPLINE COMMISSION (Difference)\n";
                echo "Upline: {$upline['name']} (Level {$upline['current_level']})\n";
                echo "Upline Rate: {$uplineLevels[0]['direct_percentage']}%\n";
                echo "Associate Rate: {$levels[0]['direct_percentage']}%\n";
                echo str_repeat("-", 50) . "\n";
                echo "DIFFERENCE: {$differencePercent}% of ₹" . number_format($testAmount, 2) . " = ";
                echo "₹" . number_format($differenceCommission, 2) . "\n\n";
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
