<?php

/**
 * Daily Database Backup Cron Job
 * Creates a SQL dump and notifies admins on failure
 */

require_once __DIR__ . '/../../app/core/App.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

// Ensure autoloader and environment are initialized
require_once __DIR__ . '/../../app/core/autoload.php';

$db = \App\Core\App::database();
$emailService = new EmailService();
$nm = new NotificationManager($db->getConnection(), $emailService);

$backupDir = __DIR__ . '/../backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
    // Create .htaccess to prevent public access
    file_put_contents($backupDir . '.htaccess', "Order Deny,Allow\nDeny from all");
}

$date = date('Ymd_His');
$filename = "apsdreamhome_backup_{$date}.sql";
$fullPath = $backupDir . $filename;

// Database credentials from environment via ORM/Config
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbuser = getenv('DB_USER') ?: 'root';
$dbpass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'apsdreamhome';
$dbport = getenv('DB_PORT') ?: '3306';

// Find mysqldump executable
$mysqldumpPath = 'mysqldump'; // Default to system path
$commonPaths = [
    'C:\xampp\mysql\bin\mysqldump.exe',
    'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
    'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysqldump.exe',
    '/usr/bin/mysqldump',
    '/usr/local/bin/mysqldump'
];

foreach ($commonPaths as $path) {
    if (file_exists($path)) {
        $mysqldumpPath = '"' . $path . '"';
        break;
    }
}

// Prepare command
$cmd = "{$mysqldumpPath} -h{$dbhost} -P{$dbport} -u{$dbuser} ";
if ($dbpass !== '') $cmd .= "-p" . escapeshellarg($dbpass) . " ";
$cmd .= escapeshellarg($dbname) . " > " . escapeshellarg($fullPath);

try {
    // Execute backup
    system($cmd, $retval);

    if ($retval === 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Database backup successful: $filename\n";

        // Cleanup old backups (older than 30 days)
        $files = glob($backupDir . "*.sql");
        $now = time();
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 30) {
                    unlink($file);
                }
            }
        }
    } else {
        throw new Exception("mysqldump failed with exit code $retval");
    }
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    error_log("Database backup failed: " . $errorMsg);
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: $errorMsg\n";

    // Notify Admins of failure
    $admin_emails = ['techguruabhay@gmail.com'];
    foreach ($admin_emails as $email) {
        $admin_user = $db->fetch("SELECT uid FROM user WHERE uemail = :email LIMIT 1", ['email' => $email]);
        if ($admin_user) {
            $nm->send([
                'user_id' => $admin_user['uid'] ?? 1,
                'email' => $email,
                'template' => 'SYSTEM_ALERT',
                'data' => [
                    'subject' => 'Database Backup Failed',
                    'details' => $errorMsg,
                    'time' => date('Y-m-d H:i:s')
                ],
                'channels' => ['db', 'email']
            ]);
        }
    }
}
