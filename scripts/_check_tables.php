<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = ['retained_earnings','salary_tracker','mlm_advanced_analytics','rera_requests','packages'];
foreach ($tables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '$t'");
    echo $t . ': ' . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . PHP_EOL;
}
