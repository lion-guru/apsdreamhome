<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Check if pincodes table exists
$stmt = $pdo->query("SHOW TABLES LIKE 'pincodes'");
echo "pincodes table exists: " . ($stmt->rowCount() > 0 ? "YES" : "NO") . "\n";

if ($stmt->rowCount() > 0) {
    $stmt = $pdo->query("DESCRIBE pincodes");
    echo "pincodes schema:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} ({$row['Type']})" . ($row['Key'] ? " [{$row['Key']}]" : "") . "\n";
    }
    $count = $pdo->query("SELECT COUNT(*) FROM pincodes")->fetchColumn();
    echo "  Row count: $count\n";
}

// Check the pincode lookup API
$stmt = $pdo->query("SELECT * FROM pincodes LIMIT 5");
echo "\nSample pincodes:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . json_encode($row) . "\n";
}