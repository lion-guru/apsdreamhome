<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $menus = [
        ['section' => 'operations', 'icon' => 'fa-tachometer-alt', 'name' => 'Backoffice Dashboard', 'url' => '/admin/backoffice', 'order_index' => 5, 'parent_id' => 0],
        ['section' => 'operations', 'icon' => 'fa-calendar-check', 'name' => 'Attendance', 'url' => '/admin/backoffice/attendance', 'order_index' => 10, 'parent_id' => 0],
        ['section' => 'operations', 'icon' => 'fa-file-invoice-dollar', 'name' => 'Payslips', 'url' => '/admin/backoffice/payslips', 'order_index' => 15, 'parent_id' => 0],
        ['section' => 'operations', 'icon' => 'fa-chart-line', 'name' => 'Lead Pipeline', 'url' => '/admin/backoffice/leads', 'order_index' => 20, 'parent_id' => 0],
        ['section' => 'operations', 'icon' => 'fa-clipboard-list', 'name' => 'Operations Log', 'url' => '/admin/backoffice/operations', 'order_index' => 25, 'parent_id' => 0],
        ['section' => 'operations', 'icon' => 'fa-file-alt', 'name' => 'Reports', 'url' => '/admin/backoffice/reports', 'order_index' => 30, 'parent_id' => 0],
    ];

    $stmt = $pdo->prepare('INSERT INTO admin_menu_items (section, icon, name, url, order_index, parent_id, is_active, permission_key) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');

    $inserted = 0;
    foreach ($menus as $m) {
        $stmt->execute([$m['section'], $m['icon'], $m['name'], $m['url'], $m['order_index'], $m['parent_id'], 'admin']);
        $inserted++;
    }

    echo "Inserted $inserted Module 5 menu items.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}?>