<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== audit_log ===\n";
foreach ($pdo->query("DESCRIBE audit_log")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  {$c['Field']} ({$c['Type']})\n";
}
echo "\n=== audit_log_archive ===\n";
foreach ($pdo->query("DESCRIBE audit_log_archive")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  {$c['Field']} ({$c['Type']})\n";
}
echo "\n=== Sample archive data ===\n";
foreach ($pdo->query("SELECT * FROM audit_log_archive LIMIT 1")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}?>