<?php
require_once __DIR__ . '/../includes/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("DELETE FROM transactions"); // quick reset for demo

$txns = [
    ['user_id' => 1, 'amount' => 250000, 'type' => 'Credit', 'description' => 'Booking advance for Plot A-101'],
    ['user_id' => 1, 'amount' => 150000, 'type' => 'Credit', 'description' => 'Second installment'],
];

$stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description, date, created_at) VALUES (?, ?, ?, ?, CURDATE(), NOW())");
$stmt->bind_param('idss', $uid, $amt, $typ, $desc);

foreach ($txns as $t) {
    [$uid, $amt, $typ, $desc] = array_values($t);
    $stmt->execute();
}
$stmt->close();

echo "✅ 2 sample transactions inserted.\n";

$conn->close();