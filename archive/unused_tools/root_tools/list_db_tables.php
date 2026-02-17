<?php
require_once __DIR__ . '/../includes/db_config.php';

echo "=== DB Tables ===\n";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo "Connection error: " . $conn->connect_error . "\n";
    exit(1);
}
$result = $conn->query('SHOW TABLES');
if (!$result) {
    echo "Query error: " . $conn->error . "\n";
    exit(1);
}
$tables = [];
while ($row = $result->fetch_array()) { $tables[] = $row[0]; }
sort($tables);
echo 'Total tables: ' . count($tables) . "\n";
foreach (array_slice($tables, 0, 50) as $t) { echo " - $t\n"; }
$conn->close();
?>
