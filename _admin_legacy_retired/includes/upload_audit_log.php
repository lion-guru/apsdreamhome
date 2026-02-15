<?php
// Simple function to log uploads and notifications
function log_upload_event($conn, $event_type, $entity_id, $entity_table, $file_name, $drive_id, $uploader, $slack_status, $telegram_status) {
    $stmt = $conn->prepare("INSERT INTO upload_audit_log (event_type, entity_id, entity_table, file_name, drive_file_id, uploader, slack_status, telegram_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissssss', $event_type, $entity_id, $entity_table, $file_name, $drive_id, $uploader, $slack_status, $telegram_status);
    $stmt->execute();
}
?>
