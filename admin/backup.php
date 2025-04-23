<?php
session_start();
require_once '../config.php';
require_role('Admin');
$date = date('Ymd_His');
$backup_file = __DIR__ . "/../backups/db_backup_$date.sql";
if (!is_dir(__DIR__ . '/../backups')) mkdir(__DIR__ . '/../backups');
$cmd = "mysqldump -u{$db_user} -p'{$db_pass}' {$db_name} > $backup_file";
exec($cmd, $output, $result);
if ($result === 0) {
    $msg = "Backup successful: db_backup_$date.sql";
} else {
    $msg = "Backup failed.";
}
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Database Backup</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Database Backup</h2><div class='alert alert-info'><?= $msg ?></div><a href='../backups/db_backup_<?= $date ?>.sql' class='btn btn-success'>Download Latest Backup</a></div></body></html>
