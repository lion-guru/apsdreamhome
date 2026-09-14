<?php
require_once 'vendor/autoload.php';
$db = \App\Core\Database::getInstance();
$pdo = $db->getConnection();
$stmt = $pdo->query('DESCRIBE users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}