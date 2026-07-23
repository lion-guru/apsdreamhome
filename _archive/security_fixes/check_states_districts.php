<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Check states table
echo "=== STATES TABLE ===\n";
$stmt = $pdo->query("DESCRIBE states");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})" . ($row['Key'] ? " [{$row['Key']}]" : "") . "\n";
}
$count = $pdo->query("SELECT COUNT(*) FROM states")->fetchColumn();
echo "  Row count: $count\n";

$stmt = $pdo->query("SELECT id, name, code FROM states LIMIT 10");
echo "  Sample data:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "    " . json_encode($row) . "\n";
}

// Check districts table
echo "\n=== DISTRICTS TABLE ===\n";
$stmt = $pdo->query("DESCRIBE districts");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})" . ($row['Key'] ? " [{$row['Key']}]" : "") . "\n";
}
$count = $pdo->query("SELECT COUNT(*) FROM districts")->fetchColumn();
echo "  Row count: $count\n";

$stmt = $pdo->query("SELECT id, name, state_id FROM districts LIMIT 10");
echo "  Sample data:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "    " . json_encode($row) . "\n";
}