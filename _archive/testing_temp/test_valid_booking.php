<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

require_once 'app/Services/MLM/MLMCommissionEngine.php';

$engine = new \App\Services\MLM\MLMCommissionEngine($pdo);

// Test with booking 9001 (associate_id=1 - Admin User)
$booking = $pdo->prepare("SELECT id, sales_manager_id, associate_id, customer_id, agreement_value FROM plot_bookings WHERE id = 9001");
$booking->execute();
$b = $booking->fetch(PDO::FETCH_ASSOC);

echo "=== Booking 9001 ===\n";
echo "associate_id: {$b['associate_id']}, sales_manager_id: {$b['sales_manager_id']}\n";

// Check upline for associate_id 1
$assoc = $pdo->prepare("SELECT user_id FROM associates WHERE user_id = 1");
$assoc->execute();
$assocRow = $assoc->fetch(PDO::FETCH_ASSOC);
echo "Associate 1 user_id: " . ($assocRow ? $assocRow['user_id'] : 'NOT FOUND') . "\n";

// Upline for user 1 (Admin User)
echo "\nUpline for user 1:\n";
$current = 1;
for ($level = 1; $level <= 7; $level++) {
    $stmt = $pdo->prepare('SELECT id, name, referred_by FROM users WHERE id = ?');
    $stmt->execute([$current]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['referred_by'])) break;
    $parentId = (int)$row['referred_by'];
    $parentStmt = $pdo->prepare('SELECT id, name, referred_by FROM users WHERE id = ?');
    $parentStmt->execute([$parentId]);
    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
    if (!$parent) break;
    echo "  Level $level: User {$parent['id']} ({$parent['name']}) referred_by={$parent['referred_by']}\n";
    $current = $parentId;
}

// Now test the actual commission calculation
echo "\n--- Commission Calculation for Booking 9001 ---\n";
$result = $engine->calculateBookingCommission(9001);
$totalPct = 0;
$totalAmt = 0;
foreach ($result['entries'] as $e) {
    echo sprintf("  Level %d: User %d, Type: %s, Rate:s, Rate: %s%%, Amount: ₹%s\n", 
        $e['level'], $e['beneficiary_user_id'], $e['commission_type'], 
        number_format($e['pct'], 2), number_format($e['amount'], 2));
    $totalPct += $e['pct'];
    $totalAmt += $e['amount'];
}
echo sprintf("  TOTAL: %.2f%% = ₹%s\n", $totalPct, number_format($totalAmt, 2));

// Now test with booking 99934 (associate_id=1, customer=2071)
echo "\n\n=== Booking 99934 ===\n";
$result = $engine->calculateBookingCommission(99934);
$totalPct = 0;
$totalAmt = 0;
foreach ($result['entries'] as $e) {
    echo sprintf("  Level %d: User %d, Type: %s, Rate: %s%%, Amount: ₹%s\n", 
        $e['level'], $e['beneficiary_user_id'], $e['commission_type'], 
        number_format($e['pct'], 2), number_format($e['amount'], 2));
    $totalPct += $e['pct'];
    $totalAmt += $e['amount'];
}
echo sprintf("  TOTAL: %.2f%% = ₹%s\n", $totalPct, number_format($totalAmt, 2));

// Test with booking 99939 (associate_id=2)
echo "\n\n=== Booking 99939 (associate_id=2) ===\n";
$result = $engine->calculateBookingCommission(99939);
$totalPct = 0;
$totalAmt = 0;
foreach ($result['entries'] as $e) {
    echo sprintf("  Level %d: User %d, Type: %s, Rate: %s%%, Amount: ₹%s\n", 
        $e['level'], $e['beneficiary_user_id'], $e['commission_type'], 
        number_format($e['pct'], 2), number_format($e['amount'], 2));
    $totalPct += $e['pct'];
    $totalAmt += $e['amount'];
}
echo sprintf("  TOTAL: %.2f%% = ₹%s\n", $totalPct, number_format($totalAmt, 2));