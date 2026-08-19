<?php
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Check for payments table
$stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
$exists = $stmt->fetch() !== false;
echo "payments table: " . ($exists ? "EXISTS" : "MISSING") . "\n";

// Check for other tables
$other_tables = ['mlm_payouts', 'notifications', 'documents'];
foreach ($other_tables as $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    $exists = $stmt->fetch() !== false;
    echo "$table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}
