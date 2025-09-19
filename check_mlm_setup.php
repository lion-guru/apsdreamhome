<?php
/**
 * Script to check MLM commission setup status
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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "=== MLM Setup Check ===\n\n";

    // 1. Check MLM commission plans
    echo "1. Checking MLM Commission Plans:\n";
    $plans = $pdo->query("SELECT * FROM mlm_commission_plans")->fetchAll();
    if (empty($plans)) {
        echo "   ❌ No commission plans found. Please run setup_mlm_commissions.php first.\n";
    } else {
        echo "   ✅ Found " . count($plans) . " commission plan(s):\n";
        foreach ($plans as $plan) {
            echo "      - ID: {$plan['id']}, Name: {$plan['name']}";
            echo $plan['is_active'] ? " (Active)" : " (Inactive)";
            echo "\n";
        }
    }

    // 2. Check MLM commission levels
    echo "\n2. Checking MLM Commission Levels:\n";
    $levels = $pdo->query("SELECT COUNT(*) as count FROM mlm_commission_levels")->fetch()['count'];
    if ($levels == 0) {
        echo "   ❌ No commission levels found. Please run setup_mlm_commissions.php.\n";
    } else {
        echo "   ✅ Found $levels commission levels.\n";
        
        // Show sample levels
        $sampleLevels = $pdo->query("
            SELECT level, CONCAT(direct_percentage, '%') as percentage, 
                   CONCAT('₹', FORMAT(min_business, 2)) as min_business,
                   CASE WHEN max_business IS NULL THEN 'No Limit' 
                        ELSE CONCAT('₹', FORMAT(max_business, 2)) 
                   END as max_business
            FROM mlm_commission_levels 
            WHERE plan_id = 1 
            ORDER BY level ASC
            LIMIT 5
        ")->fetchAll();
        
        foreach ($sampleLevels as $level) {
            echo "      - Level {$level['level']}: {$level['percentage']} (Range: {$level['min_business']} - {$level['max_business']})\n";
        }
        if ($levels > 5) {
            echo "      ... and " . ($levels - 5) . " more levels.\n";
        }
    }

    // 3. Check test users
    echo "\n3. Checking Test Users:\n";
    $testUsers = $pdo->query("
        SELECT u.id, u.email, u.name, 
               a.id as associate_id, a.parent_id, a.current_level,
               CONCAT('₹', FORMAT(IFNULL(a.total_business, 0), 2)) as total_business
        FROM users u
        LEFT JOIN associates a ON u.id = a.user_id
        WHERE u.email LIKE 'test%@example.com' OR u.email LIKE 'user%@example.com'
        ORDER BY u.id
    ")->fetchAll();

    if (empty($testUsers)) {
        echo "   ℹ️ No test users found.\n";
    } else {
        echo "   Found " . count($testUsers) . " test user(s):\n";
        foreach ($testUsers as $user) {
            echo "      - ID: {$user['id']}, Name: {$user['name']}";
            if ($user['associate_id']) {
                echo " (Associate ID: {$user['associate_id']}, Level: {$user['current_level']})\n";
                echo "         Parent ID: " . ($user['parent_id'] ?? 'None') . ", ";
                echo "Total Business: {$user['total_business']}\n";
            } else {
                echo " (No associate record)\n";
            }
        }
    }

    // 4. Check transactions
    echo "\n4. Checking Test Transactions:\n";
    $txCount = $pdo->query("SELECT COUNT(*) as count FROM transactions")->fetch()['count'];
    echo "   Total transactions in system: $txCount\n";
    
    // Check MLM commissions
    $commissions = $pdo->query("
        SELECT COUNT(*) as count, 
               CONCAT('₹', FORMAT(SUM(commission_amount), 2)) as total_commissions
        FROM mlm_commissions
    ")->fetch();
    
    echo "   MLM Commissions: {$commissions['count']} records, Total: {$commissions['total_commissions']}\n";
    
    // Show recent commissions
    $recentCommissions = $pdo->query("
        SELECT mc.id, mc.commission_amount, mc.commission_type, 
               u.email as associate_email, mc.status, mc.created_at
        FROM mlm_commissions mc
        JOIN users u ON mc.user_id = u.id
        ORDER BY mc.created_at DESC
        LIMIT 3
    ")->fetchAll();
    
    if (!empty($recentCommissions)) {
        echo "   Recent commissions:\n";
        foreach ($recentCommissions as $comm) {
            echo "      - ID: {$comm['id']}, Amount: ₹{$comm['commission_amount']}, ";
            echo "Type: {$comm['commission_type']}, Status: {$comm['status']}, ";
            echo "Associate: {$comm['associate_email']}, ";
            echo "Date: {$comm['created_at']}\n";
        }
    }

    echo "\n=== Check Complete ===\n";
    
} catch (PDOException $e) {
    die("❌ Database Error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
