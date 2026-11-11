<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$map = [
    '/admin/call-logs' => '/admin/ai-calling/call-logs',
    '/admin/telecalling' => '/admin/telecalling/dashboard',
    '/admin/export/excel/receivables-aging' => '/admin/export/excel/receivables-aging', // already correct (false positive)
];

// Actually just fix the 2 broken ones
$fixes = [
    '/admin/call-logs' => '/admin/ai-calling/call-logs',
    '/admin/telecalling' => '/admin/telecalling/dashboard',
];

$check = $pdo->prepare("SELECT id, url FROM admin_menu_items WHERE url = ?");
$upd = $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE id = ?");

foreach ($fixes as $old => $new) {
    $check->execute([$old]);
    $rows = $check->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $upd->execute([$new, $r['id']]);
        echo "FIXED #{$r['id']}: {$old} -> {$new}\n";
    }
}

// sidebar cache bust
\App\Core\Cache::delete('admin_sidebar_all');
echo "cache busted\n";
echo "DONE\n";