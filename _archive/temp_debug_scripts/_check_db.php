<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    echo "DB OK\n";
    foreach ($pdo->query('SHOW TABLES LIKE "ab%"') as $r) echo "ab: " . $r[0] . "\n";
    foreach ($pdo->query('SHOW TABLES LIKE "saved%"') as $r) echo "saved: " . $r[0] . "\n";
    $cols = $pdo->query('DESCRIBE ab_experiments')->fetchAll(PDO::FETCH_COLUMN);
    echo "ab_experiments cols: " . implode(', ', $cols) . "\n";
} catch (Exception $e) { echo 'ERR: ' . $e->getMessage(); }
