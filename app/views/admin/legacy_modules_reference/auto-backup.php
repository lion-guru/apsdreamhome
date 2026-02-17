<?php
// Database Auto-Backup Script
// Usage: schedule this script via cron/task scheduler for auto-backups

require_once __DIR__ . '/../includes/config/DatabaseConfig.php';

// Initialize configuration
DatabaseConfig::init();

$backup_dir = __DIR__ . '/../backups';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'apsdreamhomes';

$filename = $backup_dir . '/' . $db_name . '_' . date('Ymd_His') . '.sql';

// Construct mysqldump command
// Note: On Windows, ensure mysqldump is in the PATH
$password_param = $db_pass ? "--password=$db_pass" : "";
$cmd = "mysqldump -h $db_host -u $db_user $password_param $db_name > \"$filename\"";

// Execute backup (only if not in a web context for safety, or with proper authorization)
if (php_sapi_name() === 'cli') {
    system($cmd, $retval);
    if ($retval === 0) {
        echo "Backup created successfully: $filename\n";
    } else {
        echo "Backup failed with error code: $retval\n";
    }
} else {
    echo "This script must be run from the command line.\n";
}

