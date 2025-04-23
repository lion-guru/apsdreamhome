<?php
// Simple admin activity logger
function log_admin_activity($action, $details = '') {
    $log_file = __DIR__ . '/../logs/admin_activity.log';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    $entry = date('Y-m-d H:i:s') . ' | ' . ($_SESSION['admin_logged_in'] ?? 'unknown') . ' | ' . $action . ' | ' . $details . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
}
?>
