<?php
require_once __DIR__ . '/config/bootstrap.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();

$colonies = $pdo->query("SELECT id, name FROM colonies")->fetchAll(PDO::FETCH_ASSOC);
$customerIds = $pdo->query("SELECT id FROM users WHERE role = 'customer' LIMIT 20")->fetchAll(PDO::FETCH_COLUMN);

$colony = $colonies[array_rand($colonies)];
$customerId = $customerIds[array_rand($customerIds)];

$firstNames = ['Rajesh', 'Priya', 'Amit', 'Sunita', 'Vikash', 'Anjali', 'Sanjay', 'Pooja', 'Rahul', 'Neha', 'Manoj', 'Kavita', 'Deepak', 'Ritu', 'Suresh', 'Meena', 'Ankit', 'Shweta', 'Vivek', 'Priyanka'];
$lastNames = ['Sharma', 'Singh', 'Gupta', 'Verma', 'Yadav', 'Mishra', 'Agarwal', 'Jain', 'Patel', 'Kumar', 'Tiwari', 'Pandey', 'Shukla', 'Dubey', 'Srivastava', 'Chauhan', 'Rathore', 'Malhotra', 'Kapoor', 'Bansal'];

$visitDate = date('Y-m-d', mt_rand(strtotime('2026-01-01'), strtotime('2026-10-20')));

echo "Colony ID: " . var_export($colony['id'], true) . "\n";
echo "Customer ID: " . var_export($customerId, true) . "\n";
echo "Name: " . var_export($firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)], true) . "\n";
echo "Phone: " . var_export('91' . mt_rand(7000000000, 9999999999), true) . "\n";
echo "Email: " . var_export(strtolower($firstNames[array_rand($firstNames)]) . '@example.com', true) . "\n";
echo "Visit Date: " . var_export($visitDate, true) . "\n";

$params = [
    $colony['id'], $customerId,
    $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
    '91' . mt_rand(7000000000, 9999999999),
    strtolower($firstNames[array_rand($firstNames)]) . '@example.com',
    $visitDate, '10:00:00', 1, 'Gorakhpur Railway Station', 'scheduled'
];

echo "Params count: " . count($params) . "\n";
echo "Params: " . json_encode($params) . "\n";

$sql = "INSERT INTO site_visits (colony_id, user_id, visitor_name, visitor_phone, visitor_email, visit_date, visit_time, pickup_required, pickup_location, status, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())";

echo "Placeholder count: " . substr_count($sql, '?') . "\n";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo "✅ Insert successful\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}