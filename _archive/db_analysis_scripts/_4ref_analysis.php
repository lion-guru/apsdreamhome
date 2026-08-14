<?php
/**
 * Find 4+ ref tables for wrap+drop
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$skip = ['plots', 'users', 'bookings', 'properties', 'user_properties', 'leads', 'colonies',
         'admin_menu_items', 'districts', 'states', 'countries', 'cities', 'pincodes',
         'settings', 'banks', 'bank_branches', 'projects', 'roles', 'permissions',
         'invoices', 'expenses', 'transactions', 'commissions', 'payouts',
         'documents', 'notifications', 'notification_settings', 'notification_queue',
         'email_queue', 'sms_queue', 'campaigns', 'visits', 'site_visits',
         'plot_master', 'inventory_plots', 'role_permissions', 'points_rules',
         'tier_benefits', 'wallet_configuration', 'notification_templates', 'error_logs',
         'comparison_criteria', 'lead_scoring_history', 'lead_scoring_rules'];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $allFiles[$path] = file_get_contents($f->getPathname(), FILE_IGNORE_NEW_LINES);
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (in_array($t, $skip)) continue;
    $codeRef = 0;
    $refFiles = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m > 0) { $codeRef += $m; $refFiles[] = basename($path); }
    }
    if ($codeRef < 4 || $codeRef > 8) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo sprintf("%-40s %5d refs %5d rows  %s\n", $t, $codeRef, $rows, implode(', ', array_slice($refFiles, 0, 5)));
}?>