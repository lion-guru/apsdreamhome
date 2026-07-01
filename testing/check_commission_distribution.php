<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

require_once 'app/Services/MLM/MLMCommissionEngine.php';

$engine = new \App\Services\MLM\MLMCommissionEngine($pdo);

echo "=== Commission Distribution Test ===\n\n";

// Get a booking to test with
$stmt = $pdo->query("SELECT id, agreement_value, total_plot_value, sales_manager_id, associate_id, customer_id FROM plot_bookings WHERE status IN ('emi_active','partially_paid','fully_paid') LIMIT 3");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($bookings as $booking) {
    $saleValue = (float)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? 0);
    if ($saleValue <= 0) continue;
    echo "\n";
    
    echo sprintf("Booking #%d: Sale Value = ₹%s\n", $booking['id'], number_format($saleValue));
    
    $result = $engine->calculateBookingCommission($booking['id']);
    
    echo "  Entries created: " . count($result['entries']) . "\n";
    $totalPct = 0;
    $totalAmt = 0;
    foreach ($result['entries'] as $e) {
        echo sprintf("    Level %d: User %d, Type: %s, Rate: %s%%, Amount: ₹%s\n", 
            $e['level'], $e['beneficiary_user_id'], $e['commission_type'], 
            number_format($e['pct'], 2), number_format($e['amount'], 2));
        $totalPct += $e['pct'];
        $totalAmt += $e['amount'];
    }
    echo sprintf("  TOTAL: %.2f%% = ₹%s (Cap: 20%% = ₹%s)\n\n", 
        $totalPct, number_format($totalAmt, 2), number_format($saleValue * 0.20, 2));
}