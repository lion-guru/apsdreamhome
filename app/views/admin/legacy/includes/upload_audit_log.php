<?php
// Simple function to log uploads and notifications
function log_upload_event($event_type, $entity_id, $entity_table, $file_name, $drive_id, $uploader, $slack_status, $telegram_status) {
    $db = \App\Core\App::database();
    $db->execute("INSERT INTO upload_audit_log (event_type, entity_id, entity_table, file_name, drive_file_id, uploader, slack_status, telegram_status) VALUES (:event_type, :entity_id, :entity_table, :file_name, :drive_id, :uploader, :slack_status, :telegram_status)", 
                [
                    'event_type' => $event_type,
                    'entity_id' => $entity_id,
                    'entity_table' => $entity_table,
                    'file_name' => $file_name,
                    'drive_id' => $drive_id,
                    'uploader' => $uploader,
                    'slack_status' => $slack_status,
                    'telegram_status' => $telegram_status
                ]);
}
?>


