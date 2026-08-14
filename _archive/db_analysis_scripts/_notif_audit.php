<?php
/**
 * Notification tables audit - find consolidation opportunities
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    'notifications', 'notifications_unified', 'notification_queue',
    'notification_feed', 'notification_templates', 'notification_settings',
    'notification_campaigns', 'user_notification_preferences',
    'payment_notifications', 'mlm_notification_log',
    'email_queue', 'email_templates',
    'sms_queue', 'sms_templates',
    'whatsapp_messages', 'whatsapp_templates', 'whatsapp_campaigns',
];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

foreach ($tables as $t) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t'")->fetchColumn();
    if (!$exists) { echo "[MISSING] $t\n"; continue; }

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $codeRef = 0;
    $refFiles = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m) { $codeRef += $m; $refFiles[] = basename($path); }
    }

    $cols = array_column($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $colStr = implode(', ', $cols);

    echo sprintf("%-35s %3d rows  %2d refs  %s\n", $t, $rows, $codeRef, $colStr);
}?>