<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Check users table for role column
echo "=== users role column ===\n";
$r = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
$row = $r->fetch(PDO::FETCH_ASSOC);
echo $row['Field'] . ' | ' . $row['Type'] . "\n\n";

// Check if telecaller role exists in leads
echo "=== leads with assigned_to ===\n";
$r = $pdo->query("SELECT l.assigned_to, u.role FROM leads l JOIN users u ON u.id = l.assigned_to WHERE l.assigned_to IS NOT NULL LIMIT 5");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "assigned_to={$row['assigned_to']} role={$row['role']}\n";
}

// Check onboarding_track column
echo "\n=== users onboarding_track column ===\n";
$r = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'onboarding_track'");
$row = $r->fetch(PDO::FETCH_ASSOC);
if ($row) echo $row['Field'] . ' | ' . $row['Type'] . "\n";
else echo "Column does NOT exist\n";
