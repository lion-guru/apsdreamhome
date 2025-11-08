<?php
require_once __DIR__ . '/../includes/db_connection.php';
$con = getDbConnection();
$result = $con->query('SELECT auser, apass, status FROM admin');
echo "<pre>";
while($row = $result->fetch_assoc()) {
    echo htmlspecialchars($row['auser']) . ' | ' . htmlspecialchars($row['apass']) . ' | ' . htmlspecialchars($row['status']) . "\n";
}
echo "</pre>";
$con->close();
?>
