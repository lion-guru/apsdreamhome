<?php
$config = include 'config/database.php';
$dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'];
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Find broken sidebar URLs
$broken = [
    '/admin/mlm/rank-promotion' => '/admin/mlm/associate-ranks',
    '/admin/mlm/commission-plans' => '/admin/commission-plans',
    '/admin/security/scorecard' => '/admin/compliance-scorecard',
    '/admin/ad-manager' => '/admin/ads',
];

foreach ($broken as $old => $new) {
    $r = $pdo->prepare("SELECT id, name, url FROM admin_menu_items WHERE url = ?");
    $r->execute([$old]);
    $rows = $r->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            echo "FIXING: [{$row['id']}] {$row['name']}: {$old} -> {$new}" . PHP_EOL;
            $u = $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE id = ?");
            $u->execute([$new, $row['id']]);
        }
    } else {
        echo "NOT FOUND: $old" . PHP_EOL;
    }
}

// Also check for any other broken URLs
echo PHP_EOL . "--- All sidebar items with /admin/ URLs ---" . PHP_EOL;
$r = $pdo->query("SELECT id, name, url FROM admin_menu_items WHERE url LIKE '/admin/%' ORDER BY url");
foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "[{$row['id']}] {$row['name']}: {$row['url']}" . PHP_EOL;
}
