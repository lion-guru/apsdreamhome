<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("DESCRIBE bookings");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Table: bookings\n";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
