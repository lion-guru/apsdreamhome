<?php
// Seed sample commission_transactions for demo/testing
require_once __DIR__ . '/includes/config/config.php';

$sample = [
    // associate_id, amount, commission_amount, transaction_date, description
    [1, 10000, 2500, '2025-04-01', 'Initial Sale'],
    [2, 20000, 5000, '2025-04-02', 'Level 2 Sale'],
    [3, 30000, 7500, '2025-04-03', 'Level 3 Sale'],
    [4, 40000, 10000, '2025-04-04', 'Level 4 Sale'],
    [5, 50000, 12500, '2025-04-05', 'Level 5 Sale'],
    [6, 60000, 15000, '2025-03-15', 'Older Sale'],
    [7, 70000, 17500, '2025-02-20', 'Oldest Sale'],
];
foreach ($sample as $row) {
    $stmt = $con->prepare("INSERT INTO commission_transactions (associate_id, amount, commission_amount, transaction_date, description, status) VALUES (?, ?, ?, ?, ?, 'approved')");
    $stmt->bind_param('iddss', $row[0], $row[1], $row[2], $row[3], $row[4]);
    $stmt->execute();
    $stmt->close();
}
echo "Sample commission_transactions seeded!\n";
