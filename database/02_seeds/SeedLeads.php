<?php
require_once __DIR__ . '/../includes/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("DELETE FROM leads"); // quick reset for demo

$leads = [
    ['name' => 'Rahul Sharma',    'email' => 'rahul@example.com',   'phone' => '9876543210', 'source' => 'Website', 'status' => 'New'],
    ['name' => 'Priya Patel',     'email' => 'priya@example.com',   'phone' => '9123456789', 'source' => 'Facebook', 'status' => 'Follow-up'],
    ['name' => 'Amit Verma',      'email' => 'amit@example.com',    'phone' => '9988776655', 'source' => 'Referral', 'status' => 'Converted'],
];

$stmt = $conn->prepare("INSERT INTO leads (name, email, phone, source, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param('sssss', $n, $e, $p, $so, $st);

foreach ($leads as $l) {
    [$n, $e, $p, $so, $st] = array_values($l);
    $stmt->execute();
}
$stmt->close();

echo "✅ 3 sample leads inserted.\n";

$conn->close();