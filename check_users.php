<?php
require_once __DIR__ . '/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'] . ' - ' . ($c['Default'] ?? 'NO DEFAULT') . PHP_EOL;