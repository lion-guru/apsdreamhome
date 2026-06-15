<?php
define('APP_ROOT', dirname(__DIR__));
$cfg = require APP_ROOT . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset=utf8mb4",
    $cfg['username'], $cfg['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo "<pre>";

// Check FK on marketing_campaign_recipients
$stmt = $pdo->query("SHOW CREATE TABLE marketing_campaign_recipients");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "=== marketing_campaign_recipients ===\n";
echo $row['Create Table'] . "\n\n";

// Check if campaigns table exists
$exists = $pdo->query("SHOW TABLES LIKE 'campaigns'")->fetchAll();
echo "=== 'campaigns' table exists: " . (count($exists) > 0 ? 'YES' : 'NO') . " ===\n\n";

// Check marketing_campaigns structure
$stmt2 = $pdo->query("SHOW CREATE TABLE marketing_campaigns");
$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "=== marketing_campaigns ===\n";
echo $row2['Create Table'] . "\n";

echo "</pre>";
