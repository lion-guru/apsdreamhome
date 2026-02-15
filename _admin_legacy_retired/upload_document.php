<?php
session_start();
include 'config.php';
require_permission('upload_document');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $owner_type = $_POST['owner_type'];
    $owner_id = $_POST['owner_id'];
    $doc_type = $_POST['doc_type'];
    $uploaded_by = $_SESSION['auser'];
    $filename = basename($_FILES['document']['name']);
    $target_dir = '../uploads/documents/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . time() . '_' . $filename;
    if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO documents (owner_type, owner_id, doc_type, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sissi', $owner_type, $owner_id, $doc_type, $target_file, $uploaded_by);
        if ($stmt->execute()) {
            require_once __DIR__ . '/../includes/functions/notification_util.php';
            addNotification($conn, 'Document', 'Document uploaded: ' . $doc_type, $uploaded_by);
            // Auto-upload to Google Drive and save file ID
            require_once __DIR__ . '/includes/integration_helpers.php';
            $driveId = upload_to_google_drive_and_save_id($target_file, 'documents', 'id', $stmt->insert_id, 'drive_file_id');
            if ($driveId) {
                $msg = 'Document uploaded successfully and sent to Google Drive.';
            } else {
                $msg = 'Document uploaded, but Google Drive upload failed.';
            }
            // Slack notification
            require_once __DIR__ . '/includes/integration_helpers.php';
            $driveLink = $driveId ? "https://drive.google.com/file/d/$driveId/view" : '';
            $slackMsg = "📄 *New Document Uploaded*\n" .
                "Type: $doc_type\n" .
                "Owner: $owner_type #$owner_id\n" .
                "By: $uploaded_by\n" .
                ($driveLink ? "[View on Google Drive]($driveLink)" : '');
            send_slack_notification($slackMsg);
            send_telegram_notification($slackMsg);
            require_once __DIR__ . '/includes/upload_audit_log.php';
            $slack_status = 'sent'; // You may want to check/send status from send_slack_notification
            $telegram_status = 'sent'; // You may want to check/send status from send_telegram_notification
            log_upload_event($conn, 'document', $stmt->insert_id, 'documents', $filename, $driveId, $uploaded_by, $slack_status, $telegram_status);
        } else {
            $msg = 'Database error: ' . htmlspecialchars($stmt->error);
        }
    } else {
        $msg = 'File upload failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Upload Document</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Upload Document</h2><?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?><form method="POST" enctype="multipart/form-data"><div class="mb-3"><label>Owner Type</label><select name="owner_type" class="form-control" required><option value="customer">Customer</option><option value="associate">Associate</option><option value="employee">Employee</option><option value="property">Property</option><option value="company">Company</option></select></div><div class="mb-3"><label>Owner ID</label><input type="number" name="owner_id" class="form-control" required></div><div class="mb-3"><label>Document Type</label><input type="text" name="doc_type" class="form-control" required></div><div class="mb-3"><label>Document File</label><input type="file" name="document" class="form-control" required></div><button type="submit" class="btn btn-success">Upload</button></form></div></body></html>
