<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Checking first associate data...\n";
$stmt = $conn->query("SELECT * FROM associates LIMIT 1");
$assoc = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assoc) {
    print_r($assoc);
} else {
    echo "No associates found.\n";
}
