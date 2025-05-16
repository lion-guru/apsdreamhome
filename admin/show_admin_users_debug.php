<?php
require_once __DIR__ . '/../includes/db_connection.php';
$con = getDbConnection();
$result = $con->query('SELECT id, auser, apass, status FROM admin');
echo "<pre>";
while($row = $result->fetch_assoc()) {
    echo 'ID: ' . $row['id'] . ' | Username: ' . $row['auser'] . ' | Password Hash: ' . $row['apass'] . ' | Status: ' . $row['status'] . "\n";
}
echo "</pre>";
$con->close();
?>
