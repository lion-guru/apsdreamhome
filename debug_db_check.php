<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/views/layouts/config.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total employees: " . count($employees) . "\n";
print_r($employees);

$stmt = $conn->query("SELECT * FROM admin");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total admins: " . count($admins) . "\n";
print_r($admins);
