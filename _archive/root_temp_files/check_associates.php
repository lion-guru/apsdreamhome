<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

echo "=== Associates Level Check ===\n\n";
$assoc = $pdo->query("SELECT id, user_id, level FROM associates LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($assoc as $a) {
    echo "ID: {$a['id']}, User ID: {$a['user_id']}, Level: '{$a['level']}'\n";
}?>