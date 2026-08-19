<?php
$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role IN ('admin', 'super_admin')");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}\n";
}