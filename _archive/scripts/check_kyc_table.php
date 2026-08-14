<?php
require 'C:\xampp\htdocs\apsdreamhome\config\database.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
try {
    $stmt = $pdo->query('DESCRIBE kyc_verification_logs');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo 'Table does not exist: ' . $e->getMessage();
}?>