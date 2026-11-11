<?php
require_once __DIR__ . '/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$cols = $pdo->query("DESCRIBE bookings")->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo "  {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " DEFAULT: " . ($c['Default'] ?? 'NULL') . "\n";
}