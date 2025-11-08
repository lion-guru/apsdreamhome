<?php
// This script will update ALL admin passwords to a new Argon2 hash of 'Aps@128128'.
require_once __DIR__ . '/../includes/db_connection.php';
$new_password = 'Aps@128128';
$new_hash = password_hash($new_password, PASSWORD_ARGON2ID);

$con = getDbConnection();
if (!$con) {
    die('Database connection failed');
}

$result = $con->query('SELECT id, auser FROM admin');
$updated = 0;
while ($row = $result->fetch_assoc()) {
    $stmt = $con->prepare('UPDATE admin SET apass = ? WHERE id = ?');
    $stmt->bind_param('si', $new_hash, $row['id']);
    if ($stmt->execute()) {
        $updated++;
    }
    $stmt->close();
}
$con->close();
echo "Updated $updated admin passwords to 'Aps@128128' (Argon2). Please change after first login!\n";
?>
