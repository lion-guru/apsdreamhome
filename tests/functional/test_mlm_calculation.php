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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "=== Testing MLM Commission Calculation ===\n\n";

    // 1. Get a test transaction
    $transaction = $pdo->query("
        SELECT t.*, u.name, u.email 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE t.amount > 0
        ORDER BY t.id DESC
        LIMIT 1
    ")->fetch();

    if (!$transaction) {
        die("❌ No transactions found for testing. Please create a test transaction first.\n");
    }

    echo "Testing with transaction #{$transaction['id']}:\n";
    echo "- Amount: ₹{$transaction['amount']}\n";
    echo "- User: {$transaction['name']} ({$transaction['email']})\n\n";

    // 2. Get associated associates
    $associates = $pdo->query("
        SELECT a.*, u.name, u.email
        FROM transaction_associates ta
        JOIN associates a ON ta.associate_id = a.id
        JOIN users u ON a.user_id = u.id
        WHERE ta.transaction_id = {$transaction['id']}
    ")->fetchAll();

    if (empty($associates)) {
        die("❌ No associates found for this transaction.\n");
    }

    echo "Associated Associates:\n";
    foreach ($associates as $assoc) {
        echo "- {$assoc['name']} (ID: {$assoc['id']}, Level: {$assoc['current_level']})\n";
        
        // Get commission for this associate
        $commission = $pdo->query("
            SELECT * FROM mlm_commissions 
            WHERE transaction_id = {$transaction['id']} 
            AND user_id = {$assoc['user_id']}
            LIMIT 1
        ")->fetch();

        if ($commission) {
            echo "  Commission: ₹{$commission['commission_amount']} ";
            echo "({$commission['commission_type']}, {$commission['status']})\n";
        } else {
            echo "  ❌ No commission found\n";
        }
        
        // Get upline commissions
        $uplineCommissions = $pdo->query("
            SELECT mc.*, u.name as upline_name
            FROM mlm_commissions mc
            JOIN users u ON mc.user_id = u.id
            WHERE mc.transaction_id = {$transaction['id']}
            AND mc.upline_id = {$assoc['user_id']}
        ")->fetchAll();
        
        if (!empty($uplineCommissions)) {
            echo "  Upline Commissions:\n";
            foreach ($uplineCommissions as $uc) {
                echo "  - {$uc['upline_name']}: ₹{$uc['commission_amount']} ";
                echo "({$uc['commission_type']}, {$uc['status']})\n";
            }
        }
        
        echo "\n";
    }
    
    // 3. Show commission levels for this transaction
    $commissionLevels = $pdo->query("
        SELECT mcl.*
        FROM mlm_commission_levels mcl
        JOIN associates a ON mcl.plan_id = a.commission_plan_id
        JOIN transaction_associates ta ON a.id = ta.associate_id
        WHERE ta.transaction_id = {$transaction['id']}
        ORDER BY mcl.level
    ")->fetchAll();
    
    if (!empty($commissionLevels)) {
        echo "Applicable Commission Levels:\n";
        foreach ($commissionLevels as $level) {
            echo "- Level {$level['level']}: {$level['direct_percentage']}% ";
            echo "(Business: ₹{$level['min_business']} - ";
            echo ($level['max_business'] ? "₹{$level['max_business']}" : "No Limit") . ")\n";
        }
        echo "\n";
    }
    
    // 4. Show transaction details
    echo "Transaction Details:\n";
    echo "- ID: {$transaction['id']}\n";
    echo "- Amount: ₹{$transaction['amount']}\n";
    echo "- Type: {$transaction['type']}\n";
    echo "- Status: {$transaction['status']}\n";
    echo "- Created: {$transaction['created_at']}\n";
    
    // 5. Show all commissions for this transaction
    $allCommissions = $pdo->query("
        SELECT mc.*, u.name, u.email
        FROM mlm_commissions mc
        JOIN users u ON mc.user_id = u.id
        WHERE mc.transaction_id = {$transaction['id']}
        ORDER BY mc.created_at DESC
    ")->fetchAll();
    
    if (!empty($allCommissions)) {
        echo "\nAll Commissions for This Transaction:\n";
        $totalCommissions = 0;
        
        foreach ($allCommissions as $comm) {
            echo "- {$comm['name']} ({$comm['email']}): ";
            echo "₹{$comm['commission_amount']} ";
            echo "({$comm['commission_type']}, {$comm['status']})\n";
            $totalCommissions += $comm['commission_amount'];
        }
        
        echo "\nTotal Commissions: ₹$totalCommissions ";
        $percentage = ($totalCommissions / $transaction['amount']) * 100;
        echo "(" . number_format($percentage, 2) . "% of transaction amount)\n";
    }
    
    echo "\n=== End of Test ===\n";
    
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
