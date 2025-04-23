<?php
// Simple Database Auto-Backup Script (demo)
// Usage: schedule this script via cron/task scheduler for auto-backups
$backup_dir = __DIR__ . '/../backups';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);
$filename = $backup_dir . '/apsdreamhomes_' . date('Ymd_His') . '.sql';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'apsdreamhomes';
$cmd = "mysqldump -h $db_host -u $db_user --password=$db_pass $db_name > $filename";
system($cmd);
echo "Backup created: $filename\n";
