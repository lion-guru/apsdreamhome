<?php
/**
 * Script to check the current state of MLM setup
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

    echo "=== MLM System Status ===\n\n";

    // 1. Check MLM tables
    echo "1. Checking MLM Tables:\n";
    $tables = [
        'mlm_commission_plans',
        'mlm_commission_levels',
        'mlm_commissions',
        'associates',
        'transaction_associates'
    ];
    
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
            echo "   ✅ $table: $count records\n";
        } catch (Exception $e) {
            echo "   ❌ $table: " . $e->getMessage() . "\n";
        }
    }
    
    // 2. Check commission plans
    echo "\n2. Commission Plans:\n";
    $plans = $pdo->query("SELECT * FROM mlm_commission_plans")->fetchAll();
    if (empty($plans)) {
        echo "   ❌ No commission plans found\n";
    } else {
        foreach ($plans as $plan) {
            echo "   Plan #{$plan['id']}: {$plan['name']}";
            echo $plan['is_active'] ? " (Active)" : " (Inactive)";
            echo "\n";
            
            // Show levels for this plan
            $levels = $pdo->query("
                SELECT level, direct_percentage, 
                       min_business, 
                       IFNULL(max_business, 'No Limit') as max_business
                FROM mlm_commission_levels 
                WHERE plan_id = {$plan['id']}
                ORDER BY level
                LIMIT 5
            ")->fetchAll();
            
            foreach ($levels as $level) {
                echo "      Level {$level['level']}: {$level['direct_percentage']}% ";
                echo "(Business: {$level['min_business']} - {$level['max_business']})\n";
            }
            
            if (count($levels) >= 5) {
                $totalLevels = $pdo->query("SELECT COUNT(*) FROM mlm_commission_levels WHERE plan_id = {$plan['id']}")->fetchColumn();
                echo "      ... and " . ($totalLevels - 5) . " more levels\n";
            }
        }
    }
    
    // 3. Check test users
    echo "\n3. Test Users:\n";
    $testUsers = $pdo->query("
        SELECT u.id, u.email, u.name, 
               a.id as associate_id, a.parent_id, a.current_level,
               a.total_business, a.direct_business, a.team_business
        FROM users u
        LEFT JOIN associates a ON u.id = a.user_id
        WHERE u.email LIKE '%@test.com' OR u.email LIKE '%@example.com'
        ORDER BY u.id
    ")->fetchAll();
    
    if (empty($testUsers)) {
        echo "   No test users found\n";
    } else {
        foreach ($testUsers as $user) {
            echo "   User #{$user['id']}: {$user['name']} ({$user['email']})\n";
            if ($user['associate_id']) {
                echo "      Associate ID: {$user['associate_id']}, ";
                echo "Level: {$user['current_level']}, ";
                echo "Parent: " . ($user['parent_id'] ?: 'None') . "\n";
                echo "      Business - Total: ₹{$user['total_business']}, ";
                echo "Direct: ₹{$user['direct_business']}, ";
                echo "Team: ₹{$user['team_business']}\n";
                
                // Show commissions
                $commissions = $pdo->query("
                    SELECT id, commission_amount, commission_type, status, created_at
                    FROM mlm_commissions
                    WHERE user_id = {$user['id']}
                    ORDER BY created_at DESC
                    LIMIT 2
                ")->fetchAll();
                
                if (!empty($commissions)) {
                    echo "      Recent Commissions:\n";
                    foreach ($commissions as $comm) {
                        echo "         #{$comm['id']}: ₹{$comm['commission_amount']} ";
                        echo "({$comm['commission_type']}, {$comm['status']})\n";
                    }
                }
            } else {
                echo "      No associate record\n";
            }
            echo "\n";
        }
    }
    
    // 4. Check recent transactions
    echo "\n4. Recent Transactions:\n";
    $transactions = $pdo->query("
        SELECT t.id, t.user_id, t.amount, t.status, t.created_at,
               u.name as user_name, u.email,
               COUNT(ta.associate_id) as associate_count
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN transaction_associates ta ON t.id = ta.transaction_id
        GROUP BY t.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ")->fetchAll();
    
    if (empty($transactions)) {
        echo "   No recent transactions found\n";
    } else {
        foreach ($transactions as $tx) {
            echo "   TX #{$tx['id']}: ₹{$tx['amount']} by {$tx['user_name']} ({$tx['email']})\n";
            echo "      Status: {$tx['status']}, ";
            echo "Date: {$tx['created_at']}, ";
            echo "Associates: {$tx['associate_count']}\n";
        }
    }
    
    echo "\n=== End of Status ===\n";
    
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
