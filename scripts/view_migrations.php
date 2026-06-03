<?php
/**
 * View recent migrations
 * Usage: php scripts/view_migrations.php [limit]
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$limit = $argv[1] ?? 20;

echo "\n=== MIGRATION HISTORY (last $limit) ===\n\n";
$rows = $pdo->prepare("SELECT * FROM _migrations ORDER BY applied_at DESC LIMIT " . (int)$limit);
$rows->execute();
$rows->setFetchMode(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo sprintf("[%s] %-35s %-12s %s\n",
        $r['applied_at'],
        $r['script_name'],
        $r['category'],
        substr($r['description'] ?? '', 0, 60)
    );
}

$total = $pdo->query("SELECT COUNT(*) FROM _migrations")->fetchColumn();
echo "\nTotal migrations: $total\n";

// Category breakdown
echo "\nBy category:\n";
$cats = $pdo->query("SELECT category, COUNT(*) as n FROM _migrations GROUP BY category ORDER BY n DESC");
foreach ($cats as $c) {
    echo sprintf("  %-15s %d\n", $c['category'], $c['n']);
}
