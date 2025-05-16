<?php
// auto_backup_cron.php: Simple script to back up the database daily (MySQL)
$backupDir = __DIR__ . '/../backups/';
if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);
$date = date('Ymd_His');
$filename = $backupDir . "apsdreamhome_backup_{$date}.sql";
// Update these with your actual DB credentials
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'apsdreamhome';
$cmd = "mysqldump -h$dbhost -u$dbuser ";
if ($dbpass !== '') $cmd .= "-p$dbpass ";
$cmd .= "$dbname > \"$filename\"";
// SECURITY: Removed potentially dangerous code$cmd);
?>

