<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db_config.php';
$conn = getDbConnection();
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}
echo "<h2>Database Tables and Columns</h2><ul>";
foreach ($tables as $table) {
    echo "<li><b>$table</b><ul>";
    $cols = $conn->query("DESCRIBE `$" . $table . "`");
    while ($col = $cols->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($col['Field']) . " (" . htmlspecialchars($col['Type']) . ")</li>";
    }
    echo "</ul></li>";
}
echo "</ul>";
$conn->close();
?>
