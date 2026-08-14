<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

echo "=== MLM Profiles Current Level Check ===\n\n";
$profiles = $pdo->query("SELECT id, user_id, current_level FROM mlm_profiles LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($profiles as $p) {
    echo "ID: {$p['id']}, User ID: {$p['user_id']}, Current Level: '{$p['current_level']}'\n";
}

echo "\n=== Count by Level ===\n";
$counts = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll(PDO::FETCH_ASSOC);
foreach ($counts as $c) {
    echo "Level: '{$c['current_level']}', Count: {$c['cnt']}\n";
}?>