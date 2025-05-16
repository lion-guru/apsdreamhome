<?php
// auto_admin_notification_cron.php: Notify admins about important automatic events
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();

// Example: Notify about new auto-converted leads today
$converted = $conn->query("SELECT COUNT(*) as c FROM leads WHERE status = 'Converted' AND DATE(updated_at) = CURDATE() AND notes LIKE '%[Auto] Converted%'")->fetch_assoc()['c'];
if ($converted > 0) {
    $msg = "$converted lead(s) were automatically converted today.";
    $conn->query("INSERT INTO notifications (user_type, message, created_at) VALUES ('admin', '" . $conn->real_escape_string($msg) . "', NOW())");
}

// Example: Notify about new automatic follow-up reminders today
$reminders = $conn->query("SELECT COUNT(*) as c FROM reminders WHERE reminder_type = 'followup' AND DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
if ($reminders > 0) {
    $msg = "$reminders follow-up reminders were generated automatically today.";
    $conn->query("INSERT INTO notifications (user_type, message, created_at) VALUES ('admin', '" . $conn->real_escape_string($msg) . "', NOW())");
}
?>
