<?php
// auto_admin_notification_cron.php: Notify admins about important automatic events
// require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/includes/config/config.php';
global $con;
$conn = $con;

// Example: Notify about new auto-converted leads today using prepared statement
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM leads WHERE status = 'Converted' AND DATE(updated_at) = CURDATE() AND notes LIKE '%[Auto] Converted%'");
$stmt->execute();
$result = $stmt->get_result();
$converted = $result->fetch_assoc()['c'];
$stmt->close();

if ($converted > 0) {
    $msg = "$converted lead(s) were automatically converted today.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_type, message, created_at) VALUES ('admin', ?, NOW())");
    $stmt->bind_param("s", $msg);
    $stmt->execute();
    $stmt->close();
}

// Example: Notify about new automatic follow-up reminders today using prepared statement
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM reminders WHERE reminder_type = 'followup' AND DATE(created_at) = CURDATE()");
$stmt->execute();
$result = $stmt->get_result();
$reminders = $result->fetch_assoc()['c'];
$stmt->close();

if ($reminders > 0) {
    $msg = "$reminders follow-up reminders were generated automatically today.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_type, message, created_at) VALUES ('admin', ?, NOW())");
    $stmt->bind_param("s", $msg);
    $stmt->execute();
    $stmt->close();
}
?>
