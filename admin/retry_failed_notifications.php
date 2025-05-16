<?php
// retry_failed_notifications.php: Retry failed Slack/Telegram notifications for uploads
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/integration_helpers.php';
require_once __DIR__ . '/includes/upload_audit_log.php';

// Find failed Slack notifications
$failed = $conn->query("SELECT * FROM upload_audit_log WHERE (slack_status != 'sent' OR slack_status IS NULL OR slack_status = '') OR (telegram_status != 'sent' OR telegram_status IS NULL OR telegram_status = '') ORDER BY created_at DESC LIMIT 50");

while ($row = $failed->fetch_assoc()) {
    $message = "[RETRY] Upload Event: {$row['event_type']}\n" .
        "Entity: {$row['entity_table']} #{$row['entity_id']}\n" .
        "File: {$row['file_name']}\n" .
        ($row['drive_file_id'] ? "Drive: https://drive.google.com/file/d/{$row['drive_file_id']}/view\n" : '') .
        "Uploader: {$row['uploader']}\n" .
        "Date: {$row['created_at']}";
    $slack_status = $row['slack_status'];
    $telegram_status = $row['telegram_status'];
    // Retry Slack
    if ($slack_status !== 'sent') {
        $slack_status = send_slack_notification($message) ? 'sent' : 'fail';
    }
    // Retry Telegram
    if ($telegram_status !== 'sent') {
        $telegram_status = send_telegram_notification($message) ? 'sent' : 'fail';
    }
    // Update audit log
    $stmt = $conn->prepare("UPDATE upload_audit_log SET slack_status=?, telegram_status=? WHERE id=?");
    $stmt->bind_param('ssi', $slack_status, $telegram_status, $row['id']);
    $stmt->execute();
}
echo "Notification retry complete.";
