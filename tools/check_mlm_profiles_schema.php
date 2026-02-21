<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("DESCRIBE mlm_profiles");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Table: mlm_profiles\n";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
