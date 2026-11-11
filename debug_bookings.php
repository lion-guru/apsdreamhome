<?php
require_once __DIR__ . '/config/bootstrap.php';

use App\Core\Database\Database;

echo "=== Debug Booking Insert ===\n\n";

$pdo = Database::getInstance()->getConnection();

// Get one plot
$stmt = $pdo->query("SELECT id, plot_number, colony_id, total_price FROM plots WHERE status = 'available' LIMIT 1");
$plot = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Plot: " . json_encode($plot) . "\n\n";

$customerId = 122015;
$bookingNumber = 'BK-' . date('Ymd') . '-0001';
$bookingDate = '2026-01-15';
$totalValue = (float)$plot['total_price'];
$bookingAmount = round($totalValue * 0.1, 2);
$status = 'pending';
$channel = 'direct';
$associateId = null;
$commissionPct = 1.0;
$commissionAmount = round($totalValue * $commissionPct / 100, 2);

echo "Values:\n";
echo "  plot_id: " . var_export($plot['id'], true) . "\n";
echo "  colony_id: " . var_export($plot['colony_id'], true) . "\n";
echo "  customer_id: " . var_export($customerId, true) . "\n";
echo "  booking_number: " . var_export($bookingNumber, true) . "\n";
echo "  booking_date: " . var_export($bookingDate, true) . "\n";
echo "  total_price: " . var_export($totalValue, true) . "\n";
echo "  booking_amount: " . var_export($bookingAmount, true) . "\n";
echo "  status: " . var_export($status, true) . "\n";
echo "  approval_status: pending\n";
echo "  channel: " . var_export($channel, true) . "\n";
echo "  associate_id: " . var_export($associateId, true) . "\n";
echo "  commission_pct: " . var_export($commissionPct, true) . "\n";
echo "  commission_amount: " . var_export($commissionAmount, true) . "\n";
echo "  notes: Revenue activation booking\n";

$stmt = $pdo->prepare("
    INSERT INTO bookings (
        plot_id, colony_id, customer_id, booking_number, booking_date,
        total_price, booking_amount, status, approval_status,
        channel, associate_id, commission_pct, commission_amount, notes, created_by, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
");

$params = [
    $plot['id'], $plot['colony_id'], $customerId, $bookingNumber, $bookingDate,
    $totalValue, $bookingAmount, $totalValue, $status, 'pending',
    $channel, $associateId, $commissionPct, $commissionAmount, 'Revenue activation booking'
];

echo "  Params count: " . count($params) . "\n";
echo "  Placeholders: 15\n";

try {
    $stmt->execute($params);
    echo "  ✅ Insert successful\n";
} catch (\Throwable $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}