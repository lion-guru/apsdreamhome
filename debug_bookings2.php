<?php
require_once __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Direct insert test
$sql = "INSERT INTO bookings (
    plot_id, colony_id, customer_id, booking_number, booking_date,
    total_price, booking_amount, agreement_value, status, approval_status,
    channel, associate_id, commission_pct, commission_amount, notes, created_by, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

$params = [
    1, 1, 122015, 'BK-20261105-0001', '2026-01-15',
    550000.00, 55000.00, 550000.00, 'pending', 'pending',
    'direct', NULL, 1.00, 5500.00, 'Revenue activation booking'
];

echo "Params count: " . count($params) . "\n";
echo "SQL: $sql\n";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo "✅ Insert successful\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}