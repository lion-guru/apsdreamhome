<?php
/**
 * Database Backup - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

// Check if user has admin privileges for backup
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$date = date("Ymd_His");
$backup_file = __DIR__ . "/../backups/db_backup_$date.sql";

// Create backups directory if it doesn't exist
if (!is_dir(__DIR__ . "/../backups")) {
    mkdir(__DIR__ . "/../backups", 0755, true);
}

// Get database configuration from constants
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;

// Build mysqldump command
$cmd = "mysqldump -u" . $db_user . " -p" . $db_pass . " " . $db_name . " > " . $backup_file;

// Execute backup command
exec($cmd, $output, $result);

if ($result === 0) {
    $msg = "Backup successful: db_backup_$date.sql";
    $msg_type = "success";
    
    // Log backup activity
    $user_id = getAuthUserId();
    log_admin_activity($user_id, "Database Backup", "Created database backup: db_backup_$date.sql");
} else {
    $msg = "Backup failed. Please check database configuration and permissions.";
    $msg_type = "danger";
    
    // Log backup failure
    $user_id = getAuthUserId();
    log_admin_activity($user_id, "Database Backup Failed", "Failed to create database backup");
}
?>
