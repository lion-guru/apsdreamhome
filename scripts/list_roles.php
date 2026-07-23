<?php
require __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
$roles = $db->fetchAll('SELECT DISTINCT role, COUNT(*) as cnt FROM users WHERE role IS NOT NULL GROUP BY role ORDER BY cnt DESC');
foreach ($roles as $r) {
    echo $r['role'] . ' -> ' . $r['cnt'] . " users\n";
}