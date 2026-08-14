<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "Testing expenses query...\n";
try {
    $expense_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(amount), 0) as total_expenses,
            COUNT(*) as total_expense_transactions,
            COALESCE(AVG(amount), 0) as avg_expense
        FROM expenses
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetch(PDO::FETCH_ASSOC);
    echo "expenses query OK: " . json_encode($expense_stats) . "\n";
} catch (Exception $e) {
    echo "expenses query ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting commissions query...\n";
try {
    $commission_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) as total_commissions,
            COUNT(*) as total_commission_transactions,
            COALESCE(AVG(amount), 0) as avg_commission
        FROM commissions
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetch(PDO::FETCH_ASSOC);
    echo "commissions query OK: " . json_encode($commission_stats) . "\n";
} catch (Exception $e) {
    echo "commissions query ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting booking_payments query...\n";
try {
    $bp_revenue = $pdo->query("
        SELECT COALESCE(SUM(payment_amount), 0) as total, COUNT(*) as count
         FROM booking_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetch(PDO::FETCH_ASSOC);
    echo "booking_payments query OK: " . json_encode($bp_revenue) . "\n";
} catch (Exception $e) {
    echo "booking_payments query ERROR: " . $e->getMessage() . "\n";
}

echo "\nTesting activity_logs_unified query...\n";
try {
    $activities = $pdo->query("
        SELECT id, activity_type as type, description, created_at
         FROM activity_logs_unified 
         ORDER BY created_at DESC 
         LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "activity_logs_unified query OK: " . count($activities) . " rows\n";
} catch (Exception $e) {
    echo "activity_logs_unified query ERROR: " . $e->getMessage() . "\n";
}?>