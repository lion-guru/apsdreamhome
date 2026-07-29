<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$stmt = $pdo->query("SELECT section, COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1 GROUP BY section ORDER BY section");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['section'] . ': ' . $row['cnt'] . " items\n";
}