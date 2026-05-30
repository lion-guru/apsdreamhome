<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role IN ('admin','super_admin') OR name LIKE '%admin%'");
    $rows = $stmt->fetchAll();
    echo "Admin users found: " . count($rows) . "\n";
    foreach ($rows as $row) {
        echo implode(' | ', $row) . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
