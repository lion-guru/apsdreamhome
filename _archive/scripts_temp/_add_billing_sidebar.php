<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

$check = $db->query("SELECT id FROM admin_menu_items WHERE url LIKE '/admin/billing%' OR url = '/admin/billing'")->fetchAll(\PDO::FETCH_ASSOC);
if (count($check) > 0) {
    echo 'Billing sidebar items already exist (' . count($check) . ' items)' . PHP_EOL;
    exit;
}

$items = [
    ['name' => 'Billing & Subscriptions', 'url' => '/admin/billing', 'icon' => 'fas fa-credit-card', 'section' => 'saas'],
    ['name' => 'Manage Plans', 'url' => '/admin/billing/plans', 'icon' => 'fas fa-tags', 'section' => 'saas'],
];

// Check what columns exist
$cols = $db->query("SHOW COLUMNS FROM admin_menu_items")->fetchAll(\PDO::FETCH_COLUMN);
echo "Columns: " . implode(', ', $cols) . PHP_EOL;

// Build dynamic insert based on available columns
$validCols = ['name', 'url', 'icon', 'section', 'is_active', 'created_at'];
$availableCols = array_intersect($validCols, $cols);

foreach ($items as $item) {
    $colStr = implode(', ', $availableCols);
    $placeholders = implode(', ', array_fill(0, count($availableCols), '?'));
    $vals = [];
    foreach ($availableCols as $c) {
        if ($c === 'created_at') $vals[] = date('Y-m-d H:i:s');
        elseif ($c === 'is_active') $vals[] = 1;
        else $vals[] = $item[$c] ?? '';
    }
    $db->prepare("INSERT INTO admin_menu_items ($colStr) VALUES ($placeholders)")->execute($vals);
    echo "Added: {$item['name']}" . PHP_EOL;
}
echo 'Done - ' . count($items) . ' billing sidebar items added' . PHP_EOL;
