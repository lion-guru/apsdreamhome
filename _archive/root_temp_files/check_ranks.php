<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

echo "=== Current Ranks in Database ===\n\n";
$rows = $pdo->query("DESCRIBE mlm_rank_benefits")->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in table:\n";
foreach ($rows as $r) {
    echo "  - " . $r['Field'] . " (" . $r['Type'] . ")\n";
}

echo "\n=== Rank Data ===\n";
$ranks = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($ranks as $r) {
    echo "Rank: " . ($r['rank_name'] ?? 'N/A') . "\n";
    foreach ($r as $k => $v) {
        if ($k != 'id') echo "  $k: $v\n";
    }
    echo "\n";
}

echo "\n=== Associates with Ranks ===\n\n";
$assoc = $pdo->query("SELECT u.name, a.level, mp.current_level FROM users u LEFT JOIN associates a ON u.id = a.user_id LEFT JOIN mlm_profiles mp ON u.id = mp.user_id WHERE a.level IS NOT NULL LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

foreach ($assoc as $a) {
    echo sprintf("%s - associates.level: %s, mlm_profiles.current_level: %s\n", $a['name'], $a['level'], $a['current_level']);
}?>