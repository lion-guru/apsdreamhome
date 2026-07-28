<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

// Deactivate the Marketing duplicates (CRM versions are primary)
$updates = [
    ['id' => 87, 'name' => 'Campaigns Hub', 'reason' => 'Duplicate of id=13 (CRM Campaigns) at /admin/campaigns'],
    ['id' => 88, 'name' => 'Visits Log', 'reason' => 'Duplicate of id=11 (CRM Site Visits) at /admin/visits'],
];

foreach ($updates as $u) {
    echo "Deactivating id={$u['id']} ({$u['name']}) — {$u['reason']}" . PHP_EOL;
    $db->query("UPDATE admin_menu_items SET is_active = 0 WHERE id = ?", [$u['id']]);
    echo "  => Done" . PHP_EOL;
}

// Verify
echo PHP_EOL . "=== Verification ===" . PHP_EOL;
$remaining = $db->fetchAll("SELECT id, name, url, section FROM admin_menu_items WHERE url IN ('/admin/visits', '/admin/campaigns') AND is_active = 1 ORDER BY url");
foreach ($remaining as $r) {
    echo "ACTIVE: {$r['id']} | {$r['name']} | {$r['url']} | {$r['section']}" . PHP_EOL;
}

$total = $db->fetchAll("SELECT COUNT(*) as cnt FROM admin_menu_items WHERE is_active = 1");
echo PHP_EOL . "Active menu items: " . $total[0]['cnt'] . " (was 253, now " . $total[0]['cnt'] . ")" . PHP_EOL;
