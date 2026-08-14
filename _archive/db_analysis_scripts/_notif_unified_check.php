<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check what notifications_unified has that notifications doesn't
echo "=== notifications_unified columns ===\n";
foreach ($pdo->query("DESCRIBE notifications_unified")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  {$c['Field']} ({$c['Type']})\n";
}

echo "\n=== Sample data ===\n";
foreach ($pdo->query("SELECT * FROM notifications_unified")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  " . json_encode(array_filter($r, fn($v) => $v !== null && $v !== '')) . "\n";
}

// Check code refs
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match('/notifications_unified/', $content)) {
        echo "  Ref: " . basename($f->getPathname()) . "\n";
    }
}?>